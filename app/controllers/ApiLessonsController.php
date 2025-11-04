<?php
class ApiLessonsController extends Controller
{
    /**
     * GET /languages/{langSlug}/lessons
     * Returns a list of all lessons for a given language.
     */
    public function listByLanguage($langSlug)
    {
        $stmt = $this->db->raw(
            "SELECT 
                l.slug AS path,
                l.title,
                l.description,
                l.order_index
             FROM lessons l
             INNER JOIN languages g ON g.id = l.language_id
             WHERE g.slug = ?
             ORDER BY l.order_index ASC, l.id ASC",
            [$langSlug]
        );

        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($lessons) {
            $this->api->respond($lessons);
        } else {
            $this->api->respond_error('No lessons found for this language', 404);
        }
    }

    /**
     * GET /languages/{langSlug}/lessons/{lessonSlug}
     * Returns a single lesson\u2019s full content with sections.
     */
    public function getLesson($langSlug, $lessonSlug)
    {
        // Step 1: Fetch base lesson info
        $stmt = $this->db->raw("
            SELECT 
                l.id,
                l.title,
                l.description
            FROM lessons l
            INNER JOIN languages g ON g.id = l.language_id
            WHERE g.slug = ? AND l.slug = ?
            LIMIT 1
        ", [$langSlug, $lessonSlug]);

        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lesson) {
            return $this->api->respond_error('Lesson not found', 404);
        }

        // Step 2: Fetch its sections from lesson_sections
        $sectionStmt = $this->db->raw("
            SELECT 
                subtitle, 
                content, 
                code_example AS code, 
                example_id AS exampleId
            FROM lesson_sections
            WHERE lesson_id = ?
            ORDER BY order_index ASC
        ", [$lesson['id']]);

        $sections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 3: Combine lesson with its sections
        $lessonData = [
            'title' => $lesson['title'],
            'description' => $lesson['description'],
            'sections' => $sections
        ];

        // Step 4: Send the formatted response
        $this->api->respond($lessonData);
    }

    /**
     * POST /lessons (Admin-only)
     * Creates or updates a lesson (no longer saves content or code).
     */
    public function save()
    {
        $this->api->require_method('POST');
        $this->api->require_auth(['admin']);

        $input = $this->api->body();

        if (empty($input['language_id']) || empty($input['slug']) || empty($input['title'])) {
            return $this->api->respond_error('Missing required fields', 400);
        }

        // Insert or update basic lesson info only
        $this->db->raw(
            "INSERT INTO lessons 
                (language_id, title, description, slug, order_index)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                description = VALUES(description),
                order_index = VALUES(order_index),
                updated_at = CURRENT_TIMESTAMP",
            [
                $input['language_id'],
                $input['title'],
                $input['description'] ?? null,
                $input['slug'],
                $input['order_index'] ?? 0
            ]
        );

        $this->api->respond(['message' => 'Lesson saved successfully']);
    }
}
