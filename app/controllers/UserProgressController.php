<?php

class UserProgressController extends Controller
{
    // GET /admin/users/{id}/progress
    public function getProgress($user_id)
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                up.total_lessons_completed,
                up.current_lesson_id,
                l.title as current_lesson,
                up.percent_completion,
                up.time_spent,
                up.updated_at
            FROM 
                user_progress up
            LEFT JOIN 
                lessons l ON up.current_lesson_id = l.id
            WHERE 
                up.user_id = ?
        ";
        
        $stmt = $this->db->raw($sql, [$user_id]);
        $progress = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($progress) {
            $this->api->respond($progress);
        } else {
            // Return default progress if not found
            $this->api->respond([
                'total_lessons_completed' => 0,
                'current_lesson_id' => null,
                'current_lesson' => null,
                'percent_completion' => 0,
                'time_spent' => 0,
                'updated_at' => null
            ]);
        }
    }
    
    // GET /admin/users/{id}/learning-paths
    public function getLearningPaths($user_id)
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                ulp.path_id as enrollment_id,
                lp.name as path_name,
                ulp.progress_percentage,
                ulp.completed_lessons,
                lp.total_lessons,
                ulp.current_lesson_id,
                l.title as current_lesson,
                ulp.enrolled_at,
                ulp.updated_at
            FROM 
                user_learning_paths ulp
            JOIN 
                learning_paths lp ON ulp.path_id = lp.id
            LEFT JOIN 
                lessons l ON ulp.current_lesson_id = l.id
            WHERE 
                ulp.user_id = ?
            ORDER BY 
                ulp.updated_at DESC
        ";
        
        $stmt = $this->db->raw($sql, [$user_id]);
        $paths = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->api->respond($paths);
    }
}