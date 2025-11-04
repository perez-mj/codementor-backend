<?php
class ApiLanguageController extends Controller
{
    /**
     * GET /languages
     * Returns a list of all available programming languages.
     */
    public function list()
    {
        $stmt = $this->db->raw("
            SELECT 
                id,
                name,
                slug,
                description
            FROM languages
            ORDER BY name ASC
        ");

        $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($languages) {
            $this->api->respond($languages);
        } else {
            $this->api->respond_error('No languages found', 404);
        }
    }

    /**
     * GET /languages/{slug}
     * Returns detailed info for one language.
     */
    public function get($slug)
    {
        $stmt = $this->db->raw("
            SELECT id, name, slug, description
            FROM languages
            WHERE slug = ?
            LIMIT 1
        ", [$slug]);

        $language = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($language) {
            $this->api->respond($language);
        } else {
            $this->api->respond_error('Language not found', 404);
        }
    }
}
