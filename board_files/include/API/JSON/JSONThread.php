<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use \Nelliel\Domain;
use \Nelliel\FileHandler;

class JSONThread extends JSONOutput
{

    function __construct(Domain $domain, FileHandler $file_handler)
    {
        $this->domain = $domain;
        $this->file_handler = $file_handler;
        $this->data_array['thread'] = array();
    }

    public function prepareData(array $data)
    {
        $thread_array = array();
        $thread_array['thread_id'] = nel_cast_to_datatype($data['thread_id'], 'integer');
        $thread_array['first_post'] = nel_cast_to_datatype($data['first_post'], 'integer');
        $thread_array['last_post'] = nel_cast_to_datatype($data['last_post'], 'integer');
        $thread_array['last_bump_time'] = nel_cast_to_datatype($data['last_bump_time'], 'integer');
        $thread_array['last_bump_time_milli'] = nel_cast_to_datatype($data['last_bump_time_milli'], 'integer');
        $thread_array['last_update'] = nel_cast_to_datatype($data['last_update'], 'integer');
        $thread_array['last_update_milli'] = nel_cast_to_datatype($data['last_update_milli'], 'integer');
        $thread_array['post_count'] = nel_cast_to_datatype($data['post_count'], 'integer');
        $thread_array['total_files'] = nel_cast_to_datatype($data['total_files'], 'integer');
        $thread_array['thread_sage'] = nel_cast_to_datatype($data['thread_sage'], 'boolean');
        $thread_array['sticky'] = nel_cast_to_datatype($data['sticky'], 'boolean');
        $thread_array['locked'] = nel_cast_to_datatype($data['locked'], 'boolean');
        $this->addIfNotEmpty($thread_array, 'slug', $data['slug'], 'string');
        $thread_array = nel_plugins()->processHook('nel-json-prepare-thread', [$data], $thread_array);
        return $thread_array;
    }

    public function addPostData(array $post_data)
    {
        $this->data_array['post-list'][] = $post_data;
    }
}