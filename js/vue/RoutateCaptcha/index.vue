<template name="RoutateCaptcha">
	<div :class="'captcha-root'+ (runtime.show ? ' show' : '')" :style="theme.root">
		<div class="captcha-modal">
			<div class="captcha-modal-close" @click.stop="close"><img src="./svg/close.svg" /></div>
			<div class="captcha-wrap">
				<div class="captcha" :style="theme.wrap">
					<div class="captcha-title">
						<div class="title">{{options.title}}</div>
						<span class="desc">{{options.desc}}</span>
					</div>
					<div :class="'captcha-main'+runtime.stateClass">
						<div class="captcha-wrap">
							<div class="captcha-image" :style="theme.image">
								<div :class="runtime.loaded ? 'captcha-img' : 'captcha-img captcha-loading'" :style="theme.img">
									<img :src="url" :style="runtime.transform" @load="imageLoaded" />
									<div class="captcha-loader">
										<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 38 38">
											<defs>
												<linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a">
													<stop :stop-color="options.theme || '#07f'" stop-opacity="0" offset="0%" />
													<stop :stop-color="options.theme || '#07f'" stop-opacity=".631" offset="63.146%" />
													<stop :stop-color="options.theme || '#07f'" offset="100%" />
												</linearGradient>
											</defs>
											<g fill="none" fill-rule="evenodd">
												<g transform="translate(1 1)">
													<path d="M36 18c0-9.94-8.06-18-18-18" id="Oval-2" stroke="url(#a)" stroke-width="2">
														<animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite" />
													</path>
													<circle :fill="options.theme || '#07f'" cx="36" cy="18" r="1">
														<animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite" />
													</circle>
												</g>
											</g>
										</svg>
									</div>
								</div>
								<div class="captcha-coordinate" v-if="runtime.coordinate"></div>
								<div class="captcha-state">
									<div class="captcha-state-icon-success">
										<img src="./svg/success.svg" />
									</div>
									<div class="captcha-state-icon-fail">
										<img src="./svg/fail.svg" />
									</div>
								</div>
							</div>
						</div>
						<div :class="'captcha-control'+runtime.control" :style="theme.control">
							<div class="captcha-control-wrap"></div>
							<div ref="captchaControlButton" :style="runtime.buttonTransform" :class="runtime.buttonActive ? 'captcha-control-button captcha-button-active' : 'captcha-control-button'" :x="x" :disabled="disabled"><i class="icon"></i></div>
						</div>
					</div>
					<div class="captcha-timer-progress-bar-wrap">
						<div class="captcha-timer-progress-bar" :style="runtime.progressBar"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
