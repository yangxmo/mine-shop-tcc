<?php

namespace Tcc\TccTransaction\Util;

use Tcc\TccTransaction\Exception\Handle;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nsq\Nsq;
use Hyperf\Redis\Redis;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\ApplicationContext;

class Di
{
    /**
     * @return Nsq
     */
    public static function nsq() {
        return Di::get(Nsq::class);
    }

    /**
     * @return Redis
     */
    public static function redis() {
        return Di::get(Redis::class);
    }

    /**
     * @return StdoutLoggerInterface
     */
    public static function logger() {
        return Di::get(Di::config('tcc.logger', StdoutLoggerInterface::class));
    }

    /**
     * @return Handle
     */
    public static function exception() {
        return Di::get(Di::config('tcc.exception', Handle::class));
    }

    /**
     * @return IdGeneratorInterface
     */
    public static function idGenerator() {
        return Di::get(IdGeneratorInterface::class);
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public static function config(string $key, $default = null) {
        return Di::get(ConfigInterface::class)->get($key, $default);
    }

    /**
     * @param string $id
     * @return object|mixed
     */
    public static function get(string $id) {
        $container = ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }
        return $container;
    }
}