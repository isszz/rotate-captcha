<?php

namespace isszz\captcha\rotate\interface;

interface StoreInterface
{
    public function get(string $token): array;
    public function put(?int $degrees): string;
}
