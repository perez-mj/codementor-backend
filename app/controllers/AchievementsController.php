<?php

class AchievementsController extends Controller
{
    // GET /achievements
    public function list()
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                id, name, description, criteria
            FROM 
                achievements
            ORDER BY 
                id ASC
        ";

        $stmt = $this->db->raw($sql);
        $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->api->respond($achievements);
    }

    // GET /achievements/{id}
    public function get($achievement_id)
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                id, name, description, criteria
            FROM 
                achievements
            WHERE 
                id = ?
        ";

        $stmt = $this->db->raw($sql, [$achievement_id]);
        $achievement = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($achievement) {
            $this->api->respond($achievement);
        } else {
            $this->api->respond_error('Achievement not found', 404);
        }
    }
}