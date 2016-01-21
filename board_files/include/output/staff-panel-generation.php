<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_staff_panel_add($dataforce, $auth)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render->parse('staff_panel_add.tpl', 'management');
    nel_render_footer($render, FALSE, FALSE, FALSE, FALSE, FALSE);
    $render->output(TRUE);
}

function nel_render_staff_panel_edit($dataforce, $auth)
{
    $render = new nel_render();
    array_walk($auth['perms'], create_function('&$item1', '$item1 = is_bool($item1) ? $item1 === TRUE ? "checked" : "" : $item1;'));
    $render->add_multiple_data($auth['perms']);
    nel_render_header($dataforce, $render, array());
    $render->parse('staff_panel_edit.tpl', 'management');
    nel_render_footer($render, FALSE, FALSE, FALSE, FALSE, FALSE);
    $render->output(TRUE);
}
?>