<template>
	<view :class="runtime.show ? 'captcha-root show': 'captcha-root'" :style="theme.root">
		<view class="captcha-modal">
			<view class="captcha-modal-close" @click="close"><image src="./svg/close.svg"></image></view>
			<view class="captcha-wrap">
				<view class="captcha" :style="theme.wrap">
					<view class="captcha-title">
						<view class="title">{{options.title}}</view>
						<text class="desc">{{options.desc}}</text>
					</view>
					<view :class="'captcha-main'+ runtime.stateClass">
						<view class="captcha-wrap">
							<view class="captcha-image" :style="theme.image">
								<view :class="runtime.imageLoaded ? 'captcha-img' : 'captcha-img'" :style="theme.img">
									<canvas 
										v-if="runtime.imageLoaded"
										class="captcha-canvas-image"
										canvas-id="captchaImage"
										:width="size.img"
										:height="size.img"
										:style="'width:' + size.img + 'px; height:' + size.img + 'px;'"
										disable-scroll
									>
										<!--cover-view class="captcha-loader" v-if="!runtime.imageLoaded">
											<cover-image src="./svg/loading.svg"></cover-image>
										</cover-view-->
										<cover-view class="captcha-coordinate" v-if="runtime.coordinate">
											<cover-image src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAcoAAAHLBAMAAAC67sVKAAAAG1BMVEUAAAD////////////////////////////////rTT7CAAAACXRSTlMAzGcNwPRatBpQE/jnAAACVElEQVR42u3asU3DUBSG0ShZgBsxAO9NkMILBCagSI+yAg0lo1PYCZZoiW3de05DiT7Br2sHdjsA8viuEHn4rFC5P1aoHJ5PBSpbvBSY5TneCswyosAwh4gCw2wR+Yd5OEfkH+Y+osAwh4gCw2wR+Yd5OEfkH+Y+osAwh7Ey+TDHWSYf5jTL5MOcZpl8mMOtMvUwb7NMPcz7LFMP8z7L1MMcfisTD/N3lomHOZtl4mHOZpl4mMO8Mu0w57PMO8xr7733iGPvvfeP1K8lEU+7/GpUAoB7qVKlSpUqAcC9VKlSpUqV3kkAwL1UqVKlSpUA4F6qVKlSpUrvJADgXqpUqVKlSgBwL1WqVKlSpXcSAHAvVapUqVIlALiXKlWqVKkSAPBUoFKlSpUqAcC9VKlSpUqVAICnApUqVapUCQDupUqVKlWqBAA8FahUWbTyeqpQ2V5PFSpj/czHa1Ehs0WFzBYVMltUyBwrs2dOlckzb5UrZi5yL1fPXLTynhnjt13sy+Xy/vhv9CczeeWUmb0yvvwsc+0y871M/WDQKkRu4dnHc6x3Eu+X2/usYKGn9ZV/kstUrv3rWuPzWJ+t/5MafyfZAP+7BQDupUqVKlWqBAD3UqVKlSpVAgCeClSqVKlSJQC4lypVqlSpEgDwVKBSpUqVKgHAvVSpUqVKlQCApwKVKlWqVAkA7qVKlSpVqgQA7yQqVapUqVIlALiXKlWqVKkSALyTqFSpUqVKlQDgXqpUqVKlSgDwTqJSpUqVKlUCgHupUqVKlSoBwL1UqVKlSpUqN+YHG9iGwKYOVR0AAAAASUVORK5CYII="></cover-image>
										</cover-view>
										<cover-view class="captcha-state" v-if="runtime.state || runtime.fail">
											<cover-image src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAMAAABHPGVmAAAAV1BMVEUAAAD///////////////////////////////////////////////////////////////////////////////////////////////////////////////+ORg7oAAAAHHRSTlMAKtR/p4+Jo+2empWEeXBGEM+tWjDev3piUEUfi15F+wAAAQ1JREFUaN7t0Uluw0AMRNFuS7KVQYMdZ+b9zxkgQfwXNtCbKm3MvybwQLJkWZZl2V23TP1h/irO1iF+64uveoz/iqu6i0vvhdQGLYVcRkyFDAa/dxkgPoMmn0HLBsawgXFc/cauppFGGmlsYdTqNr5PY8R4OjuNl/hrnH3GJ6Ody1hfgzqDwbFQhAbtg1AkBr0FoUgNNkGRGfQRhCIx6DxeKyqD5iAUjUF9Q2kYAkVgNBSFQYeG0jAESsMQKA1DoKgMerihNAyR0jBEisqgxxtKw9AoOgPFaNCT00BxGihGgwangeI0UJwGitNAMRq0dxooTgPFa6Bg2Hp2GihOAwXDWOc0UGrJsizLsrvoB9CYnev7MjguAAAAAElFTkSuQmCC" v-if="runtime.state"></cover-image>
											<!--cover-view class="captcha-state-icon-success" v-if="runtime.state">
												<cover-image src="./svg/success.svg"></cover-image>
											</cover-view-->
											<cover-image src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAMAAABHPGVmAAAAQlBMVEUAAAD///////////////////////////////////////////////////////////////////////////////////8IX9KGAAAAFXRSTlMAVX9cemN1aeZxbRTqx8aZlTMyLiNUX5/pAAABcElEQVRo3u3Zy47CMAyFYaekUAgtUPD7v+ps0JxBXRynrkdUyr9u9C2SSrlIq9VqtVo7rzxlRc8i9vKkOtwedcLrPqhOWYzpu67GSNf3KOPnukLBoCSWRv0t1xs6muZcERRqoCK8p/7tWG2oaWEORCHGIJZualZgoJsJeehn5ypDjb9XZ1ZgsGXPlcuGBspU4Ua9ctrQQEeqcKNe6QMMojgNdF4oNYZf8Rvoop8dTIZf4UZ9p6XCDb+yoYF6/SxZDL9iMPxKgAElxECHSANKpAEl0oASaKAUakBZGrtE0v9OCcqRBpRIA0qggY6RBpRAA50jjK6jit/gJwu/wU8WfsNysvAbTPEbNecXv7FU+gCD7PndBlf8Bt/z+w2u+A2+5/cbXPEbXEkbGqhfp7yuC6NCub5MyJ0YRLmbkMFmoEP99eBMDKrMwivEoEoRQxMxiDKJpey7Rs/f8yAgksa1TxtjEntllhXNRVqtVqvV2nc/mHqMd2BbR8EAAAAASUVORK5CYII=" v-if="runtime.fail"></cover-image>
											<!--cover-view class="captcha-state-icon-fail" v-if="runtime.fail">
												<cover-image src="./svg/fail.svg"></cover-image>
											</cover-view-->
										</cover-view>
									</canvas>
								</view>
							</view>
						</view>
						<movable-area :class="runtime.control ? 'captcha-control captcha-control-horizontal' : 'captcha-control'" :style="theme.control">
							<view class="captcha-control-wrap"></view>
							<movable-view :disabled="disabled" :class="runtime.buttonActive ? 'captcha-control-button captcha-button-active' : 'captcha-control-button'" :x="x" :animation="false" :damping="0" direction="horizontal"
				@change="onChange" @touchstart="touchStart" @touchend="touchEnd"><text class="icon"></text></movable-view>
						</movable-area>
					</view>
					<view class="captcha-timer-progress-bar-wrap">
						<view class="captcha-timer-progress-bar" :style="runtime.progressBar"></view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
