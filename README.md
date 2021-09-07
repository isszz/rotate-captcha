# Rotate captcha
旋转图片角度验证码, 包含php生成验证图片(gd 或者 imagick)

## 演示图
![image](https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/1.png)
![image](https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/2.png)
![image](https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/3.png)
![image](https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/4.png)

## Ctrl+鼠标左键, 查看演示视频
<a target="_blank" href="https://www.bilibili.com/blackboard/html5mobileplayer.html?aid=250374453&bvid=BV1wv411w7u1&cid=404070048&page=2"><img src="https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/video-cover.png" alt="点击查看视频演示" /></a>

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
    'sarea' => 10,
    'gd' => [
        'quality' => 80,
        'compress' => 0, // 0强制转换jpg白底, 压缩后30KB左右, 1根据图片格式压缩png保持透明
        'bgcolor' => '#fff', // compress = 0 时, 底色, 只支持16进制颜色
    ],
    'imagick' => [
        'quality' => 80,
        'compress' => 0, // 0转jpg白底, 压缩后30KB左右, 1png保持背景透明, 有损压缩后90KB左右, 2png保持背景透明, 无损压缩只能剪掉几KB
        'bgcolor' => 'white', // compress = 0 时, 底色
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
        $id = $request->get('id');

        [$format, $image] = RotateCaptcha::img($id, upload_path('captcha'));

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
$id = $_GET['id'] ?? null;

if(empty($id)) {
    echo '';
}

[$format, $image] = RotateCaptcha::img($id, upload_path('captcha'));

if(empty($image)) {
    echo '';
}

header('Content-Disposition: inline; filename=captcha_' . $id . '.' . $format);
header('Content-type: image/'. $format);
echo $image;
```
## 前端配置项
```javascript
options = {
    theme: '#07f', // 验证码主色调
    title: '安全验证',
    desc: '拖动滑块，使图片角度为正',
    width: 305, // 暂时无用, 计划用于大小设置
    successClose: 1500, // 验证成功后页面关闭时间
    timerProgressBar: !0, // 验证成功后关闭时是否显示进度条
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
```javascript
// 点击某个dom触发modal
// 此处的modal换成自己框架的
element.find('.J_open_captcha').off('click.open.captcha').on('click.open.captcha', function(e) {
    e.preventDefault();
    $.modal.flow({
        closeType: !0,
        content: '<div class="J__captcha__"></div>',
        // 你的modal初始化回调内, 或者在show回调内放置captcha的初始化
        init: function(modal) {
            // 使用验证码只关注这部分
            // 这里是重点渲染captcha到J__captcha__这个dom里面
            modal.element.find('.J__captcha__').captcha({
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
            // 获取Captcha实例
            // var captcha = modal.element.find('.J__captcha__').data('captcha');
            // console.log(captcha.state());
        }
    });
});
```

## 结语
> 因为是基于tp6写的代码, 可能依赖的tp6的部分有点多, 稍后会出一个不依赖任何框架的版本
> JS部分也会逐步移除一些没有发出来的部分
> 