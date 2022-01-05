<?php

namespace isszz\captcha\rotate\facade;

// use think\Facade; // 如果使用的tinkphp6+也可以使用自带的facade类
use isszz\captcha\rotate\Facade;

/**
 * Class Captcha
 * 
 * @package isszz\captcha\rotate\facade
 * @mixin \isszz\captcha\rotate\Captcha
 * @method static \isszz\captcha\rotate\Captcha create($image = '', $uploadPath = null) 创建旋转验证码所需图像
 * @method static array get($size = 350) 生成验证图片并获取相关信息
 * @method static bool check(string $token, int|float|string $angle = null) 检查是否旋转到正确的角度
 * @method static array output(?string $str = '', string $uploadPath = null) 根据加密后的字符串，获取图片内容数据
 * @method static array info() 获取一些信息
 * @method static void buildBase() 组装必要的参数
 * @method static string getMime() 获取需要生成图像的mime类型
 * @method static \isszz\captcha\rotate\Captcha configDrive(string $config) 设置配置驱动
 * @method static \isszz\captcha\rotate\Captcha config(string|null $name = null, string|null $defaultValue = null) 获取配置
 * @method static \isszz\captcha\rotate\Captcha setLang(string $language = 'en', ?string $file = null) 设置语言
 * @method static object lang(string $language = null, ?string $file = null) 初始化语言
 * @method static __toString() 输出图像内容|建议只用于测试
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \isszz\captcha\rotate\Captcha::class;
    }
}
