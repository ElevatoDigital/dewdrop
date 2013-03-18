<?php

namespace Dewdrop\Cli\Command;

/**
 * Generate files for a new admin component.
 *
 * Once generated, you should end up with something like this:
 *
 * |~admin/
 * | `~my-component/
 * |   |~view-scripts/
 * |   | `-index.phtml
 * |   |-Component.php
 * |   `-Index.php
 *
 * @category   Dewdrop
 * @package    Cli
 * @subpackage Command
 */
class GenAdminComponent extends CommandAbstract
{
    /**
     * The title for the generated component, as it will be displayed in the
     * WordPress admin's menu.
     *
     * @var string
     */
    private $title;

    /**
     * The name of the subfolder that will be created in your plugin's "admin"
     * folder.  If not specified, it will be inflected from the component
     * title.
     *
     * @var string
     */
    private $folder;

    /**
     * The namespace that will be used for all component classes.  If not
     * specified, it will be inflected.
     *
     * @var string
     */
    private $namespace;

    /**
     * @inheritdoc
     */
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

    /**
     * Create component folders and files from the templates in "gen-templates".
     *
     * @return void
     */
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

    /**
     * @param string $title
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $title
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @param string $title
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getComponentPath()
    {
        return $this->paths->getAdmin();
    }

    /**
     * Create a folder at the specified path.
     *
     * This is a separate method so that it's easy to mock during testing.
     *
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    protected function createFolder($path)
    {
        mkdir($path);

        return $this;
    }

    /**
     * Write a file at the specified path with the supplied contents.
     *
     * This is a separate method so that it's easy to mock during testing.
     *
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    protected function writeFile($path, $contents)
    {
        file_put_contents($path, $contents);

        return $this;
    }

    /**
     * Check to see if the component folder already exists.
     *
     * This is a separate method so that it's easy to mock during testing.
     *
     * @return boolean
     */
    protected function componentAlreadyExists($newDir)
    {
        return file_exists($newDir);
    }

    /**
     * Inflect folder name from title by lower-casing, replacing spaces with
     * hyphens and eliminating non-alpha-numeric characters.
     *
     * For example:
     *
     * My Super New Component
     *
     * Becomes:
     *
     * my-super-new-component
     *
     * @return string
     */
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

    /**
     * Generate a namespace from the folder name by deleting hyphens and
     * CamelCasing each word.
     *
     * For example:
     *
     * my-super-new-component
     *
     * Becomes:
     *
     * MySuperNewComponent
     *
     * @return string
     */
    private function inflectNamespaceFromFolder()
    {
        $words = explode('-', $this->folder);
        return implode('', array_map('ucfirst', $words));
    }
}
