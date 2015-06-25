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
 * Use PHP_CodeSniffer to check that your plugin code conforms to your coding
 * style of choice.  By default, we use the PSR-2 coding style.
 */
class Sniff extends CommandAbstract
{
    /**
     * The path to the phpcs executable.  We'll attempt to auto-detect if it
     * isn't specified.
     *
     * @var string
     */
    protected $phpcs;

    /**
     * The standard to use for the test.  Defaults to PSR-2.
     *
     * @var string
     */
    protected $standard = 'PSR2';

    /**
     * Set basic command information, arguments and examples
     *
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription('Run PHP_CodeSniffer on your plugin to ensure it follows coding style guidelines')
            ->setCommand('sniff')
            ->setSupportFallbackArgs(true)
            ->addAlias('code-sniff')
            ->addAlias('cs');

        $this->addArg(
            'phpcs',
            'The path to the phpcs executable',
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'standard',
            'The standard you want to check your code against',
            self::ARG_OPTIONAL
        );
    }

    /**
     * Manually set the path to the phpcs binary
     *
     * @param string $phpcs
     * @return \Dewdrop\Cli\Command\Sniff
     */
    public function setPhpcs($phpcs)
    {
        $this->phpcs = $phpcs;

        return $this;
    }

    /**
     * Set the standard you'd like to check you code style against
     *
     * @param string $standard
     * @return \Dewdrop\Cli\Command\Sniff
     */
    public function setStandard($standard)
    {
        $this->standard = $standard;

        return $this;
    }

    /**
     * Run PHP_CodeSniffer on the plugin code.
     *
     * @return void
     */
    public function execute()
    {
        if (null === $this->phpcs) {
            $this->phpcs = $this->autoDetectExecutable('phpcs');
        }

        $cmd = sprintf(
            '%s --standard=%s --extensions=php '
            . '--ignore=*/vendor/* --ignore=*/metadata/* --ignore=*/tests/* --ignore=*/www/* --ignore=*/.git/* '
            . '%s %s',
            $this->phpcs,
            escapeshellarg($this->standard),
            $this->getFallbackArgString(),
            escapeshellarg($this->paths->getPluginRoot())
        );

        $this->passthru($cmd);
    }
}
