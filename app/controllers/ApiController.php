<?php
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
class ApiController extends Controller
{
    private $user_id;

    public function login()
    {
        $this->api->require_method('POST');
        $input = $this->api->body();
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        $stmt = $this->db->raw('SELECT * FROM users WHERE username = ?', [$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $tokens = $this->api->issue_tokens(['id' => $user['id'], 'role' => $user['role']]);
            $userData = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'joined_at' => $user['joined_at']
            ];

            $this->api->respond(['tokens' => $tokens, 'user' => $userData]); //output: {"tokens":{"access_token": "...", "refresh_token": "..."},"user":{...}}
        } else {
            $this->api->respond_error('Invalid credentials', 401); // output: {"error":"Invalid credentials"}
        }
    }

    public function logout()
    {
        $this->api->require_method('POST');
        $input = $this->api->body();
        $token = $input['refresh_token'] ?? '';
        $this->api->revoke_refresh_token($token);
        $this->api->respond(['message' => 'Logged out']);
    }

    public function list()
    {
        $stmt = $this->db->table('users')
            ->select('id, username, email, role, joined_at')
            ->get_all();
        $this->api->respond($stmt);
    }
    
    public function register()
    {
        $this->api->require_method('POST');
        $input = $this->api->body();

        $username = trim($input['username'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $confirm_password = $input['confirm'] ?? '';
        $role = $input['role'] ?? 'user';

        // ------------------------
        // 1️⃣ Basic validation
        // ------------------------
        if (!$username || !$email || !$password) {
            $this->api->respond_error('Username, email, and password are required', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->api->respond_error('Invalid email format', 422); 
        }

        if (strlen($password) < 6) {
            $this->api->respond_error('Password must be at least 6 characters', 422);
        }

        if ($password === $confirm_password) {
            $this->api->respond_error("Password didn't match", 422);
        }

        // ------------------------
        // 2️⃣ Check for duplicate email/username
        // ------------------------
        $stmt = $this->db->raw('SELECT id FROM users WHERE email = ? OR username = ?', [$email, $username]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->api->respond_error('Username or email already exists', 409);
        }

        // ------------------------
        // 3️⃣ Insert user into DB with verification token
        // ------------------------
        $token = bin2hex(random_bytes(32)); // unique verification token
        $token_expiry = date('Y-m-d H:i:s', strtotime('+1 day')); // link expires in 24h

        try {
            $this->db->raw(
                "INSERT INTO users (username, email, password_hash, role, joined_at, verification_token, token_expires_at) 
         VALUES (?, ?, ?, ?, NOW(), ?, ?)",
                [$username, $email, password_hash($password, PASSWORD_BCRYPT), $role, $token, $token_expiry]
            );
        } catch (\Exception $e) {
            $this->api->respond_error('Failed to create user: ' . $e->getMessage(), 500);
        }

        // ------------------------
        // 4️⃣ Send welcome email (non-blocking)
        // ------------------------
        try {
            $mailer = new MailerService();
            $subject = "Confirm your CodeMentor account";
            $verification_link = "http://localhost:5173/verify-email?token={$token}";
            $html = "
        <h1>Hello {$username}!</h1>
        <p>Thanks for registering. Please verify your email by clicking the link below:</p>
        <p><a href='{$verification_link}'>Verify Email</a></p>
        <p>This link expires in 24 hours.</p>
    ";
            $mailer->send($email, $subject, $html);
        } catch (TransportExceptionInterface $e) {
            error_log("Failed to send verification email to {$email}: " . $e->getMessage());
        }

        // ------------------------
        // 5️⃣ Respond success
        // ------------------------
        $this->api->respond(['message' => 'Registration successful! Check your email.']);
    }



    public function update($id)
    {
        $input = $this->api->body();
        $this->db->raw(
            "UPDATE users SET username=?, email=?, role=? WHERE id=?",
            [$input['username'], $input['email'], $input['role'], $id]
        );
        $this->api->respond(['message' => 'User updated']);
    }

    public function delete($id)
    {
        $this->db->raw("DELETE FROM users WHERE id = ?", [$id]);
        $this->api->respond(['message' => 'User deleted']);
    }

    public function profile()
    {
        $auth = $this->api->require_jwt();
        $this->user_id = $auth['sub'];
        $stmt = $this->db->raw(
            "SELECT id, username, email, role, joined_at FROM users WHERE id = ?",
            [$this->user_id]
        );
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->api->respond($user ?: ['message' => 'User not found']);
    }

    public function refresh()
    {
        $this->api->require_method('POST');
        $input = $this->api->body();
        $refresh_token = $input['refresh_token'] ?? '';
        $this->api->refresh_access_token($refresh_token);
    }

    public function verify_email()
    {
        $this->api->require_method('GET');
        $token = $_GET['token'] ?? '';

        if (!$token) {
            $this->api->respond_error('Verification token is missing', 400);
        }

        $stmt = $this->db->raw(
            "SELECT id, token_expires_at, email_verified FROM users WHERE verification_token = ?",
            [$token]
        );
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->api->respond_error('Invalid token', 404);
        }

        if ($user['email_verified']) {
            $this->api->respond(['message' => 'Email already verified.']);
        }

        if (strtotime($user['token_expires_at']) < time()) {
            $this->api->respond_error('Token has expired', 410);
        }

        // Mark user as verified
        $this->db->raw(
            "UPDATE users SET email_verified = 1, verification_token = NULL, token_expires_at = NULL WHERE id = ?",
            [$user['id']]
        );

        $this->api->respond(['message' => 'Email verified successfully!']);
    }

}