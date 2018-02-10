<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_derp($diagnostic)
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header(array(), $render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'derp.html');
    $dom->getElementById('error-message')->setContent($diagnostic['error-message']);
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something{PHP_SELF2}{PHP_EXT}
    $return_link = $dom->getElementById('return-link')->extSetAttribute('href', nel_board_references(INPUT_BOARD_ID, 'directory') .
         '/' . PHP_SELF2 . PHP_EXT);
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
}

function nel_render_board_derp($board_id, $diagnostic)
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_board_header($board_id, array(), $render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'derp.html');
    $dom->getElementById('error-message')->setContent($diagnostic['error-message']);
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something{PHP_SELF2}{PHP_EXT}
    $return_link = $dom->getElementById('return-link')->extSetAttribute('href', nel_board_references(INPUT_BOARD_ID, 'directory') .
    '/' . PHP_SELF2 . PHP_EXT);
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_board_footer($board_id, $render);
    echo $render->outputRenderSet();
}
