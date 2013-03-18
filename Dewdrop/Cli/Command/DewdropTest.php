<?php

namespace Dewdrop\Cli\Command;

/**
 * Run PHPUnit tests available for the Dewdrop library.
 *
 * @category   Dewdrop
 * @package    Cli
 * @subpackage Command
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
     * Where you'd like to generate an HTML code coverage report for the tests.
     *
     * @var string
     */
    protected $coverageHtml;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription("Run the Dewdrop library's unit tests")
            ->setCommand('dewdrop-test')
            ->addAlias('test-dewdrop')
            ->addAlias('phpunit-dewdrop');

        $this->addArg(
            'phpunit',
            'The location of the PHPUnit executable',
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'coverage-html',
            "The location where you'd like to generate an HTML code coverage report",
            self::ARG_OPTIONAL
        );
    }

    /**
     * @param string $phpunit
     * @return \Dewdrop\Cli\Command\DewdropTest
     */
    public function setPhpunit($phpunit)
    {
        $this->phpunit = $phpunit;

        return $this;
    }

    /**
     * @param string $phpunit
     * @return \Dewdrop\Cli\Command\DewdropTest
     */
    public function setCoverageHtml($coverageHtml)
    {
        $this->coverageHtml = $coverageHtml;

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

        $testPath = $this->paths->getLib() . '/tests';

        $cmd = sprintf(
            '%s -c %s',
            $this->phpunit,
            escapeshellarg($testPath . '/phpunit.xml')
        );

        if ($this->coverageHtml) {
            $cmd .= sprintf(
                ' --coverage-html=%s',
                escapeshellarg($this->evalPathArgument($this->coverageHtml))
            );
        }

        $cmd .= ' ' . escapeshellarg($testPath);

        $this->passthru($cmd);
    }
}
