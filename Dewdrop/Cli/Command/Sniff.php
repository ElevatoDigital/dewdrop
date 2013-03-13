<?php

namespace Dewdrop\Cli\Command;

class Sniff extends CommandAbstract
{
    public function init()
    {
        $this
            ->setDescription('Run PHP_CodeSniffer on your plugin to ensure it follows PSR-2')
            ->setCommand('sniff')
            ->addAlias('code-sniff')
            ->addAlias('cs');
    }

    public function execute()
    {
        $cmd = sprintf(
            'phpcs --standard=PSR2 --ignore=*/Zend/* --ignore=*/tests/* %s',
            escapeshellarg(
                dirname(dirname(dirname(__DIR__)))
            )
        );

        passthru($cmd);
    }
}
