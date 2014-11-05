<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Listing\BulkActions;

/**
 * Communicate the result of processing your action.  You can include a message
 * and also specify whether it was successful or failed (e.g. an input validation
 * error of some kind).
 */
class Result
{
    /**
     * The process() method completed successfully.
     *
     * @const
     */
    const SUCCESS = true;

    /**
     * The process() method failed to complete.
     *
     * @const
     */
    const FAILURE = false;

    /**
     * A message to display to the user letting them know who things went.
     * Plain-text only.  Will be escaped.
     *
     * @var string
     */
    private $message;

    /**
     * Provide the message and success/failure result.
     *
     * @param bool $result
     * @param string $message
     * @throws Exception
     */
    public function __construct($result, $message)
    {
        if (self::SUCCESS !== $result && self::FAILURE !== $result) {
            throw new Exception('Result should be Result::SUCCESS or Result::FAILURE');
        }

        $this->result  = $result;
        $this->message = $message;

    }

    /**
     * Get the message that should be displayed with this result.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Check to see if the process() succeeded.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return self::SUCCESS === $this->result;
    }

    /**
     * Check to see if the process() failed.
     *
     * @return bool
     */
    public function isFailure()
    {
        return self::FAILURE === $this->result;
    }
}
