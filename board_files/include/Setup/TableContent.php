<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableContent extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = '_content';
        $this->increment_column = 'entry';
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'parent_thread' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'post_ref' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'content_order' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'format' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'mime' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'filename' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'extension' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'display_width' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'display_height' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'preview_name' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'preview_extension' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'preview_width' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'preview_height' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'filesize' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'md5' => ['pdo_type' => PDO::PARAM_LOB, 'row_check' => false, 'auto_inc' => false],
            'sha1' => ['pdo_type' => PDO::PARAM_LOB, 'row_check' => false, 'auto_inc' => false],
            'sha256' => ['pdo_type' => PDO::PARAM_LOB, 'row_check' => false, 'auto_inc' => false],
            'sha512' => ['pdo_type' => PDO::PARAM_LOB, 'row_check' => false, 'auto_inc' => false],
            'source' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'license' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'alt_text' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'url' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'spoiler' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'nsf' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'exif' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'meta' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
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
            entry                 " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            parent_thread           INTEGER DEFAULT NULL,
            post_ref                INTEGER DEFAULT NULL,
            content_order           SMALLINT NOT NULL DEFAULT 0,
            type                    VARCHAR(255) NOT NULL,
            format                  VARCHAR(255) NOT NULL,
            mime                    VARCHAR(255) DEFAULT NULL,
            filename                VARCHAR(255) DEFAULT NULL,
            extension               VARCHAR(255) DEFAULT NULL,
            display_width           INTEGER DEFAULT NULL,
            display_height          INTEGER DEFAULT NULL,
            preview_name            VARCHAR(255) DEFAULT NULL,
            preview_extension       VARCHAR(255) DEFAULT NULL,
            preview_width           SMALLINT DEFAULT NULL,
            preview_height          SMALLINT DEFAULT NULL,
            filesize                INTEGER DEFAULT NULL,
            md5                     " . $this->sql_helpers->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            sha1                    " . $this->sql_helpers->sqlAlternatives('VARBINARY', '20') . " DEFAULT NULL,
            sha256                  " . $this->sql_helpers->sqlAlternatives('VARBINARY', '32') . " DEFAULT NULL,
            sha512                  " . $this->sql_helpers->sqlAlternatives('VARBINARY', '64') . " DEFAULT NULL,
            source                  VARCHAR(255) DEFAULT NULL,
            license                 VARCHAR(255) DEFAULT NULL,
            alt_text                VARCHAR(255) DEFAULT NULL,
            url                     VARCHAR(2048) DEFAULT NULL,
            spoiler                 SMALLINT DEFAULT NULL,
            nsf                     SMALLINT DEFAULT NULL,
            exif                    TEXT DEFAULT NULL,
            meta                    TEXT DEFAULT NULL,
            CONSTRAINT fk_post_ref_" . $other_tables['posts_table'] . "_post_number
            FOREIGN KEY(post_ref) REFERENCES " . $other_tables['posts_table'] . "(post_number)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        ;
    }
}