let disPageX = 0
let isStart = false
let dragTimerState = false

let pos = {
	y: 0,
	x: 0,
	ave: 0
}
export default {
	name: 'isszz-captcha',
	props: {
		options: {
			type: Object,
			default: {
				theme: '#07f',
				title: '安全验证',
				desc: '拖动滑块，使图片角度为正',
				successClose: 1500, // 验证成功后页面关闭时间
				timerProgressBar: true, // 验证成功后关闭时是否显示进度条
				timerProgressBarColor: 'rgba(0, 0, 0, 0.2)',
				path: '',
				url: {
					info: '/captcha', // 获取验证码信息
					check: '/captcha/check', // 验证
					img: '/captcha/img', // 交换图片
				}
			}
		},
	},
	data() {
		return {
			rotate: 0,
			animation: null,
			animationData: {},
			x: 0,
			id: 0,
			url: '',
			// disPageX: 0,
			disabled: false,
			isStart: false,
			dragTimerState: false,
			runtime: {
				deg: 0,
				token: '',
				show: true,
				control: false,
				imageLoaded: false,
				progressBar: 'display: none',
				// transform: 'transform: rotate(0deg)',
				transform: 'transform: matrix(1, 0, 0, 1, 0, 0)',
				buttonTransform: '',
				buttonActive: false,
				stateClass: '',
				fail: false,
				state: false,
				coordinate: false,
			},
			theme: {
				root: '',
				wrap: '',
				image: '',
				img: '',
				control: '',
			},
			size: {
				img: 152,
				width: 305,
				imgMargin: 10,
				control: 275,
			},
		};
	},
	created() {
	},
	mounted() {
		this.init()
	},
	methods: {
		init() {
			const sysInfo = uni.getSystemInfoSync()
			// console.log(sysInfo)
			
			let index = 0
			
			// this.data.id = index++ || 0
			let padding = 60
			if(sysInfo.windowWidth < 417) {
				padding = 40
			}
			this.size.width = sysInfo.windowWidth - 60
			if(this.size.width > 520) {
				this.size.width = 520
			}
			
			this.size.img = parseInt(this.size.width / 2)
			// this.size.imgMargin = parseInt(this.size.width / 10)
			this.size.control = parseInt(this.size.width - 30)
		
			this.theme.root = '--theme: '+this.options.theme+';--progress-bar-color: '+this.options.timerProgressBarColor+';--size-width: 305px;--size-img: 152px;--size-img-margin: 28px;--size-control: 275px;'
			this.theme.wrap = '--size-width: '+this.size.width+'px'
			this.theme.image = '--size-img-margin: '+parseInt(this.size.width / 10)+'px'
			this.theme.img = '--size-img: '+this.size.img+'px'
			this.theme.control = '--size-control: '+this.size.control+'px'
			
			let _this = this
			
			pos.x = pos.y = this.size.img / 2
			pos.ave = Math.round((360 / (this.size.control - 50)) * 100 ) / 100
			
			this.ctx = uni.createCanvasContext('captchaImage', this)
			// this.ctx.width = this.size.img
			// this.ctx.height = this.size.img
			
			
			this.loadImage()
			
			//#ifdef MP
			this.$emit('init')// , _this
			//#endif 
			
			//#ifndef MP
			this.$emit('init', _this)
			//#endif
			/*
			let animation = uni.createAnimation({
				duration: 0
			})
			this.animation = animation*/
		},
		loadImage() {
			let callback = callback || function() {};
			
			let _this = this
			uni.request({
				url: _this.options.url.info,
				success: ((res) => {
					if(res.statusCode != 200) {
						uni.showModal({
							title: '提示',
							content: '系统出错：'+ res.statusCode +'，请关闭重试！',
							showCancel: false,
							success: function (res) {
								_this.$emit('close', false)
							}
						})
						return false
					}
					if(res.data.code == 0 || res.data.errno == 0) {
						_this.runtime.token = res.header['x-captchatoken'] || res.header['X-Captchatoken']
						
						/*uni.getImageInfo({
							src: _this.options.url.img + '?id=' + res.data.data.str,
							success: res => {
								// console.log(res)
								_this.url = res.path
								img.src = _this.url
								// _this.runtime.imageLoaded = true
							}
						})*/
						// let img = _this.ctx.node.createImage()
						// const canvas = _this.ctx._context.canvas.getContext('2d')
						// let img = _this.ctx._context.canvas.createImage()
						
						// console.log(img)
							
						//#ifdef MP-WEIXIN
						uni.downloadFile({
							url: _this.options.url.img + '?id=' + res.data.data.str,
							success: res => {
								if (res.statusCode == 200) {
									_this.runtime.imageLoaded = true
									_this.url = res.tempFilePath
									_this.ctx.drawImage(_this.url, 0, 0, _this.size.img, _this.size.img)
									// _this.ctx.restore()
									_this.ctx.draw()
								} else {
									uni.showToast({
										title: '图片下载异常',
										duration: 2000,
										icon: 'none'
									})
								}
							},
							fail(res) {
								console.log(res)
								uni.showToast({
									title: '图片下载异常2',
									duration: 2000,
									icon: 'none'
								})
							}
						})
						//#endif
						
						//#ifndef MP-WEIXIN
						const img = new Image()
						img.crossOrigin = 'anonymous'
						img.onerror = err => {
							uni.showToast({
								title: '图片下载异常',
								duration: 2000,
								icon: 'none'
							})
						}

						img.onload = (r) => {
							// _this.ctx.drawImage(r.path[0].src, 0, 0, _this.size.img, _this.size.img)
							_this.ctx.drawImage(_this.url, 0, 0, _this.size.img, _this.size.img)
							_this.ctx.restore()
							_this.ctx.draw(true)
						}
						
						uni.downloadFile({
							url: _this.options.url.img + '?id=' + res.data.data.str,
							success: res => {
								if (res.statusCode == 200) {
									_this.runtime.imageLoaded = true
									img.src = _this.url = res.tempFilePath
								} else {
									uni.showToast({
										title: '图片下载异常',
										duration: 2000,
										icon: 'none'
									})
								}
							},
							fail(res) {
								uni.showToast({
									title: '图片下载异常',
									duration: 2000,
									icon: 'none'
								})
							}
						})
						//#endif
						return false
					}
					_this.runtime.token = ''
				})
			})
			
		},
		check() {
			let _this = this
			
			uni.request({
				url: _this.options.url.check,
				data: {
					angle: this.runtime.deg
				},
				header: {
					'X-CaptchaToken': _this.runtime.token
				},
				success: ((res) => {
					_this.runtime.deg = 0
					_this.runtime.coordinate = false
					if(res.statusCode == 200 && (res.data.code == 0 || res.data.errno == 0)) {
						_this.runtime.state = true
						_this.runtime.fail = false
						_this.runtime.stateClass = ' captcha-success'
						_this.$emit('success')
						_this.$emit('complete', true)
						_this.timerProgressBar(parseInt(_this.options.successClose) || 1500)
						return false
					}
					
					_this.$emit('fail')
					_this.$emit('complete', false)
					_this.runtime.token = ''
					_this.runtime.state = false
					_this.runtime.fail = true
					_this.runtime.stateClass = ' captcha-fail'
					_this.runtime.control = true
					_this.dragTimerState = true
					
					if(res.statusCode != 200) {
						uni.showModal({
							title: '提示',
							content: '系统出错：'+ res.statusCode +'，请重试！',
							showCancel: false,
						})
					}
					setTimeout(function() {
						_this.x = 0
						_this.disabled = false
						_this.runtime.stateClass = ''
						_this.runtime.control = false
						_this.dragTimerState = false
						_this.refresh()
					}, 1000);
				})
			})
		},
		close() {
			this.$emit('close', this.runtime.state)
			this.runtime = {
				deg: 0,
				token: '',
				show: true,
				control: false,
				imageLoaded: false,
				progressBar: 'display: none',
				transform: 'transform: rotate(0deg)',
				buttonActive: false,
				stateClass: '',
				fail: false,
				state: false,
				coordinate: false,
			};
		},
        refresh() {
			this.runtime = {
				deg: 0,
				token: '',
				show: true,
				control: false,
				imageLoaded: false,
				progressBar: 'display: none',
				transform: 'transform: rotate(0deg)',
				buttonActive: false,
				stateClass: '',
				fail: false,
				state: false,
				coordinate: false,
			};
			
			this.loadImage()
		},
		onChange(e) {
			if (!this.runtime.imageLoaded || this.dragTimerState || this.runtime.state || this.runtime.stateClass != '') {
				this.disabled = true
				return false
			}
			
			this.disabled = false
			if(e.detail.source === 'touch') {
				this.x = e.detail.x
			}

			this.move()
		},
		touchStart(e) {
			let _this = this
			if (!_this.runtime.imageLoaded || _this.dragTimerState || _this.runtime.state || _this.runtime.stateClass != '') {
				_this.disabled = true
				return false;
			}
			
			_this.isStart = true
			_this.disabled = false
			_this.runtime.buttonActive = true
			_this.runtime.coordinate = true
		},
		
		touchEnd() {
			if (!this.isStart) {
				return false
			}
			
			this.isStart = false
			
			if (this.runtime.state || this.runtime.stateClass != '') {
				return false
			}
			
			this.runtime.buttonActive = false
			this.runtime.coordinate = false
			
			if(!this.runtime.deg || this.x < 5) {
				this.runtime.coordinate = false
				return false
			}
			
			this.check()
		},
		move() {
			if (this.x < 0) {
				this.x = 0
			} else if (this.x >= this.size.control - 50) {
				this.x = this.size.control - 50
			}
			
			let width = this.size.control - 50
			this.runtime.deg = (360 / width) * this.x
			// let deg = (360 / width) * _this.x
			
			this.ctx.translate(pos.x, pos.y)
			this.ctx.rotate(this.x * pos.ave * Math.PI / 180)
			this.ctx.translate(-pos.x, -pos.y)
			this.ctx.drawImage(
				this.url,
				0,
				0,
				this.size.img,
				this.size.img
			)
			this.ctx.restore()
			this.ctx.draw()
			
			// this.rotate = deg
			// _this.imageAnimation(deg)
			
			// this.runtime.transform = 'perspective: 1000;transform: translateZ(0);transform: rotate('+ deg +'deg)'
		
			if (this.x > (width + 1)) {
				this.x = 0
			}
		
			if(!this.runtime.fail) {
				if (this.x < (width + 1) && this.x > -1) {
					if (this.x == 0) {
						this.x = 0
					}
				} else {
					this.x = width
					this.runtime.fail = true
					this.runtime.buttonActive = false
				}
			}
			
			// this.buttonAnimation(this.x)
			
			// this.runtime.buttonTransform = '-webkit-overflow-scrolling:touch;transform: translateZ(0);transform: translateX('+ this.x +'px)'
			// this.spinImage()
		},
		spinImage() {
			this.runtime.transform = 'transform: translateZ(0);transform: rotate('+ this.runtime.deg +'deg)'
		},
		imageLoaded() {
			this.runtime.imageLoaded = true
		},
		timerProgressBar(timer) {
			const _this = this;

			if(!timer) {
				return false
			}

			if(!_this.options.timerProgressBar) {
				setTimeout(function() {
					_this.$emit('close', _this.runtime.state)
				}, timer)
				return false
			}

			setTimeout(function() {
				_this.$emit('close', _this.runtime.state)
			}, timer + 10)

			this.runtime.progressBar = 'display: flex'
			
			setTimeout(() => {
				_this.runtime.progressBar = `display: flex;transition: width ${timer / 1000}s linear;width: 0%`
			}, 10)
		},
		imageAnimation(deg) {
			let _this = this
			// this.animation.rotate3d(1, 1, 1, deg).step()
			// this.animation.rotate(deg).step()
			
			let rad = deg * (Math.PI / 180)
			let m11 = Math.cos(rad), m12 = -1 * Math.sin(rad), m21 = Math.sin(rad), m22 = m11
			
			_this.runtime.transform = `transform: matrix(${m11}, ${m12}, ${m21}, ${m22}, 0, 0)`
			
			// this.animation.matrix(m11, m12, m21, m22, 0, 0).step()
			// this.animation.matrix3d(m11, m12, 0, 0, m21, m22, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1).step()
			// this.animationData = this.animation.export()
			
		}
	},
	watch: {
	}
}
</script>
<style scoped>
/*! Rotate captcha CSS - v0.0.1 | https://github.com/isszz/rotate-captcha | https://cfyun.cc | Copyright (c) 2021 CFYun | MIT license */
.captcha-root .captcha-canvas-image {
	width: 100%;
	height: 100%;
	/*position: relative;
	z-index: 1;*/
}
.captcha-root {
	position: fixed;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	z-index: 999;
	opacity: 0;
	outline: 0;
	text-align: center;
	-ms-transform: scale(1.185);
	transform: scale(1.185);
	backface-visibility: hidden;
	perspective: 2000upx;
	background: rgba(0, 0, 0, 0.6);
	transition: all 0.3s ease-in-out 0s;
	pointer-events: none;
}
.captcha-root::before {
	content: "\200B";
	display: inline-block;
	height: 100%;
	vertical-align: middle;
}

