<?php
/**
 * Created by PhpStorm.
 * User: mayunfeng
 * Date: 2018/2/22
 * Time: 14:02
 */

namespace Printer\Foundation;


use Mayunfeng\Supports\Collection;
use Printer\Core\AccessToken;
use Printer\Core\Http;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Application
 *
 * @property \Printer\YLY\Client              $yly
 *
 * @package Printer\Foundation
 */
class Application extends Container
{

    protected $providers = [
        ServiceProviders\YLYServiceProvider::class,
    ];

    public function __construct($config)
    {
        parent::__construct();

        $this['config'] = function () use ($config) {
            return new Collection($config);
        };
        
        if ($this['config']['debug'] == true) {
            error_reporting(E_ALL);
        }
        $this->registerProviders();
        $this->registerBase();
        Http::setDefaultOptions($this['config']->get('guzzle', ['timeout' => 5.0]));
    }


    // 初始化token信息
    private function registerBase()
    {
        $this['request'] = function () {
            return Request::createFromGlobals();
        };

        $this['access_token'] = function () {
            return new AccessToken(
                $this['config']['client_id'],
                $this['config']['secret']
            );
        };
    }

    // 注册服务
    private function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }


    /**
     * Add a provider.
     *
     * @param string $provider
     *
     * @return Application
     */
    public function addProvider($provider)
    {
        array_push($this->providers, $provider);

        return $this;
    }

    /**
     * Set providers.
     *
     * @param array $providers
     */
    public function setProviders(array $providers)
    {
        $this->providers = [];
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }
}