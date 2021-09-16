<?php

namespace isszz\captcha\rotate;

class CaptchaException extends \Exception
{
    public function __construct($message = null)
    {
        !is_null($message) && $this->message = $message;
    }
}
