# Rotate captcha
旋转图片角度验证码, 包含php生成验证图片(gd 或者 imagick)

## 更新
- **2021-09-10 新增**
  - 新增原生JS版本, 优化部分代码
- **2021-09-16 新增**
  - 增加存储驱动功能可使用session,cache,cookie驱动
  - 验证方式改为token交换，利于vue，react，app等调用
  - 加密方式更改为AES
- **2021-09-17 新增**
  - 新增输出格式设置，可设置webp，生成图片更小，清晰度更高且支持透明底色
- **2021-09-19 更新**
  - 移除thinkphp6的依赖，现在可以在其他框架或者tp5，增加少量代码使用啦

## 安装
```
composer require isszz/rotate-captcha -vvv
```

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

## 配置说明
```php
<?php

// use isszz\captcha\rotate\store\CacheStore;
// use isszz\captcha\rotate\store\CookieStore;
use isszz\captcha\rotate\store\SessionStore;

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
	'store' => SessionStore::class,
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
		// setLang设置语言
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
## 非thinkphp6框架, 可以参考如下
```php
<?php
use isszz\captcha\rotate\facade\Captcha;

// 这里用到的Config用自己框架的配置类
class CaptchaConfig extends \isszz\captcha\rotate\Config
{
	public function get(string $name, string $defaultValue = null): mixed
	{
		return \Config::get($name, $defaultValue);
	}

	public function put(string $name, array|string $data): bool
	{
		return \Config::put($name, $data);
	}

	public function forget(string $name): bool
	{
		return \Config::forget($name);
	}
}

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

$key = array_rand($list, 1);

if(isset($list[$key])) {
	$image = $list[$key];
}

$data = Captcha::configDrive(\CaptchaConfig::class)->setLang('zh-cn')->create(path('upload') . 'rvimg' . DS . $image, path('upload') . 'captcha' . DS)->get(260);

header('Content-Type:application/json; charset=utf-8');

if($data) {
	echo json_encode([
		'code' => 0,
		'msg' => 'success',
		'data' => ['token' => $data['token'], 'str' => $data['str']],
	]);
}
 
echo json_encode([
	'code' => 1,
	'msg' => 'error',
	'data' => null,
]);

```
### 非thinkphp6框架，输出图片
```php
<?php

use isszz\captcha\rotate\facade\Captcha;

$id = $_GET['id'] ?? null;

if(empty($id)) {
	exit('');
}

[$mime, $image] = Captcha::img($id, upload_path('captcha'));

if(empty($image)) {
	exit('');
}

header('Cache-Control: private, no-cache, no-store, must-revalidate');
// header('Content-Disposition: inline; filename=captcha_' . $id . '.' . str_replace('image/', '.', $mime));
header('Content-type: '. $mime);
header('Content-Length: '. strlen($image));
echo $image;
```
## 关于配置驱动和自定义存储驱动说明
```php

use isszz\captcha\rotate\Store;
use isszz\captcha\rotate\Config;
use isszz\captcha\rotate\support\Str;

// 配置获取驱动，需要基于\isszz\captcha\rotate\Config实现如下方法:
class CaptchaConfig extends Config
{
	public function get(string $name, string $defaultValue = null): mixed
	{
		// 获取配置
		return Config::get($name, $defaultValue);
	}

	public function put(string $name, array|string $data): bool
	{
		// 存储配置 - 暂时无用
		return Config::put($name, $data);
	}

	public function forget(string $name): bool
	{
		// 删除配置 - 暂时无用
		return Config::forget($name);
	}
}

// 自定义存储驱动，需要基于\isszz\captcha\rotate\Store实现如下方法:
// 更多方式参考\isszz\captcha\rotate\store\文件夹内示例，只要能存储token怎么存自由发挥哈
// 这里大家只需要实现, 验证token是否存在(当然此出可以省略，获取后判断也是一样), 获取token, 和删除token, 存储token
class CaptchaSessionStore extends Store
{
	public function get(string $token): array
	{
		// 检测token是否存在
		if(!Session::has($token)) {
			return [];
		}
		
		// 获取token内容
		$payload = Session::get($token);

		if(empty($payload)) {
			return [];
		}

		// 解析token内容
		$payload = $this->encrypter->decrypt($payload);

		if(empty($payload)) {
			return [];
		}

		// 删除token
		Session::forget($token);

		// 返回解析后的token
		return json_decode($payload, true);
	}

	public function put(?int $degrees): string
	{
		$token = \isszz\captcha\rotate\support\Str::random(32, 'alnum');

		$payload = $this->buildPayload($degrees);

		// 存储token, 并设置token过期时间ttl
		Session::put($token, $payload, $this->ttl);

		return $token;
	}
}


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
