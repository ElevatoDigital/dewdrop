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
 * A simple wrapper for HTTP request super-globals.
 *
 * This wrapper serves two primary uses:
 *
 * 1. It makes it easier to retrieve values from $_POST and $_GET without
 *    generating E_NOTICEs due to undefined variables.
 * 2. It makes it easier to inject other values during unit testing.
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
     * An index present in $_SERVER that is set by a trusted proxy and can be used
     * to get the user's actual IP address.
     *
     * @var string
     */
    private $trustedClientIpHeader;

    /**
     * Create request, optionally injecting alterative values for post, query,
     * and method properties, primarily to aid in testing.
     *
     * @param array $post
     * @param array $query
     * @param string $method
     */
    public function __construct(array $post = null, array $query = null, $method = null)
    {
        $this->post   = ($post ?: $_POST);
        $this->query  = ($query ?: $_GET);

        if (null !== $method) {
            $this->method = $method;
        } elseif (isset($_SERVER['REQUEST_METHOD'])) {
            $this->method = $_SERVER['REQUEST_METHOD'];
        } else {
            $this->method = 'GET';
        }
    }

    /**
     * @param string $trustedClientIpHeader
     * @return $this
     */
    public function setTrustedClientIpHeader($trustedClientIpHeader)
    {
        $this->trustedClientIpHeader = $trustedClientIpHeader;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        if ($this->trustedClientIpHeader && isset($_SERVER[$this->trustedClientIpHeader])) {
            return $_SERVER[$this->trustedClientIpHeader];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Assemble a URL based upon the current Request information.
     *
     * @return string
     */
    public function getUrl()
    {
        $url = preg_replace('/\?.+$/', '', $_SERVER['REQUEST_URI']);

        if (0 < count($this->query)) {
            $url .= '?';
            $needsAmpersand = false;
            foreach ($this->query as $name => $value) {
                $url .= ($needsAmpersand ? '&' : '') . sprintf('%s=%s', rawurlencode($name), rawurlencode($value));
                $needsAmpersand = true;
            }
        }

        return $url;
    }

    /**
     * Just a simple utility method to check whether the request is an AJAX
     * call by looking for the HTTP_X_REQUESTED_WITH header, which should be
     * added by most (all?) major JS libraries.
     *
     * @return boolean
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            'xmlhttprequest' === strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
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
     * Whether the request is a GET.
     *
     * @return boolean
     */
    public function isGet()
    {
        return 'GET' === $this->method;
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
        // WordPress seriously emulates magic_quotes_gpc.  Have to remove its dumbass slashes.
        if (function_exists('stripslashes_deep')) {
            if (null === $name) {
                return stripslashes_deep($this->post);
            } else {
                return (isset($this->post[$name]) ? stripslashes_deep($this->post[$name]) : $default);
            }
        } else {
            if (null === $name) {
                return $this->post;
            } else {
                return (isset($this->post[$name]) ? $this->post[$name] : $default);
            }
        }
    }

    /**
     * Get the contents of the body of a POST request.  Useful in RESTful
     * API work.
     *
     * @return string
     */
    public function getPostBody()
    {
        return file_get_contents('php://input');
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

    /**
     * Manually override the request method after instantiation.  This is
     * primarily helpful in testing.
     *
     * @param string $method
     * @return \Dewdrop\Request
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Modify or add the POST value specified by key.  This is primarily useful
     * for manipulating requests during testing.  If you pass an array as the
     * $key, you'll overwrite the entirety of the POST data.
     *
     * @param mixed $key
     * @param mixed $value
     * @return \Dewdrop\Request
     */
    public function setPost($key, $value = null)
    {
        if (is_array($key)) {
            $this->post = $key;
        } else {
            $this->post[$key] = $value;
        }

        return $this;
    }

    /**
     * Modify or add the GET value specified by key.  This is primarily useful
     * for manipulating requests during testing.  If you pass an array as the
     * $key, you'll overwrite the entirety of the GET data.
     *
     * @param mixed $key
     * @param mixed $value
     * @return \Dewdrop\Request
     */
    public function setQuery($key, $value = null)
    {
        if (is_array($key)) {
            $this->query = $key;
        } else {
            $this->query[$key] = $value;
        }

        return $this;
    }
}
