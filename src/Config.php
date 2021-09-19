<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate;

use isszz\captcha\rotate\interface\ConfigInterface;

abstract class Config implements ConfigInterface
{
    abstract public function get(string $name, string $defaultValue = null): mixed;
    abstract public function put(string $name, array|string $data): bool;
    abstract public function forget(string $name): bool;
}