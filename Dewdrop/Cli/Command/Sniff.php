<?php

namespace Dewdrop\Cli\Command;

/**
 * Use PHP_CodeSniffer to check that your plugin code conforms to your coding
 * style of choice.  By default, we use the PSR-2 coding style.
 *
 * @package Dewdrop
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
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription('Run PHP_CodeSniffer on your plugin to ensure it follows coding style guidelines')
            ->setCommand('sniff')
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
     * @param string $phpcs
     * @return \Dewdrop\Cli\Command\Sniff
     */
    public function setPhpcs($phpcs)
    {
        $this->phpcs = $phpcs;

        return $this;
    }

    /**
     * @param string $phpcs
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
            '%s --standard=%s --ignore=*/Zend/* --ignore=*/tests/* --ignore=*/models/metadata/* %s',
            $this->phpcs,
            escapeshellarg($this->standard),
            escapeshellarg($this->paths->getPluginRoot())
        );

        $this->passthru($cmd);
    }
}
