<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Dewdrop\Exception;

/**
 * Experimental support for packaging a Dewdrop-based plugin for use
 * in other WordPress installations.  Prefixes all namespaces so that
 * this plugin won't conflict with others using Dewdrop and/or
 * Zend Framework.
 */
class Package extends CommandAbstract
{
    /**
     * Where to put the generated ZIP file.
     *
     * @var string
     */
    private $outputDir;

    /**
     * The namespace prefix to use to avoid conflicts with other Dewdrop-based
     * plugins.
     *
     * @var string
     */
    private $namespace;

    /**
     * Set up basic command properties and arguments.
     */
    public function init()
    {
        $this
            ->setDescription('Package your plugin for use in other WordPress installs')
            ->setCommand('package');

        $this->addArg(
            'output-dir',
            'Where you would like to save the generated ZIP file',
            self::ARG_REQUIRED,
            array('o')
        );

        $this->addArg(
            'namespace',
            'The namespace to prepend to all classes in your package',
            self::ARG_REQUIRED
        );
    }

    /**
     * Set the folder into which you'd like the generated ZIP file to be placed.
     *
     * @param string $outputDir
     * @return \Dewdrop\Cli\Command\Package
     */
    public function setOutputDir($outputDir)
    {
        $this->outputDir = $outputDir;

        return $this;
    }

    /**
     * Set the namespace you'd like to use when prefixing all Dewdrop, Zend, Model
     * and Admin class names so that they don't conflict with other Dewdrop-based
     * plugins installed in the same WP instance.
     *
     * @param string $namespace
     * @return \Dewdrop\Cli\Command\Package
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Generate the plugin package in the specified output directory.
     */
    public function execute()
    {
        $root = $this->paths->getPluginRoot();
        $out  = $this->outputDir . basename($root);

        if (file_exists($out)) {
            throw new Exception("Output folder \"{$out}\" already exists.  Cannot create package.");
        }

        mkdir($out);

        $files = $this->findFiles($root);

        foreach ($files as $file) {
            if (preg_match('/\.php$/', $file)) {
                $contents = $this->transformFile($file);
            } else {
                $contents = file_get_contents($file);
            }

            $this->writeFile($file, $out, $contents);
        }
    }

    /**
     * Recursively find all files inside the plugin forlder so that they can
     * be included in the package.
     *
     * @param string $path
     * @return array
     */
    protected function findFiles($path)
    {
        $dir   = opendir($path);
        $files = array();

        while ($filename = readdir($dir)) {
            if ('.' === $filename || '..' === $filename) {
                continue;
            }

            $filePath = "$path/$filename";

            if (is_dir($filePath)) {
                $files = array_merge($files, $this->findFiles($filePath));
            } else {
                $files[] = $filePath;
            }
        }

        return $files;
    }

    /**
     * Tokenize a PHP file, prefixing class names as needed to prevent
     * collisions with other Dewdrop-based plugins.
     *
     * @param string $path
     * @return string
     */
    protected function transformFile($path)
    {
        $src    = file_get_contents($path);
        $tokens = token_get_all($src);
        $out    = '';

        $parsedToIndex = 0;

        foreach ($tokens as $index => $token) {
            if ($index < $parsedToIndex) {
                continue;
            }

            if (!is_array($token)) {
                $out .= $token;
                continue;
            }

            $name = token_name($token[0]);

            if (!$this->isClassNameToken($name)) {
                $out .= $token[1];
            } else {
                $className = $this->getPackagedClassName($tokens, $index, $parsedToIndex);

                $out .= $token[1] . ' ' . $className;
            }
        }

        return $out;
    }

    /**
     * Whether the token name indicates that a class name is coming up.
     *
     * @param string $token
     * @return boolean
     */
    protected function isClassNameToken($token)
    {
        return in_array(
            $token,
            array(
                'T_NAMESPACE',
                'T_USE',
                'T_NEW'
            )
        );
    }

    /**
     * Write a file into the package directory.
     *
     * @param string $absolutePath Absolute/full path to the source file.
     * @param string $outputDir The root directory of the generated package.
     * @param string $contents The contents to write.
     * @return boolean Whether it was successfully written.
     */
    protected function writeFile($absolutePath, $outputDir, $contents)
    {
        $rootPath     = preg_quote($this->paths->getPluginRoot(), '/');
        $relativePath = preg_replace('/^' . $rootPath . '\//', '', $absolutePath);
        $outputFile   = "$outputDir/$relativePath";
        $outputDir    = dirname($outputFile);

        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        return false !== file_put_contents($outputFile, $contents);
    }

    /**
     * Search the supplied tokens starting at $startIndex to find the class
     * name value at that point in the source.  If the class name is in
     * a prefix-able namespace, we prefix it now.  The $parsedToIndex property
     * indicates to the caller which tokens have been parsed/handled by this
     * method so that the caller can skip them when iteration resumes.
     *
     * @param array $tokens
     * @param integer $startIndex
     * @param integer $parsedToIndex
     * @return string
     */
    protected function getPackagedClassName(array $tokens, $startIndex, &$parsedToIndex)
    {
        $open = false;
        $out  = '';

        foreach ($tokens as $index => $token) {
            if ($index < $startIndex) {
                continue;
            }

            if (';' === $token || '(' === $token) {
                $parsedToIndex = $index;
                break;
            }

            if (!is_array($token)) {
                continue;
            }

            $name = token_name($token[0]);

            if (!$open && ($name === 'T_STRING' || $name === 'T_NS_SEPARATOR')) {
                $open = true;
                $out  = $token[1];
            } elseif ($open) {
                if ($name !== 'T_WHITESPACE' && $name !== 'T_DOUBLE_COLON') {
                    $out .= $token[1];
                } else {
                    $parsedToIndex = $index;
                    break;
                }
            }
        }

        $absolute = (0 === strpos($out, '\\'));
        $out      = ltrim($out, '\\');

        if ($this->isInPackageNamespace($out)) {
            $out = $this->namespace . '\\' . $out;
        }

        if ($absolute) {
            $out = '\\' . $out;
        }

        return $out;
    }

    /**
     * Determine whether the supplied class name ought to be prefixed with
     * the package's prefix namespace.
     *
     * @param string $className
     * @return boolean
     */
    protected function isInPackageNamespace($className)
    {
        $prefix       = $className;
        $separatorPos = strpos($prefix, '\\');

        if (false !== $separatorPos) {
            $prefix = substr($className, 0, $separatorPos);
        }

        return in_array(
            $prefix,
            array('Zend', 'Dewdrop', 'Model', 'Admin')
        );
    }
}
