<?php

namespace Dewdrop;

/**
 * @package Dewdrop
 */
class Request
{
    /**
     * @var array
     */
    private $post;

    /**
     * @var array
     */
    private $query;

    /**
     * @var string
     */
    private $method;

    /**
     * @param array $post
     * @param array $query
     */
    public function __construct(array $post = null, array $query = null, $method = null)
    {
        $this->post   = ($post ?: $_POST);
        $this->query  = ($query ?: $_GET);
        $this->method = ($method ?: $_SERVER['REQUEST_METHOD']);
    }

    public function isPost()
    {
        return 'POST' === $this->method;
    }

    /**
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
