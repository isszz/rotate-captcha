<template name="isszz-captcha">
	<view :class="'captcha-root'+ runtime.show" :style="theme.root">
		<view class="captcha-modal">
			<view class="captcha-modal-close" @click.stop="close"><image src="./svg/close.svg"></image></view>
			<view class="captcha-wrap">
				<view class="captcha" :style="theme.wrap">
					<view class="captcha-title">
						<view class="title">{{options.title}}</view>
						<text class="desc">{{options.desc}}</text>
					</view>
					<view :class="'captcha-main'+runtime.stateClass">
						<view class="captcha-wrap">
							<view class="captcha-image" :style="theme.image">
								<view :class="runtime.imageLoaded ? 'captcha-img' : 'captcha-img captcha-loading'" :style="theme.img">
									<image :src="url" :style="runtime.transform" @load="imageLoaded"></image>
									<view class="captcha-loader">
										<image src="./svg/loading.svg"></image>
									</view>
								</view>
								<view class="captcha-coordinate" v-show="runtime.coordinate"></view>
								<view class="captcha-state">
									<view class="captcha-state-icon-success">
										<image src="./svg/success.svg"></image>
									</view>
									<view class="captcha-state-icon-fail">
										<image src="./svg/fail.svg"></image>
									</view>
								</view>
							</view>
						</view>
						<movable-area :class="'captcha-control'+runtime.control" :style="theme.control">
							<view class="captcha-control-wrap"></view>
							<movable-view :class="runtime.buttonActive ? 'captcha-control-button captcha-button-active' : 'captcha-control-button'" :x="x" :damping="100" :disabled="disabled" direction="horizontal"
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
			x: 0,
			disPageX: 0,
			id: 0,
			url: '',
			disabled: false,
			isStart: false,
			dragTimerState: false,
			runtime: {
				deg: 0,
				token: '',
				show: ' show',
				control: '',
				imageLoaded: false,
				progressBar: 'display: none',
				transform: 'transform: rotate(0deg)',
				buttonActive: false,
				stateClass: '',
				fail: false,
				state: false,
				loaded: false,
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
		const sysInfo = uni.getSystemInfoSync()
		// console.log(sysInfo)
		
		let index = 0
		this.token = ''
		
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
		this.size.control = parseInt(this.size.width - 30)
		this.size.imgMargin = parseInt(this.size.width / 10);
	},
	mounted() {
		this.init()
		// PC兼容, 有bug, 需重写
		if(navigator.userAgent.indexOf('Mobile') === -1) {
			document.querySelector('.captcha-control-button').addEventListener('mousedown', this.touchStart, false)
			document.querySelector('.captcha-control-button').addEventListener('mouseup', this.touchEnd, false)
		}
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
			
			this.loadImage();
		},
		loadImage() {
			let callback = callback || function() {};
			
			const _this = this
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
					if(res.data.code == 0) {
						_this.runtime.token = res.header['x-captchatoken']
						_this.url = _this.options.url.img + '?id=' + res.data.data.str
						return false
					}
					_this.runtime.token = ''
				})
			})
		},
		check() {
			const _this = this
			
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
					if(res.statusCode == 200 && res.data.code == 0) {
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
						_this.runtime.control = ''
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
				show: ' show',
				control: '',
				imageLoaded: false,
				progressBar: 'display: none',
				transform: 'transform: rotate(0deg)',
				buttonActive: false,
				stateClass: '',
				fail: false,
				state: false,
				loaded: false,
				coordinate: false,
			};
		},
        refresh() {
			this.runtime = {
				deg: 0,
				token: '',
				show: ' show',
				control: '',
				imageLoaded: false,
				progressBar: 'display: none',
				transform: 'transform: rotate(0deg)',
				buttonActive: false,
				stateClass: '',
				fail: false,
				state: false,
				loaded: false,
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

			this.move(this.x)
		},
		touchStart() {
			if (!this.runtime.imageLoaded || this.runtime.state || this.dragTimerState) {
				this.disabled = true
				return false;
			}
			this.isStart = true
			this.disabled = false
			this.runtime.buttonActive = true
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
			
			if(!this.runtime.deg || this.x < 5) {
				this.runtime.coordinate = false
				this.runtime.transform = 'transform: rotate(0deg)'
				return false
			}
			this.check()
		},
		move(x) {
			if (x < 0) {
				x = 0
			} else if (x >= this.size.control) {
				x = this.size.control
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
					}
				}
			}
			this.spinImage()
		},
		spinImage() {
			if(this.runtime.deg) {
				this.runtime.coordinate = true
			} else {
				this.runtime.coordinate = false
			}
			
			this.runtime.transform = 'transform: rotate('+ this.runtime.deg +'deg)'
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
		}
	},
	watch: {
	}
}
</script>
<style>
	@import 'captcha.css';
</style>
