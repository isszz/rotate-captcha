<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\drive;

use isszz\captcha\rotate\Drive;
use isszz\captcha\rotate\support\Str;

use think\facade\Session;
use isszz\captcha\CaptchaException;

class SessionDrive extends Drive
{
    public function get(string $token): array
    {
        if(!Session::has($token)) {
            return [];
        }

        $payload = Session::get($token);

        if(empty($payload)) {
            return [];
        }

        $payload = $this->encrypter->decrypt($payload);

        if(empty($payload)) {
            return [];
        }

        Session::delete($token);
        return json_decode($payload, true);
    }
    
    public function put(?int $degrees): string
    {
        $token = Str::random(32, 'alnum');

        $payload = $this->buildPayload($degrees);

        Session::set($token, $payload, $this->ttl);

        return $token;
    }
}
