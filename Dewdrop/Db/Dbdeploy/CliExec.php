<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Dbdeploy;

use Dewdrop\Db\Dbdeploy\Exception;
use Dewdrop\Db\Dbdeploy\Exception\ScriptExecutionFailed;

/**
 * This class makes it easy to run SQL scripts through the CLI tool of your
 * RDBMS.  It will attempt to auto-detect the location of your "psql" or "mysql"
 * binaries and will run the scripts in such a way that errors halt execution.
 */
class CliExec
{
    /**
     * The output generated during script execution.  Useful for trouble-shooting
     * failed scripts.
     *
     * @var string
     */
    private $output;

    /**
     * The full path to your "mysql" or "psql" binary, if it is in an abnormal
     * location (e.g. you've got an alternative Postgres installed version in
     * /Applications or /opt).
     *
     * @var string
     */
    private $binaryPath;

    /**
     * Which type of RDBMS are we using ("pgsql" or "mysql").
     *
     * @var string
     */
    private $dbType;

    /**
     * The username used to connect to the DB.
     *
     * @var string
     */
    private $username;

    /**
     * The password used to connect to the DB.
     *
     * @var string
     */
    private $password;

    /**
     * The hostname used to connect to the DB.
     *
     * @var string
     */
    private $hostname;

    /**
     * The name of the database.
     *
     * @var string
     */
    private $dbName;

    /**
     * Note that $dbType must be "pgsql" or "mysql" so that we can construct
     * a CLI command for your DB.
     *
     * @param string $dbType
     * @param string $username
     * @param string $password
     * @param string $hostname
     * @param string $dbName
     * @param string $binaryPath
     * @throws Exception
     */
    public function __construct($dbType, $username, $password, $hostname, $dbName, $binaryPath = null)
    {
        // Be nice to people using "psql" instead of "pgsql"
        if ('psql' === $dbType) {
            $dbType = 'pgsql';
        }

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

    /**
     * Run the supplied SQL script.  If it fails, by default, we'll throw an
     * exception with the command's output.  If the command generates a
     * successful exit status (0), then we return true.  Any non-zero exit
     * status will return false.
     *
     * @param string $path
     * @param bool $throwExceptions
     * @return bool
     * @throws ScriptExecutionFailed
     */
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
            throw new ScriptExecutionFailed("Failed processing of {$path}: {$this->output}");
        }

        return $success;
    }

    /**
     * Get the full output from the most recent script execution.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * A little wrapper around the built-in exec() funciton so it can be mocked
     * during testing.
     *
     * @param string $cmd
     * @param array $output
     * @param int $exitStatus
     * @return string
     */
    protected function exec($cmd, &$output, &$exitStatus)
    {
        return exec($cmd, $output, $exitStatus);
    }

    /**
     * Generate a full CLI command for the supplied path.  Notice the additional
     * ON_ERROR_STOP=1 argument in the psql template to ensure it halts execution
     * if an error is encountered.  MySQL does that by default.
     *
     * @param $path
     * @return string
     */
    private function createCmd($path)
    {
        if ('pgsql' === $this->dbType) {
            return sprintf(
                'PGPASSWORD=%s %s -v ON_ERROR_STOP=1 -U %s -h %s %s < %s 2>&1',
                escapeshellarg($this->password),
                $this->detectExecutable(),
                escapeshellarg($this->username),
                escapeshellarg($this->hostname),
                escapeshellarg($this->dbName),
                escapeshellarg($path)
            );
        } else {
            return sprintf(
                '%s --user=%s --password=%s --host=%s %s < %s 2>&1',
                $this->detectExecutable(),
                escapeshellarg($this->username),
                escapeshellarg($this->password),
                escapeshellarg($this->hostname),
                escapeshellarg($this->dbName),
                escapeshellarg($path)
            );
        }
    }

    /**
     * Find the executable using either the manually supplied $binaryPath
     * class property or using Unix's which to find it in the $PATH.
     *
     * @return string
     */
    private function detectExecutable()
    {
        if (null !== $this->binaryPath) {
            $cmd = $this->binaryPath;
        } else {
            if ('pgsql' === $this->dbType) {
                $name = 'psql';
            } elseif ('mysql' === $this->dbType) {
                $name = 'mysql';
            } else {
                throw new Exception('Unknown dbType in use.  Use "pgsql" or "mysql".');
            }

            $cmd = $this->which($name);
        }

        return $cmd;
    }

    /**
     * A little wrapper around Unix's which.  If which isn't available in
     * and executable in /usr/bin, we just return the input, hoping that
     * the binary can be found in our $PATH.
     *
     * @param $name
     * @return string
     */
    private function which($name)
    {
        if (!file_exists('/usr/bin/which') || !is_executable('/usr/bin/which')) {
            return $name;
        } else {
            return trim(shell_exec("which {$name}")) ?: $name;
        }
    }
}
