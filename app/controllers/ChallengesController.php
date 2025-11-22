<?php
class ChallengesController extends Controller
{
    private $user_id;

    // ===========================
    // GET /challenges  (list)
    // ===========================
    public function list()
    {
        $this->api->require_method('GET');

        $sql = "
            SELECT 
                c.id,
                c.slug,
                c.title,
                c.description,
                c.difficulty,
                c.xp_reward,
                c.category_id,
                c.solved_count,
                ucs.is_solved AS solved,
                cat.name AS category_name
            FROM challenges c
            LEFT JOIN user_challenge_status ucs
                ON ucs.challenge_id = c.id
                AND ucs.user_id = ?
            LEFT JOIN categories cat ON cat.id = c.category_id
        ";

        $stmt = $this->db->raw($sql, [$this->user_id]);
        $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Transform to match frontend structure
        $transformed = array_map(function($challenge) {
            return [
                'id' => (int)$challenge['id'],
                'slug' => $challenge['slug'],
                'title' => $challenge['title'],
                'difficulty' => $challenge['difficulty'],
                'solved' => (bool)$challenge['solved'],
                'tags' => [$challenge['category_name']], // Using category as tag
                'xp_reward' => (int)$challenge['xp_reward'],
                'time_limit' => '1s', // Default value
                'memory_limit' => '64MB', // Default value
                'total_submissions' => (int)$challenge['solved_count'],
                'accepted_submissions' => (int)$challenge['solved_count'],
                'description' => $challenge['description'] ?? '',
                'examples' => [],
                'hints' => [],
                'testcases' => [],
                'submissions' => []
            ];
        }, $challenges);

        $this->api->respond($transformed);
    }

    // ===========================
    // GET /challenges/{id|slug}
    // ===========================
    public function get($value)
    {
        $this->api->require_method('GET');

        $field = is_numeric($value) ? "c.id" : "c.slug";

        $sql = "
            SELECT 
                c.*,
                cat.name AS category_name,
                ucs.is_solved,
                ucs.solved_at,
                ucs.last_submitted_at,
                ucs.attempts,
                ucs.best_execution_time,
                ucs.best_memory_used
            FROM challenges c
            JOIN categories cat ON cat.id = c.category_id
            LEFT JOIN user_challenge_status ucs ON ucs.challenge_id = c.id AND ucs.user_id = ?
            WHERE $field = ? AND c.is_published = 1
        ";
        
        $stmt = $this->db->raw($sql, [$this->user_id, $value]);
        $challenge = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$challenge) {
            $this->api->respond_error('Challenge not found', 404);
        }

