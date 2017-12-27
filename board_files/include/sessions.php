<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_session_settings()
{
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    //ini_set('session.cookie_secure', 1); // TODO: Use this once https properly supported
}

//
// Regenerate session (Swiper no swiping!)
//
function nel_regen_session()
{
    if (hash_equals(session_id(), $_COOKIE['PHPSESSID']) && !nel_session_is_old())
    {
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
        $_SESSION['ignores'] = array('default' => false);
    }
    else
    {
        nel_terminate_session();
        nel_derp(105, nel_stext('ERROR_105'));
    }

    nel_set_session_cookie();
}

//
// Check for existing session and process
// If no session exists, confirm login info and set up a new one
//
function nel_initialize_session($dataforce)
{
    $authorize = nel_authorize();
    session_start();

    if (!empty($_SESSION) && !nel_session_is_old())
    {
        if (isset($dataforce['get_mode']))
        {
            if ($dataforce['get_mode'] === 'log_out')
            {
                nel_terminate_session();
                nel_clean_exit($dataforce);
            }
            else if ($dataforce['get_mode'] === 'admin')
            {
                nel_login($dataforce);
            }
        }
    }
    else
    {
        if (isset($dataforce['mode']) && $dataforce['mode'] === 'admin->login')
        {
            if($dataforce['login_valid'])
            {
                $_SESSION['ignores'] = array('default' => false);
                $_SESSION['active'] = true;
                $_SESSION['username'] = $dataforce['username'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
            }
            else
            {
                nel_terminate_session();
                nel_derp(107, nel_stext('ERROR_107'));
            }

            nel_set_session_cookie();
            nel_login($dataforce);
        }
        else
        {
            nel_terminate_session();
            nel_login($dataforce);
        }
    }
}

function nel_terminate_session()
{
    session_unset();
    session_destroy();
    setcookie("PHPSESSID", "", time() - 3600, "/");
}

function nel_set_session_cookie()
{
    setcookie(session_name(), session_id(), 0, '/', '; HttpOnly');
}

function nel_session_is_old()
{
    return !isset($_SESSION['login_time']) || (time() - $_SESSION['last_activity']) > 1800;
}

function nel_session_is_active()
{
    return !empty($_SESSION) && $_SESSION['active'];
}

function nel_session_is_ignored($reason = 'default', $value = null)
{
    if (!nel_session_is_active())
    {
        return true;
    }

    if(!is_null($value))
    {
        $_SESSION['ignores'][$reason] = $value;
    }

    return isset($_SESSION['ignores'][$reason]) && $_SESSION['ignores'][$reason];
}
