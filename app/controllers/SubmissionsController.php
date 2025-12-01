<?php
class SubmissionsController extends Controller
{
    private $user_id;

    // ===========================
    // GET /submissions/{id}
    // ===========================
    public function get($submission_id)
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
    // GET /submissions (list user's submissions)
    // ===========================
    public function list()
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
            WHERE s.user_id = ?
            ORDER BY s.submitted_at DESC
            LIMIT 50
        ";
        
        $stmt = $this->db->raw($sql, [$this->user_id]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->api->respond($submissions);
    }

    // ===========================
    // GET /challenges/{id}/submissions (list submissions for a specific challenge)
    // ===========================
    public function challengeSubmissions($challenge_id)
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
            WHERE s.user_id = ? AND s.challenge_id = ?
            ORDER BY s.submitted_at DESC
            LIMIT 20
        ";
        
        $stmt = $this->db->raw($sql, [$this->user_id, $challenge_id]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->api->respond($submissions);
    }
}