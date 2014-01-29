<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

/**
 * This is a simple config singleton that wraps the dewdrop.json file.  It's a
 * singleton to make it easy to access from anywhere in the application, but its
 * use should be limited as much as possible.  The primary purpose of this config
 * class is informing Dewdrop about the environment it is running in (WP or other,
 * basically).
 */
class Config
{
    /**
     * The data obtained from parsing the dewdrop.json file.  We decode the JSON
     * so that the data is represented as nested stdClass objects, rather than
     * arrays.  That makes it easier to fluently reference nested values.
     *
     * @var stdClass
     */
    private $data;

    /**
     * The instance for the configuration, retrieved by the static getInstance()
     * method.
     *
     * @var \Dewdrop\Config
     */
    private static $instance;

    /**
     * Create a new \Dewdrop\Config object using the supplied file.
     *
     * @param string $file Path to the configuration file.
     * @throws \Dewdrop\Exception
     */
    public function __construct($file = null)
    {
        if (self::$instance) {
            throw new Exception('Config already instantiated');
        }

        if (null === $file) {
            $file = getcwd() . '/dewdrop.json';
        }

        if (!file_exists($file) || !is_readable($file)) {
            throw new Exception(
                'Could not find dewdrop.json configuration file'
            );
        }

        $this->data = @json_decode(
            file_get_contents($file)
        );

        if (!$this->data) {
            throw new Exception('Could not parse dewdrop.json contents.');
        }
    }

    /**
     * Get a singleton instance of the app's configuration.
     *
     * @param string $file
     * @return \Dewdrop\Config
     */
    public static function getInstance($file = null)
    {
        if (!self::$instance) {
            self::$instance = new Config($file);
        }

        return self::$instance;
    }

    /**
     * Retrieve the specified key from the config's data.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->data->$key;
    }

    /**
     * Check if the specified key is present in the config's data.
     *
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->data->$key);
    }
}
