<?php
require_once INCLUDE_PATH . 'output/rules.php';

function nel_render_posting_form($board_id, $render, $response_to, $dotdot = null)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $language = new \Nelliel\Language\Language($authorization);
    $session = new \Nelliel\Sessions($authorization);
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $board_settings = nel_parameters_and_data()->boardSettings($board_id);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'posting_form.html');
    $dotdot = (!empty($dotdot)) ? $dotdot : '';
    $url_constructor = new \Nelliel\URLConstructor();
    $post_form_return_link = $dom->getElementById('post-form-return-link');
    $posting_form = $dom->getElementById('posting-form');
    $posting_form->extSetAttribute('action', $dotdot . PHP_SELF . '?module=threads&area=general&action=new-post&board_id=' . $board_id);
    $dom->getElementById('board_id_field_post_form')->extSetAttribute('value', $board_id);

    if ($response_to)
    {
        if ($session->inModmode($board_id))
        {
            $return_url = $url_constructor->dynamic(PHP_SELF, ['module' => 'render', 'action' => 'view-index', 'section' => '0', 'board_id' => $board_id, 'modmode' => 'true']);
        }
        else
        {
            $return_url = $dotdot . $references['board_directory'] . '/' . PHP_SELF2 . PHP_EXT;
        }

        $dom->getElementById('return-url')->extSetAttribute('href', $return_url);
    }
    else
    {
        $post_form_return_link->remove();
    }

    $new_post_element = $posting_form->doXPathQuery(".//input[@name='new_post[post_info][response_to]']", $posting_form)->item(0);
    $new_post_element->extSetAttribute('value', $response_to);

    if(!$session->inModmode($board_id))
    {
        $dom->getElementById('posting-form-staff')->remove();
    }

    $dom->getElementById('not-anonymous')->extSetAttribute('maxlength', $board_settings['max_name_length']);
    $dom->getElementById('spam-target')->extSetAttribute('maxlength', $board_settings['max_email_length']);
    $dom->getElementById('verb')->extSetAttribute('maxlength', $board_settings['max_subject_length']);

    if ($board_settings['force_anonymous'])
    {
        $dom->getElementById('form-not-anonymous');
        $dom->getElementById('form-spam-target');
    }

    // File Block
    $file_block = $dom->getElementById('form-file-1');
    $source_block = $dom->getElementById('form-sauce-1');
    $license_block = $dom->getElementById('form-lol_drama-1');
    $alt_text_block = $dom->getElementById('form-alt_text-1');
    $posting_form_table = $dom->getElementById('posting-form-table');
    $source_block->getElementById('sauce-1')->extSetAttribute('maxlength', $board_settings['max_source_length']);
    $license_block->getElementById('lol_drama-1')->extSetAttribute('maxlength', $board_settings['max_license_length']);

    if ($board_settings['allow_multifile'] && $board_settings['max_post_files'] > 1)
    {
        for ($i = 2, $j = 3; $i <= $board_settings['max_post_files']; ++ $i, ++ $j)
        {
            if (!$response_to && !$board_settings['allow_op_multifile'])
            {
                break;
            }

            $temp_file_block = $file_block->cloneNode(true);
            $temp_file_block->changeId('form-file-' . $i);
            $temp_file_block->modifyAttribute('class', ' hidden', 'after');
            $temp_source_block = $source_block->cloneNode(true);
            $temp_source_block->changeId('form-sauce-' . $i);
            $temp_license_block = $license_block->cloneNode(true);
            $temp_license_block->changeId('form-lol_drama-' . $i);
            $temp_alt_text_block = $alt_text_block->cloneNode(true);
            $temp_alt_text_block->changeId('form-alt_text-' . $i);
            $insert_before_point = $dom->getElementById('form-fgsfds');
            $posting_form_table->insertBefore($temp_file_block, $insert_before_point);
            $posting_form_table->insertBefore($temp_source_block, $insert_before_point);
            $posting_form_table->insertBefore($temp_license_block, $insert_before_point);
            $posting_form_table->insertBefore($temp_alt_text_block, $insert_before_point);

            $for_label_file = $temp_file_block->doXPathQuery(".//label[@for='up-file-1']")->item(0);
            $for_label_file->extSetAttribute('for', 'up-file-' . $i);
            $file_num = $temp_file_block->getElementById('file-num-1');
            $file_num->setContent($i);
            $file_num->changeId('file-num-' . $i);
            $up_file_element = $temp_file_block->getElementById('up-file-1');
            $up_file_element->extSetAttribute('name', 'up_file_' . $i);
            $up_file_element->changeId('up-file-' . $i);
            $add_source_element = $temp_file_block->getElementById('add-sauce-1');
            $add_source_element->changeId('add-sauce-' . $i);
            $add_license_element = $temp_file_block->getElementById('add-lol_drama-1');
            $add_license_element->changeId('add-lol_drama-' . $i);
            $add_alt_text_element = $temp_file_block->getElementById('add-alt_text-1');
            $add_alt_text_element->changeId('add-alt_text-' . $i);

            $for_label_sauce = $temp_source_block->doXPathQuery(".//label[@for='sauce-1']")->item(0);
            $for_label_sauce->extSetAttribute('for', 'sauce-' . $i);
            $source_element = $temp_source_block->getElementById('sauce-1');
            $source_element->extSetAttribute('name', 'new_post[file_info][up_file_' . $i . '][sauce]');
            $source_element->extSetAttribute('maxlength', $board_settings['max_source_length']);
            $source_element->changeId('sauce-' . $i);

            $for_label_license = $temp_license_block->doXPathQuery(".//label[@for='lol_drama-1']")->item(0);
            $for_label_license->extSetAttribute('for', 'lol_drama-' . $i);
            $license_element = $temp_license_block->getElementById('lol_drama-1');
            $license_element->extSetAttribute('name', 'new_post[file_info][up_file_' . $i . '][lol_drama]');
            $license_element->extSetAttribute('maxlength', $board_settings['max_license_length']);
            $license_element->changeId('lol_drama-' . $i);

            $for_label_alt_text = $temp_alt_text_block->doXPathQuery(".//label[@for='alt_text-1']")->item(0);
            $for_label_alt_text->extSetAttribute('for', 'alt_text-' . $i);
            $alt_text_element = $temp_alt_text_block->getElementById('alt_text-1');
            $alt_text_element->extSetAttribute('name', 'new_post[file_info][up_file_' . $i . '][alt_text]');
            $alt_text_element->extSetAttribute('maxlength', '255');
            $alt_text_element->changeId('alt_text-' . $i);
        }
    }

    $fgsfds_form = $dom->getElementById('form-fgsfds');

    if (!$board_settings['use_fgsfds'])
    {
        $dom->removeChild($fgsfds_form);
    }
    else
    {
        $fgsfds_label = $fgsfds_form->doXPathQuery(".//label[@for='fgsfds']")->item(0);
        $fgsfds_label->setContent($board_settings['fgsfds_name']);
    }

    if ($response_to)
    {
        $dom->getElementById('which-post-mode')->setContent('Posting mode: Reply');
    }

    $rules = $dom->importNode(nel_render_rules_list($board_id), true);
    $dom->getElementById('form-rules-list')->appendChild($rules);

    if (!$board_settings['use_spambot_trap'])
    {
        $dom->removeChild($dom->getElementById('form-trap1'));
        $dom->removeChild($dom->getElementById('form-trap2'));
    }

    $language->i18nDom($dom, nel_parameters_and_data()->boardSettings($board_id, 'board_language'));
    $render->appendHTMLFromDOM($dom);
}