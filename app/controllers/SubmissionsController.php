<?php
class SubmissionsController extends Controller
{
    private $user_id; // Assume this is populated by middleware/token

    // GET /submissions (List user's own submissions)
    public function list()
    {
        $this->api->require_method('GET');
        
        // Only fetch submissions for the authenticated user
        $sql = 'SELECT s.id, s.challenge_id, s.language, s.status, s.execution_time, s.submitted_at, c.title as challenge_title
                FROM submissions as s
                JOIN challenges as c ON c.id = s.challenge_id
                WHERE s.user_id = ?
                ORDER BY s.submitted_at DESC';
        
        $stmt = $this->db->raw($sql, [$this->user_id]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->api->respond($submissions);
    }

    // GET /submissions/{id} (View a single submission)
    public function get($submission_id)
    {
        $this->api->require_method('GET');
        
        $sql = 'SELECT * FROM submissions WHERE id = ? AND user_id = ?';
        $stmt = $this->db->raw($sql, [$submission_id, $this->user_id]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($submission) {
            $this->api->respond($submission);
        } else {
            $this->api->respond_error('Submission not found or access denied', 404);
        }
    }

    // POST /submissions (Create a new submission)
    public function create()
    {
        $this->api->require_method('POST');
        $input = $this->api->body();
        
        if (empty($input['challenge_id']) || empty($input['code_content']) || empty($input['language'])) {
            $this->api->respond_error('Missing required submission data', 400);
        }
        
        // NOTE: In a real system, the status, time, and memory would be set 
        // by a separate "code runner" service, not directly by the user via the API.
        
        $sql = 'INSERT INTO submissions (user_id, challenge_id, code_content, language, status) 
                VALUES (?, ?, ?, ?, ?)';
        $stmt = $this->db->raw($sql, [
            $this->user_id,
            (int)$input['challenge_id'],
            $input['code_content'],
            $input['language'],
            'Pending' // Initial status before execution
        ]);

        if ($stmt->rowCount() > 0) {
            $id = $this->db->lastInsertId();
            // A real app would trigger a runner service here
            $this->api->respond(['id' => $id, 'message' => 'Submission received. Awaiting execution.', 'status' => 'Pending'], 202);
        } else {
            $this->api->respond_error('Failed to record submission', 500);
        }
    }
}