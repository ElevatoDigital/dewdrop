<?php

namespace Dewdrop\Fields\Template;

use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\TableCell\Content as TableCell;
use HtmlNode\Node;

class Link
{
    private $id;

    private $urlTemplate;

    private $title;

    private $target;

    private $cssClasses = [];

    public static function createInstance()
    {
        return new Link();
    }

    public function setUrlTemplate($urlTemplate)
    {
        $this->urlTemplate = $urlTemplate;

        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    public function setCssClasses($cssClasses)
    {
        if (!is_array($cssClasses)) {
            $cssClasses = [$cssClasses];
        }

        $this->cssClasses = $cssClasses;

        return $this;
    }

    public function __invoke(FieldInterface $field)
    {
        $field->addHelperFilter(
            'TableCell.Content',
            function ($content, TableCell $helper, array $rowData, $rowIndex, $columnIndex) {
                if (!$content) {
                    return null;
                }

                if (!$this->urlTemplate || !isset($rowData[$this->id]) || !$rowData[$this->id]) {
                    return $content;
                } else {
                    $node = new Node('a');
                    $node
                        ->setHtml($content)
                        ->setAttribute('href', sprintf($this->urlTemplate, $rowData[$this->id]))
                        ->setAttribute('class', implode(' ', $this->cssClasses));

                    if ($this->title) {
                        $node->setAttribute('title', $this->title);
                    }

                    if ($this->target) {
                        $node->setAttribute('target', $this->target);
                    }

                    return $node;
                }

            }
        );
    }
}
