<?php

class Create_lesson_views
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
        // Step 1: Create lesson_views table
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ],
            'viewed_at' => [
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->_lava->dbforge->add_key('id', TRUE);
        $this->_lava->dbforge->create_table('lesson_views');

        // Step 2: Add foreign key (lesson_id -> lessons.id)
        $this->_lava->db->raw("
        ALTER TABLE lesson_views
        ADD CONSTRAINT fk_lesson_views_lesson 
        FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
    ");

        // Step 3: Create indexes
        $this->_lava->db->raw("
        ALTER TABLE lesson_views 
        ADD INDEX (lesson_id)
    ");

        $this->_lava->db->raw("
        ALTER TABLE lesson_views 
        ADD INDEX (viewed_at)
    ");
    }

    public function down()
    {
        // Drop the table
        $this->_lava->dbforge->drop_table('lesson_views');
    }
}
