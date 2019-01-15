<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableRoles extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = ROLES_TABLE;
        $this->columns = ['role_id', 'role_level', 'role_title', 'capcode_text'];
        $this->pdo_types = [PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR];
    }

    public function createTable()
    {
        $auto_inc = $this->sql_helpers->autoincrementColumn('INTEGER');
        $options = $this->sql_helpers->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            role_id                 VARCHAR(255) NOT NULL,
            role_level              SMALLINT NOT NULL DEFAULT 0,
            role_title              VARCHAR(255) DEFAULT NULL,
            capcode_text            TEXT DEFAULT NULL
        ) " . $options . ";";

        $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['SUPER_ADMIN', 1000, 'Site Administrator', '## Site Administrator ##']);
        $this->insertDefaultRow(['BOARD_ADMIN', 100, 'Board Administrator', '## Board Administrator ##']);
        $this->insertDefaultRow(['MOD', 50, 'Moderator', '## Moderator ##']);
        $this->insertDefaultRow(['JANITOR', 10, 'Janitor', '## Janitor ##']);
    }
}