.captcha-root.show {
	opacity: 1;
	transition-duration: 0.3s;
	-ms-transform: scale(1);
	transform: scale(1);
	overflow-x: hidden;
	overflow-y: auto;
	pointer-events: auto;
}
.captcha-modal {
	position: relative;
	display: inline-block;
	vertical-align: middle;
	margin-left: auto;
	margin-right: auto;
	max-width: 100%;
	background-color: #fff;
	border-radius: 10upx;
	overflow: hidden;
}
.captcha-modal-close,
.captcha-modal-close image {
	width: 30rpx;
	height: 30rpx;
}
.captcha-modal-close {
	position: absolute;
	top: 20rpx;
	right: 20rpx;
	z-index: 993;
	cursor: pointer;
}
.captcha {
	position: relative;
	width: var(--size-width);
	padding: 20px 15px 25px;
	text-align: center;
}
.captcha .captcha-title .title {
	font-size: 14px;
	line-height: 14px;
	color: #b8b8b8;
	padding-bottom: 10px;
}
.captcha .captcha-title .desc {
	font-size: 18px;
	line-height: 24px;
	color: #1f1f1f;
}
.captcha-wrap {
	display: flex;
	align-items: center;
	justify-content: center;
}
.captcha-image {
	position: relative;
	overflow: hidden;
	margin: var(--size-img-margin) auto;
}
.captcha-img {
	position: relative;
	z-index: 991;
	width: var(--size-img);
	height: var(--size-img);
	-webkit-border-radius: 50%;
	border-radius: 50%;
	background-color: #f5f5f5;
	background-image: url(./svg/loading.gif);
	background-repeat: no-repeat;
	background-position: center;
}
.captcha-img img,
.captcha-img image {
	width: var(--size-img);
	height: var(--size-img);
	pointer-events: none;
	border: 0;
	-webkit-border-radius: 50%;
	border-radius: 50%;
	-webkit-background-size: 100% 100%;
	background-size: 100% 100%;
	-webkit-animation: fadeIn 0.8s forwards;
	animation: fadeIn 0.8s forwards;
}

