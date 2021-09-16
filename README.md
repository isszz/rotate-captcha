# Rotate captcha
旋转图片角度验证码, 包含php生成验证图片(gd 或者 imagick)

## 更新
- 2021-09-10更新
  - 新增原生JS版本, 优化部分代码
- 2021-09-16更新
  - 增加存储驱动功能可使用session,cache,cookie驱动, 验证方式改为token交换
  - 验证方式改为token交换
  - 加密方式更改为AES
- 2021-09-17更新
  - 新增输出格式设置，可设置webp，生成图片更小，清晰度更高且支持透明底色

## 演示图
![image](https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/demo.gif)

## Ctrl+鼠标左键, 查看演示视频
<a target="_blank" href="https://www.bilibili.com/blackboard/html5mobileplayer.html?aid=250374453&bvid=BV1wv411w7u1&cid=404070048&page=2"><img src="https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/video-cover.png" alt="点击查看视频演示" /></a>

## PHP部分说明
GD方式在本地开发环境PHP8X做了测试可以正常使用
Imagick方式只做了基本的测试

**如有BUG请issue, 谢谢**

## JS部分说明
依赖jquery, ~~暂时使用了一个model插件, 下次更新把这个model改成接口, 可对接自己的model~~

vue, react版本有能力的朋友参考jquery版自己实现下哦

## 安装
```
composer require isszz/rotate-captcha -vvv
```

## 配置说明
```php
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
    'drive' => SessionDrive::class, // 存储驱动, 可以继承 isszz\captcha\rotate\Drive实现自定义
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
        $data = RotateCaptcha::setLang('zh-cn')->create(
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
     * 验证
     */
    public function verify(Request $request)
    {
        $token = $request->get('token');
        $angle = $request->get('angle');

        if(empty($token) || empty($angle)) {
            $this->result(1, 'error');
        }

        try {
            if(RotateCaptcha::setLang('zh-cn')->check($token, $angle) === true) {
                $this->result(0, 'success');
            }
        } catch(CaptchaException $e) {
            $this->result(1, $e->getMessage());
        }

        $this->result(1, 'error');
    }

    /**
     * 通过前端传递str字段给后端叫唤图片显示到前端
     */
    public function img(Request $request)
    {
        $str = $request->get('id');

        [$mime, $image] = RotateCaptcha::output($str, upload_path('captcha'));

        if(empty($image)) {
            return '';
        }

        return response($image, 200, [
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Content-Type' => $mime,
            'Content-Length' => strlen($image)
        ]);
    }
}

```
##### 没有使用框架输出图片/未经测试
```php
$id = $_GET['id'] ?? null;

if(empty($id)) {
    echo '';
}

[$mime, $image] = RotateCaptcha::img($id, upload_path('captcha'));

if(empty($image)) {
    echo '';
}

header('Cache-Control: private, no-cache, no-store, must-revalidate');
// header('Content-Disposition: inline; filename=captcha_' . $id . '.' . str_replace('image/', '.', $mime));
header('Content-type: '. $mime);
header('Content-Length: '. strlen($image));
echo $image;
```
## 前端配置项
```javascript
options = {
    theme: '#07f', // 验证码主色调
    title: '安全验证',
    desc: '拖动滑块，使图片角度为正',
    width: 305, // 验证界面的宽度
    successClose: 1500, // 验证成功后页面关闭时间
    timerProgressBar: !0, // 验证成功后关闭时是否显示进度条
    timerProgressBarColor: '#07f', // 进度条颜色
    url: {
        info: '/captcha', // 获取验证码信息
        check: '/captcha/check', // 验证
        img: '/captcha/img', // 交换图片
    },
    init: function (captcha) {}, // 初始化
    success: function () {}, // 验证成功
    fail: function () {}, // 验证失败
    complete: function (state) {}, // 触发验证, 不管成功与否
    close: function (state) {}, // 关闭验证码窗口并返回验证状态state
};
```
### 前端使用

#### 原生JS版本的使用方式
```javascript
// .J__captcha__是输出验证码的容器
// 方式1
let myCaptcha = document.querySelectorAll('.J__captcha__').item(0).captcha({
    // 验证成功时显示
    timerProgressBar: !0, // 是否启用进度条
    timerProgressBarColor: '#07f', // 进度条颜色
    url: {
        create: '/common/captcha/rotate', // 获取验证码信息
        check: '/common/captcha/verify', // 验证
        img: '/common/captcha/img', // 交换旋转图
    },
    // 初始化回调
    init: function (captcha) {
        // console.log(captcha);
    },
    // 验证成回调
    success: function() {
        console.log('Captcha state：success');
    },
    // 验证失败回调
    fail: function() {
        console.log('Captcha state：fail');
    },
    // 触发验证回调, 不管成功与否
    complete: function(state) {
        console.log('Captcha complete， state：', state);
    },
    // 验证码触发关闭(验证成功后需要关闭)
    close: function(state) {
        modal.close(); // 关闭你的modal
        console.log('Captcha close， state：', state);
    }
});
// 方式2
let myCaptcha = new Captcha(document.querySelectorAll('.J__captcha__').item(0), {
    // options同上...
});
```
#### jquery版本的使用方式
```javascript
// .J__captcha__是输出验证码的容器
modal.element.find('.J__captcha__').captcha({
    // options同上...
});
// 获取Captcha实例
// var captcha = modal.element.find('.J__captcha__').data('captcha');
// console.log(captcha.state());
```
