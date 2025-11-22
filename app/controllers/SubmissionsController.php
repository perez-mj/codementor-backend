<?php

class SubmissionsController extends Controller
{
    // GET /admin/users/{id}/submissions
    public function getUserSubmissions($user_id)
    {
        $this->api->require_method('GET');
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = min(50, max(10, intval($_GET['perPage'] ?? 10)));
        $offset = ($page - 1) * $perPage;
        
        $sql = "
            SELECT 
                s.id as submission_id,
                c.title as challenge_title,
                s.language,
                s.status,
                s.execution_time as runtime,
                s.memory_used,
                s.code_content as code,
                s.submitted_at,
                (
                    SELECT COUNT(*) 
                    FROM submission_test_results str 
                    WHERE str.submission_id = s.id AND str.passed = 1
                ) as passed_tests,
                (
                    SELECT COUNT(*) 
                    FROM submission_test_results str 
                    WHERE str.submission_id = s.id
                ) as total_tests
            FROM 
                submissions s
            JOIN 
                challenges c ON s.challenge_id = c.id
            WHERE 
                s.user_id = ?
            ORDER BY 
                s.submitted_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $this->db->raw($sql, [$user_id, $perPage, $offset]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format test case results for frontend
        foreach ($submissions as &$submission) {
            $passed = intval($submission['passed_tests']);
            $total = intval($submission['total_tests']);
            $submission['test_case_results'] = [];
            
            for ($i = 0; $i < $total; $i++) {
                $submission['test_case_results'][] = [
                    'passed' => $i < $passed
                ];
            }
        }
        
        $this->api->respond($submissions);
    }
}