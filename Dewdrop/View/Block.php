<?php

namespace Dewdrop\View;

use Dewdrop\View\Helper\Block as BlockHelper;

class Block
{
    /**
     * @var View
     */
    private $view;

    /**
     * @var string|Block
     */
    private $content;

    /**
     * @var Block[]
     */
    private $beforeBlocks = [];

    /**
     * @var Block[]
     */
    private $afterBlocks = [];

    /**
     * @var bool
     */
    private $removed = false;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $debug = false;

    public function __construct(View $view, $name = '')
    {
        $this->view = $view;
        $this->name = $name;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function resetContent()
    {
        $this->content = null;

        return $this;
    }

    public function before($content)
    {
        $this->beforeBlocks[] = $this->normalizeContent($content);

        return $this;
    }

    public function replaceWith($content)
    {
        $this->content = $this->normalizeContent($content);

        return $this;
    }

    public function remove()
    {
        $this->removed = true;

        return $this;
    }

    public function after($content)
    {
        $this->afterBlocks[] = $this->normalizeContent($content);

        return $this;
    }

    public function open()
    {
        ob_start();

        return $this;
    }

    public function close()
    {
        $bufferedContent = ob_get_clean();

        if (!$this->content) {
            $this->content = $this->normalizeContent(trim($bufferedContent));
        }

        $this->renderContent();

        return $this;
    }

    public function fromCallable(callable $callable)
    {
        if (!$this->content) {
            $this->content = $callable;
        }

        $this->renderContent();
        return $this;
    }

    protected function renderContent()
    {
        $debug = $this->debug && $this->name;

        if ($debug) {
            $this->renderDebugOpenTag();
        }

        if ($this->removed) {
            echo '';
            return;
        }

        foreach ($this->beforeBlocks as $beforeBlock) {
            $beforeBlock->renderContent();
        }

        if ($this->content instanceof Block) {
            $this->content->renderContent();
        } elseif (is_callable($this->content)) {
            $contentCallable = $this->content;
            $contentCallable();
        } else {
            echo $this->content;
        }

        foreach ($this->afterBlocks as $afterBlock) {
            $afterBlock->renderContent();
        }

        if ($this->debug) {
            $this->renderDebugCloseTag();
        }

        return $this;
    }

    private function normalizeContent($content)
    {
        if (!$content instanceof Block) {
            $content = (new Block($this->view))->setContent($content);
        }

        return $content;
    }

    private function renderDebugOpenTag()
    {
        echo '<div style="background: #fafafa; border: 1px solid #ccc; margin-bottom: 3px; padding: 3px;">';
        echo '<div style="color: red; font-size: 10px;">' . $this->view->escapeHtml($this->name) . '</div>';
    }

    private function renderDebugCloseTag()
    {
        echo '</div>';
    }
}