<?php
class UserChallengeController extends Controller
{
    private $user_id;

    // ===========================
    // GET /challenges  (list)
    // ===========================
    public function list()
    {
        $this->api->require_method('GET');
        
        // Require JWT authentication
        $auth = $this->api->require_jwt();
        $this->user_id = $auth['sub'];

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
        
        // Require JWT authentication
        $auth = $this->api->require_jwt();
        $this->user_id = $auth['sub'];

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
        
        // Require JWT authentication
        $auth = $this->api->require_jwt();
        $this->user_id = $auth['sub'];

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
            // Use last_id() instead of lastInsertId()
            $id = $this->db->last_id();
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
        
        // Require JWT authentication
        $auth = $this->api->require_jwt();
        $this->user_id = $auth['sub'];

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

    // ===========================
    // POST /challenges/{id}/submit
    // ===========================
    public function submit($challenge_id)
    {
        $this->api->require_method('POST');
        
        // Require JWT authentication
        $auth = $this->api->require_jwt();
        $this->user_id = $auth['sub'];

        $input = $this->api->body();

        // Validate submission
        if (empty($input['code']) || empty($input['language'])) {
            $this->api->respond_error('Code and language are required', 400);
        }

        // Validate challenge exists and is published
        $challenge_stmt = $this->db->raw(
            "SELECT * FROM challenges WHERE id = ? AND is_published = 1", 
            [$challenge_id]
        );
        $challenge = $challenge_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$challenge) {
            $this->api->respond_error('Challenge not found', 404);
        }

        // Start transaction using LavaLust method names
        $this->db->transaction();
        
        try {
            // Create submission
            $submission_sql = "
                INSERT INTO submissions 
                (user_id, challenge_id, code_content, language, status, submitted_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ";
            $stmt = $this->db->raw($submission_sql, [
                $this->user_id, 
                $challenge_id, 
                $input['code'], 
                $input['language']
            ]);
            
            // Use last_id() instead of lastInsertId()
            $submission_id = $this->db->last_id();

            // Update user challenge status (increment attempts)
            $status_sql = "
                INSERT INTO user_challenge_status 
                (user_id, challenge_id, attempts, last_submitted_at) 
                VALUES (?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE 
                attempts = attempts + 1, last_submitted_at = NOW()
            ";
            $this->db->raw($status_sql, [$this->user_id, $challenge_id]);

            // Commit using LavaLust method name
            $this->db->commit();

            // Process the submission asynchronously
            $this->processSubmission($submission_id, $challenge_id, $input['code'], $input['language']);

            $this->api->respond([
                'submission_id' => $submission_id,
                'message' => 'Submission received and being processed',
                'status' => 'pending'
            ]);

        } catch (Exception $e) {
            // Rollback using LavaLust method name
            $this->db->roll_back();
            $this->api->respond_error('Submission failed: ' . $e->getMessage(), 500);
        }
    }

    // ===========================
    // GET /submissions/{id}
    // ===========================
    public function getSubmission($submission_id)
    {
        $this->api->require_method('GET');
        
        // Require JWT authentication
        $auth = $this->api->require_jwt();
        $this->user_id = $auth['sub'];

        $sql = "
            SELECT 
                s.*,
                c.title as challenge_title,
                c.slug as challenge_slug
            FROM submissions s
            JOIN challenges c ON s.challenge_id = c.id
            WHERE s.id = ? AND s.user_id = ?
        ";
        
        $stmt = $this->db->raw($sql, [$submission_id, $this->user_id]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$submission) {
            $this->api->respond_error('Submission not found', 404);
        }

        // Get test results if available
        $test_results_sql = "
            SELECT input, expected_output, actual_output, passed, execution_time
            FROM submission_test_results 
            WHERE submission_id = ?
            ORDER BY id
        ";
        $test_results_stmt = $this->db->raw($test_results_sql, [$submission_id]);
        $test_results = $test_results_stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            'id' => (int)$submission['id'],
            'challenge_id' => (int)$submission['challenge_id'],
            'challenge_title' => $submission['challenge_title'],
            'challenge_slug' => $submission['challenge_slug'],
            'code_content' => $submission['code_content'],
            'language' => $submission['language'],
            'status' => $submission['status'],
            'execution_time' => $submission['execution_time'],
            'memory_used' => $submission['memory_used'],
            'submitted_at' => $submission['submitted_at'],
            'evaluated_at' => $submission['evaluated_at'],
            'test_results' => $test_results
        ];

        $this->api->respond($response);
    }

    // ===========================
    // Process submission (internal method)
    // ===========================
    private function processSubmission($submission_id, $challenge_id, $code, $language)
    {
        try {
            // Update status to running
            $this->db->raw(
                "UPDATE submissions SET status = 'running' WHERE id = ?",
                [$submission_id]
            );

            // Get test cases for this challenge
            $test_case_stmt = $this->db->raw("
                SELECT input, expected_output 
                FROM challenge_test_cases 
                WHERE challenge_id = ? AND is_visible = 0
                ORDER BY order_index
            ", [$challenge_id]);
            $test_cases = $test_case_stmt->fetchAll(PDO::FETCH_ASSOC);

            $all_passed = true;
            $results = [];
            $total_execution_time = 0;
            $max_memory_used = 0;

            foreach ($test_cases as $test_case) {
                // Execute code with test case input
                $execution_result = $this->executeCode($code, $language, $test_case['input']);
                
                $test_passed = (trim($execution_result['output']) === trim($test_case['expected_output']));
                
                $results[] = [
                    'input' => $test_case['input'],
                    'expected_output' => $test_case['expected_output'],
                    'actual_output' => $execution_result['output'],
                    'passed' => $test_passed,
                    'execution_time' => $execution_result['execution_time'],
                    'memory_used' => $execution_result['memory_used']
                ];

                if (!$test_passed) {
                    $all_passed = false;
                }

                $total_execution_time += $execution_result['execution_time'];
                $max_memory_used = max($max_memory_used, $execution_result['memory_used']);

                // Store individual test result
                $this->db->raw("
                    INSERT INTO submission_test_results 
                    (submission_id, input, expected_output, actual_output, passed, execution_time)
                    VALUES (?, ?, ?, ?, ?, ?)
                ", [
                    $submission_id,
                    $test_case['input'],
                    $test_case['expected_output'],
                    $execution_result['output'],
                    $test_passed ? 1 : 0,
                    $execution_result['execution_time']
                ]);
            }

            // Update submission status
            $status = $all_passed ? 'passed' : 'failed';
            $avg_execution_time = count($test_cases) > 0 ? $total_execution_time / count($test_cases) : 0;

            $update_sql = "
                UPDATE submissions 
                SET status = ?, execution_time = ?, memory_used = ?, evaluated_at = NOW()
                WHERE id = ?
            ";
            $this->db->raw($update_sql, [$status, $avg_execution_time, $max_memory_used, $submission_id]);

            // Update challenge statistics
            $this->updateChallengeStats($challenge_id, $all_passed);

            // If passed, update user challenge status and award XP
            if ($all_passed) {
                $this->updateUserChallengeStatus($challenge_id, $avg_execution_time, $max_memory_used);
            }

        } catch (Exception $e) {
            // Mark submission as error
            $this->db->raw(
                "UPDATE submissions SET status = 'error' WHERE id = ?", 
                [$submission_id]
            );
            error_log("Submission processing error: " . $e->getMessage());
        }
    }

    // ===========================
    // Execute code (simplified - replace with actual execution service)
    // ===========================
    private function executeCode($code, $language, $input)
    {
        // For now, simulate execution based on language
        // In production, you'd use a code execution service like Judge0, Piston, or custom containers
        
        $simulated_output = $this->simulateCodeExecution($code, $language, $input);
        
        return [
            'output' => $simulated_output,
            'execution_time' => rand(10, 100) / 1000, // Random time between 10-100ms
            'memory_used' => rand(1000, 5000) // Random memory between 1-5MB
        ];
    }

    private function simulateCodeExecution($code, $language, $input)
    {
        // Simple simulation - in real implementation, use proper code execution
        // This is just for demonstration
        
        if ($language === 'javascript') {
            // Simulate JS execution
            if (strpos($code, 'sum') !== false || strpos($code, '+') !== false) {
                $parts = explode(',', $input);
                if (count($parts) === 2) {
                    return (intval(trim($parts[0])) + intval(trim($parts[1])));
                }
            }
        } 
        
        // Default fallback
        return "Simulated output for input: " . $input;
    }

    // ===========================
    // Update challenge statistics
    // ===========================
    private function updateChallengeStats($challenge_id, $passed)
    {
        // Update total submissions
        $this->db->raw("
            UPDATE challenges 
            SET total_submissions = total_submissions + 1 
            WHERE id = ?
        ", [$challenge_id]);

        // Update accepted submissions if passed
        if ($passed) {
            $this->db->raw("
                UPDATE challenges 
                SET accepted_submissions = accepted_submissions + 1,
                    solved_count = solved_count + 1
                WHERE id = ?
            ", [$challenge_id]);
        }
    }

    // ===========================
    // Update user challenge status when they pass
    // ===========================
    private function updateUserChallengeStatus($challenge_id, $execution_time, $memory_used)
    {
        $check_sql = "SELECT is_solved FROM user_challenge_status WHERE user_id = ? AND challenge_id = ?";
        $stmt = $this->db->raw($check_sql, [$this->user_id, $challenge_id]);
        $current_status = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_status || !$current_status['is_solved']) {
            // First time solving - update with best scores and award XP
            $update_sql = "
                UPDATE user_challenge_status 
                SET is_solved = 1, solved_at = NOW(), 
                    best_execution_time = ?, best_memory_used = ?
                WHERE user_id = ? AND challenge_id = ?
            ";
            $this->db->raw($update_sql, [$execution_time, $memory_used, $this->user_id, $challenge_id]);

            // Award XP
            $challenge_stmt = $this->db->raw("SELECT xp_reward FROM challenges WHERE id = ?", [$challenge_id]);
            $challenge = $challenge_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($challenge) {
                $this->awardXP($challenge['xp_reward']);
                
                // Update user stats
                $this->updateUserStats($challenge_id);
            }
        } else {
            // Update best scores if better
            $update_sql = "
                UPDATE user_challenge_status 
                SET best_execution_time = LEAST(COALESCE(best_execution_time, 999), ?),
                    best_memory_used = LEAST(COALESCE(best_memory_used, 999999), ?)
                WHERE user_id = ? AND challenge_id = ? 
                AND (best_execution_time > ? OR best_memory_used > ?)
            ";
            $this->db->raw($update_sql, [
                $execution_time, $memory_used, $this->user_id, $challenge_id,
                $execution_time, $memory_used
            ]);
        }
    }

    // ===========================
    // Award XP to user
    // ===========================
    private function awardXP($xp_amount)
    {
        // Update user's XP
        $this->db->raw("
            UPDATE users 
            SET xp = xp + ? 
            WHERE id = ?
        ", [$xp_amount, $this->user_id]);
        
        // Update user_stats
        $this->db->raw("
            INSERT INTO user_stats (user_id, xp, challenges_solved, total_xp_earned)
            VALUES (?, ?, 1, ?)
            ON DUPLICATE KEY UPDATE 
            xp = xp + ?,
            challenges_solved = challenges_solved + 1,
            total_xp_earned = total_xp_earned + ?
        ", [$this->user_id, $xp_amount, $xp_amount, $xp_amount, $xp_amount]);
    }

    // ===========================
    // Update user stats
    // ===========================
    private function updateUserStats($challenge_id)
    {
        // This method can be expanded to update various user statistics
        $this->db->raw("
            INSERT INTO user_stats (user_id, challenges_solved)
            VALUES (?, 1)
            ON DUPLICATE KEY UPDATE 
            challenges_solved = challenges_solved + 1
        ", [$this->user_id]);
    }

    // ===========================
    // GET /challenges/{id}/leaderboard
    // ===========================
    public function leaderboard($challenge_id)
    {
        $this->api->require_method('GET');
        
        // Leaderboard might not require authentication, but you can add it if needed
        // $auth = $this->api->require_jwt();
        // $this->user_id = $auth['sub'];

        $sql = "
            SELECT 
                u.username,
                ucs.best_execution_time,
                ucs.best_memory_used,
                ucs.solved_at,
                s.submitted_at as first_solved_at
            FROM user_challenge_status ucs
            JOIN users u ON ucs.user_id = u.id
            LEFT JOIN submissions s ON (
                s.user_id = ucs.user_id 
                AND s.challenge_id = ucs.challenge_id 
                AND s.status = 'passed'
                AND s.submitted_at = (
                    SELECT MIN(submitted_at) 
                    FROM submissions 
                    WHERE user_id = ucs.user_id 
                    AND challenge_id = ucs.challenge_id 
                    AND status = 'passed'
                )
            )
            WHERE ucs.challenge_id = ? AND ucs.is_solved = 1
            ORDER BY ucs.best_execution_time ASC, ucs.solved_at ASC
            LIMIT 100
        ";
        
        $stmt = $this->db->raw($sql, [$challenge_id]);
        $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($leaderboard);
    }
}