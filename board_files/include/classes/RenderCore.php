<?php

namespace Nelliel;

class RenderCore
{
    private $template_instance;
    private $dom_documents;
    private $render_sets;
    private $output_filter;
    private $file_handler;

    function __construct()
    {
        $this->template_instance = new \NellielTemplates\TemplateCore($this);
        libxml_use_internal_errors(true);
        $this->createRenderSet('default');
        $this->output_filter = new OutputFilter();
        $this->file_handler = new FileHandler();
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function newDOMDocument()
    {
        $dom = new \phpDOMExtend\ExtendedDOMDocument();
        $this->dom_documents[spl_object_hash($dom)]['template'] = null;
        $dom->formatOutput = true;
        $dom->strictErrorChecking = false;
        $dom->validateOnParse = true;
        return $dom;
    }

    public function clearRenderSet($render_set = 'default')
    {
        $this->render_sets[$render_set]['content'] = '';
    }

    public function createRenderSet($render_set = 'default')
    {
        if (!isset($this->render_sets[$render_set]))
        {
            $this->render_sets[$render_set]['content'] = '';
        }
    }

    public function startRenderTimer($render_set = 'default')
    {
        $this->render_sets[$render_set]['start_time'] = microtime(true);
    }

    public function endRenderTimer($render_set = 'default')
    {
        if (!isset($this->render_sets[$render_set]['start_time']))
        {
            return 0;
        }

        $this->render_sets[$render_set]['end_time'] = microtime(true);
        return $this->render_sets[$render_set]['end_time'] - $this->render_sets[$render_set]['start_time'];
    }

    public function getTemplateInstance()
    {
        return $this->template_instance;
    }

    public function loadTemplateFromFile($dom_document, $template_file)
    {
        $this->dom_documents[spl_object_hash($dom_document)]['template'] = $template_file;
        $source = $this->template_instance->getTemplate($template_file);
        $dom_document->loadHTML($source, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    }

    public function outputHTML($dom_document)
    {
        return $this->template_instance->outputHTMLFromDom($dom_document,
                $this->dom_documents[spl_object_hash($dom_document)]['template']);
    }

    public function appendHTML($html, $render_set = 'default')
    {
        $this->createRenderSet($render_set);
        $this->render_sets[$render_set]['content'] .= $html;
    }

    public function appendHTMLFromDOM($dom_document, $render_set = 'default')
    {
        $this->createRenderSet($render_set);
        $this->render_sets[$render_set]['content'] .= $this->outputHTML($dom_document,
                $this->dom_documents[spl_object_hash($dom_document)]['template']);
    }

    public function outputRenderSet($render_set = 'default')
    {
        return $this->render_sets[$render_set]['content'];
    }
}
