<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate;

/**
 * Facade
 */
abstract class Facade
{
    private static $accessor = [];

    abstract protected static function getFacadeClass();

    protected static function initArgs()
    {
        return [];
    }

    public static function __callStatic($method, $parameters)
    {
        return self::getInstance()->$method(...$parameters);
    }

    public static function getInstance()
    {
        $cl = static::getFacadeClass();

        if (!isset(self::$accessor[$cl])) {
            self::$accessor[$cl] = new $cl(...static::initArgs());
        }

        return self::$accessor[$cl];
    }

    public static function clear($class)
    {
        unset(self::$accessor[$class]);
    }
}
