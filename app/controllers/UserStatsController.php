<?php

class UserStatsController extends Controller
{
    private $user_id; // Assume this is populated by middleware/token

    // GET /user_stats
    public function get_stats()
    {
        $this->api->require_method('GET');

        $stmt = $this->db->raw('SELECT * FROM user_stats WHERE user_id = ?', [$this->user_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stats) {
            $this->api->respond($stats);
        } else {
            // Stats don't exist yet, return a default/empty structure
            $this->api->respond([
                'user_id' => $this->user_id,
                'xp' => 0,
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_submissions' => 0,
                'challenges_solved' => 0,
            ]);
        }
    }

    // PUT /user_stats (Primarily for administrative or system updates)
    public function update_stats()
    {
        $this->api->require_method('PUT');
        $input = $this->api->body();
        
        // Define allowed fields to update
        $data = [];
        $params = [];
        $setClauses = [];

        if (isset($input['xp'])) {
            $data['xp'] = (int)$input['xp'];
            $setClauses[] = 'xp = ?';
            $params[] = $data['xp'];
        }
        if (isset($input['current_streak'])) {
            $data['current_streak'] = (int)$input['current_streak'];
            $setClauses[] = 'current_streak = ?';
            $params[] = $data['current_streak'];
        }
        // ... include other fields as needed ...

        if (empty($data)) {
            $this->api->respond_error('No valid fields provided for update', 400);
        }

        // Add user_id to parameters
        $params[] = $this->user_id;

        $sql = 'UPDATE user_stats SET ' . implode(', ', $setClauses) . ' WHERE user_id = ?';
        $stmt = $this->db->raw($sql, $params);
        
        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            $this->api->respond(['message' => 'User stats updated successfully']);
        } else {
            // Handle case where stats might not exist yet (requires an initial CREATE)
            // Or if the update failed
            $this->api->respond_error('Failed to update user stats or stats not found', 404);
        }
    }
}