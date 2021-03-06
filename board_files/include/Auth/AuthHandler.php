<?php

namespace Nelliel\Auth;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class AuthHandler
{
    public $database;
    public $auth_data = array();
    public $auth_id;

    public function authDataOrDefault($data_name, $default)
    {
        if (isset($this->auth_data[$data_name]))
        {
            return $this->auth_data[$data_name];
        }

        return $default;
    }

    public abstract function loadFromDatabase($temp_database = null);

    public abstract function writeToDatabase($temp_database = null);

    public abstract function setupNew();

    public abstract function remove();
}

