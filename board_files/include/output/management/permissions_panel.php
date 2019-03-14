<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_permissions_panel($user, \Nelliel\Domain $domain)
{
    if (!$user->domainPermission($domain, 'perm_permissions_access'))
    {
        nel_derp(450, _gettext('You are not allowed to access the Permissions panel.'));
    }

    $database = $domain->database();
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Permissions')];
    $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/permissions_panel.html');
    $permissions = $database->executeFetchAll('SELECT * FROM "' . PERMISSIONS_TABLE . '" ORDER BY "entry" ASC',
            PDO::FETCH_ASSOC);
    $form_action = $url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'permissions', 'action' => 'add']);
    $dom->getElementById('add-permission-form')->extSetAttribute('action', $form_action);

    $permission_list = $dom->getElementById('permission-list');
    $permission_list_nodes = $permission_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($permissions as $permission)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $permission_row = $dom->copyNode($permission_list_nodes['permission-row'], $permission_list, 'append');
        $permission_row_nodes = $permission_row->getElementsByAttributeName('data-parse-id', true);
        $permission_row->extSetAttribute('class', $bgclass);
        $permission_row_nodes['permission']->setContent($permission['permission']);
        $permission_row_nodes['description']->setContent($permission['description']);
        $remove_link = $url_constructor->dynamic(MAIN_SCRIPT,
                ['module' => 'permissions', 'action' => 'remove', 'permission' => $permission['permission']]);
        $permission_row_nodes['permission-remove-link']->extSetAttribute('href', $remove_link);
    }

    $permission_list_nodes['permission-row']->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}