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
                ucs.is_solved AS solved
            FROM challenges c
            LEFT JOIN user_challenge_status ucs
                ON ucs.challenge_id = c.id
                AND ucs.user_id = ?
        ";

        $stmt = $this->db->raw($sql, [$this->user_id]);
        $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->api->respond($challenges);
    }

    // ===========================
    // GET /challenges/{id|slug}
    // ===========================
    public function get($value)
    {
        $this->api->require_method('GET');

        // Determine if the parameter is numeric ID or slug
        $field = is_numeric($value) ? "c.id" : "c.slug";

        // Fetch challenge with category and user status
        $sql = "
        SELECT 
            c.*,
            cat.name AS category_name,
            ucs.is_solved,
            ucs.solved_at,
            ucs.last_submitted_at
        FROM challenges c
        JOIN categories cat ON cat.id = c.category_id
        LEFT JOIN user_challenge_status ucs
            ON ucs.challenge_id = c.id
            AND ucs.user_id = ?
        WHERE $field = ?
    ";
        $stmt = $this->db->raw($sql, [$this->user_id, $value]);
        $challenge = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$challenge) {
            $this->api->respond_error('Challenge not found', 404);
        }

        // Fetch last 10 submissions by this user for this challenge
        $subStmt = $this->db->raw("
        SELECT 
            id, 
            language AS lang, 
            status, 
            execution_time AS time, 
            submitted_at AS date
        FROM submissions
        WHERE challenge_id = ? AND user_id = ?
        ORDER BY submitted_at DESC
        LIMIT 10
    ", [$challenge['id'], $this->user_id]);

        $challenge['submissions'] = $subStmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the full challenge object with submissions
        $this->api->respond($challenge);
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