# Rotate captcha
旋转图片角度验证码, 使用 PHP 生成验证图片(gd 或者 imagick) 用于旋转验证，可用于各种框架</br>

前端已经支持原生`JS`，`jquery`，`vue2`，`uniapp`版本, 持续更新, 可放心使用</br>

暂未实现`react`版，有能力的朋友参考现有版自行实现下哦</br>
已知uniapp打包微信小程序（IOS有卡顿bug希望有能力的可以修复下，我没有设备无法复现问题）</br>

若发现`bug`, 或更好的建议, 还请`issue`反馈
<p>
    <a href="https://packagist.org/packages/isszz/rotate-captcha"><img src="https://poser.pugx.org/isszz/rotate-captcha/v/stable" alt="Stable Version"></a>
    <a href="https://packagist.org/packages/isszz/rotate-captcha"><img src="https://poser.pugx.org/isszz/rotate-captcha/downloads" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/isszz/rotate-captcha"><img src="https://poser.pugx.org/isszz/rotate-captcha/license" alt="License"></a>
</p>

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
  - 移除thinkphp6的依赖，可在其他框架增加少量代码使用啦
- **2021-09-20 更新**
  - token存储增加了前缀
  - 新增Redis存储驱动，不依赖框架，支持redis即可
- **2021-09-22 更新**
  - 新增uniapp版，暂时兼容PC版有BUG
- **2021-09-23 更新**
  - 新增vue版，基于vue2，未测试vue3
- **2021-09-24 更新**
  - 修复uniapp小程序安卓真机卡顿问题(ios貌似还是有问题, 因为没设备测试, 暂时无法修复- -...)
- **2021-09-25 更新**
  - vue版增加了touch事件的支持, 兼容h5
- **2021-09-26 更新**
  - vue版改为canvas
- **2021-10-07 更新**
  - 修复Imagick方式旋转角度问题
  - 修复旧的存储方式逻辑bug，隔月无法找到相同角度图片
  - 新增图片存储开关，存储后，生成相同角度图片时，可以二次找回，无需再次生成
  - 启用存储生成图片时，可以设置存储图片深度，`storeImage`设置`true`或`1`时存储为角度文件夹，设置`2`时根据角度生成`2`个文件夹，大于`2`时生成`3`个文件夹
  - 未启用存储生成图片时，~~每次图片访问后会清理存储图片的目录内所有文件~~，删除当前访问生成验证码图片
- **2021-10-20 更新**
  - 将语言改到为配置项
- **2022-01-05 更新**
  - 增加facade注释
  - 移除助手类的rotate_captcha_img方法使用rotate_captcha_output代替，用法和\isszz\captcha\rotate\facade\Captcha::output方法相同，返回数组[$mime, $image]，生成图片的mime类型和图片内容

## 安装
```
composer require isszz/rotate-captcha -vvv
```

## 演示图
![image](https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/demo.gif)

## Ctrl+鼠标左键, 查看演示视频
<a target="_blank" href="https://www.bilibili.com/blackboard/html5mobileplayer.html?aid=250374453&bvid=BV1wv411w7u1&cid=404070048&page=2"><img src="https://raw.githubusercontent.com/isszz/rotate-captcha/main/demo/video-cover.png" alt="点击查看视频演示" /></a>

## 配置说明
```php
<?php

return [
	'lang' => 'zh-cn', // 默认语言
	'size' => 350, // 生成图片尺寸
	'expire' => 300, // 生成验证有效期
	'salt' => '%%*$*$#$~#$^isszz@cfyun.cc^&*$#$~',
	'outputType' => 'webp', // 输出类型, png, jpg, webp, 建议使用webp, png文件较大, jpg不支持透明
	'storeImage' => true, // 是否存储生成的图片, 如果保存, 也可以设置存储深度, true或1是角度文件夹, 2根据角度生成2个文件夹, 大于2则根据角度生成3个文件夹
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
	// Redis存储驱动需要的配置
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
	// token存储驱动，默认为thinkphp6，需要其他的可以参考下面实现
	// 'store' => isszz\captcha\rotate\store\CacheStore::class, // cache token存储驱动，基于thinkphp6
	// 'store' => isszz\captcha\rotate\store\CookieStore::class, // cookie token存储驱动，基于thinkphp6
	// 'store' => isszz\captcha\rotate\store\SessionStore::class, // session token存储驱动，基于thinkphp6
	'store' => isszz\captcha\rotate\store\RedisStore::class, // redis token存储驱动，需支持redis，不依赖框架
];

```
## PHP部分使用

**tp6中使用**

