<?php
class UserSettingsController extends Controller
{
    private $user_id; // Assume this is populated by middleware/token

    // GET /users/me/settings
    public function get_all()
    {
        $this->api->require_method('GET');
        
        $sql = "
            SELECT 
                setting_key, setting_value
            FROM 
                user_settings
            WHERE 
                user_id = ?
        ";

        $stmt = $this->db->raw($sql, [$this->user_id]);
        $settings_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert the list of key/value pairs into a single object/map for convenience
        $settings_map = [];
        foreach ($settings_list as $setting) {
            $settings_map[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $this->api->respond($settings_map);
    }

    // PUT /users/me/settings (Handles insert OR update for a single setting key)
    public function update()
    {
        $this->api->require_method('PUT');
        $input = $this->api->body();

        // Basic validation
        if (empty($input['setting_key']) || !isset($input['setting_value'])) {
            $this->api->respond_error('Missing setting_key or setting_value', 400);
        }

        $setting_key = $input['setting_key'];
        $setting_value = $input['setting_value'];
        
        // This query performs an UPSERT (Update or Insert) using MySQL's ON DUPLICATE KEY UPDATE.
        // This is possible because (user_id, setting_key) is the PRIMARY KEY.
        $sql = "
            INSERT INTO user_settings (user_id, setting_key, setting_value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value);
        ";

        try {
            $this->db->raw($sql, [$this->user_id, $setting_key, $setting_value]);
            $this->api->respond(['message' => "Setting '{$setting_key}' updated successfully"]);
        } catch (Exception $e) {
            $this->api->respond_error('Failed to update setting', 500);
        }
    }
}