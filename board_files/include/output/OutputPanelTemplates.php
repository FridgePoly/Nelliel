<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelTemplates extends OutputCore
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

        if (!$user->domainPermission($this->domain, 'perm_templates_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the templates panel.'));
        }

        $final_output = '';

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startRenderTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Templates')];
        $final_output .= $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('management/panels/templates_panel');
        $templates = $this->database->executeFetchAll(
                'SELECT * FROM "' . TEMPLATES_TABLE . '" ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($templates as $template)
        {
            $template_data = array();
            $template_info = json_decode($template['info'], true);
            $template_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $template['id'];
            $template_data['id'] = $template['id'];
            $template_data['name'] = $template_info['name'];
            $template_data['directory'] = $template_info['directory'];
            $template_data['output'] = $template_info['output_type'];
            $template_data['is_default'] = $template['is_default'] == 1;
            $template_data['default_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'make-default', 'template-id' => $template['id']]);
            $template_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'remove', 'template-id' => $template['id']]);
            $render_input['installed_list'][] = $template_data;
        }

        $ini_parser = new \Nelliel\INIParser($this->file_handler);
        $template_inis = $ini_parser->parseDirectories(TEMPLATES_FILE_PATH, 'template_info.ini');
        $bgclass = 'row1';

        foreach ($template_inis as $template)
        {
            $template_data = array();
            $template_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $template_data['id'] = $template['id'];
            $template_data['name'] = $template['name'];
            $template_data['directory'] = $template_info['directory'];
            $template_data['output'] = $template['output_type'];
            $template_data['is_installed'] = in_array($template['id'], $installed_ids);
            $template_data['install_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'templates', 'action' => 'add', 'template-id' => $template['id']]);
            $render_input['available_list'][] = $template_data;
        }

        $this->render_instance->appendHTML($render_instance->render('management/panels/templates_panel', $render_input));
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}