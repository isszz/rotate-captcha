<?php

namespace isszz\captcha\rotate\facade;

// use think\Facade;
use isszz\captcha\rotate\Facade;

class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \isszz\captcha\rotate\Captcha::class;
    }
}
