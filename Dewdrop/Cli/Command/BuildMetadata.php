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
 * This command will refresh the dewdrop-build.php file in your project's
 * root folder with a new timestamp.  This can be helpful for two use
 * cases:
 *
 * 1) Displaying this build timestamp somewhere in your responses to enable
 *    you to determine when the current build was deployed.
 *
 * 2) Including this in a URL prefix to aid in cache busting when you push
 *    new builds (see the UrlCachePrefix view helper for more info).
 *
 * Integrate this command with your deploy scripts so that your build info
 * is updated every time you push.
 */
class BuildMetadata extends CommandAbstract
{
    /**
     * Set up basic command and aliases.
     *
     * @return void
     */
    public function init()
    {
        $this
            ->setCommand('build-metadata')
            ->addAlias('refresh-build')
            ->addAlias('refresh-build-metadata')
            ->setDescription('Update the dewdrop-build.php build metadata file.');
    }

    /**
     * Update the dewdrop-build.php file with a new timestamp.  Read this
     * timestamp by requiring the PHP file or accessing the dewdrop-build
     * Pimple resource.
     *
     * @return void
     */
    public function execute()
    {
        /* @var $paths \Dewdrop\Paths */
        $paths = $this->runner->getPimple()['paths'];

        file_put_contents(
            $paths->getAppRoot() . '/dewdrop-build.php',
            '<?php' . PHP_EOL . 'return ' . var_export(date('Ymd-Gis'), true) . ';' . PHP_EOL
        );
    }
}
