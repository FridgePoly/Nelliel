<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/post.php';

function nel_thread_generator(\Nelliel\Domain $domain, $write, $thread_id, $command = null)
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $site_settings = nel_parameters_and_data()->siteSettings();
    $file_handler = new \Nelliel\FileHandler();
    $dotdot = ($write) ? '../../../' : '';
    $domain->renderInstance(new \Nelliel\RenderCore());
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'thread.html');
    $translator->translateDom($dom, $domain->setting('language'));
    $expand_dom = $domain->renderInstance()->newDOMDocument();
    $collapse_dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->startRenderTimer();
    $dom->getElementById('form-content-action')->extSetAttribute('action',
            $dotdot . PHP_SELF . '?module=threads&board_id=' . $domain->id());
    $prepared = $database->prepare('SELECT * FROM "' . $domain->reference('thread_table') . '" WHERE "thread_id" = ?');
    $gen_data['thread'] = $database->executePreparedFetch($prepared, [$thread_id], PDO::FETCH_ASSOC);
    $prepared = $database->prepare(
            'SELECT * FROM "' . $domain->reference('post_table') .
            '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
    $treeline = $database->executePreparedFetchAll($prepared, [$thread_id], PDO::FETCH_ASSOC);

    if (empty($treeline))
    {
        return;
    }

    $json_thread = new \Nelliel\API\JSON\JSONThread($domain, $file_handler);
    $json_thread->prepareData($gen_data['thread'], true);
    $json_post = new \Nelliel\API\JSON\JSONPost($domain, $file_handler);
    $json_content = new \Nelliel\API\JSON\JSONContent($domain, $file_handler);
    $post_counter = 0;
    $gen_data['posts_ending'] = false;
    $gen_data['index_rendering'] = false;
    $gen_data['abbreviate'] = false;
    $hr_added = false;
    $total_posts = $gen_data['thread']['post_count'];
    $abbreviate = $total_posts > $domain->setting('abbreviate_thread');

    while ($post_counter < $total_posts)
    {
        if (!isset($treeline[$post_counter]))
        {
            ++ $post_counter;
            continue;
        }

        $gen_data['post'] = $treeline[$post_counter];
        $json_post->prepareData($gen_data['post'], true);

        if ($post_counter === 0)
        {
            nel_render_board_header($domain, $dotdot, $treeline);
            nel_render_posting_form($domain, $thread_id, $dotdot);
        }

        if ($post_counter == $total_posts - 1)
        {
            $gen_data['posts_ending'] = true;
        }

        if ($gen_data['post']['has_file'] == 1)
        {
            $query = 'SELECT * FROM "' . $domain->reference('content_table') .
                    '" WHERE "post_ref" = ? ORDER BY "content_order" ASC';
            $prepared = $database->prepare($query);
            $gen_data['files'] = $database->executePreparedFetchAll($prepared, [$gen_data['post']['post_number']],
                    PDO::FETCH_ASSOC);

            foreach ($gen_data['files'] as $content_data)
            {
                $json_post->addContentData($json_content->prepareData($content_data));
            }
        }

        if ($post_counter === 99)
        {
            $render_temp = clone $domain->renderInstance();
            nel_render_insert_hr($dom);
            $hr_added = true;
            nel_render_general_footer($domain, $dotdot, true);
            $file_handler->writeFile(
                    $domain->reference('page_path') . $thread_id . '/thread-' . $thread_id . '-0-100.html',
                    $render_temp->outputRenderSet(), FILE_PERM, true);
            unset($rendertemp);
        }

        if ($gen_data['post']['op'] == 1)
        {
            $new_post_node = nel_render_post($domain, $gen_data, $dom);
            $expand_div = $dom->getElementById('thread-expand-cid_0_0_0');
            $expand_div->changeId(
                    'thread-expand-' . \Nelliel\ContentID::createIDString($gen_data['thread']['thread_id']));
            $omitted_element = $expand_div->getElementsByClassName('omitted-posts')->item(0);

            if ($abbreviate)
            {
                $omitted_element->firstChild->setContent($total_posts - $domain->setting('abbreviate_thread'));
            }
            else
            {
                $omitted_element->remove();
            }

            $import_node = $collapse_dom->importNode($expand_div->cloneNode(true), true);
            $collapse_dom->appendChild($import_node);
            $expand_div->remove();
        }
        else
        {
            $new_post_node = nel_render_post($domain, $gen_data, $dom);

            if ($abbreviate && $post_counter > $total_posts - $domain->setting('abbreviate_thread'))
            {
                $import_node = $collapse_dom->importNode($new_post_node, true);
                $collapse_dom->getElementById(
                        'thread-expand-' . \Nelliel\ContentID::createIDString($gen_data['thread']['thread_id']))->appendChild(
                        $import_node);
            }

            $import_node = $expand_dom->importNode($new_post_node, true);
            $expand_dom->appendChild($import_node);
        }

        $imported = $dom->importNode($new_post_node, true);
        $dom->getElementById('thread-cid_0_0_0')->appendChild($imported);
        ++ $post_counter;
        $json_thread->addPostData($json_post->retrieveData(true));
    }

    $dom->getElementById('post-id-cid_0_0_0')->remove();
    $dom->getElementById('thread-cid_0_0_0')->changeId('thread-cid_' . $thread_id . '_0_0');

    if (!$hr_added)
    {
        nel_render_insert_hr($dom);
    }

    nel_render_thread_form_bottom($domain, $dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    $domain->renderInstance()->appendHTMLFromDOM($collapse_dom, 'collapse');
    $domain->renderInstance()->appendHTMLFromDOM($expand_dom, 'expand');
    nel_render_general_footer($domain, $dotdot, true);

    if ($write)
    {
        $file_handler->writeFile($domain->reference('page_path') . $thread_id . '/thread-' . $thread_id . '.html',
                $domain->renderInstance()->outputRenderSet(), FILE_PERM, true);
        $file_handler->writeFile(
                $domain->reference('page_path') . $thread_id . '/thread-' . $thread_id . '-expand.html',
                $domain->renderInstance()->outputRenderSet('expand'), FILE_PERM, true);
        $file_handler->writeFile(
                $domain->reference('page_path') . $thread_id . '/thread-' . $thread_id . '-collapse.html',
                $domain->renderInstance()->outputRenderSet('collapse'), FILE_PERM, true);
        $json_thread->writeStoredData($domain->reference('page_path') . $thread_id . '/',
                sprintf('thread-%d', $thread_id));
    }
    else
    {

        switch ($command)
        {
            case 'view-thread':
                echo $domain->renderInstance()->outputRenderSet();
                break;

            case 'expand-thread':
                echo $domain->renderInstance()->outputRenderSet('expand');
                break;

            case 'collapse-thread':
                echo $domain->renderInstance()->outputRenderSet('collapse');
                break;

            default:
                echo $domain->renderInstance()->outputRenderSet();
        }

        nel_clean_exit();
    }
}
