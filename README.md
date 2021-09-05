# Rotate captcha
旋转图片角度验证码, 包含php生成验证图片(gd 或者 imagick)

## 演示图
![image](https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/1.png)
![image](https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/2.png)
![image](https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/3.png)
![image](https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/4.png)

## PHP部分说明
GD方式在本地开发环境PHP8X做了测试可以正常使用
Imagick方式只做了基本的测试

**如有BUG请issue, 谢谢**

## JS部分说明
依赖jquery, 暂时使用了一个model插件, 下次更新把这个model改成接口, 可对接自己的model

vue, react版本有能力的朋友参考jquery版自己实现下哦

## 安装
```
composer require isszz/rotate-captcha -vvv
```

## 配置说明
```php
<?php

return [
    'size' => 350,
    'salt' => '%%*$*$#$~#$^isszz@cfyun.cc^&*$#$~',
    'handle' => 'gd',
    'sarea' => 10, // 容错率
    'gd' => [
        'quality' => 80,
        'compress' => 0, // 0根据图片格式压缩png保持透明, 1强制转换jpg白底, 压缩后30KB左右
    ],
    'imagick' => [
        'quality' => 80,
        'compress' => 0, // 0转jpg白底, 压缩后30KB左右, 1png保持背景透明, 有损压缩后90KB左右, 2png保持背景透明, 无损压缩只能剪掉几KB
        'bgcolor' => 'white', // compress = 0 时,底色
    ],
];
```
## PHP部分使用

**tp6中使用**

```php
<?php
declare (strict_types = 1);

namespace app\common\controller;

use isszz\captcha\rotate\facade\Captcha as RotateCaptcha;

use think\Response;
use think\Request;

// 这个用自己的哦
use app\common\traits\Showmsg;

class Captcha
{
    use Showmsg;

    /**
     * 生成验证码图片和相关信息
     */
    public function rotate(Request $request)
    {
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
    }

    /**
     * 通过前端传递str字段给后端叫唤图片显示到前端
     */
    public function img(Request $request)
    {
        $str = $request->get('id');

        [$format, $image] = RotateCaptcha::img($str, upload_path('captcha'));

        if(empty($image)) {
            return '';
        }

        return response($image, 200, ['Content-Length' => strlen($image)])->contentType('image/'. trim($format, '.'));
    }
    
    /**
     * 验证
     */
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

}

```
##### 没有使用框架输出图片/未经测试
```php
$str = $_GET['str'] ?? null;

if(empty($str)) {
    echo '';
}

[$format, $image] = RotateCaptcha::img($str, upload_path('captcha'));

if(empty($image)) {
    echo '';
}

header('Content-Disposition: inline; filename=captcha_' . $str . '.' . $format);
header('Content-type: image/'. $format);
echo $image;
```
##### 前端使用, 暂时代码逻辑有点问题, 因为只是为了做功能测试
```javascript
// J_open_captcha 需要触发打开验证码
// 这里设计逻辑有问题, 当时需要做测试没细想这个- -...
// 正常这个应该是验证码的容器dom, 需要把验证码渲染到这个dom容器
// 后面再改吧= =...
$('.J_open_captcha').rotateCaptcha({
    // 初始化
    init: function (self) {
        console.log(self);
    },
    // 验证成功, 例如发送验证的后续操作, 之类的
    success: function() {
        console.log('captcha state：success');
    },
    // 验证失败
    fail: function() {
        console.log('captcha state：fail');
    },
    // 触发验证时回调验证状态state
    complete: function(state) {
        console.log('captcha complete， state：', state);
    },
    // 关闭验证码窗口并返回验证状态state
    close: function(state) {
        console.log('captcha close， state：', state);
    }
});

```

## 结语
> 因为是基于tp6写的代码, 可能依赖的tp6的部分有点多, 稍后会出一个不依赖任何框架的版本
> JS部分也会逐步移除一些没有发出来的部分
> 