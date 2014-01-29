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
 * Run PHPUnit tests available for the Dewdrop library.
 */
class DewdropTest extends CommandAbstract
{
    /**
     * The location of the phpunit executable.  We'll attempt to auto-detect
     * this if it isn't set manually.
     *
     * @var string
     */
    protected $phpunit = null;

    /**
     * Set basic command information, arguments and examples
     *
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription("Run the Dewdrop library's unit tests")
            ->setCommand('dewdrop-test')
            ->addAlias('test-dewdrop')
            ->addAlias('phpunit-dewdrop')
            ->setSupportFallbackArgs(true);

        $this->addArg(
            'phpunit',
            'The location of the PHPUnit executable',
            self::ARG_OPTIONAL
        );
    }

    /**
     * Manually set the path to the phpunit binary
     *
     * @param string $phpunit
     * @return \Dewdrop\Cli\Command\DewdropTest
     */
    public function setPhpunit($phpunit)
    {
        $this->phpunit = $phpunit;

        return $this;
    }

    /**
     * Run PHPUnit on the Dewdrop library tests folder.
     *
     * @return void
     */
    public function execute()
    {
        if (null === $this->phpunit) {
            $this->phpunit = $this->autoDetectExecutable('phpunit');
        }

        $testPath = $this->paths->getVendor() . '/deltasystems/dewdrop/tests';

        $cmd = sprintf(
            '%s -c %s %s',
            $this->phpunit,
            escapeshellarg($testPath . '/phpunit.xml'),
            $this->getFallbackArgString()
        );

        $cmd .= ' ' . escapeshellarg($testPath);

        $this->runner->halt($this->passthru($cmd));
    }
}
