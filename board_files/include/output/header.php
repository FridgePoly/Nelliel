<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_board_header(\Nelliel\Domain $domain, $dotdot = null, $treeline = null)
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $site_domain = new \Nelliel\Domain('', new \Nelliel\CacheHandler(), $database);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'header.html');
    $dotdot = (!empty($dotdot)) ? $dotdot : '';
    $head_element = $dom->getElementsByTagName('head')->item(0);
    nel_build_header_styles($dom, $dotdot);
    $dom->getElementById('js-main-file')->extSetAttribute('src', $dotdot . SCRIPTS_WEB_PATH . 'nel.js');
    $dom->getElementById('js-onload')->setContent(
            'window.onload = function () {nelliel.setup.doImportantStuff(\'' . $domain->id() . '\', \'' .
            $session->inModmode($domain->id()) . '\');};');
    $dom->getElementById('js-style-set')->setContent('setStyle(nelliel.core.getCookie("style-' . $domain->id() . '"));');

    if ($domain->setting('use_honeypot'))
    {
        $honeypot_css = '#form-user-info-1{display: none !important;}#form-user-info-2{display: none !important;}#form-user-info-3{position: absolute; top: 3px; left: -9001px;}';
        $style_element = $dom->createElement('style', $honeypot_css);
        $dom->getElementsByTagName('head')->item(0)->appendChild($style_element);
    }

    $title_element = $head_element->getElementsByTagName('title')->item(0);
    $title_content = $domain->setting('board_name');

    if (isset($treeline[0]['subject']) && $treeline[0]['subject'] === '')
    {
        $title_content = $domain->setting('board_name') . ' > Thread #' . $treeline[0]['post_number'];
    }
    else
    {
        $title_content = $domain->setting('board_name') . ' > ' . $treeline[0]['subject'];
    }

    $title_element->setContent($title_content);
    $board_navigation = $dom->getElementById("board-navigation");
    $board_navigation->appendChild($dom->createTextNode('[ '));
    $board_data = $database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);
    $end = end($board_data);

    foreach ($board_data as $data)
    {
        $board_link = $dom->createElement('a');
        $board_link->extSetAttribute('class', 'board-navigation-link');
        $board_link->extSetAttribute('href', $dotdot . $data['board_directory']);
        $board_link->extSetAttribute('title', $domain->setting('board_name'));
        $board_link->setContent($data['board_directory']);
        $board_navigation->appendChild($board_link);

        if ($data !== $end)
        {
            $board_navigation->appendChild($dom->createTextNode(' / '));
        }
    }

    $board_navigation->appendChild($dom->createTextNode(' ]'));
    $board_banner = $dom->getElementById('top-board-banner');
    $favicon = $dom->getElementById('favicon-link');

    if($site_domain->setting('show_site_favicon') || $domain->setting('show_board_favicon'))
    {
        if ($site_domain->setting('show_site_favicon'))
        {
            $favicon->extSetAttribute('href', $site_domain->setting('site_favicon'));
        }

        if ($domain->setting('show_board_favicon'))
        {
            $favicon->extSetAttribute('href', $domain->setting('board_favicon'));
        }
    }
    else
    {
        $favicon->remove();
    }

    $site_banner = $dom->getElementById('top-site-banner');

    if ($site_domain->setting('show_site_banner'))
    {
        $site_banner->extSetAttribute('src', $site_domain->setting('site_banner'));
    }
    else
    {
        $site_banner->remove();
    }

    if ($domain->setting('show_board_banner'))
    {
        $board_banner->extSetAttribute('src', $domain->setting('board_banner'));
    }
    else
    {
        $board_banner->remove();
    }

    $board_title = $dom->getElementById('top-board-title');

    if ($domain->setting('show_board_title'))
    {
        $board_title->setContent($domain->setting('board_title'));
    }
    else
    {
        $board_title->remove();
    }

    $top_admin_span = $dom->getElementById('top-admin-span');
    $a_elements = $top_admin_span->getElementsByTagName('a');
    $a_elements->item(1)->extSetAttribute('href', nel_parameters_and_data()->siteSettings('home_page'));

    if ($session->isActive() && !$domain->renderActive())
    {
        $a_elements->item(2)->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?module=main-panel');
    }
    else
    {
        $a_elements->item(2)->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?module=login');
    }

    $a_elements->item(3)->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?about_nelliel');

    $dom->getElementById('manage-board-header')->remove();
    $dom->getElementById('manage-sub-header')->remove();

    if ($session->inModmode($domain->id()) && !$domain->renderActive())
    {
        $dom->getElementById('manage-header-text')->setContent(_gettext('Mod Mode'));
        $a_elements->item(0)->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?module=logout');
    }
    else
    {
        $a_elements->item(0)->parentNode->remove();
        $dom->getElementById('manage-header')->remove();
    }

    $translator->translateDom($dom, $domain->setting('language'));

    $domain->renderInstance()->appendHTMLFromDOM($dom);
}

