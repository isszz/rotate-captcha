<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\drive;

use isszz\captcha\rotate\Drive;
use isszz\captcha\rotate\support\Str;
use isszz\captcha\rotate\support\request\Request;

use think\facade\Cookie;
use isszz\captcha\CaptchaException;

class CookieDrive extends Drive
{
    public function get(string $token): array
    {
        if(!Cookie::has($token)) {
            return [];
        }

        $payload = Cookie::get($token);

        if(empty($payload)) {
            return [];
        }

        $payload = $this->encrypter->decrypt($payload);

        if(empty($payload)) {
            return [];
        }

        Cookie::delete($token);
        return json_decode($payload, true);
    }
    
    public function put(?int $degrees): string
    {
        $token = Str::random(32, 'alnum');

        $payload = $this->buildPayload($degrees);

        Cookie::set($token, $payload, $this->ttl);

        return $token;
    }
}
