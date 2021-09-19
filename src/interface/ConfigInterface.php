<?php

namespace isszz\captcha\rotate\interface;

interface ConfigInterface
{
    public function get(string $name, string $defaultValue = null): mixed;
    public function put(string $name, array|string $data): bool;
    public function forget(string $name): bool;
}
