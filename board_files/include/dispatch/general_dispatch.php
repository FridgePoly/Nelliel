<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function general_dispatch($dataforce)
{
    switch ($dataforce['mode_segments'][1])
    {
        case 'bans':
            if ($dataforce['mode_segments'][2] === 'appeal')
            {
                nel_apply_ban($dataforce);
            }
            break;

        case 'post':
            if ($dataforce['mode_segments'][2] === 'new')
            {
                nel_process_new_post($dataforce);

                if (nel_fgsfds('noko'))
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' . PAGE_DIR . nel_fgsfds('noko_topic') . '/' . nel_fgsfds('noko_topic') . '.html">';
                }
                else
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' . INPUT_BOARD_ID . '/' . PHP_SELF2 . PHP_EXT . '">';
                }
            }

            nel_clean_exit($dataforce, true);
            break;

        case 'threads':
            if ($dataforce['mode_segments'][2] === 'update')
            {
                $updates = nel_thread_updates($dataforce);
                nel_regen_threads($dataforce, true, $updates);
                nel_regen_index($dataforce);
                nel_clean_exit($dataforce, false);
            }

            break;
    }
}