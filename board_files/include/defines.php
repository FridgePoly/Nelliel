<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

define('SQLITE_DB_DEFAULT_PATH', BASE_PATH . '/' . BOARD_FILES); // Base SQLite DB location
define('FILES_PATH', BASE_PATH . '/' . BOARD_FILES); // Base files path
define('PLUGINS_PATH', BASE_PATH . '/' . BOARD_FILES . 'plugins/'); // Base cache path
define('TEMPLATE_PATH', BASE_PATH . '/' . BOARD_FILES . 'templates/nelliel/'); // Base template path
define('LANGUAGE_PATH', BASE_PATH . '/' . BOARD_FILES . 'languages/'); // Language files path
define('LIBRARY_PATH', BASE_PATH . '/' . BOARD_FILES . 'libraries/'); // Libraries path
define('CSSDIR', BOARD_FILES . 'css/'); // location of the css files
define('JSDIR', BOARD_FILES . 'js/'); // location of the javascript files
define('CACHE_DIR', 'cache/'); // Cache directory, only used internally by Nelliel
define('CACHE_PATH', FILES_PATH . CACHE_DIR); // Base cache path
define('SRC_PATH', BASE_PATH . '/' . SRC_DIR); // Base src path
define('THUMB_PATH', BASE_PATH . '/' . THUMB_DIR); // Base thumbnail path
define('PAGE_PATH', BASE_PATH . '/' . PAGE_DIR); // Base page path
define('ARCHIVE_PATH', BASE_PATH . '/' . ARCHIVE_DIR); // Base archive path
define('ARC_SRC_PATH', BASE_PATH . '/' . ARCHIVE_DIR . SRC_DIR); // Archive src path
define('ARC_THUMB_PATH', BASE_PATH . '/' . ARCHIVE_DIR . THUMB_DIR); // Archive thumbnail path
define('ARC_PAGE_PATH', BASE_PATH . '/' . ARCHIVE_DIR . PAGE_DIR); // Archive page path

define('POST_TABLE', TABLEPREFIX . '_posts'); // Table used for post data
define('THREAD_TABLE', TABLEPREFIX . '_threads'); // Table used for thread data
define('FILE_TABLE', TABLEPREFIX . '_files'); // Table used for file data
define('EXTERNAL_TABLE', TABLEPREFIX . '_external'); // Table used for external content
define('ARCHIVE_POST_TABLE', TABLEPREFIX . '_archive_posts'); // Stores archived threads
define('ARCHIVE_THREAD_TABLE', TABLEPREFIX . '_archive_threads'); // Stores archived thread data
define('ARCHIVE_FILE_TABLE', TABLEPREFIX . '_archive_files'); // Stores archived file data
define('ARCHIVE_EXTERNAL_TABLE', TABLEPREFIX . '_archive_external'); // Stores archived external content
define('CONFIG_TABLE', TABLEPREFIX . '_config'); // Table to store board configuration. Best to leave it as-is unless you really need to change it
define('BAN_TABLE', 'nelliel_bans'); // Table containing ban info
define('USER_TABLE', 'nelliel_users'); // Table used for post data
define('ROLES_TABLE', 'nelliel_roles'); // Table used for post data
define('USER_ROLE_TABLE', 'nelliel_user_role'); // Table used for post data
define('PERMISSIONS_TABLE', 'nelliel_permissions'); // Table used for post data
define('LOGINS_TABLE', 'nelliel_login_attempts'); // Table used for post data