<?php

class UsersController extends Controller
{
    // GET /admin/users
    public function list()
    {
        $this->api->require_method('GET');
        
        // Get pagination and filter parameters
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = min(100, max(10, intval($_GET['perPage'] ?? 10)));
        $offset = ($page - 1) * $perPage;
        
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['dateFrom'] ?? '';
        $dateTo = $_GET['dateTo'] ?? '';
        $lastActivity = $_GET['lastActivity'] ?? '';
        
        // Build WHERE conditions
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
            $searchTerm = "%$search%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm);
        }
        
        if (!empty($role)) {
            $whereConditions[] = "u.role = ?";
            $params[] = $role;
        }
        
        if (!empty($status)) {
            $whereConditions[] = "u.account_status = ?";
            $params[] = $status;
        }
        
        if (!empty($dateFrom)) {
            $whereConditions[] = "DATE(u.joined_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if (!empty($dateTo)) {
            $whereConditions[] = "DATE(u.joined_at) <= ?";
            $params[] = $dateTo;
        }
        
        // Last activity filter
        if (!empty($lastActivity)) {
            $dateMap = [
                'today' => 'CURDATE()',
                'week' => 'DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
                'month' => 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)',
                '3months' => 'DATE_SUB(CURDATE(), INTERVAL 90 DAY)'
            ];
            
            if (isset($dateMap[$lastActivity])) {
                $whereConditions[] = "u.last_login_at >= {$dateMap[$lastActivity]}";
            }
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM users u $whereClause";
        $countStmt = $this->db->raw($countSql, $params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get users with progress
        $sql = "
            SELECT 
                u.id as user_id,
                u.username,
                u.email,
                u.full_name,
                u.role,
                u.account_status,
                u.joined_at as created_at,
                u.last_login_at,
                COALESCE(up.percent_completion, 0) as overall_progress,
                up.total_lessons_completed,
                up.time_spent
            FROM 
                users u
            LEFT JOIN 
                user_progress up ON u.id = up.user_id
            $whereClause
            ORDER BY 
                u.joined_at DESC
            LIMIT ? OFFSET ?
        ";
        
        array_push($params, $perPage, $offset);
        $stmt = $this->db->raw($sql, $params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $lastPage = ceil($total / $perPage);
        
        $this->api->respond([
            'users' => $users,
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'lastPage' => $lastPage,
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ]);
    }
    
    // GET /admin/users/stats
    public function stats()
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN account_status = 'active' THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN account_status = 'pending' THEN 1 ELSE 0 END) as pending_users,
                AVG(COALESCE(up.percent_completion, 0)) as avg_progress
            FROM 
                users u
            LEFT JOIN 
                user_progress up ON u.id = up.user_id
        ";
        
        $stmt = $this->db->raw($sql);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get AI interactions count (last 7 days)
        $aiSql = "
            SELECT COUNT(*) as ai_interactions 
            FROM ai_interactions 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ";
        
        $aiStmt = $this->db->raw($aiSql);
        $aiStats = $aiStmt->fetch(PDO::FETCH_ASSOC);
        
        $this->api->respond([
            'totalUsers' => intval($stats['total_users']),
            'activeUsers' => intval($stats['active_users']),
            'avgProgress' => round(floatval($stats['avg_progress']), 1),
            'pendingApprovals' => intval($stats['pending_users']),
            'aiInteractions' => intval($aiStats['ai_interactions'])
        ]);
    }
    
    // GET /admin/users/{id}
    public function get($user_id)
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                u.id as user_id,
                u.username,
                u.email,
                u.full_name,
                u.role,
                u.account_status,
                u.joined_at as created_at,
                u.last_login_at,
                up.total_lessons_completed,
                up.current_lesson_id,
                l.title as current_lesson,
                up.percent_completion,
                up.time_spent
            FROM 
                users u
            LEFT JOIN 
                user_progress up ON u.id = up.user_id
            LEFT JOIN
                lessons l ON up.current_lesson_id = l.id
            WHERE 
                u.id = ?
        ";
        
        $stmt = $this->db->raw($sql, [$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $this->api->respond($user);
        } else {
            $this->api->respond_error('User not found', 404);
        }
    }
    
    // PUT /admin/users/{id}/role
    public function updateRole($user_id)
    {
        $this->api->require_method('PUT');
        
        $data = $this->api->get_input();
        $role = $data['role_id'] ?? '';
        
        if (!in_array($role, ['user', 'moderator', 'admin'])) {
            $this->api->respond_error('Invalid role', 400);
        }
        
        $sql = "UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->raw($sql, [$role, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $this->api->respond(['message' => 'Role updated successfully']);
        } else {
            $this->api->respond_error('User not found', 404);
        }
    }
    
    // POST /admin/users/{id}/moderate
    public function moderate($user_id)
    {
        $this->api->require_method('POST');
        
        $data = $this->api->get_input();
        $action = $data['action'] ?? '';
        $reason = $data['reason'] ?? '';
        
        $validActions = ['approve', 'suspend', 'ban', 'reactivate'];
        if (!in_array($action, $validActions)) {
            $this->api->respond_error('Invalid action', 400);
        }
        
        $statusMap = [
            'approve' => 'active',
            'suspend' => 'suspended', 
            'ban' => 'banned',
            'reactivate' => 'active'
        ];
        
        $newStatus = $statusMap[$action];
        
        $sql = "UPDATE users SET account_status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->raw($sql, [$newStatus, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $this->api->respond(['message' => 'User account status updated successfully']);
        } else {
            $this->api->respond_error('User not found', 404);
        }
    }
}