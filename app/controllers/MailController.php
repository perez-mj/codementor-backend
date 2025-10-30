<?php
require_once APP_DIR . 'libraries/MailerService.php';
class MailController extends Controller
{
    private $mailer;

    public function __construct()
    {
        // Initialize the mailer service (handles DSN setup)
        $this->mailer = new MailerService();
    }

    /**
     * Send an email (JWT-protected endpoint)
     *
     * POST /api/mail/send
     * Body: { "to": "...", "subject": "...", "message": "..." }
     */
    public function send()
    {
        // Require POST method (uses Api::require_method)
        $this->api->require_method('POST');

        // Require JWT (uses Api::require_jwt)
        $auth = $this->api->require_jwt(); // returns decoded payload, e.g., ['sub' => 1, 'role' => 'admin']

        // Get JSON/form request body (uses Api::body)
        $input = $this->api->body();

        $to = $input['to'] ?? '';
        $subject = $input['subject'] ?? 'No subject';
        $message = $input['message'] ?? '';

        // Validate email input
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->api->respond_error('Invalid recipient email address', 400);
        }

        try {
            // Compose basic HTML body
            $html = "<p>{$message}</p>";

            // Send email using your MailerServices
            $this->mailer->send($to, $subject, $html);

            // Respond success
            $this->api->respond(['message' => 'Email sent successfully']);
        } catch (\Exception $e) {
            // Respond with error if sending fails
            $this->api->respond_error('Failed to send email: ' . $e->getMessage(), 500);
        }
    }
}