.captcha-img.captcha-loading image,
.captcha-img.captcha-loading .captcha-canvas-image {
	/*display: none;*/
}
.captcha-img .captcha-loader {
	width: 100%;
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
}
.captcha-img .captcha-loader image,
.captcha-img .captcha-loader cover-image {
	width: 77rpx;
	height: 77rpx;
	display: block;
}
.captcha-img.captcha-loading .captcha-loader {
	display: flex;
}
/*
.captcha-coordinate {
	position: absolute;
	z-index: 99992;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAcoAAAHLBAMAAAC67sVKAAAAG1BMVEUAAAD////////////////////////////////rTT7CAAAACXRSTlMAzGcNwPRatBpQE/jnAAACVElEQVR42u3asU3DUBSG0ShZgBsxAO9NkMILBCagSI+yAg0lo1PYCZZoiW3de05DiT7Br2sHdjsA8viuEHn4rFC5P1aoHJ5PBSpbvBSY5TneCswyosAwh4gCw2wR+Yd5OEfkH+Y+osAwh4gCw2wR+Yd5OEfkH+Y+osAwh7Ey+TDHWSYf5jTL5MOcZpl8mMOtMvUwb7NMPcz7LFMP8z7L1MMcfisTD/N3lomHOZtl4mHOZpl4mMO8Mu0w57PMO8xr7733iGPvvfeP1K8lEU+7/GpUAoB7qVKlSpUqAcC9VKlSpUqV3kkAwL1UqVKlSpUA4F6qVKlSpUrvJADgXqpUqVKlSgBwL1WqVKlSpXcSAHAvVapUqVIlALiXKlWqVKkSAPBUoFKlSpUqAcC9VKlSpUqVAICnApUqVapUCQDupUqVKlWqBAA8FahUWbTyeqpQ2V5PFSpj/czHa1Ehs0WFzBYVMltUyBwrs2dOlckzb5UrZi5yL1fPXLTynhnjt13sy+Xy/vhv9CczeeWUmb0yvvwsc+0y871M/WDQKkRu4dnHc6x3Eu+X2/usYKGn9ZV/kstUrv3rWuPzWJ+t/5MafyfZAP+7BQDupUqVKlWqBAD3UqVKlSpVAgCeClSqVKlSJQC4lypVqlSpEgDwVKBSpUqVKgHAvVSpUqVKlQCApwKVKlWqVAkA7qVKlSpVqgQA7yQqVapUqVIlALiXKlWqVKkSALyTqFSpUqVKlQDgXqpUqVKlSgDwTqJSpUqVKlUCgHupUqVKlSoBwL1UqVKlSpUqN+YHG9iGwKYOVR0AAAAASUVORK5CYII=);
	background-size: 100% 100%;
	background-repeat: no-repeat;
}*/
.captcha-coordinate {
	width: 100%;
	height: 100%;
}
.captcha-fail .captcha-coordinate {
	display: none;
}
.captcha-control {
	position: relative;
	width: var(--size-control);
	height: 50px;
	margin: 0 auto;    
}
.captcha-control-wrap,
.captcha-control-button {
	position: absolute;
	top: 0;
	height: 100%;
	-webkit-border-radius: 100px;
	border-radius: 100px;
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
}
.captcha-control-wrap {
	left: 0;
	width: 100%;
	background: #f5f5f5;
	overflow: hidden;
}
.captcha-control-button {
	position: absolute;
	width: 50px;
	background: #fff;
	cursor: pointer;
	-webkit-box-shadow: 0 21px 52px 0 rgba(82,82,82,.2);
	box-shadow: 0 21px 52px 0 rgba(82,82,82,.2);
	-webkit-transform: translateX(0);
	-ms-transform: translateX(0);
	-moz-transform: translateX(0);
	transform: translateX(0);
}
.captcha-control-button .icon {
	position: absolute;
	top: 50%;
	left: 50%;
	width: 28px;
	height: 28px;
	margin-left: -14px;
	margin-top: -14px;
	background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 1024 1024" version="1.1"><path fill="%23444444" d="M384 896a32 32 0 0 1-32-32V160a32 32 0 0 1 64 0v704a32 32 0 0 1-32 32z m257.056 0.128a32 32 0 0 1-32-32v-704a32 32 0 1 1 64 0v704a32 32 0 0 1-32 32zM864 736a32 32 0 0 1-32-32V320a32 32 0 1 1 64 0v384a32 32 0 0 1-32 32zM160 736a32 32 0 0 1-32-32V320a32 32 0 0 1 64 0v384a32 32 0 0 1-32 32z"></path></svg>');
	-webkit-background-size: 100% 100%;
	background-size: 100% 100%;
	background-repeat: no-repeat;
}
.captcha-fail .captcha-control-button {
	border: 1px solid #f33;
	background: #f33
}
.captcha-control-button.captcha-button-active {
	color: #fff;
	background: var(--theme);
}
.captcha-control-button.captcha-button-active .icon,
.captcha-fail .captcha-control-button .icon {
	background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 1024 1024" version="1.1"><path fill="%23ffffff" d="M384 896a32 32 0 0 1-32-32V160a32 32 0 0 1 64 0v704a32 32 0 0 1-32 32z m257.056 0.128a32 32 0 0 1-32-32v-704a32 32 0 1 1 64 0v704a32 32 0 0 1-32 32zM864 736a32 32 0 0 1-32-32V320a32 32 0 1 1 64 0v384a32 32 0 0 1-32 32zM160 736a32 32 0 0 1-32-32V320a32 32 0 0 1 64 0v384a32 32 0 0 1-32 32z"></path></svg>');
}
/* state */
.captcha-image .captcha-state {
	/*display: none;*/
	position: absolute;
	z-index: 992;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	-webkit-border-radius: 50%;
	border-radius: 50%;
	background: rgba(0,0,0,0.7);
	transform: translate(0, 0);
	-webkit-animation: fadeIn 0.3s forwards;
	animation: fadeIn 0.3s forwards;
	display: flex;
	justify-content: center;
	align-items: center;
	justify-items: center;
}
.captcha-image .captcha-state cover-image {
	width: 80px;
	height: 80px;
}
/* fade anim */
@-webkit-keyframes fadeIn {
	from {
		opacity: 0;
	}
	to {
		opacity: 1;
	}
}
@keyframes fadeIn {
	from {
		opacity: 0;
	}
	to {
		opacity: 1;
	}
}
/* fail anim */
@-webkit-keyframes horizontal {
	0% {
		-webkit-transform: translate(0px,0);
		-ms-transform: translate(0px,0);
		transform: translate(0px,0)
	}

	10%,30%,50%,70%,90% {
		-webkit-transform: translate(-1px,0);
		transform: translate(-1px,0)
	}

	20%,40%,60%,80% {
		-webkit-transform: translate(1px,0);
		transform: translate(1px,0)
	}

	100% {
		-webkit-transform: translate(0px,0);
		transform: translate(0px,0)
	}
}
@keyframes horizontal {
	0% {
		-webkit-transform: translate(0px,0);
		transform: translate(0px,0)
	}

	10%,30%,50%,70%,90% {
		-webkit-transform: translate(-1px,0);
		transform: translate(-1px,0)
	}

	20%,40%,60%,80% {
		-webkit-transform: translate(1px,0);
		transform: translate(1px,0)
	}

	100% {
		-webkit-transform: translate(0px,0);
		transform: translate(0px,0)
	}
}
.captcha-control-horizontal {
	-webkit-animation: horizontal .6s .2s ease both;
	-moz-animation: horizontal .6s .2s ease both;
	animation: horizontal .6s .2s ease both
}
/* success timer progress */
.captcha .captcha-timer-progress-bar-wrap {
	position: absolute;
	right: 0;
	bottom: 0;
	left: 0;
	height: 4px;
	overflow: hidden;
	border-bottom-right-radius: 5px;
	border-bottom-left-radius: 5px;
}
.captcha .captcha-timer-progress-bar {
	width: 100%;
	height: 4px;
	background-color: var(--progress-bar-color);
}
</style>
