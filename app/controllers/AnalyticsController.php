<?php
class AnalyticsController extends Controller
{
    // GET /api/admin/analytics/overview
    public function overview() 
    {
        // $auth = $this->api->require_jwt();
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }

        // Total Users
        $totalUsers = $this->db->raw("SELECT COUNT(*) as val FROM users")->fetch(PDO::FETCH_ASSOC)['val'];

        // Active Sessions (users active in last 30 minutes)
        $activeSessions = $this->db->raw("
            SELECT COUNT(DISTINCT user_id) as val 
            FROM submissions 
            WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ")->fetch(PDO::FETCH_ASSOC)['val'];

        // Total Submissions
        $totalSubmissions = $this->db->raw("SELECT COUNT(*) as val FROM submissions")->fetch(PDO::FETCH_ASSOC)['val'];

        // Submission Success Rate
        $successResult = $this->db->raw("
            SELECT 
                CASE 
                    WHEN COUNT(*) = 0 THEN 0
                    ELSE ROUND((COUNT(CASE WHEN status = 'Passed' THEN 1 END) * 100.0 / COUNT(*)), 1)
                END as val 
            FROM submissions
        ")->fetch(PDO::FETCH_ASSOC);
        $successRate = $successResult['val'] ?? 0;

        // Total Lessons
        $totalLessons = $this->db->raw("SELECT COUNT(*) as val FROM lessons")->fetch(PDO::FETCH_ASSOC)['val'];

        // Total Challenges
        $totalChallenges = $this->db->raw("SELECT COUNT(*) as val FROM challenges")->fetch(PDO::FETCH_ASSOC)['val'];

        // User Growth (last 30 days)
        $recentUsers = $this->db->raw("
            SELECT COUNT(*) as val 
            FROM users 
            WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ")->fetch(PDO::FETCH_ASSOC)['val'];
        $userGrowth = $totalUsers > 0 ? round(($recentUsers / $totalUsers) * 100, 1) : 0;

        $this->api->respond([
            'totalUsers' => (int)$totalUsers,
            'activeSessions' => (int)$activeSessions,
            'totalSubmissions' => (int)$totalSubmissions,
            'submissionSuccessRate' => (float)$successRate,
            'totalLessons' => (int)$totalLessons,
            'totalChallenges' => (int)$totalChallenges,
            'userGrowth' => $userGrowth
        ]);
    }

    // GET /api/admin/analytics/user-growth?range=30d
    public function userGrowth() 
    {
        // $auth = $this->api->require_jwt();
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }

        $range = $_GET['range'] ?? '30d';
        
        switch($range) {
            case '7d': $interval = '7 DAY'; $format = '%Y-%m-%d'; break;
            case '90d': $interval = '90 DAY'; $format = '%Y-%m-%d'; break;
            case '1y': $interval = '1 YEAR'; $format = '%Y-%m'; break;
            default: $interval = '30 DAY'; $format = '%Y-%m-%d';
        }

        $stmt = $this->db->raw("
            SELECT 
                DATE_FORMAT(joined_at, ?) AS period,
                COUNT(*) as new_users
            FROM users
            WHERE joined_at >= DATE_SUB(NOW(), INTERVAL ?)
            GROUP BY period
            ORDER BY period
        ", [$format, $interval]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->api->respond($rows);
    }

    // GET /api/admin/analytics/submission-activity?range=30d
    public function submissionActivity() 
    {
        // $auth = $this->api->require_jwt();
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }

        $range = $_GET['range'] ?? '30d';
        
        switch($range) {
            case '7d': $interval = '7 DAY'; $format = '%Y-%m-%d'; break;
            case '90d': $interval = '90 DAY'; $format = '%Y-%m-%d'; break;
            case '1y': $interval = '1 YEAR'; $format = '%Y-%m'; break;
            default: $interval = '30 DAY'; $format = '%Y-%m-%d';
        }

        // Daily submission stats
        $stmt = $this->db->raw("
            SELECT 
                DATE_FORMAT(submitted_at, ?) AS period,
                COUNT(*) as total_submissions,
                COUNT(CASE WHEN status = 'Passed' THEN 1 END) as passed_submissions,
                COUNT(CASE WHEN status = 'Failed' THEN 1 END) as failed_submissions
            FROM submissions
            WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL ?)
            GROUP BY period
            ORDER BY period
        ", [$format, $interval]);

        $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Language distribution
        $langStmt = $this->db->raw("
            SELECT 
                language,
                COUNT(*) as count,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM submissions WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL ?))), 1) as percentage
            FROM submissions
            WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL ?)
            GROUP BY language
            ORDER BY count DESC
            LIMIT 5
        ", [$interval, $interval]);

        $languageUsage = $langStmt->fetchAll(PDO::FETCH_ASSOC);

        $this->api->respond([
            'dailyStats' => $dailyStats,
            'languageUsage' => $languageUsage
        ]);
    }

    // GET /api/admin/analytics/learning-paths
    public function learningPaths() 
    {
        // $auth = $this->api->require_jwt();
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }

        // Language enrollment stats (based on submissions in each language)
        $stmt = $this->db->raw("
            SELECT 
                l.name as language_name,
                COUNT(DISTINCT s.user_id) as active_users,
                COUNT(s.id) as total_submissions,
                CASE 
                    WHEN COUNT(s.id) = 0 THEN 0
                    ELSE ROUND((COUNT(CASE WHEN s.status = 'Passed' THEN 1 END) * 100.0 / COUNT(s.id)), 1)
                END as success_rate
            FROM languages l
            LEFT JOIN submissions s ON l.name = s.language
            GROUP BY l.id, l.name
            ORDER BY active_users DESC
        ");

        $paths = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->api->respond($paths);
    }

    // GET /api/admin/analytics/challenge-difficulty
    public function challengeDifficulty() 
    {
        // $auth = $this->api->require_jwt();
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }

        $stmt = $this->db->raw("
            SELECT 
                c.id,
                c.title,
                c.difficulty,
                cat.name as category_name,
                COUNT(s.id) as total_attempts,
                COUNT(CASE WHEN s.status = 'Passed' THEN 1 END) as passed_attempts,
                CASE 
                    WHEN COUNT(s.id) = 0 THEN 0
                    ELSE ROUND((COUNT(CASE WHEN s.status = 'Passed' THEN 1 END) * 100.0 / COUNT(s.id)), 1)
                END as pass_rate,
                c.solved_count as times_solved
            FROM challenges c
            LEFT JOIN submissions s ON c.id = s.challenge_id
            LEFT JOIN categories cat ON c.category_id = cat.id
            GROUP BY c.id, c.title, c.difficulty, cat.name
            ORDER BY pass_rate ASC
        ");

        $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Overall stats
        $overall = $this->db->raw("
            SELECT 
                CASE 
                    WHEN COUNT(*) = 0 THEN 0
                    ELSE ROUND(AVG(pass_rate), 1)
                END as avg_pass_rate,
                CASE 
                    WHEN COUNT(*) = 0 THEN 0
                    ELSE ROUND(AVG(total_attempts), 1)
                END as avg_attempts
            FROM (
                SELECT 
                    c.id,
                    COUNT(s.id) as total_attempts,
                    CASE 
                        WHEN COUNT(s.id) = 0 THEN 0
                        ELSE (COUNT(CASE WHEN s.status = 'Passed' THEN 1 END) * 100.0 / COUNT(s.id))
                    END as pass_rate
                FROM challenges c
                LEFT JOIN submissions s ON c.id = s.challenge_id
                GROUP BY c.id
            ) as stats
        ")->fetch(PDO::FETCH_ASSOC);

        $this->api->respond([
            'challenges' => $challenges,
            'overall' => $overall
        ]);
    }

    // GET /api/admin/analytics/lesson-performance
    public function lessonPerformance() 
    {
        // $auth = $this->api->require_jwt();
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }

        // Get lessons with language info
        $stmt = $this->db->raw("
            SELECT 
                l.id,
                l.title,
                lg.name as language_name,
                l.order_index,
                l.created_at
            FROM lessons l
            LEFT JOIN languages lg ON l.language_id = lg.id
            ORDER BY l.created_at DESC
            LIMIT 10
        ");

        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->api->respond($lessons);
    }

    // GET /api/admin/analytics/session-stats
    public function sessionStats() 
    {
        // $auth = $this->api->require_jwt();
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }

        // Daily activity stats
        $dailyActivity = $this->db->raw("
            SELECT 
                DATE(submitted_at) as date,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(*) as total_submissions
            FROM submissions
            WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(submitted_at)
            ORDER BY date DESC
            LIMIT 30
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Peak hours
        $peakHours = $this->db->raw("
            SELECT 
                HOUR(submitted_at) as hour,
                COUNT(*) as submissions
            FROM submissions
            GROUP BY HOUR(submitted_at)
            ORDER BY submissions DESC
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);

        $this->api->respond([
            'dailyActivity' => $dailyActivity,
            'peakHours' => $peakHours
        ]);
    }

    // GET /api/admin/analytics/top-performers?limit=10
    public function topPerformers() 
    {
        // $auth = $this->api->require_jwt();
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }

        $limit = intval($_GET['limit'] ?? 10);

        $stmt = $this->db->raw("
            SELECT 
                u.id,
                u.username,
                u.email,
                u.joined_at,
                COALESCE(us.xp, 0) as xp,
                COALESCE(us.challenges_solved, 0) as challenges_solved,
                COALESCE(us.total_submissions, 0) as total_submissions,
                CASE 
                    WHEN COALESCE(us.total_submissions, 0) = 0 THEN 0
                    ELSE ROUND((COALESCE(us.challenges_solved, 0) * 100.0 / us.total_submissions), 1)
                END as success_rate,
                (SELECT COUNT(*) FROM user_achievements ua WHERE ua.user_id = u.id) as achievements_count
            FROM users u
            LEFT JOIN user_stats us ON u.id = us.user_id
            ORDER BY us.xp DESC, us.challenges_solved DESC
            LIMIT ?
        ", [$limit]);

        $performers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->api->respond($performers);
    }

    // GET /api/admin/analytics/recent-activity?limit=20
    public function recentActivity() 
    {
        // $auth = $this->api->require_jwt();
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }

        $limit = intval($_GET['limit'] ?? 20);

        // Combine recent submissions, new users, and challenge solves
        $activities = [];

        // Recent submissions
        $submissions = $this->db->raw("
            SELECT 
                s.id,
                s.submitted_at as time,
                'submission' as type,
                s.status,
                u.username,
                c.title as challenge_title,
                CONCAT(u.username, ' submitted ', c.title) as description
            FROM submissions s
            JOIN users u ON s.user_id = u.id
            JOIN challenges c ON s.challenge_id = c.id
            ORDER BY s.submitted_at DESC
            LIMIT ?
        ", [$limit])->fetchAll(PDO::FETCH_ASSOC);

        // New users
        $newUsers = $this->db->raw("
            SELECT 
                id,
                joined_at as time,
                'new_user' as type,
                username,
                CONCAT('New user joined: ', username) as description
            FROM users
            ORDER BY joined_at DESC
            LIMIT ?
        ", [$limit])->fetchAll(PDO::FETCH_ASSOC);

        // Challenge solves
        $solves = $this->db->raw("
            SELECT 
                ucs.solved_at as time,
                'challenge_solve' as type,
                u.username,
                c.title as challenge_title,
                CONCAT(u.username, ' solved ', c.title) as description
            FROM user_challenge_status ucs
            JOIN users u ON ucs.user_id = u.id
            JOIN challenges c ON ucs.challenge_id = c.id
            WHERE ucs.is_solved = 1
            ORDER BY ucs.solved_at DESC
            LIMIT ?
        ", [$limit])->fetchAll(PDO::FETCH_ASSOC);

        // Combine and sort all activities
        $activities = array_merge($submissions, $newUsers, $solves);
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        $activities = array_slice($activities, 0, $limit);
        $this->api->respond($activities);
    }

    // GET /api/admin/analytics/user-stats
    public function userStats() 
    {
        // $auth = $this->api->require_jwt();
        // if ($auth['role'] !== 'admin') {
        //     $this->api->respond_error('Access denied', 403);
        // }

        // Daily new users
        $dailyNew = $this->db->raw("
            SELECT COUNT(*) as count
            FROM users 
            WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        ")->fetch(PDO::FETCH_ASSOC)['count'];

        // Weekly new users
        $weeklyNew = $this->db->raw("
            SELECT COUNT(*) as count
            FROM users 
            WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ")->fetch(PDO::FETCH_ASSOC)['count'];

        // Total users
        $totalUsers = $this->db->raw("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC)['count'];

        // Monthly growth rate (simplified)
        $lastMonthUsers = $this->db->raw("
            SELECT COUNT(*) as count
            FROM users 
            WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) 
            AND joined_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ")->fetch(PDO::FETCH_ASSOC)['count'];

        $monthlyGrowthRate = $lastMonthUsers > 0 ? 
            round((($weeklyNew - $lastMonthUsers) / $lastMonthUsers) * 100, 1) : 0;

        $this->api->respond([
            'dailyNewUsers' => (int)$dailyNew,
            'weeklyNewUsers' => (int)$weeklyNew,
            'monthlyGrowthRate' => $monthlyGrowthRate,
            'totalUsers' => (int)$totalUsers
        ]);
    }
}