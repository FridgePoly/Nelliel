<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelIconSets extends OutputCore
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

        if (!$user->domainPermission($this->domain, 'perm_icon_sets_access'))
        {
            nel_derp(460, _gettext('You are not allowed to access the Icon Sets panel.'));
        }

        $this->prepare('management/panels/icon_sets_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Icon Sets')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $ini_parser = new \Nelliel\INIParser($this->file_handler);
        $icon_set_inis = $ini_parser->parseDirectories(ICON_SETS_WEB_PATH, 'icon_set_info.ini');
        $icon_sets = $this->database->executeFetchAll(
                'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'icon-set\' ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
        $installed_ids = array();
        $installed_icon_set_list = $this->dom->getElementById('installed-icon-set-list');
        $installed_icon_set_list_nodes = $installed_icon_set_list->getElementsByAttributeName('data-parse-id', true);
        $bgclass = 'row1';

        foreach ($icon_sets as $icon_set)
        {
            $icon_set_info = json_decode($icon_set['info'], true);
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $icon_set['id'];
            $icon_set_row = $this->dom->copyNode($installed_icon_set_list_nodes['icon-set-row'], $installed_icon_set_list,
                    'append');
            $icon_set_row->extSetAttribute('class', $bgclass);
            $icon_set_row_nodes = $icon_set_row->getElementsByAttributeName('data-parse-id', true);
            $icon_set_row_nodes['id']->setContent($icon_set['id']);
            $icon_set_row_nodes['set_type']->setContent(strtoupper($icon_set_info['set_type']));
            $icon_set_row_nodes['name']->setContent($icon_set_info['name']);
            $icon_set_row_nodes['directory']->setContent($icon_set_info['directory']);

            if ($icon_set['is_default'] == 1)
            {
                $icon_set_row_nodes['default-link']->remove();
                $icon_set_row_nodes['remove-link']->remove();
                $icon_set_row_nodes['action-1']->setContent(_gettext('Default Icon Set'));
            }
            else
            {
                $default_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'icon-sets', 'action' => 'make-default', 'icon-set-id' => $icon_set['id'],
                        'set-type' => $icon_set_info['set_type']]);
                        $icon_set_row_nodes['default-link']->extSetAttribute('href', $default_link);
                        $remove_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                                ['module' => 'icon-sets', 'action' => 'remove', 'icon-set-id' => $icon_set['id'],
                                'set-type' => $icon_set_info['set_type']]);
                                $icon_set_row_nodes['remove-link']->extSetAttribute('href', $remove_link);
            }
        }

        $installed_icon_set_list_nodes['icon-set-row']->remove();

        $available_icon_set_list = $this->dom->getElementById('available-icon-set-list');
        $available_icon_set_list_nodes = $available_icon_set_list->getElementsByAttributeName('data-parse-id', true);
        $bgclass = 'row1';

        foreach ($icon_set_inis as $icon_set)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $icon_set_row = $this->dom->copyNode($available_icon_set_list_nodes['icon-set-row'], $available_icon_set_list,
                    'append');
            $icon_set_row->extSetAttribute('class', $bgclass);
            $icon_set_row_nodes = $icon_set_row->getElementsByAttributeName('data-parse-id', true);
            $icon_set_row_nodes['id']->setContent($icon_set['id']);
            $icon_set_row_nodes['set_type']->setContent(strtoupper($icon_set['set_type']));
            $icon_set_row_nodes['name']->setContent($icon_set['name']);
            $icon_set_row_nodes['directory']->setContent($icon_set['directory']);

            if (in_array($icon_set['id'], $installed_ids))
            {
                $icon_set_row_nodes['install-link']->remove();
                $icon_set_row_nodes['action-1']->setContent(_gettext('Icon Set Installed'));
            }
            else
            {
                $install_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'icon-sets', 'action' => 'add', 'icon-set-id' => $icon_set['id'],
                        'set-type' => $icon_set['set_type']]);
                        $icon_set_row_nodes['install-link']->extSetAttribute('href', $install_link);
            }
        }

        $available_icon_set_list_nodes['icon-set-row']->remove();
        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}