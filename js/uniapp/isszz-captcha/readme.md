## 配置项
```javascript
options = {
	theme: '#07f',
	title: '安全验证',
	desc: '拖动滑块，使图片角度为正',
	successClose: 1500, // 验证成功后页面关闭时间
	timerProgressBar: true, // 验证成功后关闭时是否显示进度条
	timerProgressBarColor: 'rgba(0, 0, 0, 0.2)',
	// 后端相关地址
	url: {
		info: '/common/captcha/rotate', // 获取验证码信息
		check: '/common/captcha/check', // 验证
		img: '/common/captcha/img', // 交换图片
	},	
};
```

## uniapp示例
```js
<template >
	<view>
		<isszz-captcha :options="captchaOptions" v-if="captchaShow" @close="captchaShow = false" @init="captchaInit" @complete="captchaComplete" @success="captchaSuccess" @fail="captchaFail"></isszz-captcha>
	</view>
</template>
<script>
	export default {
		data() {
			return {
				title: '打开验证码',
				// 是否打开验证码
				captchaShow: false,
				// 验证码配置项
				captchaOptions: {
					theme: '#07f',
					title: '安全验证',
					desc: '拖动滑块，使图片角度为正',
					successClose: 1500, // 验证成功后页面关闭时间
					timerProgressBar: true, // 验证成功后关闭时是否显示进度条
					timerProgressBarColor: 'rgba(0, 0, 0, 0.2)',
					// 后端相关地址
					url: {
						info: 'http://vmd.co/common/captcha/rotate', // 获取验证码信息
						check: 'http://vmd.co/common/captcha/verify', // 验证
						img: 'http://vmd.co/common/captcha/img', // 交换图片
					},	
				},
			}
		},
		onLoad() {
		},
		methods: {
			// 打开验证码窗口
			openCaptcha() {
				this.captchaShow = true
			},
			// 初始化回调
			captchaInit(captcha) {
			},
			// 不管验证是否成功都回调
			captchaComplete(state) {
			},
			// 验证成功回调
			captchaSuccess() {
			},
			// 验证失败回调
			captchaFail() {
			},
		}
	}
</script>
```
