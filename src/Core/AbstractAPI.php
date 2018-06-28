<?php
/**
 * Created by PhpStorm.
 * User: mayunfeng
 * Date: 2017/11/15
 * Time: 15:18
 */

namespace Printer\Core;

use Mayunfeng\Supports\Collection;

/**
 * BaseApi use before login
 * Class BaseApi
 * @package common\library\api\core
 */
abstract class AbstractAPI
{
    /**
     * Http instance.
     *
     * @var \Printer\Core\Http
     */
    protected $http;

    /**
     * The request token.
     *
     * @var \Printer\Core\AccessToken
     */
    protected $accessToken;
    const GET = 'get';
    const POST = 'post';
    /**
     * @var int
     */
    protected static $maxRetries = 2;

    public $config;
    /**
     * Constructor.
     *
     * @param \Printer\Core\AccessToken $accessToken
     * @param Collection $config
     */
    public function __construct(AccessToken $accessToken,Collection $config)
    {
        $this->setAccessToken($accessToken);
        $this->config = $config;
    }
    /**
     * Return the http instance.
     *
     * @return \Printer\Core\Http
     */
    public function getHttp()
    {
        if (is_null($this->http)) {
            $this->http = new Http();
        }
//        var_dump(count($this->http->getMiddlewares()));die();
//        if (0 === count($this->http->getMiddlewares())) {
//            $this->registerHttpMiddlewares();
//        }
        return $this->http;
    }
    /**
     * Set the http instance.
     *
     * @param \Printer\Core\Http $http
     *
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;
        return $this;
    }
    /**
     * Return the current accessToken.
     *
     * @return \Printer\Core\AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
    /**
     * Set the request token.
     *
     * @param \Printer\Core\AccessToken $accessToken
     *
     * @return $this
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }
    /**
     * @param int $retries
     */
    public static function maxRetries($retries)
    {
        self::$maxRetries = abs($retries);
    }


    /**
     * @param $method
     * @param array $args
     * @return Collection
     */
    public function parseJSON($method, array $args)
    {
        $http = $this->getHttp();
        $timestamp = time();
        $params = array_merge($args[1],[
            'client_id' => $this->config->get('client_id'),
            'sign' => md5($this->config->get('client_id').$timestamp.$this->config->get('secret')),
            'timestamp' => $timestamp,
            'id' => $this->uuid4(),
            'access_token' => $this->accessToken->getToken()
//            'scope' => 'all',
//            'grant_type' => 'client_credentials',
        ]);
        $args[1] = $params;
        $contents = $http->parseJSON(call_user_func_array([$http, $method], $args));
        return new Collection($contents);
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

}