```php
<?php
declare (strict_types = 1);

namespace app\common\controller;

use isszz\captcha\rotate\CaptchaException;
use isszz\captcha\rotate\facade\Captcha as RotateCaptcha;
use isszz\captcha\rotate\support\File;


use think\Response;
use think\Request;

class Captcha
{
	/**
	 * 生成验证码图片和相关信息
	 */
	public function rotate(Request $request)
	{
		// 用于测试, 这部分, 可以自己整个素材库, 去数据库, 或者缓存下来总之很灵活
		/*$list = [
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
		}*/

		// 说明: upload_path方法为自定义方法，更具自己使用的框架获取你存储素材的根目录即可
		// 例如在tp6框架可以这么写:
		/*
		function upload_path(string $path = ''): string
		{
			return public_path(DIRECTORY_SEPARATOR .'uploads' . DIRECTORY_SEPARATOR . ($path ? ltrim($path, DIRECTORY_SEPARATOR) : $path));
		}
		*/

		// 新增: 从指定目录随机读取文件，这个方法不知道效率如何，基于FilesystemIterator类实现
		$image = File::make(upload_path('captcha_mtl'))->rand();

		// 生成验证码需要的图片
		// setLang设置语言
		// $rotateCaptcha = RotateCaptcha::setLang('zh-cn');
		// $rotateCaptcha->create(...);
		$data = RotateCaptcha::create(
			$image,
			upload_path('captcha') // 用于存储生成图片的目录
		)->get(260); // 260为最终生成的图片尺寸

		if(!$data) {
			$this->result(1, 'error');
		}
		// $data['str']是图片的path加密, 用于前端交换验证码图片
		// 这里前端不涉及拿到角度, 都是去后端验证
		// 可以使用header传递token为X-CaptchaToken
		$this->result(0, 'success', ['str' => $data['str']], ['X-CaptchaToken' => $data['token']]);
	}
	
	/**
	 * 验证
	 */
	public function verify(Request $request)
	{
		$angle = $request->get('angle');
		// 优先从header获取token
		$token = $request->header('X-CaptchaToken') ?: $request->get('token');

		if(empty($token) || empty($angle)) {
			$this->result(1, 'error');
		}

		try {
			if(RotateCaptcha::check($token, $angle) === true) {
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

	/**
	 * 返回json
	 */
	public function result(int|string $code = 0, string $msg = 'success', array|string $data, array $header = [])
	{
		$result = [
			'code' => $code,
			'data' => $data,
			'msg' => lang($msg) ?: ($code > 0 ? 'error' : 'success'),
		];

		$response = Response::create($result, 'json')->code(200);

		if(!empty($header)) {
			$response = $response->header($header);
		}
		throw new \think\exception\HttpResponseException($response);
	}
}

```
## 非thinkphp6框架, 可以参考如下
```php
<?php
use isszz\captcha\rotate\support\File;
use isszz\captcha\rotate\facade\Captcha;
use isszz\captcha\rotate\CaptchaException;

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

/*
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

$image = path('upload') . 'captcha_mtl' . DIRECTORY_SEPARATOR . $image;
*/

// 新增: 从指定目录随机读取文件，这个方法不知道效率如何，基于FilesystemIterator类实现
$image = File::make(path('upload') . 'captcha_mtl' . DIRECTORY_SEPARATOR)->rand();

$data = Captcha::configDrive(\CaptchaConfig::class)->create($image, path('upload') . 'captcha' . DIRECTORY_SEPARATOR)->get(260);

header('Content-Type:application/json; charset=utf-8');

if($data) {
	// 可以使用header传递token
	header('X-CaptchaToken: '. $data['token']);
	echo json_encode([
		'code' => 0,
		'msg' => 'success',
		'data' => [/*'token' => $data['token'], */'str' => $data['str']],
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

[$mime, $image] = Captcha::output($id, upload_path('captcha'));

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
// 这里大家只需要实现, 验证token是否存在(当然此处可以省略，获取后判断也是一样), 获取token, 和删除token, 存储token
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
		[$token, $payload] = $this->buildPayload($degrees);

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
#### vue使用示例
```js
<template>
	<div id="app">
		<div @click="openCaptcha">
			<img alt="Vue logo" src="./assets/logo.png">
			<div>打开验证码</div>
		</div>
		<RoutateCaptcha :options="captchaOptions" v-if="captchaShow" @close="captchaShow = false" @init="captchaInit" @complete="captchaComplete" @success="captchaSuccess" @fail="captchaFail"></RoutateCaptcha>
	</div>
</template>

<script>
// 组件路径, 在这里引入. 组件位置:js/vue/RoutateCaptcha
import RoutateCaptcha from './components/RoutateCaptcha/index.vue'

