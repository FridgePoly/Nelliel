<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputThread extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array(), bool $data_only = false)
    {
        $render_data = array();
        $this->startTimer();
        $write = ($parameters['write']) ?? false;
        $thread_id = ($parameters['thread_id']) ?? 0;
        $dotdot = ($write) ? '../../../' : '';
        $command = ($parameters['command']) ?? 'view-thread';
        $thread_content_id = \Nelliel\ContentID::createIDString($thread_id);
        $render_data['form_action'] = $dotdot . MAIN_SCRIPT . '?module=threads&board_id=' . $this->domain->id();
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $thread_data = $this->database->executePreparedFetch($prepared, [$thread_id], PDO::FETCH_ASSOC);
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $render_data['header'] = $output_header->render(['header_type' => 'board', 'dotdot' => $dotdot], true);

        if (empty($thread_data))
        {
            return;
        }

        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
        $treeline = $this->database->executePreparedFetchAll($prepared, [$thread_id], PDO::FETCH_ASSOC);

        if (empty($treeline))
        {
            return;
        }

        $json_thread = new \Nelliel\API\JSON\JSONThread($this->domain, $this->file_handler);
        $json_thread->storeData($json_thread->prepareData($thread_data), 'thread');
        $json_content = new \Nelliel\API\JSON\JSONContent($this->domain, $this->file_handler);
        $json_instances = ['thread' => $json_thread, 'content' => $json_content];
        $post_counter = 0;
        $gen_data['index_rendering'] = false;
        $gen_data['abbreviate'] = false;
        $total_posts = $thread_data['post_count'];
        $render_data['abbreviate'] = false;
        $output_posting_form = new \Nelliel\Output\OutputPostingForm($this->domain);
        $render_data['body'] = $output_posting_form->render(['dotdot' => $dotdot, 'response_to' => $thread_id]);
        $output_post = new \Nelliel\Output\OutputPost($this->domain);
        $render_data['op_post'] = '';
        $render_data['thread_posts'] = '';
        $render_data['thread_id'] = $thread_content_id;
        $render_data['thread_expand_id'] = 'thread-expand-' . $thread_content_id;
        $render_data['thread_corral_id'] = 'thread-' . $thread_content_id;
        $render_data['board_id'] = $this->domain->id();
        $collapse_start = $total_posts - ($this->domain->setting('abbreviate_thread') - 1);

        foreach ($treeline as $post_data)
        {
            $json_post = new \Nelliel\API\JSON\JSONPost($this->domain, $this->file_handler);
            $json_instances['post'] = $json_post;
            $parameters = ['thread_data' => $thread_data, 'dotdot' => $dotdot, 'post_data' => $post_data,
                'gen_data' => $gen_data, 'json_instances' => $json_instances];
            $post_render = $output_post->render($parameters);

            if ($post_data['op'] == 1)
            {
                $render_data['op_post'] = $post_render;
            }
            else
            {
                $this->render_core->appendToOutput($post_render, 'expand');
                $render_data['thread_posts'] .= $post_render;

                if ($post_counter >= $collapse_start)
                {
                    $this->render_core->appendToOutput($post_render, 'collapse');
                }
            }

            $json_thread->addPostData($json_post->retrieveData());
            $post_counter ++;
        }

        $render_data['body'] .= $this->render_core->renderFromTemplateFile('thread/thread', $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot], true);
        $output = $this->output($render_data, 'page');

        if ($write)
        {
            $this->file_handler->writeFile(
                    $this->domain->reference('page_path') . $thread_id . '/thread-' . $thread_id . '.html', $output,
                    FILE_PERM, true);
            $this->file_handler->writeFile(
                    $this->domain->reference('page_path') . $thread_id . '/thread-' . $thread_id . '-expand.html',
                    $this->render_core->getOutput('expand'), FILE_PERM, true);
            $this->file_handler->writeFile(
                    $this->domain->reference('page_path') . $thread_id . '/thread-' . $thread_id . '-collapse.html',
                    $this->render_core->getOutput('collapse'), FILE_PERM, true);
            $json_thread->writeStoredData($this->domain->reference('page_path') . $thread_id . '/',
                    sprintf('thread-%d', $thread_id));
        }
        else
        {
            switch ($command)
            {
                case 'view-thread':
                    echo $output;
                    break;

                case 'expand-thread':
                    echo $this->render_core->getOutput('expand');
                    break;

                case 'collapse-thread':
                    echo $this->render_core->getOutput('collapse');
                    break;

                default:
                    echo $output;
            }

            nel_clean_exit();
        }
    }
}