        // Fetch test cases
        $testCaseStmt = $this->db->raw("
            SELECT input, expected_output, is_example 
            FROM challenge_test_cases 
            WHERE challenge_id = ? AND is_visible = 1
            ORDER BY order_index
        ", [$challenge['id']]);
        $testCases = $testCaseStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch hints
        $hintStmt = $this->db->raw("
            SELECT hint_text 
            FROM challenge_hints 
            WHERE challenge_id = ? 
            ORDER BY order_index
        ", [$challenge['id']]);
        $hints = $hintStmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Fetch tags
        $tagStmt = $this->db->raw("
            SELECT tag_name 
            FROM challenge_tags 
            WHERE challenge_id = ?
        ", [$challenge['id']]);
        $tags = $tagStmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Fetch submissions
        $subStmt = $this->db->raw("
            SELECT 
                id, 
                language AS lang, 
                status, 
                execution_time AS time, 
                memory_used AS memory,
                submitted_at AS date
            FROM submissions
            WHERE challenge_id = ? AND user_id = ?
            ORDER BY submitted_at DESC
            LIMIT 10
        ", [$challenge['id'], $this->user_id]);
        $submissions = $subStmt->fetchAll(PDO::FETCH_ASSOC);

        // Separate examples and test cases
        $examples = [];
        $testcases = [];
        foreach ($testCases as $testCase) {
            if ($testCase['is_example']) {
                $examples[] = [
                    'input' => $testCase['input'],
                    'output' => $testCase['expected_output']
                ];
            } else {
                $testcases[] = [
                    'input' => $testCase['input'],
                    'expected_output' => $testCase['expected_output']
                ];
            }
        }

        // Build response
        $response = [
            'id' => (int)$challenge['id'],
            'slug' => $challenge['slug'],
            'title' => $challenge['title'],
            'difficulty' => $challenge['difficulty'],
            'solved' => (bool)$challenge['is_solved'],
            'tags' => !empty($tags) ? $tags : [$challenge['category_name']],
            'xp_reward' => (int)$challenge['xp_reward'],
            'time_limit' => $challenge['time_limit'],
            'memory_limit' => $challenge['memory_limit'],
            'total_submissions' => (int)$challenge['total_submissions'],
            'accepted_submissions' => (int)$challenge['accepted_submissions'],
            'description' => $challenge['description'],
            'examples' => $examples,
            'hints' => $hints,
            'testcases' => $testcases,
            'submissions' => $submissions,
            'user_stats' => [
                'attempts' => (int)$challenge['attempts'],
                'best_execution_time' => $challenge['best_execution_time'],
                'best_memory_used' => $challenge['best_memory_used'],
                'solved_at' => $challenge['solved_at']
            ]
        ];

        $this->api->respond($response);
    }


    // ===========================
    // POST /challenges
    // ===========================
    public function create()
    {
        $this->api->require_method('POST');
        $input = $this->api->body();

        if (
            empty($input['title']) || empty($input['description']) ||
            empty($input['difficulty']) || empty($input['xp_reward']) ||
            empty($input['category_id'])
        ) {
            $this->api->respond_error('Missing required fields', 400);
        }

        // Auto-generate slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['title'])));

        $sql = "
            INSERT INTO challenges 
            (title, slug, description, difficulty, xp_reward, category_id, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->db->raw($sql, [
            $input['title'],
            $slug,
            $input['description'],
            $input['difficulty'],
            (int) $input['xp_reward'],
            (int) $input['category_id'],
            $this->user_id
        ]);

        if ($stmt->rowCount() > 0) {
            $id = $this->db->lastInsertId();
            $this->api->respond(['id' => $id, 'slug' => $slug], 201);
        }

        $this->api->respond_error('Failed to create challenge', 500);
    }

    // ===========================
    // PUT /challenges/{id}
    // ===========================
    public function update($challenge_id)
    {
        $this->api->require_method('PUT');
        $input = $this->api->body();

        $stmt = $this->db->raw('SELECT * FROM challenges WHERE id = ?', [$challenge_id]);
        $challenge = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$challenge) {
            $this->api->respond_error('Challenge not found', 404);
        }

        $set = [];
        $params = [];

        if (isset($input['title'])) {
            $set[] = 'title = ?';
            $params[] = $input['title'];

            // Update slug when title changes
            $newSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['title'])));
            $set[] = 'slug = ?';
            $params[] = $newSlug;
        }
        if (isset($input['description'])) {
            $set[] = 'description = ?';
            $params[] = $input['description'];
        }
        if (isset($input['difficulty'])) {
            $set[] = 'difficulty = ?';
            $params[] = $input['difficulty'];
        }
        if (isset($input['xp_reward'])) {
            $set[] = 'xp_reward = ?';
            $params[] = (int) $input['xp_reward'];
        }
        if (isset($input['category_id'])) {
            $set[] = 'category_id = ?';
            $params[] = (int) $input['category_id'];
        }

        if (empty($set)) {
            $this->api->respond_error('No fields to update', 400);
        }

        $params[] = $challenge_id;

        $sql = "UPDATE challenges SET " . implode(', ', $set) . " WHERE id = ?";
        $stmt = $this->db->raw($sql, $params);

        if ($stmt->rowCount() > 0) {
            $this->api->respond(['message' => 'Challenge updated']);
        } else {
            $this->api->respond_error('No changes made', 400);
        }
    }
}
?>