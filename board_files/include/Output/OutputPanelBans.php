<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelBans extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array(), bool $data_only = false)
    {
        if (!isset($parameters['section']))
        {
            return;
        }

        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_ban_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the bans panel.'));
        }

        switch ($parameters['section'])
        {
            case 'panel':
                $output = $this->renderPanel($parameters, $data_only);
                break;

            case 'add':
                $output = $this->renderAdd($parameters, $data_only);
                break;

            case 'modify':
                $output = $this->renderModify($parameters, $data_only);
                break;
        }

        return $output;
    }

    private function renderPanel(array $parameters, bool $data_only)
    {
        $render_data = array();
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $user = $parameters['user'];
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
        $render_data['can_modify'] = $user->domainPermission($this->domain, 'perm_ban_modify');

        if ($this->domain->id() !== '')
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . BANS_TABLE . '" WHERE "board_id" = ? ORDER BY "ban_id" DESC');
            $ban_list = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }
        else
        {
            $ban_list = $this->database->executeFetchAll('SELECT * FROM "' . BANS_TABLE . '" ORDER BY "ban_id" DESC',
                    PDO::FETCH_ASSOC);
        }

        $bgclass = 'row1';

        foreach ($ban_list as $ban_info)
        {
            $ban_data = array();
            $ban_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $ban_data['ban_id'] = $ban_info['ban_id'];
            $ban_data['type'] = $ban_info['type'];
            $ban_data['ip_address_start'] = $ban_info['ip_address_start'] ? @inet_ntop($ban_info['ip_address_start']) : 'Unknown';
            $ban_data['board_id'] = $ban_info['board_id'];
            $ban_data['reason'] = $ban_info['reason'];
            $ban_data['expiration'] = date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']);
            $ban_data['appeal'] = $ban_info['appeal'];
            $ban_data['appeal_response'] = $ban_info['appeal_response'];
            $ban_data['appeal_status'] = $ban_info['appeal_status'];
            $ban_data['modify_url'] = MAIN_SCRIPT . '?module=board&module=bans&action=modify&ban_id=' .
                    $ban_info['ban_id'] . '&board_id=' . $this->domain->id();
            $ban_data['remove_url'] = MAIN_SCRIPT . '?module=board&module=bans&action=remove&ban_id=' .
                    $ban_info['ban_id'] . '&board_id=' . $this->domain->id();
            $render_data['ban_list'][] = $ban_data;
        }

        $render_data['new_ban_url'] = MAIN_SCRIPT . '?module=board&module=bans&action=new&board_id=' .
                $this->domain->id();
        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/bans_panel_main',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }

    private function renderAdd(array $parameters, bool $data_only)
    {
        $render_data = array();
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Add Ban')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
        $render_data['ban_board'] = (!empty($this->domain->id())) ? $this->domain->id() : '';
        $ip = $parameters['ip'];
        $type = $parameters['type'];

        if ($type === 'POST' && isset($_GET['post-id']))
        {
            $render_data['is_post_ban'] = true;
            $post_param = '&post-id=' . $_GET['post-id'];
        }
        else
        {
            $post_param = '';
        }

        $render_data['form_action'] = MAIN_SCRIPT . '?module=board&module=bans&action=add&board_id=' .
                $this->domain->id() . $post_param;
        $render_data['ban_ip'] = $ip;
        $render_data['ban_type'] = $type;
        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/bans_panel_add',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }

    private function renderModify(array $parameters, bool $data_only)
    {
        $render_data = array();
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Modify Ban')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
        $render_data['form_action'] = MAIN_SCRIPT . '?module=board&module=bans&action=update&board_id=' .
                $this->domain->id();
        $ban_id = $_GET['ban_id'];
        $ban_hammer = new \Nelliel\BanHammer($this->database);
        $ban_info = $ban_hammer->getBanById($ban_id, true);
        $render_data['ban_id'] = $ban_info['ban_id'];
        $render_data['ip_address_start'] = inet_ntop($ban_info['ip_address_start']);
        $render_data['board_id'] = $ban_info['board_id'];
        $render_data['type'] = $ban_info['type'];
        $render_data['start_time_formatted'] = date("D F jS Y  H:i:s", $ban_info['start_time']);
        $render_data['expiration'] = date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']);
        $render_data['years'] = $ban_info['years'];
        $render_data['days'] = $ban_info['days'];
        $render_data['hours'] = $ban_info['hours'];
        $render_data['minutes'] = $ban_info['minutes'];
        $render_data['all_boards'] = ($ban_info['all_boards'] > 0) ? 'checked' : '';
        $render_data['start_time'] = $ban_info['start_time'];
        $render_data['reason'] = $ban_info['reason'];
        $render_data['creator'] = $ban_info['creator'];
        $render_data['appeal'] = $ban_info['appeal'];
        $render_data['appeal_response'] = $ban_info['appeal_response'];

        if ($ban_info['appeal_status'] == 0)
        {
            $render_data['status_unappealed'] = 'checked';
        }

        if ($ban_info['appeal_status'] == 1)
        {
            $render_data['status_appealed'] = 'checked';
        }

        if ($ban_info['appeal_status'] == 2)
        {
            $render_data['status_modified'] = 'checked';
        }

        if ($ban_info['appeal_status'] == 3)
        {
            $render_data['status_denied'] = 'checked';
        }

        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/bans_panel_modify',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }
}