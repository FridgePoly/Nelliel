<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'setup/sql-tables.php';

//
// First run - checks for database, directories
// If anything does not exist yet, create it
//
function setup_check()
{
    nel_create_posts_table(POST_TABLE);
    nel_create_posts_table(ARCHIVE_POST_TABLE);
    nel_create_threads_table(THREAD_TABLE);
    nel_create_threads_table(ARCHIVE_THREAD_TABLE);
    nel_create_files_table(FILE_TABLE);
    nel_create_files_table(ARCHIVE_FILE_TABLE);
    nel_create_external_table(EXTERNAL_TABLE);
    nel_create_external_table(ARCHIVE_EXTERNAL_TABLE);
    nel_create_bans_table(BAN_TABLE);
    nel_create_config_table(CONFIG_TABLE);
    nel_create_user_table(USER_TABLE);
    nel_create_roles_table(ROLES_TABLE);
    nel_create_permissions_table(PERMISSIONS_TABLE);
    nel_create_logins_table(LOGINS_TABLE);

    nel_create_directory(SRC_PATH, DIRECTORY_PERM);
    nel_create_directory(THUMB_PATH, DIRECTORY_PERM);
    nel_create_directory(PAGE_PATH, DIRECTORY_PERM);
    nel_create_directory(CACHE_PATH, DIRECTORY_PERM);
    nel_create_directory(ARCHIVE_PATH, DIRECTORY_PERM);
    nel_create_directory(ARC_SRC_PATH, DIRECTORY_PERM);
    nel_create_directory(ARC_THUMB_PATH, DIRECTORY_PERM);
    nel_create_directory(ARC_PAGE_PATH, DIRECTORY_PERM);

    nel_setup_stuff_done('check_done_nochange');

    if (STUFF_DONE === true)
    {
        // This should go to a log or something.
        //echo '<br><br>Process completed. If there are no errors listed above then you did it right. Please wait a few seconds and you will be taken to the front page.';
    }
}

function nel_setup_stuff_done($status)
{
    if(!defined('STUFF_DONE'))
    {
        if($status === 'check_done_nochange')
        {
            define('STUFF_DONE', false);
        }
        else if(!$status !== false )
        {
            define('STUFF_DONE', true);
        }
    }
}