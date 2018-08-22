<?php
/**
 * Created by PhpStorm.
 * User: mayunfeng
 * Date: 2018/2/22
 * Time: 14:39
 */

namespace Printer\Core;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Printer\Core\Exceptions\HttpException;
/**
 * Class AccessToken.
 */
class AccessToken
{
    /**
     * App ID.
     *
     * @var string
     */
    protected $client_id;
    /**
     * App secret.
     *
     * @var string
     */
    protected $secret;
    /**
     * Cache.
     *
     * @var Cache
     */
    protected $cache;
    /**
     * Cache Key.
     *
     * @var string
     */
    protected $cacheKey;
    /**
     * Http instance.
     *
     * @var Http
     */
    protected $http;
    /**
     * Query name.
     *
     * @var string
     */
    protected $queryName = 'access_token';
    /**
     * Response Json key name.
     *
     * @var string
     */
    protected $tokenJsonKey = 'access_token';
    /**
     * Cache key prefix.
     *
     * @var string
     */
    protected $prefix = 'printer.common.access_token.';

    // API
    const API_TOKEN_GET = 'https://open-api.10ss.net/oauth/oauth';
    /**
     * Constructor.
     *
     * @param string                       $client_id
     * @param string                       $secret
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function __construct($client_id, $secret, Cache $cache = null)
    {
        $this->client_id = $client_id;
        $this->secret = $secret;
        $this->cache = $cache;
    }


    /**
     * @param bool $forceRefresh
     * @return array
     * @throws HttpException
     */
    public function getToken($forceRefresh = false)
    {
        $cacheKey = $this->getCacheKey();
        $cached = $this->getCache()->fetch($cacheKey);

        if ($forceRefresh || empty($cached)) {
            $token = $this->getTokenFromServer();

            // XXX: T_T... 7200 - 1500
            $this->getCache()->save($cacheKey, $token['body'][$this->tokenJsonKey], $token['body']['expires_in'] - 1500);
            return $token['body'][$this->tokenJsonKey];
        }
        return $cached;
    }
    /**
     * 设置自定义 token.
     *
     * @param string $token
     * @param int    $expires
     *
     * @return $this
     */
    public function setToken($token, $expires = 7200)
    {
        $this->getCache()->save($this->getCacheKey(), $token, $expires - 1500);
        return $this;
    }
    /**
     * Return the app id.
     *
     * @return string
     */
    public function getClientid()
    {
        return $this->client_id;
    }
    /**
     * Return the secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }
    /**
     * Set cache instance.
     *
     * @param \Doctrine\Common\Cache\Cache $cache
     *
     * @return AccessToken
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
        return $this;
    }
    /**
     * Return the cache manager.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        return $this->cache ?: $this->cache = new FilesystemCache(sys_get_temp_dir());
    }
    /**
     * Set the query name.
     *
     * @param string $queryName
     *
     * @return $this
     */
    public function setQueryName($queryName)
    {
        $this->queryName = $queryName;
        return $this;
    }
    /**
     * Return the query name.
     *
     * @return string
     */
    public function getQueryName()
    {
        return $this->queryName;
    }
    /**
     * Return the API request queries.
     *
     * @return array
     */
    public function getQueryFields()
    {
        return [$this->queryName => $this->getToken()];
    }
    /**
     * Get the access token from WeChat server.
     *
     * @throws HttpException
     *
     * @return array
     */
    public function getTokenFromServer()
    {
        $timestamp = time();
        $params = [
            'client_id' => $this->client_id,
            'grant_type' => 'client_credentials',
            'sign' => md5($this->client_id.$timestamp.$this->secret),
            'scope' => 'all',
            'timestamp' => $timestamp,
            'id' => $this->uuid4()
        ];
        $http = $this->getHttp();
        $token = $http->parseJSON($http->post(self::API_TOKEN_GET, $params));
        if (empty($token['body'][$this->tokenJsonKey])) {
            throw new HttpException('Request AccessToken fail. response: '.json_encode($token, JSON_UNESCAPED_UNICODE));
        }
        return $token;
    }



    public function uuid4()
    {
        mt_srand((double)microtime() * 10000);
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = '-';
        $uuidv4 =
            substr($charid, 0, 8) . $hyphen .
            substr($charid, 8, 4) . $hyphen .
            substr($charid, 12, 4) . $hyphen .
            substr($charid, 16, 4) . $hyphen .
            substr($charid, 20, 12);
        return $uuidv4;
    }

    /**
     * Return the http instance.
     *
     * @return Http
     */
    public function getHttp()
    {
        return $this->http ?: $this->http = new Http();
    }
    /**
     * Set the http instance.
     *
     * @param Http
     *
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;
        return $this;
    }
    /**
     * Set the access token prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }
    /**
     * Set access token cache key.
     *
     * @param string $cacheKey
     *
     * @return $this
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;
        return $this;
    }
    /**
     * Get access token cache key.
     *
     * @return string $this->cacheKey
     */
    public function getCacheKey()
    {
        if (is_null($this->cacheKey)) {
            return $this->prefix.$this->client_id;
        }
        return $this->cacheKey;
    }
}
