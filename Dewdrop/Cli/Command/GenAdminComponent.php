<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

/**
 * Generate files for a new admin component.
 *
 * Once generated, you should end up with something like this:
 *
 * <pre>
 * |~admin/
 * | `~my-component/
 * |   |~view-scripts/
 * |   | |-edit.phtml
 * |   | `-index.phtml
 * |   |-Component.php
 * |   |-Edit.php
 * |   `-Index.php
 * </pre>
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
     * The name of the model class that will be used in your plugin.
     *
     * @var string
     */
    private $model;

    /**
     * The namespace that will be used for all component classes.  If not
     * specified, it will be inflected.
     *
     * @var string
     */
    private $namespace;

    /**
     * Set basic command information, arguments and examples
     *
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

        $this->addArg(
            'model',
            'The model class for the component',
            self::ARG_OPTIONAL,
            array('m')
        );

        $this->addExample(
            'Generate a component with the title "Fruits" and folder and model names auto-detected',
            "./vendor/bin/dewdrop gen-admin-component 'Fruits'"
        );

        $this->addExample(
            'Manually set a folder name, model name auto-detected',
            "./vendor/bin/dewdrop gen-admin-component 'Fruits' -f 'manual-folder-name'"
        );

        $this->addExample(
            'Manually set folder and model names',
            "./vendor/bin/dewdrop gen-admin-component 'Manage Fruits' -f 'super-fruits' -m 'Fruits'"
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

        if (!$this->model) {
            $this->model = $this->inflectModelFromTitle();
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
            '{{title}}'     => str_replace("'", "\'", $this->title),
            '{{model}}'     => $this->model ?: $this->inflectModelFromTitle(),
        );

        $templatesDir = __DIR__ . '/gen-templates/admin-component';

        $this->writeFileFromTemplate(
            "{$newDir}/Component.php",
            "{$templatesDir}/Component.tpl",
            $templateReplacements
        );

        $this->writeFileFromTemplate(
            "{$newDir}/Edit.php",
            "{$templatesDir}/Edit.tpl",
            $templateReplacements
        );

        $this->writeFileFromTemplate(
            "{$newDir}/Index.php",
            "{$templatesDir}/Index.tpl",
            $templateReplacements
        );

        $this->createFolder("{$newDir}/view-scripts");

        $viewScriptDir          = "{$newDir}/view-scripts";
        $viewScriptTemplatesDir = "{$templatesDir}/view-scripts";

        $this->writeFileFromTemplate(
            "{$viewScriptDir}/edit.phtml",
            "{$viewScriptTemplatesDir}/edit.tpl",
            $templateReplacements
        );

        $this->writeFileFromTemplate(
            "{$viewScriptDir}/index.phtml",
            "{$viewScriptTemplatesDir}/index.tpl",
            $templateReplacements
        );
    }

    /**
     * Set the model class of the component
     *
     * @param string $model
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set the title of the component to be displayed in the WP navigation menu
     *
     * @param string $title
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set the name of the subfolder you'd like to create in the admin folder of
     * your plugin.
     *
     * You should usually allow the inflector to determine this value based upon
     * the folder name.  It's really only advisable to use this method in cases
     * where the inflector fails for some reason.
     *
     * @param string $folder
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Manually set the namespace for this component.
     *
     * If not set, we'll try to inflect a namespace from the title property.
     *
     * @param string $namespace
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Get the path to the admin folder of the plugin
     *
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
     * @param string $path
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
     * @param string $path
     * @param string $contents
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    protected function writeFile($path, $contents)
    {
        file_put_contents($path, $contents);

        return $this;
    }

    /**
     * Write a file at the specified path using the given template and placeholder replacement data.
     *
     * @param string $file
     * @param string $template
     * @param array $replacements
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    protected function writeFileFromTemplate($file, $template, array $replacements = array())
    {
        $this->writeFile(
            $file,
            str_replace(
                array_keys($replacements),
                $replacements,
                file_get_contents($template)
            )
        );

        return $this;
    }

    /**
     * Check to see if the component folder already exists.
     *
     * This is a separate method so that it's easy to mock during testing.
     *
     * @param string $newDir
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
     * Inflect model name from title, removing any leading non-alphabetic characters, removing any non-alphanumeric
     * characters, and upper-casing the first character.
     *
     * For example:
     *
     * My Super New Component
     *
     * Becomes:
     *
     * MySuperNewComponent
     *
     * @return string
     */
    private function inflectModelFromTitle()
    {
        $model = preg_replace('/^[^a-z]+/i', '', $this->title);
        $model = preg_replace('/[^a-z0-9]/i', '', $model);
        $model = ucfirst($model);

        return $model;
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
