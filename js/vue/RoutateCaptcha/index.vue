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
