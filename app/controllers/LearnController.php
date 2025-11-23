<?php

class LearnController extends Controller
{
    // GET /admin/learn/languages
    public function getLanguages()
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                l.*,
                COUNT(les.id) as total_lessons,
                COUNT(DISTINCT ls.id) as total_sections
            FROM 
                languages l
            LEFT JOIN 
                lessons les ON l.id = les.language_id
            LEFT JOIN
                lesson_sections ls ON les.id = ls.lesson_id
            GROUP BY 
                l.id
            ORDER BY 
                l.name ASC
        ";
        
        $stmt = $this->db->raw($sql);
        $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($languages);
    }
    
    // POST /admin/learn/languages
    public function createLanguage()
    {
        // $auth = $this->api->require_method('POST');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        $data = $this->api->body();
        
        // Validate required fields
        if (empty($data['name']) || empty($data['slug'])) {
            $this->api->respond_error('Name and slug are required', 400);
        }
        
        // Check if slug already exists
        $check_slug_stmt = $this->db->raw(
            "SELECT id FROM languages WHERE slug = ?",
            [$data['slug']]
        );
        $existing = $check_slug_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            $this->api->respond_error('Language with this slug already exists', 400);
        }
        
        $insert_stmt = $this->db->raw(
            "INSERT INTO languages (name, slug, description) VALUES (?, ?, ?)",
            [
                trim($data['name']),
                trim($data['slug']),
                $data['description'] ?? null
            ]
        );
        
        $languageId = $this->db->lastInsertId();
        
        if ($languageId) {
            $this->api->respond([
                'success' => true,
                'message' => 'Language created successfully',
                'data' => [
                    'id' => $languageId,
                    'name' => trim($data['name']),
                    'slug' => trim($data['slug']),
                    'description' => $data['description'] ?? null
                ]
            ], 201);
        } else {
            $this->api->respond_error('Failed to create language', 500);
        }
    }
    
    // PUT /admin/learn/languages/{id}
    public function updateLanguage($id)
    {
        // $auth = $this->api->require_method('PUT');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        $data = $this->api->body();
        
        // Check if language exists
        $language_stmt = $this->db->raw(
            "SELECT id FROM languages WHERE id = ?",
            [$id]
        );
        $language = $language_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$language) {
            $this->api->respond_error('Language not found', 404);
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [];
        
        if (isset($data['name'])) {
            $updateFields[] = "name = ?";
            $params[] = trim($data['name']);
        }
        if (isset($data['slug'])) {
            $updateFields[] = "slug = ?";
            $params[] = trim($data['slug']);
        }
        if (isset($data['description'])) {
            $updateFields[] = "description = ?";
            $params[] = $data['description'];
        }
        
        // Check if new slug conflicts with other languages
        if (isset($data['slug'])) {
            $existing_stmt = $this->db->raw(
                "SELECT id FROM languages WHERE slug = ? AND id != ?",
                [$data['slug'], $id]
            );
            $existing = $existing_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                $this->api->respond_error('Another language with this slug already exists', 400);
            }
        }
        
        if (!empty($updateFields)) {
            $params[] = $id; // For WHERE clause
            $sql = "UPDATE languages SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $update_stmt = $this->db->raw($sql, $params);
            
            $this->api->respond([
                'success' => true,
                'message' => 'Language updated successfully',
                'data' => array_merge(['id' => $id], $data)
            ]);
        } else {
            $this->api->respond_error('No fields to update', 400);
        }
    }
    
    // DELETE /admin/learn/languages/{id}
    public function deleteLanguage($id)
    {
        // $auth = $this->api->require_method('DELETE');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        // Check if language exists
        $language_stmt = $this->db->raw(
            "SELECT id FROM languages WHERE id = ?",
            [$id]
        );
        $language = $language_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$language) {
            $this->api->respond_error('Language not found', 404);
        }
        
        // Check if language has lessons
        $lessons_stmt = $this->db->raw(
            "SELECT id FROM lessons WHERE language_id = ?",
            [$id]
        );
        $lessons = $lessons_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($lessons)) {
            $this->api->respond_error('Cannot delete language that has lessons. Please delete or reassign the lessons first.', 400);
        }
        
        $delete_stmt = $this->db->raw(
            "DELETE FROM languages WHERE id = ?",
            [$id]
        );
        
        $this->api->respond([
            'success' => true,
            'message' => 'Language deleted successfully'
        ]);
    }
    
    // GET /admin/learn/lessons
    public function getLessons()
    {
        $this->api->require_method('GET');
        
        $language_id = $_GET['language_id'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($language_id)) {
            $where .= " AND les.language_id = ?";
            $params[] = $language_id;
        }
        
        $sql = "
            SELECT 
                les.*,
                l.name as language_name,
                l.slug as language_slug,
                COUNT(ls.id) as total_sections
            FROM 
                lessons les
            JOIN 
                languages l ON les.language_id = l.id
            LEFT JOIN 
                lesson_sections ls ON les.id = ls.lesson_id
            {$where}
            GROUP BY 
                les.id
            ORDER BY 
                les.order_index ASC, les.id ASC
        ";
        
        $stmt = $this->db->raw($sql, $params);
        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($lessons);
    }
    
    // GET /admin/learn/lessons/{id}
    public function getLesson($id)
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                les.*,
                l.name as language_name,
                l.slug as language_slug
            FROM 
                lessons les
            JOIN 
                languages l ON les.language_id = l.id
            WHERE 
                les.id = ?
        ";
        
        $stmt = $this->db->raw($sql, [$id]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$lesson) {
            $this->api->respond_error('Lesson not found', 404);
        }
        
        // Get lesson sections
        $sections_stmt = $this->db->raw(
            "SELECT * FROM lesson_sections WHERE lesson_id = ? ORDER BY order_index ASC, id ASC",
            [$id]
        );
        $lesson['sections'] = $sections_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($lesson);
    }
    
    // POST /admin/learn/lessons
    public function createLesson()
    {
        // $auth = $this->api->require_method('POST');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        $data = $this->api->body();
        
        // Validate required fields
        if (empty($data['language_id']) || empty($data['title']) || empty($data['slug'])) {
            $this->api->respond_error('Language, title, and slug are required', 400);
        }
        
        // Check if language exists
        $language_stmt = $this->db->raw(
            "SELECT id FROM languages WHERE id = ?",
            [$data['language_id']]
        );
        $language = $language_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$language) {
            $this->api->respond_error('Language not found', 404);
        }
        
        // Check if slug already exists for this language
        $existing_stmt = $this->db->raw(
            "SELECT id FROM lessons WHERE language_id = ? AND slug = ?",
            [$data['language_id'], $data['slug']]
        );
        $existing = $existing_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            $this->api->respond_error('Lesson with this slug already exists for this language', 400);
        }
        
        $insert_stmt = $this->db->raw(
            "INSERT INTO lessons (language_id, title, slug, description, content, order_index) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['language_id'],
                trim($data['title']),
                trim($data['slug']),
                $data['description'] ?? null,
                $data['content'] ?? '',
                intval($data['order_index'] ?? 0)
            ]
        );
        
        $lessonId = $this->db->lastInsertId();
        
        if ($lessonId) {
            $this->api->respond([
                'success' => true,
                'message' => 'Lesson created successfully',
                'data' => [
                    'id' => $lessonId,
                    'language_id' => $data['language_id'],
                    'title' => trim($data['title']),
                    'slug' => trim($data['slug']),
                    'description' => $data['description'] ?? null,
                    'content' => $data['content'] ?? '',
                    'order_index' => intval($data['order_index'] ?? 0)
                ]
            ], 201);
        } else {
            $this->api->respond_error('Failed to create lesson', 500);
        }
    }
    
    // PUT /admin/learn/lessons/{id}
    public function updateLesson($id)
    {
        // $auth = $this->api->require_method('PUT');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        $data = $this->api->body();
        
        // Check if lesson exists
        $lesson_stmt = $this->db->raw(
            "SELECT * FROM lessons WHERE id = ?",
            [$id]
        );
        $lesson = $lesson_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$lesson) {
            $this->api->respond_error('Lesson not found', 404);
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [];
        
        if (isset($data['title'])) {
            $updateFields[] = "title = ?";
            $params[] = trim($data['title']);
        }
        if (isset($data['slug'])) {
            $updateFields[] = "slug = ?";
            $params[] = trim($data['slug']);
        }
        if (isset($data['description'])) {
            $updateFields[] = "description = ?";
            $params[] = $data['description'];
        }
        if (isset($data['content'])) {
            $updateFields[] = "content = ?";
            $params[] = $data['content'];
        }
        if (isset($data['order_index'])) {
            $updateFields[] = "order_index = ?";
            $params[] = intval($data['order_index']);
        }
        
        // Check if new slug conflicts with other lessons in the same language
        if (isset($data['slug'])) {
            $language_id = $data['language_id'] ?? $lesson['language_id'];
            $existing_stmt = $this->db->raw(
                "SELECT id FROM lessons WHERE language_id = ? AND slug = ? AND id != ?",
                [$language_id, $data['slug'], $id]
            );
            $existing = $existing_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                $this->api->respond_error('Another lesson with this slug already exists in this language', 400);
            }
        }
        
        if (!empty($updateFields)) {
            $params[] = $id; // For WHERE clause
            $sql = "UPDATE lessons SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $update_stmt = $this->db->raw($sql, $params);
            
            $this->api->respond([
                'success' => true,
                'message' => 'Lesson updated successfully',
                'data' => array_merge(['id' => $id], $data)
            ]);
        } else {
            $this->api->respond_error('No fields to update', 400);
        }
    }
    
    // DELETE /admin/learn/lessons/{id}
    public function deleteLesson($id)
    {
        // $auth = $this->api->require_method('DELETE');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        // Check if lesson exists
        $lesson_stmt = $this->db->raw(
            "SELECT id FROM lessons WHERE id = ?",
            [$id]
        );
        $lesson = $lesson_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$lesson) {
            $this->api->respond_error('Lesson not found', 404);
        }
        
        // Delete associated sections first
        $this->db->raw(
            "DELETE FROM lesson_sections WHERE lesson_id = ?",
            [$id]
        );
        
        // Delete lesson
        $delete_stmt = $this->db->raw(
            "DELETE FROM lessons WHERE id = ?",
            [$id]
        );
        
        $this->api->respond([
            'success' => true,
            'message' => 'Lesson and its sections deleted successfully'
        ]);
    }
    
    // GET /admin/learn/sections
    public function getSections()
    {
        $this->api->require_method('GET');
        
        $lesson_id = $_GET['lesson_id'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($lesson_id)) {
            $where .= " AND ls.lesson_id = ?";
            $params[] = $lesson_id;
        }
        
        $sql = "
            SELECT 
                ls.*,
                les.title as lesson_title,
                l.name as language_name
            FROM 
                lesson_sections ls
            JOIN 
                lessons les ON ls.lesson_id = les.id
            JOIN 
                languages l ON les.language_id = l.id
            {$where}
            ORDER BY 
                ls.order_index ASC, ls.id ASC
        ";
        
        $stmt = $this->db->raw($sql, $params);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($sections);
    }
    
    // POST /admin/learn/sections
    public function createSection()
    {
        // $auth = $this->api->require_method('POST');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        $data = $this->api->body();
        
        // Validate required fields
        if (empty($data['lesson_id']) || empty($data['subtitle'])) {
            $this->api->respond_error('Lesson and subtitle are required', 400);
        }
        
        // Check if lesson exists
        $lesson_stmt = $this->db->raw(
            "SELECT id FROM lessons WHERE id = ?",
            [$data['lesson_id']]
        );
        $lesson = $lesson_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$lesson) {
            $this->api->respond_error('Lesson not found', 404);
        }
        
        $insert_stmt = $this->db->raw(
            "INSERT INTO lesson_sections (lesson_id, subtitle, content, code_example, example_id, order_index) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['lesson_id'],
                trim($data['subtitle']),
                $data['content'] ?? null,
                $data['code_example'] ?? null,
                $data['example_id'] ?? null,
                intval($data['order_index'] ?? 0)
            ]
        );
        
        $sectionId = $this->db->lastInsertId();
        
        if ($sectionId) {
            $this->api->respond([
                'success' => true,
                'message' => 'Section created successfully',
                'data' => [
                    'id' => $sectionId,
                    'lesson_id' => $data['lesson_id'],
                    'subtitle' => trim($data['subtitle']),
                    'content' => $data['content'] ?? null,
                    'code_example' => $data['code_example'] ?? null,
                    'example_id' => $data['example_id'] ?? null,
                    'order_index' => intval($data['order_index'] ?? 0)
                ]
            ], 201);
        } else {
            $this->api->respond_error('Failed to create section', 500);
        }
    }
    
    // PUT /admin/learn/sections/{id}
    public function updateSection($id)
    {
        // $auth = $this->api->require_method('PUT');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        $data = $this->api->body();
        
        // Check if section exists
        $section_stmt = $this->db->raw(
            "SELECT id FROM lesson_sections WHERE id = ?",
            [$id]
        );
        $section = $section_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$section) {
            $this->api->respond_error('Section not found', 404);
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [];
        
        if (isset($data['subtitle'])) {
            $updateFields[] = "subtitle = ?";
            $params[] = trim($data['subtitle']);
        }
        if (isset($data['content'])) {
            $updateFields[] = "content = ?";
            $params[] = $data['content'];
        }
        if (isset($data['code_example'])) {
            $updateFields[] = "code_example = ?";
            $params[] = $data['code_example'];
        }
        if (isset($data['example_id'])) {
            $updateFields[] = "example_id = ?";
            $params[] = $data['example_id'];
        }
        if (isset($data['order_index'])) {
            $updateFields[] = "order_index = ?";
            $params[] = intval($data['order_index']);
        }
        
        if (!empty($updateFields)) {
            $params[] = $id; // For WHERE clause
            $sql = "UPDATE lesson_sections SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $update_stmt = $this->db->raw($sql, $params);
            
            $this->api->respond([
                'success' => true,
                'message' => 'Section updated successfully',
                'data' => array_merge(['id' => $id], $data)
            ]);
        } else {
            $this->api->respond_error('No fields to update', 400);
        }
    }
    
    // DELETE /admin/learn/sections/{id}
    public function deleteSection($id)
    {
        // $auth = $this->api->require_method('DELETE');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        // Check if section exists
        $section_stmt = $this->db->raw(
            "SELECT * FROM lesson_sections WHERE id = ?",
            [$id]
        );
        $section = $section_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$section) {
            $this->api->respond_error('Section not found', 404);
        }
        
        // Check if this is the only section in the lesson
        $lessonSections_stmt = $this->db->raw(
            "SELECT COUNT(*) as count FROM lesson_sections WHERE lesson_id = ?",
            [$section['lesson_id']]
        );
        $lessonSections = $lessonSections_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lessonSections['count'] <= 1) {
            $this->api->respond_error('Cannot delete the only section in a lesson. Lessons must have at least one section.', 400);
        }
        
        $delete_stmt = $this->db->raw(
            "DELETE FROM lesson_sections WHERE id = ?",
            [$id]
        );
        
        $this->api->respond([
            'success' => true,
            'message' => 'Section deleted successfully'
        ]);
    }
    
    // GET /admin/learn/learning-paths
    public function getLearningPaths()
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                lp.*,
                COUNT(ulp.user_id) as enrolled_users
            FROM 
                learning_paths lp
            LEFT JOIN 
                user_learning_paths ulp ON lp.id = ulp.path_id
            GROUP BY 
                lp.id
            ORDER BY 
                lp.created_at DESC
        ";
        
        $stmt = $this->db->raw($sql);
        $paths = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($paths);
    }
    
    // POST /admin/learn/learning-paths
    public function createLearningPath()
    {
        // $auth = $this->api->require_method('POST');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        $data = $this->api->body();
        
        if (empty($data['name'])) {
            $this->api->respond_error('Learning path name is required', 400);
        }
        
        $insert_stmt = $this->db->raw(
            "INSERT INTO learning_paths (name, description, total_lessons) VALUES (?, ?, ?)",
            [
                trim($data['name']),
                $data['description'] ?? null,
                intval($data['total_lessons'] ?? 0)
            ]
        );
        
        $pathId = $this->db->lastInsertId();
        
        if ($pathId) {
            $this->api->respond([
                'success' => true,
                'message' => 'Learning path created successfully',
                'data' => [
                    'id' => $pathId,
                    'name' => trim($data['name']),
                    'description' => $data['description'] ?? null,
                    'total_lessons' => intval($data['total_lessons'] ?? 0)
                ]
            ], 201);
        } else {
            $this->api->respond_error('Failed to create learning path', 500);
        }
    }
    
    // GET /admin/learn/analytics/overview
    public function getAnalyticsOverview()
    {
        $this->api->require_method('GET');
        
        // Get total counts
        $totalLanguages_stmt = $this->db->raw("SELECT COUNT(*) as count FROM languages");
        $totalLanguages = $totalLanguages_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $totalLessons_stmt = $this->db->raw("SELECT COUNT(*) as count FROM lessons");
        $totalLessons = $totalLessons_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $totalSections_stmt = $this->db->raw("SELECT COUNT(*) as count FROM lesson_sections");
        $totalSections = $totalSections_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $totalLearningPaths_stmt = $this->db->raw("SELECT COUNT(*) as count FROM learning_paths");
        $totalLearningPaths = $totalLearningPaths_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get lessons per language
        $lessonsPerLanguage_stmt = $this->db->raw("
            SELECT l.name, COUNT(les.id) as lesson_count
            FROM languages l
            LEFT JOIN lessons les ON l.id = les.language_id
            GROUP BY l.id
            ORDER BY lesson_count DESC
        ");
        $lessonsPerLanguage = $lessonsPerLanguage_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get sections with code examples
        $sectionsWithCode_stmt = $this->db->raw("
            SELECT COUNT(*) as count 
            FROM lesson_sections 
            WHERE code_example IS NOT NULL AND code_example != ''
        ");
        $sectionsWithCode = $sectionsWithCode_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get recent activity
        $recentActivity_stmt = $this->db->raw("
            (SELECT 'lesson' as type, title as name, updated_at as date FROM lessons ORDER BY updated_at DESC LIMIT 5)
            UNION ALL
            (SELECT 'section' as type, subtitle as name, updated_at as date FROM lesson_sections ORDER BY updated_at DESC LIMIT 5)
            ORDER BY date DESC
            LIMIT 10
        ");
        $recentActivity = $recentActivity_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond([
            'totals' => [
                'languages' => $totalLanguages,
                'lessons' => $totalLessons,
                'sections' => $totalSections,
                'learningPaths' => $totalLearningPaths,
                'sectionsWithCode' => $sectionsWithCode
            ],
            'lessonsPerLanguage' => $lessonsPerLanguage,
            'recentActivity' => $recentActivity
        ]);
    }
    
    // POST /admin/learn/lessons/{id}/reorder-sections
    public function reorderSections($lesson_id)
    {
        // $auth = $this->api->require_method('POST');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        $data = $this->api->body();
        
        if (!isset($data['sections']) || !is_array($data['sections'])) {
            $this->api->respond_error('Sections array is required', 400);
        }
        
        // Check if lesson exists
        $lesson_stmt = $this->db->raw(
            "SELECT id FROM lessons WHERE id = ?",
            [$lesson_id]
        );
        $lesson = $lesson_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$lesson) {
            $this->api->respond_error('Lesson not found', 404);
        }
        
        // Update section orders
        foreach ($data['sections'] as $index => $sectionData) {
            if (isset($sectionData['id'])) {
                $this->db->raw(
                    "UPDATE lesson_sections SET order_index = ? WHERE id = ? AND lesson_id = ?",
                    [$index, $sectionData['id'], $lesson_id]
                );
            }
        }
        
        $this->api->respond([
            'success' => true,
            'message' => 'Sections reordered successfully'
        ]);
    }
    
    // GET /admin/learn/export/{type}
    public function exportContent($type)
    {
        // $auth = $this->api->require_method('GET');
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }
        
        $allowed_types = ['languages', 'lessons', 'sections'];
        if (!in_array($type, $allowed_types)) {
            $this->api->respond_error('Invalid export type', 400);
        }
        
        $data = [];
        
        switch ($type) {
            case 'languages':
                $stmt = $this->db->raw("SELECT * FROM languages");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'lessons':
                $stmt = $this->db->raw("
                    SELECT les.*, l.name as language_name 
                    FROM lessons les 
                    JOIN languages l ON les.language_id = l.id
                    ORDER BY l.name, les.order_index
                ");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'sections':
                $stmt = $this->db->raw("
                    SELECT ls.*, les.title as lesson_title, l.name as language_name
                    FROM lesson_sections ls
                    JOIN lessons les ON ls.lesson_id = les.id
                    JOIN languages l ON les.language_id = l.id
                    ORDER BY l.name, les.title, ls.order_index
                ");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
        }
        
        $this->api->respond([
            'success' => true,
            'type' => $type,
            'data' => $data,
            'exported_at' => date('Y-m-d H:i:s'),
            'total_records' => count($data)
        ]);
    }
}