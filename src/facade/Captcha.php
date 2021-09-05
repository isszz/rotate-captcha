<?php

namespace isszz\captcha\rotate\facade;

use think\Facade;

class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \isszz\captcha\rotate\Captcha::class;
    }
}
