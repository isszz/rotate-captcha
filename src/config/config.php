<?php

// use isszz\captcha\rotate\store\CacheStore;
// use isszz\captcha\rotate\store\CookieStore;
use isszz\captcha\rotate\store\SessionStore;

return [
	'lang' => 'zh-cn',
	'size' => 350, // 生成图片尺寸
	'expire' => 300, // 生成验证有效期
	'salt' => '%%*$*$#$~#$^isszz@cfyun.cc^&*$#$~',
	'outputType' => 'webp', // 输出类型, png, jpg, webp, 建议使用webp, png文件较大, jpg不支持透明
	'storeImage' => false, // 是否存储生成的图片, 如果保存, 也可以设置存储深度, true或1是角度文件夹, 2根据角度生成2个文件夹, 大于2则根据角度生成3个文件夹
	'handle' => 'gd', // 服务器支持imagick时, 建议使用imagick
	'earea' => 10, // 容错率
	'gd' => [
		'quality' => 80,
		'bgcolor' => '', // 底色, #fff
	],
	'imagick' => [
		'quality' => 80,
		'bgcolor' => '', // 底色, white
	],
	'redis' => [
		'host'       => '127.0.0.1',
		'port'       => 6379,
		'password'   => '',
		'select'     => 0,
		'timeout'    => 0,
		'expire'     => 0,
		'persistent' => false,
		'prefix'     => 'captcha_',
	],
	'store' => SessionStore::class,
];
