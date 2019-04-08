<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelRoles extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        if (!isset($parameters['section']))
        {
            return;
        }

        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_roles_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the bans panel.'));
        }

        switch ($parameters['section'])
        {
            case 'panel':
                $this->renderPanel($parameters);
                break;

            case 'edit':
                $this->renderEdit($parameters);
                break;
        }
    }

    private function renderPanel(array $parameters)
    {
        $user = $parameters['user'];

        $this->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Roles')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'manage_render' => true, 'extra_data' => $extra_data]));
        $roles = $this->database->executeFetchAll('SELECT * FROM "' . ROLES_TABLE . '"', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($roles as $role)
        {
            $role_data = array();
            $role_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $role_data['role_id'] = $role['role_id'];
            $role_data['role_level'] = $role['role_level'];
            $role_data['role_title'] = $role['role_title'];
            $role_data['capcode_text'] = $role['capcode_text'];
            $role_data['edit_url'] = MAIN_SCRIPT . '?module=roles&action=edit&role-id=' . $role['role_id'];
            $role_row_nodes['remove_url'] = MAIN_SCRIPT . '?module=roles&action=remove&role-id=' . $role['role_id'];
            $render_input['roles_list'][] = $role_data;
        }

        $render_input['new_role_url'] = MAIN_SCRIPT . '?module=roles&action=new';

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/roles_panel_main', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }

    private function renderEdit(array $parameters)
    {
        $user = $parameters['user'];
        $role_id = $parameters['role_id'];
        $authorization = new \Nelliel\Auth\Authorization($this->domain->database());
        $role = $authorization->getRole($role_id);

        $this->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Edit Role')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'manage_render' => true, 'extra_data' => $extra_data]));

        if (is_null($role_id))
        {
            $render_input['form_action'] = MAIN_SCRIPT . '?module=roles&action=add';
        }
        else
        {
            $render_input['form_action'] = MAIN_SCRIPT . '?module=roles&action=update&role-id=' . $role_id;
        }

        if (!is_null($role_id))
        {
            $render_input['role_id'] = $role->auth_data['role_id'];
            $render_input['role_level'] = $role->auth_data['role_level'];
            $render_input['role_title'] = $role->auth_data['role_title'];
            $render_input['capcode_text'] = $role->auth_data['capcode_text'];
        }

        $permissions_list = $this->database->executeFetchAll(
                'SELECT * FROM "' . PERMISSIONS_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_ASSOC);

        foreach ($permissions_list as $permission)
        {
            $permission_data = array();

            if (!is_null($role_id))
            {
                if ($role->checkPermission($permission['permission']))
                {
                    $permission_data['checked'] = 'checked';
                }
            }

            $permission_data['permission'] = $permission['permission'];
            $permission_data['label'] = '(' . $permission['permission'] . ') - ' . $permission['description'];
        }

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/roles_panel_edit', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}