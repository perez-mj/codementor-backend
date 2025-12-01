<?php

class AdminChallengeController extends Controller
{
    // GET /admin/challenges
    public function getChallenges()
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                c.*,
                cat.name as category_name,
                u.username as created_by_name,
                COUNT(DISTINCT ct.id) as total_test_cases,
                COUNT(DISTINCT s.id) as total_submissions,
                COUNT(DISTINCT CASE WHEN s.status = 'Passed' THEN s.id END) as accepted_submissions,
                COUNT(DISTINCT ucs.user_id) as total_attempts,
                COUNT(DISTINCT CASE WHEN ucs.is_solved = 1 THEN ucs.user_id END) as solved_count
            FROM 
                challenges c
            LEFT JOIN 
                categories cat ON c.category_id = cat.id
            LEFT JOIN 
                users u ON c.created_by = u.id
            LEFT JOIN 
                challenge_test_cases ct ON c.id = ct.challenge_id
            LEFT JOIN 
                submissions s ON c.id = s.challenge_id
            LEFT JOIN 
                user_challenge_status ucs ON c.id = ucs.challenge_id
            GROUP BY 
                c.id
            ORDER BY 
                c.created_at DESC
        ";
        
        $stmt = $this->db->raw($sql);
        $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($challenges);
    }
    
    // GET /admin/challenges/{id}
    public function getChallenge($id)
    {
        $this->api->require_method('GET');
        
        // Get challenge details
        $challenge_sql = "
            SELECT 
                c.*,
                cat.name as category_name,
                u.username as created_by_name
            FROM 
                challenges c
            LEFT JOIN 
                categories cat ON c.category_id = cat.id
            LEFT JOIN 
                users u ON c.created_by = u.id
            WHERE 
                c.id = ?
        ";
        
        $challenge_stmt = $this->db->raw($challenge_sql, [$id]);
        $challenge = $challenge_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$challenge) {
            $this->api->respond_error('Challenge not found', 404);
        }
        
        // Get test cases
        $test_cases_sql = "
            SELECT * FROM challenge_test_cases 
            WHERE challenge_id = ? 
            ORDER BY order_index ASC
        ";
        $test_cases_stmt = $this->db->raw($test_cases_sql, [$id]);
        $test_cases = $test_cases_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get hints
        $hints_sql = "
            SELECT * FROM challenge_hints 
            WHERE challenge_id = ? 
            ORDER BY order_index ASC
        ";
        $hints_stmt = $this->db->raw($hints_sql, [$id]);
        $hints = $hints_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get tags
        $tags_sql = "
            SELECT tag_name FROM challenge_tags 
            WHERE challenge_id = ?
        ";
        $tags_stmt = $this->db->raw($tags_sql, [$id]);
        $tags = $tags_stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        $challenge['test_cases'] = $test_cases;
        $challenge['hints'] = $hints;
        $challenge['tags'] = $tags;
        
        $this->api->respond($challenge);
    }
    
    // POST /admin/challenges
    public function createChallenge()
    {
        $this->api->require_method('POST');
        
        $data = $this->api->body();
        
        // Validate required fields
        $required = ['title', 'slug', 'description', 'difficulty', 'category_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->api->respond_error("Field '$field' is required", 400);
            }
        }
        
        // Check if slug already exists
        $check_slug_stmt = $this->db->raw(
            "SELECT id FROM challenges WHERE slug = ?",
            [$data['slug']]
        );
        $existing = $check_slug_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            $this->api->respond_error('Challenge with this slug already exists', 400);
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Insert challenge
            $insert_sql = "
                INSERT INTO challenges 
                (title, slug, description, difficulty, xp_reward, time_limit, memory_limit, category_id, created_by, is_published) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            
            $insert_stmt = $this->db->raw($insert_sql, [
                trim($data['title']),
                trim($data['slug']),
                $data['description'],
                $data['difficulty'],
                $data['xp_reward'] ?? 10,
                $data['time_limit'] ?? '1s',
                $data['memory_limit'] ?? '64MB',
                $data['category_id'],
                $data['created_by'] ?? 1, // Default to admin user
                $data['is_published'] ?? 1
            ]);
            
            $challengeId = $this->db->lastInsertId();
            
            // Insert test cases if provided
            if (!empty($data['test_cases']) && is_array($data['test_cases'])) {
                foreach ($data['test_cases'] as $index => $testCase) {
                    $test_case_sql = "
                        INSERT INTO challenge_test_cases 
                        (challenge_id, input, expected_output, is_example, is_visible, order_index) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ";
                    $this->db->raw($test_case_sql, [
                        $challengeId,
                        $testCase['input'],
                        $testCase['expected_output'],
                        $testCase['is_example'] ?? 0,
                        $testCase['is_visible'] ?? 1,
                        $testCase['order_index'] ?? $index + 1
                    ]);
                }
            }
            
            // Insert hints if provided
            if (!empty($data['hints']) && is_array($data['hints'])) {
                foreach ($data['hints'] as $index => $hint) {
                    $hint_sql = "
                        INSERT INTO challenge_hints 
                        (challenge_id, hint_text, order_index) 
                        VALUES (?, ?, ?)
                    ";
                    $this->db->raw($hint_sql, [
                        $challengeId,
                        $hint['hint_text'],
                        $hint['order_index'] ?? $index + 1
                    ]);
                }
            }
            
            // Insert tags if provided
            if (!empty($data['tags']) && is_array($data['tags'])) {
                foreach ($data['tags'] as $tag) {
                    $tag_sql = "
                        INSERT INTO challenge_tags 
                        (challenge_id, tag_name) 
                        VALUES (?, ?)
                    ";
                    $this->db->raw($tag_sql, [
                        $challengeId,
                        trim($tag)
                    ]);
                }
            }
            
            $this->db->commit();
            
            $this->api->respond([
                'success' => true,
                'message' => 'Challenge created successfully',
                'data' => ['id' => $challengeId]
            ], 201);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->api->respond_error('Failed to create challenge: ' . $e->getMessage(), 500);
        }
    }
    
    // PUT /admin/challenges/{id}
    public function updateChallenge($id)
    {
        $this->api->require_method('PUT');
        
        $data = $this->api->body();
        
        // Check if challenge exists
        $challenge_stmt = $this->db->raw(
            "SELECT id FROM challenges WHERE id = ?",
            [$id]
        );
        $challenge = $challenge_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$challenge) {
            $this->api->respond_error('Challenge not found', 404);
        }
        
        // Check if new slug conflicts with other challenges
        if (isset($data['slug'])) {
            $existing_stmt = $this->db->raw(
                "SELECT id FROM challenges WHERE slug = ? AND id != ?",
                [$data['slug'], $id]
            );
            $existing = $existing_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                $this->api->respond_error('Another challenge with this slug already exists', 400);
            }
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [];
        
        $allowedFields = [
            'title', 'slug', 'description', 'difficulty', 'xp_reward', 
            'time_limit', 'memory_limit', 'category_id', 'is_published'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (!empty($updateFields)) {
            $params[] = $id;
            $sql = "UPDATE challenges SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $update_stmt = $this->db->raw($sql, $params);
            
            $this->api->respond([
                'success' => true,
                'message' => 'Challenge updated successfully',
                'data' => array_merge(['id' => $id], $data)
            ]);
        } else {
            $this->api->respond_error('No fields to update', 400);
        }
    }
    
    // DELETE /admin/challenges/{id}
    public function deleteChallenge($id)
    {
        $this->api->require_method('DELETE');
        
        // Check if challenge exists
        $challenge_stmt = $this->db->raw(
            "SELECT id, title FROM challenges WHERE id = ?",
            [$id]
        );
        $challenge = $challenge_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$challenge) {
            $this->api->respond_error('Challenge not found', 404);
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Delete related records first
            $tables = [
                'challenge_test_cases',
                'challenge_hints', 
                'challenge_tags',
                'user_challenge_status',
                'submissions'
            ];
            
            foreach ($tables as $table) {
                $this->db->raw("DELETE FROM $table WHERE challenge_id = ?", [$id]);
            }
            
            // Delete the challenge
            $delete_stmt = $this->db->raw("DELETE FROM challenges WHERE id = ?", [$id]);
            
            $this->db->commit();
            
            $this->api->respond([
                'success' => true,
                'message' => 'Challenge deleted successfully'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->api->respond_error('Failed to delete challenge: ' . $e->getMessage(), 500);
        }
    }
    
    // GET /admin/challenges/{id}/test-cases
    public function getChallengeTestCases($id)
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT * FROM challenge_test_cases 
            WHERE challenge_id = ? 
            ORDER BY order_index ASC
        ";
        
        $stmt = $this->db->raw($sql, [$id]);
        $test_cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($test_cases);
    }
    
    // POST /admin/challenges/{id}/test-cases
    public function createTestCase($id)
    {
        $this->api->require_method('POST');
        
        $data = $this->api->body();
        
        // Validate required fields
        if (empty($data['input']) || empty($data['expected_output'])) {
            $this->api->respond_error('Input and expected_output are required', 400);
        }
        
        // Check if challenge exists
        $challenge_stmt = $this->db->raw(
            "SELECT id FROM challenges WHERE id = ?",
            [$id]
        );
        $challenge = $challenge_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$challenge) {
            $this->api->respond_error('Challenge not found', 404);
        }
        
        $insert_sql = "
            INSERT INTO challenge_test_cases 
            (challenge_id, input, expected_output, is_example, is_visible, order_index) 
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        $insert_stmt = $this->db->raw($insert_sql, [
            $id,
            $data['input'],
            $data['expected_output'],
            $data['is_example'] ?? 0,
            $data['is_visible'] ?? 1,
            $data['order_index'] ?? 0
        ]);
        
        $testCaseId = $this->db->lastInsertId();
        
        $this->api->respond([
            'success' => true,
            'message' => 'Test case created successfully',
            'data' => ['id' => $testCaseId]
        ], 201);
    }
    
    // GET /admin/submissions
    public function getSubmissions()
    {
        $this->api->require_method('GET');
        
        $filters = $this->api->get_query_params();
        
        $sql = "
            SELECT 
                s.*,
                u.username,
                u.email,
                c.title as challenge_title,
                c.difficulty as challenge_difficulty
            FROM 
                submissions s
            JOIN 
                users u ON s.user_id = u.id
            JOIN 
                challenges c ON s.challenge_id = c.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['challenge_id'])) {
            $sql .= " AND s.challenge_id = ?";
            $params[] = $filters['challenge_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND s.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['language'])) {
            $sql .= " AND s.language = ?";
            $params[] = $filters['language'];
        }
        
        $sql .= " ORDER BY s.submitted_at DESC";
        
        $stmt = $this->db->raw($sql, $params);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($submissions);
    }
    
    // GET /admin/analytics/challenges
    public function getChallengeAnalytics()
    {
        $this->api->require_method('GET');
        
        // Overall statistics
        $stats_sql = "
            SELECT 
                COUNT(*) as total_challenges,
                SUM(solved_count) as total_solved,
                SUM(total_submissions) as total_submissions,
                AVG(total_submissions) as avg_submissions_per_challenge,
                COUNT(DISTINCT created_by) as total_creators
            FROM 
                challenges
        ";
        $stats_stmt = $this->db->raw($stats_sql);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Difficulty distribution
        $difficulty_sql = "
            SELECT 
                difficulty,
                COUNT(*) as count,
                AVG(solved_count) as avg_solved,
                AVG(total_submissions) as avg_submissions
            FROM 
                challenges
            GROUP BY 
                difficulty
        ";
        $difficulty_stmt = $this->db->raw($difficulty_sql);
        $difficulty_stats = $difficulty_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Most attempted challenges
        $popular_sql = "
            SELECT 
                c.id,
                c.title,
                c.difficulty,
                c.total_submissions,
                c.solved_count,
                ROUND((c.solved_count / NULLIF(c.total_submissions, 0)) * 100, 2) as success_rate
            FROM 
                challenges c
            WHERE 
                c.total_submissions > 0
            ORDER BY 
                c.total_submissions DESC
            LIMIT 10
        ";
        $popular_stmt = $this->db->raw($popular_sql);
        $popular_challenges = $popular_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Category statistics
        $category_sql = "
            SELECT 
                cat.name as category_name,
                COUNT(c.id) as challenge_count,
                SUM(c.total_submissions) as total_submissions,
                SUM(c.solved_count) as total_solved
            FROM 
                categories cat
            LEFT JOIN 
                challenges c ON cat.id = c.category_id
            GROUP BY 
                cat.id, cat.name
        ";
        $category_stmt = $this->db->raw($category_sql);
        $category_stats = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $analytics = [
            'overview' => $stats,
            'difficulty_distribution' => $difficulty_stats,
            'popular_challenges' => $popular_challenges,
            'category_stats' => $category_stats
        ];
        
        $this->api->respond($analytics);
    }
    
    // GET /admin/categories
    public function getCategories()
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                c.*,
                COUNT(ch.id) as total_challenges
            FROM 
                categories c
            LEFT JOIN 
                challenges ch ON c.id = ch.category_id
            GROUP BY 
                c.id
            ORDER BY 
                c.name ASC
        ";
        
        $stmt = $this->db->raw($sql);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($categories);
    }
}