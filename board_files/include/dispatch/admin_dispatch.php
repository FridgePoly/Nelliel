<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_admin_dispatch($dataforce)
{
    $authorize = nel_authorize();
    $manage = (isset($_GET['manage'])) ? $_GET['manage'] : null;
    $module = (isset($_GET['module'])) ? $_GET['module'] : null;
    $section = (isset($_GET['section'])) ? $_GET['section'] : null;
    $board_id = (isset($_GET['board_id'])) ? $_GET['board_id'] : null;
    $action = (isset($_POST['action'])) ? $_POST['action'] : null;
    nel_verify_login_or_session($manage, $action, $dataforce);

    if ($manage === 'login')
    {
        nel_login($dataforce);
    }
    else if ($manage === 'logout')
    {
        nel_initialize_session($manage, $action, $dataforce);
    }
    else if ($manage === 'general')
    {
        switch ($module)
        {
            case 'main-panel':
                nel_generate_main_panel();
                break;

            case 'staff':
                require_once INCLUDE_PATH . 'admin/staff_panel.php';
                nel_staff_panel($section, $action, $dataforce);
                break;

            case 'site-settings':
                require_once INCLUDE_PATH . 'admin/site_settings_panel.php';
                nel_site_settings_control($action, $dataforce);
                break;

            case 'create-board':
                require_once INCLUDE_PATH . 'output/management/create_board.php';
                nel_generate_create_board_panel();
                break;

            default:
                nel_login($dataforce);
                break;
        }
    }
    else if ($manage === 'board')
    {
        switch ($module)
        {
            case 'board-settings':
                require_once INCLUDE_PATH . 'admin/board_settings_panel.php';
                nel_board_settings_control($board_id, $action, $dataforce);
                break;

            case 'bans':
                require_once INCLUDE_PATH . 'admin/bans_panel.php';
                nel_ban_control($board_id, $action, $dataforce);
                break;

            case 'threads':
                require_once INCLUDE_PATH . 'admin/threads_panel.php';
                nel_thread_panel($board_id, $action, $dataforce, $authorize);
                break;

            case 'regen':
                if ($action === 'pages-all')
                {
                    nel_regen_all_pages($dataforce, $board_id);
                }

                if ($action === 'cache-all')
                {
                    nel_regen_cache($board_id, $dataforce);
                }

                nel_login($dataforce);
                break;

            case 'main-panel':
                nel_generate_main_board_panel($board_id);
                break;
        }
    }
    else
    {
        nel_derp(400, nel_stext('ERROR_400'));
    }
}
