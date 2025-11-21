<?php
class CategoriesController extends Controller
{
    // GET /categories
    public function list()
    {
        $this->api->require_method('GET');
        $stmt = $this->db->raw('SELECT * FROM categories');
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->api->respond($categories);
    }

    // GET /categories/{id}
    public function get($category_id)
    {
        $this->api->require_method('GET');
        $stmt = $this->db->raw('SELECT * FROM categories WHERE id = ?', [$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($category) {
            $this->api->respond($category);
        } else {
            $this->api->respond_error('Category not found', 404);
        }
    }

    // POST /categories (Requires Admin/Auth check not shown here)
    public function create()
    {
        $this->api->require_method('POST');
        $input = $this->api->body();

        if (empty($input['name'])) {
            $this->api->respond_error('Name is required', 400);
        }

        $stmt = $this->db->raw(
            'INSERT INTO categories (name, description) VALUES (?, ?)', 
            [$input['name'], $input['description'] ?? null]
        );

        if ($stmt->rowCount() > 0) {
            $id = $this->db->lastInsertId();
            $this->api->respond(['id' => $id, 'message' => 'Category created successfully'], 201);
        } else {
            $this->api->respond_error('Failed to create category', 500);
        }
    }

    // PUT /categories/{id} (Requires Admin/Auth check not shown here)
    public function update($category_id)
    {
        $this->api->require_method('PUT');
        $input = $this->api->body();
        
        $data = [];
        $setClauses = [];
        $params = [];

        if (isset($input['name'])) {
            $setClauses[] = 'name = ?';
            $params[] = $input['name'];
        }
        if (isset($input['description'])) {
            $setClauses[] = 'description = ?';
            $params[] = $input['description'];
        }

        if (empty($setClauses)) {
            $this->api->respond_error('No data provided for update', 400);
        }

        $params[] = $category_id;

        $sql = 'UPDATE categories SET ' . implode(', ', $setClauses) . ' WHERE id = ?';
        $stmt = $this->db->raw($sql, $params);

        if ($stmt->rowCount() > 0) {
            $this->api->respond(['message' => 'Category updated successfully']);
        } else {
            $this->api->respond_error('Category not found or no changes made', 404);
        }
    }

    // DELETE /categories/{id} (Requires Admin/Auth check not shown here)
    public function delete($category_id)
    {
        $this->api->require_method('DELETE');

        // Check for related challenges before deleting (optional but good practice)
        $stmt = $this->db->raw('SELECT COUNT(*) as challenge_count FROM challenges WHERE category_id = ?', [$category_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $challenge_count = (int)$result['challenge_count'];
        
        if ($challenge_count > 0) {
            $this->api->respond_error('Cannot delete category with existing challenges', 409); // Conflict
        }
        
        $stmt = $this->db->raw('DELETE FROM categories WHERE id = ?', [$category_id]);
        $result = $stmt->rowCount();

        if ($result > 0) {
            $this->api->respond(['message' => 'Category deleted successfully']);
        } else {
            $this->api->respond_error('Category not found', 404);
        }
    }
}