export default {
	name: 'captchaDemo',
	components: {
		RoutateCaptcha
	},
	data() {
		return {
			captchaShow: false,
			captchaOptions: {
				theme: '#07f',
				title: '安全验证',
				desc: '拖动滑块，使图片角度为正',
				successClose: 1500, // 验证成功后页面关闭时间
				timerProgressBar: true, // 验证成功后关闭时是否显示进度条
				timerProgressBarColor: 'rgba(0, 0, 0, 0.2)',
				request: {
					// 获取验证码信息
					info: (callback) => {
						this.getJSON('http://vmd.co/common/captcha/rotate', null, function(res, xhr) {
							if(xhr.status != 200) {
								alert('系统出错：'+ res.statusCode +'，请关闭重试！')
								return false
							}
							// 第二个参数传递从header中获取的token, 如果嫌麻烦, 可以在res内返回token
							callback(res, xhr.getResponseHeader('X-CaptchaToken'))
						})
					},
					// 验证, angle用户旋转角度
					check: (angle, token, callback) => {
						// 将token设置好, 数据验证时传递给后端
						// 当然这里的数据请求只作为参考, 实际使用中以你的数据请求组件方式为准
						this.token = token
						this.getJSON('http://vmd.co/common/captcha/verify', {angle: angle}, function(res, xhr) {
							if(xhr.status != 200) {
								alert('系统出错：'+ res.statusCode +'，请关闭重试！')
								return false
							}
							callback(res, xhr)
						})
					},
					// 交换图片
					img: (id) => {
						return 'http://vmd.co/common/captcha/img?id=' + id
					},
				},
			},
		}
	},
	methods: {
		openCaptcha() {
			this.captchaShow = true
		},
		captchaInit(captcha) {

		},
		captchaSuccess() {
			console.log('captcha success')
		},
		captchaFail() {
			console.log('caotcha fail')
		},
		captchaComplete(state) {
			// console.log('caotcha complete state: ' + state)
		},
		// ajax请求, 这里只做演示, 请使用自己项目中的
		getJSON(url, data, callback) {
			const _this = this;
			let params = '';
			if(data && typeof data == 'object') {
				params = Object.keys(data).map(function(key) {
					return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
				}).join('&');

				url = url + ((url.indexOf('?') == -1 ? '?' : '&') + params);
			}

			let xhr, formData = null;
			xhr = new XMLHttpRequest();
			xhr.withCredentials = false;

			xhr.open('GET', url);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			if(_this.token) {
				xhr.setRequestHeader('X-CaptchaToken', _this.token);
			}
			xhr.onload = function() {
				if (xhr.status != 200) {
					return;
				}

				try {
					let res = JSON.parse(xhr.responseText) || null;
					if (!res) {
						return;
					}
					callback(res, xhr);
				} catch(e) {
					return;
				}
			};

			xhr.send(formData);
		}
	}
}
</script>

<style>
html, body {
	margin: 0;
	padding: 0;
	width: 100%;
	height: 100%;
}
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
  margin-top: 0;
}
</style>

```

#### 在uniapp中使用

**组件位置:js/vue/uniapp放在uniapp项目的uni_modules目录或者去官方插件中心搜索 isszz-captcha**
```js
<template >
	<view>
		<rotate-captcha :options="captchaOptions" v-if="captchaShow" @init="captchaInit" @close="captchaShow = false" @complete="captchaComplete" @success="captchaSuccess" @fail="captchaFail"></rotate-captcha>
	</view>
</template>
<script>
	export default {
		data() {
			return {
				captchaShow: false,
				captchaOptions: {
					theme: '#07f',
					title: '安全验证',
					desc: '拖动滑块，使图片角度为正',
					successClose: 1500, // 验证成功后页面关闭时间
					timerProgressBar: true, // 验证成功后关闭时是否显示进度条
					timerProgressBarColor: 'rgba(0, 0, 0, 0.2)',
					url: {
						info: 'http://cfyun.cc/common/captcha/rotate', // 获取验证码信息
						check: 'http://cfyun.cc/common/captcha/verify', // 验证
						img: 'http://cfyun.cc/common/captcha/img', // 交换图片
					},	
				},
			}
		},
		created: function () {
			// 显示验证码
			this.captchaShow = true
		},
		methods: {
			captchaInit(captcha) {
				// console.log(captcha)
			},
			captchaSuccess() {
				// console.log('captcha success')
			},
			captchaFail() {
				// console.log('caotcha fail')
			},
			captchaComplete(state) {
				// console.log('caotcha complete state: ' + state)
			},
		}
	}
</script>
```
