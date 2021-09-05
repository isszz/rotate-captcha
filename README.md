# rotate-captcha
旋转图片角度验证码, 包含php生成验证图片(gd 或者 imagick)

## PHP部分说明
GD方式在本地开发环境PHP8X做了测试可以正常使用
Imagick方式只做了基本的测试
**如有BUG请issue, 谢谢**

## JS部分说明
依赖jquery, 暂时使用了一个model插件, 下次更新把这个model改成接口, 可对接自己的model

vue, react版本有能力的朋友参考jquery版自己实现下哦

## 配置说明
```php
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
```
## PHP部分使用

**创建验证码图片**

```php
<?php
declare (strict_types = 1);

use isszz\captcha\rotate\facade\Captcha as RotateCaptcha;

// 用于测试, 这部分, 可以自己整个素材库, 去数据库, 或者缓存下来总之很灵活
$list = [
    '1.png',
    '2.png',
    '1.jpg',
    '2.jpg',
    '3.jpg',
    '4.jpg',
    '5.jpg',
    '6.jpg',
    '7.jpg',
    '8.jpg',
    '9.jpg',
    '10.jpg',
    '11.jpg',
    '12.jpg',
    '13.jpg',
];

// upload_path 需要自己写一个

// 随机拿一个图片
$key = array_rand($list, 1);
if(isset($list[$key])) {
    // 从素材存放目录拿一个图
    $image = upload_path('captcha_mtl') . $list[$key];  
}

// 生成验证码需要的图片
$data = RotateCaptcha::create(
    $image,
    upload_path('captcha') // 用于存储生成图片的目录
)->get(260); // 260为最终生成的图片尺寸

if(!$data) {
    $this->result(1, 'error');
}
// $data['str']是图片的path加密, 用于前端交换验证码图片
// 这里前端不涉及拿到角度, 都是去后端验证
$this->result(0, 'success', $data['str']);

```

##### 前端传递str字段给后端拿图片显示到前端

**tp6在控制器返回图片文件**
```php
// tp6在控制器返回图片文件
public function img(Request $request)
{
    $str = $request->get('str');

    if(empty($str)) {
        return '';
    }

    [$format, $image] = RotateCaptcha::img($str, upload_path('captcha'));

    if(empty($image)) {
        return '';
    }

    return response($image, 200, ['Content-Length' => strlen($image)])->contentType('image/'. trim($format, '.'));
}
```

**其他的框架/未经测试**
```php
header('Content-Disposition: inline; filename=captcha_' . $str . '.' . $format);
header('Content-type: image/'. $format);
echo $image;
```

##### 验证
```php
public function verify(Request $request)
{
    $angle = $request->get('angle');

    if(empty($angle)) {
        return false;
    }

    if(RotateCaptcha::check($angle)) {
        $this->result(0, 'success');
    }

    $this->result(1, 'error');
}
```

## 结语
> 因为是基于tp6写的代码, 可能依赖的tp6的部分有点多, 稍后会出一个不依赖任何框架的版本
> JS部分也会逐步移除一些没有发出来的部分