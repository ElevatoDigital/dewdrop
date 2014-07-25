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
 * Generates a password hash for authentication
 */
class AuthHashPassword extends CommandAbstract
{
    /**
     * Plain text password
     *
     * @var string
     */
    private $plaintext;

    /**
     * Initializations
     *
     * @return void
     */
    public function init()
    {
        $this
            ->setDescription('Generate a password hash.  Helps when adding users via dbdeploy.')
            ->setCommand('auth-hash-password');

        $this->addArg(
            'plaintext',
            "The plaintext password you'd like to hash",
            self::ARG_REQUIRED
        );
    }

    /**
     * Set plain text password
     *
     * @param string $plaintext
     * @return AuthHashPassword
     */
    public function setPlaintext($plaintext)
    {
        $this->plaintext = $plaintext;

        return $this;
    }

    /**
     * Generate and echo password hash
     *
     * @return void
     * @throws Exception
     */
    public function execute()
    {
        $pimple = $this->runner->getPimple();

        if (!$pimple['auth']) {
            throw new Exception('You must configure the \Dewdrop\Auth service in your bootstrap to use this command.');
        }

        $pimple['auth']->init();

        $encoder = $pimple['security.encoder.digest'];

        echo $encoder->encodePassword($this->plaintext, '') . PHP_EOL;
    }
}
