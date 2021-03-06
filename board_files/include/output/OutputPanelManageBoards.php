<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelManageBoards extends OutputCore
{
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        if(!isset($parameters['section']))
        {
            return;
        }

        switch ($parameters['section'])
        {
            case 'panel':
                $this->panel($parameters);
                break;

            case 'remove_interstitial':
                $this->removeInterstitial($parameters);
                break;
        }
    }

    public function panel(array $parameters)
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_manage_boards_access'))
        {
            nel_derp(370, _gettext('You are not allowed to access the board manager panel.'));
        }

        $this->prepare('management/panels/manage_boards_panel_main.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Manage Boards')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $this->dom->getElementById('create-board-form')->extSetAttribute('action', MAIN_SCRIPT . '?module=manage-boards&action=add');
        $board_data = $this->database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '" ORDER BY "board_id" DESC',
                PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $board_info_table = $this->dom->getElementById('board-info-table');
        $board_info_row = $this->dom->getElementById('board-info-row');

        foreach ($board_data as $board_info)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $temp_board_info_row = $board_info_row->cloneNode(true);
            $temp_board_info_row->extSetAttribute('class', $bgclass);
            $board_nodes = $temp_board_info_row->getElementsByAttributeName('data-parse-id', true);
            $board_nodes['board-id']->setContent($board_info['board_id']);
            $board_nodes['board-directory']->setContent($board_info['board_id']);
            $board_nodes['db-prefix']->setContent($board_info['db_prefix']);

            if ($board_info['locked'] == 0)
            {
                $board_nodes['status']->setContent(_gettext('Active'));
                $board_nodes['link-board-lock']->extSetAttribute('href',
                        $this->url_constructor->dynamic(MAIN_SCRIPT,
                                ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'lock']));
                $board_nodes['link-board-lock']->setContent(_gettext('Lock Board'));
            }
            else
            {
                $board_nodes['status']->setContent(_gettext('Locked'));
                $board_nodes['link-board-lock']->extSetAttribute('href',
                        $this->url_constructor->dynamic(MAIN_SCRIPT,
                                ['module' => 'manage-boards', 'board_id' => $board_info['board_id'],
                                'action' => 'unlock']));
                                $board_nodes['link-board-lock']->setContent(_gettext('Unlock Board'));
            }

            $board_nodes['link-board-remove']->extSetAttribute('href',
                    $this->url_constructor->dynamic(MAIN_SCRIPT,
                            ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'remove']));
            $board_nodes['link-board-remove']->setContent(_gettext('!!DANGER!! Remove Board'));
            $board_info_table->appendChild($temp_board_info_row);
        }

        $board_info_row->remove();

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }

    public function removeInterstitial(array $parameters)
    {
        $this->prepare('management/interstitials/board_removal.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Confirm Board Deletion')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $this->dom->getElementById('message-text')->setContent($parameters['message']);
        $this->dom->getElementById('continue-link')->setContent($parameters['continue_link']['text']);
        $this->dom->getElementById('continue-link')->extSetAttribute('href', $parameters['continue_link']['href']);

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}