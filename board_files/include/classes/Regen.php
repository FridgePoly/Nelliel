<?php

namespace Nelliel;

use PDO;
use Nelliel\Language\Translator;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Regen
{
    private $database;

    function __construct()
    {
        $this->database = nel_database();
    }

    private function getTemporaryDomainBoard(string $domain_id)
    {
        $domain = new DomainBoard($domain_id, $this->database);
        $domain->renderInstance(new RenderCore());
        return $domain;
    }

    private function getTemporaryDomainSite()
    {
        $domain = new DomainSite($this->database);
        $domain->renderInstance(new RenderCore());
        return $domain;
    }

    public function threads(Domain $domain, bool $write, array $ids)
    {
        require_once INCLUDE_PATH . 'output/thread_generation.php';
        $threads = count($ids);
        $i = 0;

        while ($i < $threads)
        {
            $temp_domain = $this->getTemporaryDomainBoard($domain->id());
            $temp_domain->renderActive(true);
            nel_thread_generator($temp_domain, $write, $ids[$i]);
            ++ $i;
        }
    }

    public function boardCache(Domain $domain)
    {
        $domain->regenCache();
        $filetypes = new FileTypes($this->database);
        $filetypes->generateSettingsCache($domain->id());
    }

    public function siteCache(Domain $domain)
    {
        $domain->regenCache();
    }

    public function news(Domain $domain)
    {
        $news_domain = $this->getTemporaryDomainSite();
        $news_domain->renderActive(true);
        $news = new \Nelliel\Output\OutputNews($news_domain);
        $news->render();
    }

    public function index(Domain $domain)
    {
        require_once INCLUDE_PATH . 'output/main_generation.php';
        $index_domain = $this->getTemporaryDomainBoard($domain->id());
        $index_domain->renderActive(true);
        nel_main_thread_generator($index_domain, 0, true);
        $catalog_domain = $this->getTemporaryDomainBoard($domain->id());
        $catalog_domain->renderActive(true);
        $output_catalog = new \Nelliel\Output\OutputCatalog($catalog_domain);
        $output_catalog->render(['write' => true]);
    }

    public function boardList(Domain $domain)
    {
        $board_json = new \Nelliel\API\JSON\JSONBoard($domain, new FileHandler());
        $board_list_json = new \Nelliel\API\JSON\JSONBoardList($domain, new FileHandler());
        $board_ids = $domain->database()->executeFetchAll('SELECT "board_id" FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);

        foreach($board_ids as $id)
        {
            $board_domain = new DomainBoard($id, $domain->database());
            $board_config = $domain->database()->executeFetchAll('SELECT "config_name", "setting" FROM "' . $board_domain->reference('config_table') . '"', PDO::FETCH_ASSOC);
            $board_data = ['board_id' => $id];

            foreach($board_config as $config)
            {
                $board_data[$config['config_name']] = $config['setting'];
            }

            $board_list_json->addBoardData($board_json->prepareData($board_data));
        }

        $board_list_json->writeStoredData(BASE_PATH, 'boards');
    }

    public function allSitePages(Domain $domain)
    {
        $this->boardList($domain);
    }

    public function allBoardPages(Domain $domain)
    {
        $result = $domain->database()->query(
                'SELECT "thread_id" FROM "' . $domain->reference('threads_table') . '" WHERE "archive_status" = 0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
        $this->threads($domain, true, $ids);
        $this->index($domain);
    }
}