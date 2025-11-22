<?php

class AIInteractionsController extends Controller
{
    // GET /admin/users/{id}/ai-interactions
    public function getUserInteractions($user_id)
    {
        $this->api->require_method('GET');
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = min(50, max(10, intval($_GET['perPage'] ?? 10)));
        $offset = ($page - 1) * $perPage;
        
        $sql = "
            SELECT 
                id as interaction_id,
                user_message,
                ai_response,
                created_at as timestamp
            FROM 
                ai_interactions
            WHERE 
                user_id = ?
            ORDER BY 
                created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $this->db->raw($sql, [$user_id, $perPage, $offset]);
        $interactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($interactions);
    }
}