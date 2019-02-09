<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

require_once INCLUDE_PATH . 'output/management/news_panel.php';

class AdminNews extends AdminHandler
{
    private $domain;
    private $defaults = false;

    function __construct($database, Authorization $authorization, Domain $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();

        if ($inputs['action'] === 'add')
        {
            $this->add($user);
        }
        else if ($inputs['action'] === 'remove')
        {
            $this->remove($user);
        }

        $this->renderPanel($user);
    }

    public function renderPanel($user)
    {
        nel_render_news_panel($user, $this->domain);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_news_modify'))
        {
            nel_derp(471, _gettext('You are not allowed to modify news.'));
        }

        $news_info = array();
        $news_info['poster_id'] = $user->auth_id;
        $news_info['headline'] = $_POST['headline'] ?? null;
        $news_info['time'] = time();
        $news_info['text'] = $_POST['headline'] ?? null;
        $query = 'INSERT INTO "' . NEWS_TABLE . '" ("poster_id", "headline", "time", "text") VALUES (?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
                [$news_info['poster_id'], $news_info['headline'], $news_info['time'], $news_info['text']]);
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
    }

    public function remove($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_news_modify'))
        {
            nel_derp(471, _gettext('You are not allowed to modify news.'));
        }

        $entry = $_GET['entry'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEWS_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$entry]);
        $this->renderPanel($user);
    }
}