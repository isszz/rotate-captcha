<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\config;

use think\facade\Config;
use isszz\captcha\rotate\Config as ConfigDrive;

class Think extends ConfigDrive
{
    public function get(string $name, string $defaultValue = null): mixed
    {
        return Config::get($name, $defaultValue);
    }

    public function put(string $name, array|string $data): bool
    {
        return Config::set($name, $data);
    }

    public function forget(string $name): bool
    {
        return Config::delete($name);
    }
}
