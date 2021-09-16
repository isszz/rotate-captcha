<?php

// use isszz\captcha\rotate\drive\CacheDrive;
// use isszz\captcha\rotate\drive\CookieDrive;
use isszz\captcha\rotate\drive\SessionDrive;

return [
    'size' => 350, // 生成图片尺寸
    'expire' => 300, // 生成验证有效期
    'outputType' => 'webp', // 输出类型, png, jpg, webp
    'salt' => '%%*$*$#$~#$^isszz@cfyun.cc^&*$#$~',
    'handle' => 'gd',
    'earea' => 10, // 容错率
    'gd' => [
        'quality' => 80,
        'bgcolor' => '', // 底色, #fff
    ],
    'imagick' => [
        'quality' => 80,
        'bgcolor' => '', // 底色, white
    ],
    'drive' => SessionDrive::class,
];