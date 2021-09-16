<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\support\request;

class Request
{

    public static $instance = null;

    public static function instance()
    {
        if(is_null(static::$instance)) {
            return static::$instance = new RequestBase();
        }

        return static::$instance;
    }

	public static function __callStatic($method, $parameters)
    {
		return call_user_func_array([static::instance(), $method], $parameters);
	}
}
