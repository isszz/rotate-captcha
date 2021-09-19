<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\store;

use isszz\captcha\rotate\Store;
use isszz\captcha\rotate\support\Str;

use think\facade\Session;

class SessionStore extends Store
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