const isTouch = 'ontouchstart' in window
export default {
	name: 'RoutateCaptcha',
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
				request: {
					info: (callback) => {
					},
					check: (angle, token, callback) => {
					},
					img: (id) => { 
					},
				},
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
			x: 0,
			id: 0,
			url: '',
			disPageX: 0,
			disabled: false,
			isStart: false,
			dragTimerState: false,
			runtime: {
				deg: 0,
				token: '',
				show: true,
				control: '',
				loaded: false,
				progressBar: 'display: none',
				transform: 'transform: rotate(0deg)',
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
				width: 305,
				img: 152,
				control: 275,
				imgMargin: 10,
			},
		};
	},
	created() {
		let index = 0
		let clientWidth = document.body.clientWidth
		this.token = ''
		
		let padding = 60
		if(clientWidth < 417) {
			padding = 40
		}
		this.size.width = clientWidth - 60
		if(this.size.width > 520) {
			this.size.width = 520
		}
		this.size.img = parseInt(this.size.width / 2)
		this.size.control = parseInt(this.size.width - 30)
		this.size.imgMargin = parseInt(this.size.width / 10);
	},
	mounted() {
		this.init()
	},
	methods: {
		init() {
			this.theme.root = '--theme: '+this.options.theme+';--progress-bar-color: '+this.options.timerProgressBarColor+';--size-width: 305px;--size-img: 152px;--size-img-margin: 28px;--size-control: 275px;'
			this.theme.wrap = '--size-width: '+this.size.width+'px'
			this.theme.image = '--size-img-margin: '+this.size.imgMargin+'px'
			this.theme.img = '--size-img: '+this.size.img+'px'
			this.theme.control = '--size-control: '+this.size.control+'px'
			
			this.$emit('init', this)
			
			const _this = this

            _this.$controlButton = this.$el.querySelectorAll('.captcha-root .captcha-control-button')[0]
			
			if(isTouch) {
				_this.initTouch()
			} else {
				_this.initMouse()
			}
			
			this.loadImage()
		},
		loadImage() {
			let callback = callback || function() {}
			
			const _this = this
			this.options.request.info((res, token) => {
				if(res.code == 0) {
					_this.runtime.token = token
					_this.url = _this.options.request.img(res.data.str)
					return false
				}
				_this.runtime.token = ''
			})
		},
		check() {
			const _this = this
			this.options.request.check(this.runtime.deg, _this.runtime.token, (res) => {
				_this.runtime.deg = 0
				_this.runtime.coordinate = false
				if(res.code == 0) {
					_this.runtime.state = true
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
				_this.runtime.stateClass = ' captcha-fail'
				_this.runtime.control = ' captcha-control-horizontal'
				_this.dragTimerState = true
				_this.animated = true
				
				setTimeout(function() {
					_this.x = 0
					_this.disabled = false
					_this.runtime.stateClass = ''
					_this.runtime.control = ''
					_this.dragTimerState = false
					_this.animated = false
					_this.refresh()
				}, 1000)				
			})
		},
		close() {
			this.$emit('close', this.runtime.state)
			this.refresh(false)
		},
        refresh(isLoadImage) {
			this.runtime = {
				deg: 0,
				token: '',
				show: true,
				control: '',
				progressBar: 'display: none',
				transform: 'transform: rotate(0deg)',
				buttonActive: false,
				stateClass: '',
				fail: false,
				state: false,
				loaded: false,
				coordinate: false,
			};
			
			if(isLoadImage || true) {
				this.loadImage()
			}
		},
		move(x) {
			if (x < 0) {
				x = 0
			} else if (x >= this.size.control - 50) {
				x = this.size.control - 50
			}
		
			let width = this.size.control - 50
			this.runtime.deg = (360 / width) * x
		
			if (x > (width + 1)) {
				this.x = 0
			}
		
			if(!this.runtime.fail) {
				if (x < (width + 1) && x > -1) {
					if (x == 0) {
						this.x = 0
					} else {
						this.x = x
					}
				} else {
					this.x = width
					this.runtime.fail = true
					this.runtime.buttonActive = false
				}
			}
			this.runtime.buttonTransform = 'transform: translateX('+ this.x +'px)'
			this.spinImage()
		},
		initTouch() {
			let _this = this;

			let disPageX = 0

			const move = function(e) {
				e.preventDefault()
				
				if (!_this.isStart || _this.dragTimerState) {
					return !1;
				}
				
				_this.x = e.targetTouches[0].pageX - disPageX
				
				_this.move(_this.x)
			}
			
			_this.$controlButton.addEventListener('touchstart', function (e) {
				if (
					!_this.runtime.loaded ||
					_this.runtime.state ||
					_this.dragTimerState ||
					_this.runtime.stateClass != ''
				) {
					_this.disabled = true
					return false
				}
				
				_this.isStart = true
				_this.disabled = false
				
				disPageX = e.targetTouches[0].pageX

				_this.runtime.coordinate = true
				_this.runtime.buttonActive = true
			})

			_this.$controlButton.addEventListener('touchmove', move, false)

			_this.$controlButton.addEventListener('touchend', function (e) {
				if (!_this.isStart) {
					return false
				}

				_this.isStart = false
				
				if (_this.runtime.state || _this.dragTimerState || _this.runtime.stateClass != '') {
					return false
				}
				
				_this.runtime.buttonActive = false
				
				document.removeEventListener('touchmove', move)

				if(!_this.runtime.deg || _this.runtime.left < 5) {
					_this.runtime.coordinate = false
					_this.runtime.transform = 'transform: rotate(0deg)'
					_this.runtime.buttonTransform = 'transform: translateX'
					return false
				}

				// 验证
				_this.check()
			}, false)	
		},
        initMouse() {
			const _this = this
			
			let disPageX = 0
			
			const move = function(e) {
				if (!_this.isStart) {
					return false
				}
				
				_this.x = e.pageX - disPageX
				
				_this.move(_this.x)
				e.preventDefault()
			}
			
			_this.$controlButton.addEventListener('mousedown', function (e) {
				if (
					!_this.runtime.loaded ||
					_this.dragTimerState ||
					_this.runtime.state ||
					_this.runtime.stateClass != ''
				) {
					_this.disabled = true
					return false
				}
				
				_this.isStart = true
				_this.disabled = false
				
				disPageX = e.pageX
				
				_this.runtime.buttonActive = true
			
				document.addEventListener('mousemove', move)
			}, false);

			document.addEventListener('mouseup', function () {
				if (!_this.isStart) {
					return false
				}
				
				_this.isStart = false
				
				if (_this.runtime.state || _this.runtime.stateClass != '') {
					return false
				}
				
				_this.runtime.buttonActive = false
				
				document.removeEventListener('mousemove', move)
				
				if(!_this.runtime.deg || _this.x < 5) {
					_this.runtime.coordinate = false
					_this.runtime.transform = 'transform: rotate(0deg)'
					_this.runtime.buttonTransform = 'transform: translateX'
					return false
				}
				_this.check()
			}, false)
        },
		spinImage() {
			this.runtime.transform = 'transform: rotate('+ this.runtime.deg +'deg)'
		},
		imageLoaded() {
			this.runtime.loaded = true
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
		}
	},
	watch: {
	}
}
</script>
<style scoped>
/*! Rotate captcha CSS - v0.0.1 | https://github.com/isszz/rotate-captcha | https://cfyun.cc | Copyright (c) 2021 CFYun | MIT license */
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
	perspective: 2000px;
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
	border-radius: 10px;
	overflow: hidden;
}
.captcha-modal-close,
.captcha-modal-close img {
	width: 15px;
	height: 15px;
}
.captcha-modal-close {
	position: absolute;
	top: 10px;
	right: 10px;
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
	font-size: 16px;
	line-height: 16px;
	font-weight: bold;
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
	background: #f5f5f5;
}
.captcha-img img, .captcha-img img {
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

.captcha-img.captcha-loading img {
	display: none;
}
.captcha-img .captcha-loader {
	display: none;
	width: 100%;
	height: 100%;
	align-items: center;
	justify-content: center;
}
.captcha-img .captcha-loader img {
	width: 60px;
	height: 60px;
	display: block;
}
.captcha-img.captcha-loading .captcha-loader {
	display: flex
}
.captcha-coordinate {
	/*display: none;*/
	position: absolute;
	z-index: 992;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAcoAAAHLBAMAAAC67sVKAAAAG1BMVEUAAAD////////////////////////////////rTT7CAAAACXRSTlMAzGcNwPRatBpQE/jnAAACVElEQVR42u3asU3DUBSG0ShZgBsxAO9NkMILBCagSI+yAg0lo1PYCZZoiW3de05DiT7Br2sHdjsA8viuEHn4rFC5P1aoHJ5PBSpbvBSY5TneCswyosAwh4gCw2wR+Yd5OEfkH+Y+osAwh4gCw2wR+Yd5OEfkH+Y+osAwh7Ey+TDHWSYf5jTL5MOcZpl8mMOtMvUwb7NMPcz7LFMP8z7L1MMcfisTD/N3lomHOZtl4mHOZpl4mMO8Mu0w57PMO8xr7733iGPvvfeP1K8lEU+7/GpUAoB7qVKlSpUqAcC9VKlSpUqV3kkAwL1UqVKlSpUA4F6qVKlSpUrvJADgXqpUqVKlSgBwL1WqVKlSpXcSAHAvVapUqVIlALiXKlWqVKkSAPBUoFKlSpUqAcC9VKlSpUqVAICnApUqVapUCQDupUqVKlWqBAA8FahUWbTyeqpQ2V5PFSpj/czHa1Ehs0WFzBYVMltUyBwrs2dOlckzb5UrZi5yL1fPXLTynhnjt13sy+Xy/vhv9CczeeWUmb0yvvwsc+0y871M/WDQKkRu4dnHc6x3Eu+X2/usYKGn9ZV/kstUrv3rWuPzWJ+t/5MafyfZAP+7BQDupUqVKlWqBAD3UqVKlSpVAgCeClSqVKlSJQC4lypVqlSpEgDwVKBSpUqVKgHAvVSpUqVKlQCApwKVKlWqVAkA7qVKlSpVqgQA7yQqVapUqVIlALiXKlWqVKkSALyTqFSpUqVKlQDgXqpUqVKlSgDwTqJSpUqVKlUCgHupUqVKlSoBwL1UqVKlSpUqN+YHG9iGwKYOVR0AAAAASUVORK5CYII=);
	-webkit-background-size: 100% 100%;
	background-size: 100% 100%;
	background-repeat: no-repeat;
}
.captcha-fail .captcha-coordinate {
	display: none;
}
.captcha-control {
	position: relative;
	width: var(--size-control);
	height: 50px;
	margin: 0 auto;
	user-select: none;
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
	display: none;
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
}
.captcha-image .captcha-state .captcha-state-icon-success,
.captcha-image .captcha-state .captcha-state-icon-fail,
.captcha-image .captcha-state .captcha-state-icon-success img,
.captcha-image .captcha-state .captcha-state-icon-fail img {
	width: 100%;
	height: 100%;
}
.captcha-image .captcha-state .captcha-state-icon-fail,
.captcha-image .captcha-state .captcha-state-icon-success {
	display: none;
}
.captcha-success .captcha-image .captcha-state,
.captcha-fail .captcha-image .captcha-state,
.captcha-fail .captcha-image .captcha-state .captcha-state-icon-fail,
.captcha-success .captcha-image .captcha-state .captcha-state-icon-success {
	display: block;
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
