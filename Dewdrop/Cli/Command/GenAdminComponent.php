<?php

namespace Dewdrop\Cli\Command;

class GenAdminComponent extends CommandAbstract
{
    private $title;

    private $folder;

    private $namespace;

    public function init()
    {
        $this
            ->setDescription('Generate a new admin component')
            ->setCommand('gen-admin-component')
            ->addAlias('admin-component')
            ->addAlias('generate-admin-component');

        $this->addPrimaryArg(
            'title',
            'The title of the component that will be displayed in the WordPress admin menu.',
            self::ARG_REQUIRED,
            array('t')
        );

        $this->addArg(
            'folder',
            'The name of the component folder that will be added in your plugin.',
            self::ARG_OPTIONAL,
            array('f')
        );

        $this->addArg(
            'namespace',
            'The namespace for the component\'s classes',
            self::ARG_OPTIONAL,
            array('n')
        );

        $this->addExample(
            'Generate a component with the title "Fruits" and folder name auto-detected',
            "./dewdrop gen-admin-component 'Fruits'"
        );

        $this->addExample(
            'Manually set a folder name',
            "./dewdrop gen-admin-component 'Fruits' -f 'manual-folder-name'"
        );
    }

    public function execute()
    {
        if (!$this->folder) {
            $this->folder = $this->inflectFolderFromTitle();
        }

        $path   = $this->getComponentPath();
        $newDir = "{$path}/{$this->folder}";

        if ($this->componentAlreadyExists($newDir)) {
            return $this->abort(
                'Cannot generate component because a folder with the name "' . $this->folder . '" already exists.'
            );
        }

        $this->createFolder($newDir);

        $templateReplacements = array(
            '{{namespace}}' => ($this->namespace ?: $this->inflectNamespaceFromFolder()),
            '{{title}}'     => str_replace("'", "\'", $this->title)
        );

        $this->writeFile(
            "{$newDir}/Component.php",
            str_replace(
                array_keys($templateReplacements),
                $templateReplacements,
                file_get_contents(__DIR__ . '/gen-templates/admin-component/Component.tpl')
            )
        );

        $this->writeFile(
            "{$newDir}/Index.php",
            str_replace(
                array_keys($templateReplacements),
                $templateReplacements,
                file_get_contents(__DIR__ . '/gen-templates/admin-component/Index.tpl')
            )
        );

        $this->createFolder("{$newDir}/view-scripts");

        $this->writeFile(
            "{$newDir}/view-scripts/index.phtml",
            file_get_contents(__DIR__ . '/gen-templates/admin-component/view-scripts/index.tpl')
        );
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getComponentPath()
    {
        return $this->paths->getAdmin();
    }

    protected function createFolder($path)
    {
        mkdir($path);

        return $this;
    }

    protected function writeFile($path, $contents)
    {
        file_put_contents($path, $contents);

        return $this;
    }

    protected function componentAlreadyExists($newDir)
    {
        return file_exists($newDir);
    }

    private function inflectFolderFromTitle()
    {
        $folder = strtolower($this->title);
        $folder = str_replace(' ', '-', $folder);

        $folder = preg_replace(
            '/[^a-z0-9\-]/i',
            '',
            $folder
        );

        return $folder;
    }

    private function inflectNamespaceFromFolder()
    {
        $words = explode('-', $this->folder);
        return implode('', array_map('ucfirst', $words));
    }
}
