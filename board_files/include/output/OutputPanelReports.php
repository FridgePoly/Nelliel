<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelReports extends OutputCore
{
    private $database;

    function __construct(Domain $domain)
    {
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_reports_access'))
        {
            nel_derp(380, _gettext('You are not allowed to access the reports panel.'));
        }

        $this->prepare('management/panels/reports_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Reports')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);

        if ($this->domain->id() !== '')
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . REPORTS_TABLE . '" WHERE "board_id" = ? ORDER BY "report_id" DESC');
            $report_list = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }
        else
        {
            $report_list = $this->database->executeFetchAll('SELECT * FROM "' . REPORTS_TABLE . '" ORDER BY "report_id" DESC',
                    PDO::FETCH_ASSOC);
        }

        $report_info_table = $this->dom->getElementById('report-info-table');
        $report_info_row = $this->dom->getElementById('report-info-row');
        $bgclass = 'row1';
        $domains = array();

        foreach ($report_list as $report_info)
        {
            if (!isset($domains[$report_info['board_id']]))
            {
                $domains[$report_info['board_id']] = new \Nelliel\DomainBoard($report_info['board_id'], $this->database);
            }

            $current_domain = $domains[$report_info['board_id']];
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $temp_report_info_row = $report_info_row->cloneNode(true);
            $temp_report_info_row->extSetAttribute('class', $bgclass);
            $report_nodes = $temp_report_info_row->getElementsByAttributeName('data-parse-id', true);
            $content_id = new \Nelliel\ContentID($report_info['content_id']);
            $base_domain = BASE_DOMAIN . pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
            $board_web_path = '//' . $base_domain . '/' . rawurlencode($current_domain->reference('board_directory')) . '/';
            $content_link = '';

            if ($content_id->isThread())
            {
                $content_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'render', 'action' => 'view-thread', 'thread' => $content_id->thread_id,
                        'content-id' => $content_id->getIDString(), 'board_id' => $report_info['board_id'],
                        'modmode' => 'true']);
                        $report_nodes['link-file-url']->remove();
            }
            else if ($content_id->isPost())
            {
                $content_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'render', 'action' => 'view-thread', 'thread' => $content_id->thread_id,
                        'content-id' => $content_id->getIDString(), 'board_id' => $report_info['board_id'],
                        'modmode' => 'true']);
                        $content_link .= '#t' . $content_id->thread_id . 'p' . $content_id->post_id;
                        $report_nodes['link-file-url']->remove();
            }
            else if ($content_id->isContent())
            {
                $prepared = $this->database->prepare(
                        'SELECT "filename" FROM "' . $current_domain->reference('content_table') .
                        '" WHERE "parent_thread" = ? AND post_ref = ? AND "content_order" = ?');
                $filename = $this->database->executePreparedFetch($prepared,
                        [$content_id->thread_id, $content_id->post_id, $content_id->order_id], PDO::FETCH_COLUMN);
                $src_web_path = $board_web_path . rawurlencode($current_domain->reference('src_dir')) . '/';
                $file_link = $src_web_path . $content_id->thread_id . '/' . $content_id->post_id . '/' .
                        rawurlencode($filename);
                        $report_nodes['link-file-url']->extSetAttribute('href', $file_link);

                        $content_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                                ['module' => 'render', 'action' => 'view-thread', 'thread' => $content_id->thread_id,
                                'content-id' => $content_id->getIDString(), 'board_id' => $report_info['board_id'],
                                'modmode' => 'true']);
                                $content_link .= '#t' . $content_id->thread_id . 'p' . $content_id->post_id;
            }

            $report_nodes['report-id']->setContent($report_info['report_id']);
            $report_nodes['board-id']->setContent($report_info['board_id']);
            $report_nodes['link-content-url']->setContent($report_info['content_id']);
            $report_nodes['link-content-url']->extSetAttribute('href', $content_link);
            $report_nodes['report-reason']->setContent($report_info['reason']);
            $report_nodes['reporter-ip']->setContent(@inet_ntop($report_info['reporter_ip']));
            $report_nodes['link-report-dismiss']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=reports&board_id=' . $report_info['board_id'] . '&action=dismiss&report_id=' .
                    $report_info['report_id']);
            $report_info_table->appendChild($temp_report_info_row);
        }

        $report_info_row->remove();

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}