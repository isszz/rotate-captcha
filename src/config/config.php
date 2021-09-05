<?php

return [
    'size' => 350,
    'salt' => '%%*$*$#$~#$^isszz@cfyun.cc^&*$#$~',
    'handle' => 'gd',
    'sarea' => 10, // 容错率
                   // Fault tolerance rate
    'gd' => [
        'quality' => 80,
        'compress' => 0, // 0根据图片格式压缩png保持透明, 1强制转换jpg白底, 压缩后30KB左右
                         // 0 Compress png according to the image format to keep it transparent, 1 Force convert jpg with white background, about 30KB after compression
    ],
    'imagick' => [
        'quality' => 80,
        'compress' => 0, // 0转jpg白底, 压缩后30KB左右, 1png保持背景透明, 有损压缩后90KB左右, 2png保持背景透明, 无损压缩只能剪掉几KB
                         // 0 to jpg with white background, about 30KB after compression, 1png keeps the background transparent, after lossy compression about 90KB, 2png keeps the background transparent, lossless compression can only cut a few KB
        'bgcolor' => 'white', // compress = 0 时,底色
                              // When compress = 0, the background color
    ],
];