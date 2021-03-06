<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableThreads extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = '_threads';
        $this->columns_data = [
            'thread_id' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'first_post' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'last_post' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'last_bump_time' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'last_bump_time_milli' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'last_update' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'last_update_milli' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'post_count' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'total_files' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'thread_sage' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'sticky' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'archive_status' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'locked' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'slug' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function setup()
    {
        ;
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_helpers->autoincrementColumn('INTEGER');
        $options = $this->sql_helpers->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            thread_id                   INTEGER NOT NULL PRIMARY KEY,
            first_post                  INTEGER DEFAULT NULL,
            last_post                   INTEGER DEFAULT NULL,
            last_bump_time              BIGINT DEFAULT NULL,
            last_bump_time_milli        SMALLINT DEFAULT NULL,
            last_update                 BIGINT NOT NULL,
            last_update_milli           SMALLINT NOT NULL,
            post_count                  INTEGER NOT NULL DEFAULT 0,
            total_files                 INTEGER NOT NULL DEFAULT 0,
            thread_sage                 SMALLINT NOT NULL DEFAULT 0,
            sticky                      SMALLINT NOT NULL DEFAULT 0,
            archive_status              SMALLINT NOT NULL DEFAULT 0,
            locked                      SMALLINT NOT NULL DEFAULT 0,
            slug                        VARCHAR(255) DEFAULT NULL
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        ;
    }
}