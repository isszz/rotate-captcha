define(function (require, exports, module) {

    // 用了seajs - -...
    require('modal');
    require('template.web');

    require('./style.css');

    var libName = 'rotateCaptcha';
    var index = 0;
    var instances = [];

    var defaults = {
        theme: '#07f',
        title: '安全验证',
        desc: '拖动滑块，使图片角度为正',
        width: 260,
        successClose: 1500,
        timerProgressBar: !0,
        api: '',
        init: function (RotateCaptcha) {}, // 初始化
        success: function () {}, // 验证成功
        fail: function () {}, // 验证失败
        complete: function (state) {}, // 触发验证, 不管成功与否
        close: function (state) {}, // 关闭验证码窗口
    };

	template.defaults.escape = !1;

	class RotateCaptcha {
        constructor(element, options) {
            var _this = this;

            _this.index = index++ || 0;
            _this.options = $.extend({}, defaults, options);
            _this.options.id = 'J_rotate_captcha_' + (_this.options.id || _this.index);

            _this.element = element;

            _this.element.off('click.open.' + libName).on('click.open.' + libName, function(e) {
                e.preventDefault();
                _this.show();
            });

            // _this.render();
        }

        show() {
            var _this = this;

            // console.log(_this.captchaHTML(_this.options.id, _this.options.theme, _this.options.title, _this.options.desc))

            // var render = template.compile(captchaHTML);

            // this.html = $(render(_this.options));

            // _this.element = $(render(_this.options)).appendTo('body');
            _this.modal = null;
            $.modal.flow({
                closeType: !0,
                content: _this.captchaHTML(_this.options),
                show: function(modal) {
                    _this.modal = modal;
                    _this.render(modal.element.find('.captcha-root'));
                },
                close: function(modal) {
                    _this.options.close(_this.runtime.state);
                }
            });

        }
        
        render(elem) {
            var _this = this;

            _this.$elem = elem;

            _this.runtime = {
                deg: 0,
                left: 0,
                state: !1,
                loaded: !1,
            };

            _this.options.init(_this);


            _this.$main = elem.find('.captcha-main');

            _this.$slideDragWrap = elem.find('.captcha-control');
            _this.$slideDragBtn = elem.find('.captcha-control-button');
            _this.$coordinate = elem.find('.captcha-coordinate');
            _this.$controlWrap = elem.find('.captcha-control-wrap');

            _this.$captchaImg = elem.find('.captcha-img');
            
            // _this.controlBorWrap = elem.find('.captcha-control');
            _this.loadImg(function() {
                _this.events();
            });
        }

        loadImg(callback) {
            var _this = this;

            callback = callback || function() {};
            
            _this.runtime.loaded = !1;
            _this.$captchaImg.addClass('captcha-loading');
            
            $.getJSON(_this.options.api + '/rotate').done(function(res) {
                if(res.code === 0) {
                    _this.$rotateCaptchaImg = _this.$captchaImg.find('img').attr('src', _this.options.api + '/img?id=' + res.data).css({transform: 'rotate(0deg)'});

                    _this.$rotateCaptchaImg[0].onload = function () {
                        _this.runtime.loaded = !0;
                        _this.$captchaImg.removeClass('captcha-loading');
                        // console.log('captcha loaded');
                    };

                    if(typeof callback == 'function') {
                        callback();
                    }
                }
            });
        }
        
        events(elem) {
            var _this = this;

            _this.initMouse();
            _this.initTouch();
            // this._touchstart();
            // this._touchend();
        }

        spinImg() {
			var _this = this;

            if(this.runtime.deg) {
                _this.$coordinate.show();
            } else {
                _this.$coordinate.hide();
            }
            
            _this.$rotateCaptchaImg.css({transform: 'rotate('+ this.runtime.deg +'deg)'});
            // console.log(this.runtime);
        }

        initMouse() {
			var _this = this;
			var ifThisMousedown = !1;
			_this.$slideDragBtn.on('mousedown.' + libName, function (e) {
                if (!_this.runtime.loaded || _this.runtime.state || _this.dragTimerState || _this.$slideDragBtn.is(':animated')) {
					return !1;
                }

				ifThisMousedown = !0;
				var disPageX = e.pageX;
				_this.$slideDragBtn.addClass('captcha-button-active');

				$(document).on('mousemove.' + libName, function (e) {

					if (!ifThisMousedown) {
						return !1;
					}

					var x = e.pageX - disPageX;

                    
                    _this.move(x);
					e.preventDefault();
				});
			});

			$(document).on('mouseup.' + libName, function () {
				if (!ifThisMousedown) {
					return false;
				}

				ifThisMousedown = !1;

				if (_this.runtime.state) {
					return !1;
				}

				$(document).off('mousemove.' + libName);
                _this.$slideDragBtn.removeClass('captcha-button-active');
                // _this.$slideDragBtn.css({transform: 'translateX(0px)'}).removeClass('captcha-button-active');

                if(!_this.runtime.deg || _this.runtime.left < 5) {
                    _this.$coordinate.hide();
                    _this.$rotateCaptchaImg.css({transform: 'rotate(0deg)'});
                    _this.$slideDragBtn.css({transform: 'translateX(0px)'});
					return !1;
                }

                // 验证
                _this.check();
			});
        }

        initTouch() {
			var _this = this;

			var ifThisTouchStart = !1;

            var disPageX = 0;

            _this.$slideDragBtn.on({
                'touchstart.captcha' :function (e) {
                    if (!_this.runtime.loaded || _this.runtime.state || _this.dragTimerState || _this.$slideDragBtn.is(':animated')) {
                        return !1;
                    }
                    
				    ifThisTouchStart = !0;
                    disPageX = e.originalEvent.targetTouches[0].pageX;

				    _this.$slideDragBtn.addClass('captcha-button-active');
                },
                'touchmove.captcha': function (e) {
                    e.preventDefault();
                    if (!ifThisTouchStart || _this.dragTimerState || _this.$slideDragBtn.is(':animated')) {
                        return !1;
                    }

					var x = e.originalEvent.targetTouches[0].pageX - disPageX;

                    _this.move(x);
                },
                'touchend.captcha': function (e) {

                    if (!ifThisTouchStart) {
                        return !1;
                    }

                    ifThisTouchStart = !1;

                    if (_this.runtime.state) {
                        return !1;
                    }

                    if (_this.$slideDragBtn.is(':animated')) {
                        return !1;
                    }
    
                    _this.$slideDragBtn.removeClass('captcha-button-active');

                    if(!_this.runtime.deg || _this.runtime.left < 5) {
                        _this.$coordinate.hide();
                        _this.$rotateCaptchaImg.css({transform: 'rotate(0deg)'});
                        _this.$slideDragBtn.css({transform: 'translateX(0px)'});
                        return !1;
                    }
    
                    // 验证
                    _this.check();
                }
            });
        }

        // 验证
        check() {
            var _this = this;

            $.getJSON(_this.options.api +'/verify', {angle: _this.runtime.deg}).done(function(res) {
                if(res.code === 0) {
                    _this.runtime.state = !0;
                    _this.$coordinate.hide();
                    _this.$main.addClass('captcha-success');
                    _this.options.success();
                    _this.options.complete(!0);
                    _this.$slideDragBtn.off('touchmove.captcha');

                    if(_this.options.successClose) {
                        _this.timerProgressBar(parseInt(_this.options.successClose) || 2000);
                    }

                    return !0;
                }

                _this.runtime = {
                    deg: 0,
                    left: 0,
                    state: !1,
                    loaded: !1,
                };
                
                _this.options.fail();
                _this.options.complete(!1);

                _this.dragTimerState = !0;
                _this.$main.addClass('captcha-fail');

                _this.$slideDragWrap.addClass('captcha-control-horizontal').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
                    $(this).removeClass('captcha-control-horizontal');
                });
                _this.$slideDragBtn.delay(700).animate({
                    transform: 'translateX(0px)'
                }, function () {
                    _this.dragTimerState = !1;
                    _this.$main.removeClass('captcha-fail');
                    _this.$slideDragBtn.css({transform: 'translateX(0px)'});
                    _this.refresh();
                });
            });
        }

        move(x) {
            var _this = this;
            // console.log(x);

            if (x < 0) {
                x = 0;
            } else if (x >= (_this.$slideDragWrap.width() - _this.$slideDragBtn.outerWidth())) {
                x = _this.$slideDragWrap.width() - _this.$slideDragBtn.outerWidth();
            }

            _this.runtime.deg = (360 / 210) * x;

            var isFail = _this.$main.hasClass('captcha-fail');

            if (x > 211) {
                _this.$slideDragBtn.css({transform: 'translateX(0px)'});
            }

            if(!isFail) {
                if (x < 211 && x > -1) {
                    if (x == 0) {
                        _this.$slideDragBtn.css({transform: 'translateX(0px)'});
                    } else {
                        _this.$slideDragBtn.css({transform: 'translateX('+ x +'px)'});
                    }

                } else {
                    _this.$main.addClass('captcha-fail');
                    _this.$slideDragBtn.css({transform: 'translateX(210px)'}).removeClass('captcha-button-active');

                    _this.$controlWrap.unbind();
                    _this.$slideDragBtn.unbind();
                }
            }
            
            _this.runtime.left = x;
            _this.spinImg();
        }

        timerProgressBar (timer) {
			var _this = this;

            if(!timer) {
                return !1;
            }

            if(!_this.options.timerProgressBar) {
                setTimeout(function() {
                    _this.modal.close();
                }, timer);
                return !1;
            }

            setTimeout(function() {
                _this.modal.close();
            }, timer + 10);

            var timerProgressBar = _this.$elem.find('.captcha-timer-progress-bar')[0] || null;

            if(!timerProgressBar) {
                return !1;
            }

            timerProgressBar.style.display = 'flex';
            
            setTimeout(() => {
                timerProgressBar.style.transition = `width ${timer / 1000}s linear`;
                timerProgressBar.style.width = '0%';
            }, 10);
        }

        refresh() {
            this.$coordinate.hide();
            this.loadImg(this.$elem);
		}

        destroy() {
            this.runtime = {
                deg: 0,
                left: 0,
                state: !1,
                loaded: !1,
            };
            this.modal.close();
        }

        close() {
            this.modal.close();
        }

        captchaHTML(options) {
            return `<div id="${options.id}" class="captcha-root" style="--theme: ${options.theme}">
                <div class="captcha-wrap">
                    <div class="captcha">
                        <div class="captcha-title">
                            <h2>${options.title}</h2>
                            <p>${options.desc}</p>
                        </div>
                        <div class="captcha-main">
                            <div class="captcha-wrap">
                                <div class="captcha-image">
                                    <div class="captcha-img captcha-loading">
                                        <img style="transform: rotate(0deg);">
                                        <div class="captcha-loader">
                                            <svg xmlns="https://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38">
                                                <defs>
                                                    <linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a">
                                                        <stop stop-color="${options.theme}" stop-opacity="0" offset="0%"/>
                                                        <stop stop-color="${options.theme}" stop-opacity=".631" offset="63.146%"/>
                                                        <stop stop-color="${options.theme}" offset="100%"/>
                                                    </linearGradient>
                                                </defs>
                                                <g fill="none" fill-rule="evenodd">
                                                    <g transform="translate(1 1)">
                                                        <path d="M36 18c0-9.94-8.06-18-18-18" id="Oval-2" stroke="url(#a)" stroke-width="2">
                                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/>
                                                        </path>
                                                        <circle fill="${options.theme}" cx="36" cy="18" r="1">
                                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/>
                                                        </circle>
                                                    </g>
                                                </g>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="captcha-coordinate"></div>
                                    <div class="captcha-state"><i class="captcha-state-icon"></i></div>
                                </div>
                            </div>
                            <div class="captcha-control">
                                <div class="captcha-control-wrap"></div>
                                <div class="captcha-control-button"><i></i></div>
                            </div>
                        </div>
                        <div class="captcha-timer-progress-bar-wrap">
                            <div class="captcha-timer-progress-bar" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>`;
        }
    }

    $.fn[libName] = function(options) {
        return this.each(function() {
            if(!$.data(this, libName)) {
                var instance = new RotateCaptcha($(this), options);
                $.data(this, libName, instance);
                instances.push(instance);
            }
        });
    };
});