function nel_render_general_header(\Nelliel\Domain $domain, $dotdot = null, $extra_data = array())
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $site_domain = new \Nelliel\Domain('', new \Nelliel\CacheHandler(), $database);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'header.html');
    $head_element = $dom->getElementsByTagName('head')->item(0);
    $dotdot = (!empty($dotdot)) ? $dotdot : '';
    nel_build_header_styles($dom, $dotdot);
    $dom->getElementById('js-main-file')->extSetAttribute('src', $dotdot . SCRIPTS_WEB_PATH . 'nel.js');
    $dom->getElementById('js-onload')->setContent(
            'window.onload = function () {nelliel.setup.doImportantStuff(\'' . $domain->id() . '\', \'' .
            $session->inModmode($domain->id()) . '\');};');
    $dom->getElementById('js-style-set')->setContent('setStyle(nelliel.core.getCookie("style-' . $domain->id() . '"));');
    $dom->getElementById('top-board-banner')->remove();
    $dom->getElementById('top-board-title')->remove();
    $dom->getElementById('top-site-banner')->remove();

    $favicon = $dom->getElementById('favicon-link');

    if($site_domain->setting('show_site_favicon') || $domain->setting('show_board_favicon'))
    {
        if ($site_domain->setting('show_site_favicon'))
        {
            $favicon->extSetAttribute('href', $site_domain->setting('site_favicon'));
        }

        if ($domain->setting('show_board_favicon'))
        {
            $favicon->extSetAttribute('href', $domain->setting('board_favicon'));
        }
    }
    else
    {
        $favicon->remove();
    }

    $title_element = $head_element->getElementsByTagName('title')->item(0);
    $title_element->setContent('Nelliel Imageboard');
    $top_admin_span = $dom->getElementById('top-admin-span');
    $a_elements = $top_admin_span->getElementsByTagName('a');
    $a_elements->item(1)->extSetAttribute('href', nel_parameters_and_data()->siteSettings('home_page'));

    if ($session->isActive())
    {
        $a_elements->item(2)->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?module=main-panel');
    }
    else
    {
        $a_elements->item(2)->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?module=login');
    }

    $a_elements->item(3)->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?about_nelliel');

    if (($session->isActive() || $session->inModmode($domain->id())))
    {
        if (isset($extra_data['header']))
        {
            $dom->getElementById('manage-header-text')->setContent($extra_data['header']);
        }

        if ($domain->id() !== '')
        {
            $board_data = _gettext('Current Board:') . ' ' . $domain->id();
            $dom->getElementById('manage-board-header-data')->setContent($board_data);
        }

        if (isset($extra_data['sub_header']))
        {
            $dom->getElementById('manage-sub-header-text')->setContent($extra_data['sub_header']);
        }

        $a_elements->item(0)->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?module=logout');
    }
    else
    {
        $top_admin_span->removeChild($a_elements->item(0)->parentNode);
        $dom->getElementById('manage-header')->remove();
        $dom->getElementById('manage-board-header')->remove();
        $dom->getElementById('manage-sub-header')->remove();
    }

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
}

function nel_build_header_styles($dom, $dotdot)
{
    $database = nel_database();
    $head_element = $dom->getElementsByTagName('head')->item(0);
    $top_styles_menu = $dom->getElementById('top-styles-menu');
    $styles = $database->executeFetchAll(
            'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC',
            PDO::FETCH_ASSOC);

    foreach ($styles as $style)
    {
        $info = json_decode($style['info'], true);
        $new_head_link = $dom->createElement('link');

        if ($style['is_default'])
        {
            $new_head_link->extSetAttribute('rel', 'stylesheet');
        }
        else
        {
            $new_head_link->extSetAttribute('rel', 'alternate stylesheet');
        }

        $new_head_link->extSetAttribute('data-parse-id', 'style-board');
        $new_head_link->extSetAttribute('data-id', $style['id']);
        $new_head_link->extSetAttribute('type', 'text/css');
        $new_head_link->extSetAttribute('href',
                $dotdot . STYLES_WEB_PATH . $info['directory'] . '/' . $info['main_file']);
        $new_head_link->extSetAttribute('title', $info['name']);
        $head_element->appendChild($new_head_link);

        $style_option = $dom->createElement('option', $info['name']);
        $style_option->extSetAttribute('data-command', 'change-style');
        $style_option->extSetAttribute('data-id', $style['id']);
        $style_option->extSetAttribute('value', $style['id']);
        $top_styles_menu->appendChild($style_option);
    }
}