<?php
/**
 * Created by PhpStorm.
 * User: mayunfeng
 * Date: 2018/2/22
 * Time: 14:23
 */

namespace Printer\Foundation\ServiceProviders;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Printer\YLY\Client;

class YLYServiceProvider implements ServiceProviderInterface
{

    public function register(Container $container)
    {
        $container['yly'] = function ($container) {
            return new Client($container['access_token'],$container['config']);
        };
    }
}