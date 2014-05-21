<?php

namespace Dewdrop\Db\Dbdeploy;

class CliExec
{
    private $output;

    private $binaryPath;

    private $dbType;

    private $username;

    private $password;

    private $hostname;

    private $dbName;

    public function __construct($dbType, $username, $password, $hostname, $dbName, $binaryPath)
    {
        $this->dbType     = $dbType;
        $this->username   = $username;
        $this->password   = $password;
        $this->hostname   = $hostname;
        $this->dbName     = $dbName;
        $this->binaryPath = $binaryPath;

        if ('mysql' !== $dbType && 'pgsql' !== $dbType) {
            throw new Exception('$dbType must be "pgsql" or "msyql"');
        }
    }

    public function run($path, $throwExceptions = true)
    {
        $cmd = $this->createCmd($path);

        // Initializing exit status to failed state
        $exitStatus = 1;
        $output     = array();

        $this->exec($cmd, $output, $exitStatus);

        if (isset($output) && is_array($output)) {
            $this->output = implode(PHP_EOL, $output);
        } else {
            $this->output = '';
        }

        $success = (0 === $exitStatus);

        if (!$success && $throwExceptions) {
            throw new Exception("Failed processing of {$path}: {$this->output}");
        }

        return $success;
    }

    public function getOutput()
    {
        return $this->output;
    }

    protected function exec($cmd, &$output, &$exitStatus)
    {
        return exec($cmd, $output, $exitStatus);
    }

    private function createCmd($path)
    {
        if ('pgsql' === $this->dbType) {
            $template = '%s -v ON_ERROR_STOP=1 -U %s -h %s %s < %s 2>&1';
        } else {
            $template = '%s --user=%s --password=%s --host=%s %s < %s 2>&1';
        }

        return sprintf(
            $template,
            $this->detectExecutable(),
            escapeshellarg($this->username),
            escapeshellarg($this->hostname),
            escapeshellarg($this->dbName),
            escapeshellarg($path)
        );
    }

    private function detectExecutable()
    {
        if (null !== $this->binaryPath) {
            $cmd = $this->binaryPath;
        } else {
            if ('pgsql' === $this->dbType) {
                $name = 'psql';
            } elseif ('mysql' === $this->dbType) {
                $name = 'mysql';
            }

            $cmd = $this->which($name);
        }

        return $cmd;
    }

    private function which($name)
    {
        if (!file_exists('/usr/bin/which')) {
            return $name;
        } else {
            return trim(shell_exec("which {$name}")) ?: $name;
        }
    }
}