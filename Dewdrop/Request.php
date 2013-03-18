<?php

namespace Dewdrop;

/**
 * A simple wrapper for HTTP request super-globals.
 *
 * This wrapper serves two primary uses:
 *
 * 1. It makes it easier to retrieve values from $_POST and $_GET without
 *    generating E_NOTICEs due to undefined variables.
 * 2. It makes it easier to inject other values during unit testing.
 *
 * @package Dewdrop
 */
class Request
{
    /**
     * The post values for this request.  By default, $_POST will be used, but
     * you can inject other values into the constructor.
     *
     * @var array
     */
    private $post;

    /**
     * The query string values for this request.  By default, $_POST will be
     * used, but you can inject other values into the constructor.
     *
     * @var array
     */
    private $query;

    /**
     * The request method currently being used (e.g. POST or GET).  This is
     * taken from $_SERVER['REQUEST_METHOD'] by default, but an alternate value
     * can be injected into the constructor.
     *
     * @var string
     */
    private $method;

    /**
     * @param array $post
     * @param array $query
     * @param string $method
     */
    public function __construct(array $post = null, array $query = null, $method = null)
    {
        $this->post   = ($post ?: $_POST);
        $this->query  = ($query ?: $_GET);
        $this->method = ($method ?: $_SERVER['REQUEST_METHOD']);
    }

    /**
     * Whether the request is a POST.
     *
     * @return boolean
     */
    public function isPost()
    {
        return 'POST' === $this->method;
    }

    /**
     * Get either a single POST variable (by passing a string to the name $name
     * parameter) or the entire POST array (by leaving $name empty).
     *
     * The second parameter can be used to specify an alternate default value,
     * if the variable specified by $name is not present in the post data.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getPost($name = null, $default = null)
    {
        if (null === $name) {
            return $this->post;
        } else {
            return (isset($this->post[$name]) ? $this->post[$name] : $default);
        }
    }

    /**
     * Get either a single query variable (by passing a string to the name $name
     * parameter) or the entire query array (by leaving $name empty).
     *
     * The second parameter can be used to specify an alternate default value,
     * if the variable specified by $name is not present in the post data.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getQuery($name = null, $default = null)
    {
        if (null === $name) {
            return $this->query;
        } else {
            return (isset($this->query[$name]) ? $this->query[$name] : $default);
        }
    }
}