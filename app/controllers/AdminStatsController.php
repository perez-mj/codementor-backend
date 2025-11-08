<?php
class AdminStatsController extends Controller
{
    // GET /api/admin/stats
    public function stats() 
    {
        $auth = $this->api->require_jwt();
        
        // Check if user is admin
        if ($auth['role'] !== 'admin') {
            $this->api->respond_error('Access denied', 403);
        }

        // Total active users (past 30 days)
        $activeUsers = $this->db->raw(
            "SELECT COUNT(*) as val FROM users WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        )->fetch(PDO::FETCH_ASSOC)['val'];

        // Total lessons
        $lessons = $this->db->raw(
            "SELECT COUNT(*) as val FROM lessons"
        )->fetch(PDO::FETCH_ASSOC)['val'];

        // Total challenges solved (solutions.passed_tests = 1)
        $challengesSolved = $this->db->raw(
            "SELECT COUNT(*) as val FROM solutions WHERE passed_tests = 1"
        )->fetch(PDO::FETCH_ASSOC)['val'];

        // Average completion rate (simplified: passed solutions / attempts for those challenges)
        $completion = $this->db->raw("
            SELECT ROUND((SUM(s.passed_tests) / NULLIF(COUNT(a.id),0)) * 100, 0) as completion_rate
            FROM attempts a
            LEFT JOIN solutions s ON a.challenge_id = s.challenge_id AND a.user_id = s.user_id
        ")->fetch(PDO::FETCH_ASSOC)['completion_rate'] ?? 0;

        $this->api->respond([
            'active_users_30d' => (int)$activeUsers,
            'lessons' => (int)$lessons,
            'challenges_solved' => (int)$challengesSolved,
            'avg_completion_rate' => (int)$completion,
        ]);
    }

    // GET /api/admin/user-growth?months=6
    public function userGrowth() 
    {
        $auth = $this->api->require_jwt();
        
        if ($auth['role'] !== 'admin') {
            $this->api->respond_error('Access denied', 403);
        }

        $months = intval($_GET['months'] ?? 6);

        $stmt = $this->db->raw("
            SELECT DATE_FORMAT(joined_at, '%Y-%m') AS period, COUNT(*) as count
            FROM users
            WHERE joined_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY period
            ORDER BY period
        ", [$months]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->api->respond($rows);
    }

    // GET /api/admin/lesson-engagement?limit=10
    public function lessonEngagement() 
    {
        $auth = $this->api->require_jwt();
        
        if ($auth['role'] !== 'admin') {
            $this->api->respond_error('Access denied', 403);
        }

        $limit = intval($_GET['limit'] ?? 10);

        $stmt = $this->db->raw("
            SELECT l.id, l.title, l.slug, COUNT(v.id) AS views
            FROM lessons l
            LEFT JOIN lesson_views v ON v.lesson_id = l.id
            GROUP BY l.id
            ORDER BY views DESC
            LIMIT ?
        ", [$limit]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->api->respond($rows);
    }

    // GET /api/admin/recent-activity?limit=10
    public function recentActivity() 
    {
        $auth = $this->api->require_jwt();
        
        if ($auth['role'] !== 'admin') {
            $this->api->respond_error('Access denied', 403);
        }

        $limit = intval($_GET['limit'] ?? 10);

        $recent = [];

        // Lessons updated
        $stmt = $this->db->raw(
            "SELECT id, title, updated_at AS time FROM lessons ORDER BY updated_at DESC LIMIT ?",
            [$limit]
        );
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $recent[] = [
                'id' => 'lesson-'.$r['id'], 
                'title' => 'Lesson updated: '.$r['title'], 
                'time' => $r['time'], 
                'type' => 'lesson'
            ];
        }

        // Solutions passed
        $stmt = $this->db->raw(
            "SELECT s.id, c.title AS challenge_title, s.submitted_at AS time 
             FROM solutions s 
             JOIN challenges c ON s.challenge_id = c.id 
             WHERE s.passed_tests = 1 
             ORDER BY s.submitted_at DESC 
             LIMIT ?",
            [$limit]
        );
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $recent[] = [
                'id' => 'sol-'.$r['id'], 
                'title' => 'Challenge solved: '.$r['challenge_title'], 
                'time' => $r['time'], 
                'type' => 'challenge'
            ];
        }

        // New users
        $stmt = $this->db->raw(
            "SELECT id, username, joined_at as time FROM users ORDER BY joined_at DESC LIMIT ?",
            [$limit]
        );
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $recent[] = [
                'id' => 'user-'.$r['id'], 
                'title' => 'User joined: '.$r['username'], 
                'time' => $r['time'], 
                'type' => 'user'
            ];
        }

        // Sort by time desc and trim to limit
        usort($recent, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        $recent = array_slice($recent, 0, $limit);
        $this->api->respond($recent);
    }
}