<?php

class MigrationController extends Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function create_migration($migration_class)
    {
        // e.g. $migration_class = "create_users_table"
        $this->migration->create_migration($migration_class);
    }

    public function migrate()
    {
        $this->migration->migrate();
    }

    public function rollback()
    {
        $this->migration->rollback();
    }
}