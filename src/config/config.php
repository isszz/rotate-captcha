<?php

return [
    'size' => 350,
    'salt' => '%%*$*$#$~#$^isszz@cfyun.cc^&*$#$~',
    'handle' => 'gd',
    'sarea' => 10, // 容错率
    'gd' => [
        'quality' => 80,
        'compress' => 0, // 0强制转换jpg白底, 压缩后30KB左右, 1根据图片格式压缩png保持透明
        'bgcolor' => '#fff', // compress = 0 时,底色
    ],
    'imagick' => [
        'quality' => 80,
        'compress' => 0, // 0转jpg白底, 压缩后30KB左右, 1png保持背景透明, 有损压缩后90KB左右, 2png保持背景透明, 无损压缩只能剪掉几KB
        'bgcolor' => 'white', // compress = 0 时,底色
    ],
];