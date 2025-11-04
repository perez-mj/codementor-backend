<?php

class Add_lesson_sections
{

    private $_lava;
    protected $dbforge;

    public function __construct()
    {
        $this->_lava = lava_instance();
        $this->_lava->call->dbforge();
    }

    public function up()
    {
        // Step 1: Create lesson_sections table
        $this->_lava->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],
            'lesson_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
            ],
            'subtitle' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => FALSE,
            ],
            'content' => [
                'type' => 'LONGTEXT',
                'null' => FALSE,
            ],
            'code_example' => [
                'type' => 'TEXT',
                'null' => TRUE,
            ],
            'example_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ],
            'order_index' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ],
        ]);

        $this->_lava->dbforge->add_key('id', TRUE);
        $this->_lava->dbforge->create_table('lesson_sections');

        // Step 2: Add foreign key (lesson_id -> lessons.id)
        $this->_lava->db->raw("
            ALTER TABLE lesson_sections
            ADD CONSTRAINT fk_section_lesson
            FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
        ");

        // Step 3: Migrate existing lessons
        $stmt = $this->_lava->db->raw("SELECT * FROM lessons");
        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($lessons as $lesson) {
            $this->_lava->db->raw("INSERT INTO lesson_sections
                (lesson_id, subtitle, content, code_example, example_id, order_index, created_at, updated_at)
                VALUES (?,?,?,?,?,?,NOW(),NOW())", [$lesson['id'], $lesson['title'], $lesson['content'] ?? '', $lesson['code_example'] ?? null, $lesson['example_id'] ?? null, $lesson['order_index'] ?? 0]);
        }
    }

    public function down()
    {
        $this->_lava->dbforge->drop_table('lesson_sections');
    }
}
