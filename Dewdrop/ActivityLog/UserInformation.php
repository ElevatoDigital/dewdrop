<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\ActivityLog;

use Dewdrop\Pimple;
use Dewdrop\Request;
use Geocoder\Exception\NoResult as NoResultException;
use Geocoder\Provider\GeoIP2 as GeoIp2Provider;

class UserInformation
{
    /**
     * @var string
     */
    private $cookieName = 'dewuid';

    /**
     * @var int
     */
    private $cookieTtl = 3600;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var GeoIp2Provider
     */
    private $geocoder;

    /**
     * @var DbGateway
     */
    private $dbGateway;

    /**
     * UserInformation constructor.
     * @param DbGateway $dbGateway
     * @param GeoIp2Provider $geocoder
     */
    public function __construct(DbGateway $dbGateway, Request $request = null, GeoIp2Provider $geocoder = null)
    {
        $this->dbGateway = $dbGateway;
        $this->request   = ($request ?: Pimple::getResource('dewdrop-request'));
        $this->geocoder  = $geocoder;
    }

    /**
     * @param string $cookieName
     * @return $this
     */
    public function setCookieName($cookieName)
    {
        $this->cookieName = $cookieName;

        return $this;
    }

    /**
     * @param integer $cookieTtl
     * @return $this
     */
    public function setCookieTtl($cookieTtl)
    {
        $this->cookieTtl = $cookieTtl;

        return $this;
    }

    /**
     * @return bool|int
     */
    public function getId()
    {
        $id = $this->getIdFromSignedCookie();

        if (false === $id) {
            $ipAddress      = $this->request->getClientIp();
            $geocoderResult = null;

            if ($this->geocoder) {
                try {
                    $geocoderResult = $this->geocoder->geocode($ipAddress)->first();
                } catch (NoResultException $e) {
                }
            }

            $id = $this->dbGateway->insertUserInformation(
                $ipAddress,
                $_SERVER['HTTP_USER_AGENT'],
                php_sapi_name(),
                $geocoderResult
            );

            $this->writeSignedCookie($id);
        }

        return $id;
    }

    /**
     * @return bool|int
     */
    public function getIdFromSignedCookie()
    {
        if (!$this->cookieIsSet()) {
            return false;
        }

        $secretKey = SecretCookieSigningKey::get();

        if ($secretKey) {
            $cookieId     = $this->getCookieId();
            $computedHash = $this->hmac($cookieId, $secretKey);

            if (hash_equals($this->getCookieHash(), $computedHash)) {
                return (int) $cookieId;
            }
        }

        return false;
    }

    /**
     * @param integer$id
     */
    public function writeSignedCookie($id)
    {
        if (headers_sent()) {
            return;
        }

        $secretKey = SecretCookieSigningKey::get();

        if ($secretKey) {
            $expiresTimestamp = time() + $this->cookieTtl;
            setcookie($this->cookieName, $id, $expiresTimestamp);
            setcookie($this->cookieName . 'hash', $this->hmac($id, $secretKey), $expiresTimestamp);
        }
    }

    /**
     * @param string $content
     * @param string $secretKey
     * @return mixed
     */
    private function hmac($content, $secretKey)
    {
        return hash_hmac('sha256', $content, $secretKey);
    }

    /**
     * @return bool
     */
    private function cookieIsSet()
    {
        return isset($_COOKIE[$this->cookieName]) && isset($_COOKIE[$this->cookieName . 'hash']);
    }

    /**
     * @return string
     */
    private function getCookieId()
    {
        return $_COOKIE[$this->cookieName];
    }

    /**
     * @return string
     */
    private function getCookieHash()
    {
        return $_COOKIE[$this->cookieName . 'hash'];
    }
}
