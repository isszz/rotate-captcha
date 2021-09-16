<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\support\request;

class RequestBase
{
    /**
     * 当前请求的IP地址
     * @var string
     */
    protected $realIP;

    /**
     * 前端代理服务器真实IP头
     * @var array
     */
    protected $proxyServerIpHeader = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP'];
    
    /**
     * Get $_SERVER value
     * 
     * @param string|null $name
     * @param string|null $defaultValue
     * @return array|string|null
     */
    public function getServer(string|null $name = null, string|null $defaultValue = null): array|string|null
    {
        if ($name === null) {
            return isset($_SERVER) ? $_SERVER : [];
        }

        return (isset($_SERVER[$name])) ? $_SERVER[$name] : $defaultValue;
    }
    
    /**
     * Get header
     * 
     * @param string|null $name
     * @param string|null $defaultValue
     * @return array|string|null
     */
    public function header(string|null $name = null, string|null $defaultValue = null): array|string|null
    {
        $header = $this->getAllHeaders() ?: [];

        if ($name === null) {
            return isset($header) ? $header : [];
        }

        return (isset($header[$name])) ? $header[$name] : $defaultValue;
    }

    /**
     * Get all header
     * 
     * @return array
     */
    public function getAllHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }

        $headers = [];
        foreach ($this->getServer() as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get user IP
     *
     * @return string
     */
    public function ip(): string
    {
        if (!empty($this->realIP)) {
            return $this->realIP;
        }

        $this->realIP = $this->getServer('REMOTE_ADDR', '');

        if(isset($_SERVER)) {

            $proxyIpHeader = $this->proxyServerIpHeader;

            // 从指定的HTTP头中依次尝试获取IP地址
            // 直到获取到一个合法的IP地址
            foreach ($proxyIpHeader as $header) {
                $tempIP = $this->getServer($header);

                if ($tempIP == null) {
                    continue;
                }

                $tempIP = trim(explode(',', $tempIP)[0]);

                if (!$this->isValidIP($tempIP)) {
                    $tempIP = null;
                } else {
                    break;
                }
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $tempIP = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $tempIP = getenv('HTTP_CLIENT_IP');
            } else {
                $tempIP = getenv('REMOTE_ADDR');
            }
        }

        if(!empty($tempIP)) {

            preg_match("/[\d\.]{7,15}/", $tempIP, $ip);

            if (!empty($ip[0]) && $this->isValidIP($ip[0])) {
                $this->realIP = $ip[0];
            }
        }

        return $this->realIP ?: '0.0.0.0';
    }

    /**
     * Check IP legitimacy
     *
     * @param string $ip   IP address
     * @param string $type IP type (ipv4, ipv6)
     *
     * @return boolean
     */
    public function isValidIP(string $ip, string $type = ''): bool
    {
        switch (strtolower($type)) {
            case 'ipv4':
                $flag = FILTER_FLAG_IPV4;
                break;
            case 'ipv6':
                $flag = FILTER_FLAG_IPV6;
                break;
            default:
                $flag = 0;
                break;
        }

        return boolval(filter_var($ip, FILTER_VALIDATE_IP, $flag));
    }
}
