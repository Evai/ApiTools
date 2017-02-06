<?php

namespace Request;

/**
 * Class Request
 * @package ApiTools\request
 */
class Request
{
    /**
     * Request constructor.
     */
    public function __construct()
    {
        //$this->requestLimit();
        if(!isset($_SESSION))
        {
            session_start();
        }
    }

    /**
     * 请求次数限制
     * request limit
     * @param int $limit
     * @param int $time  (unit：sec)
     */
    public function requestLimit($limit = 60, $time = 60)
    {
        //请求时间
        $RequestTime = $_SERVER['REQUEST_TIME'];
        //请求唯一标识
        $session_id = session_id();
        //请求路由
        $requestUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        //记录请求次数
        $_SESSION['request' . $requestUrl . $session_id] = isset($_SESSION['request' . $requestUrl . $session_id]) ? $_SESSION['request' . $requestUrl . $session_id] : 1;
        //记录最后请求时间
        $_SESSION['finalRequestTime'] = isset($_SESSION['finalRequestTime']) ? $_SESSION['finalRequestTime'] : $RequestTime;

        $requestRemain = $limit - $_SESSION['request' . $requestUrl . $session_id];

        if ($RequestTime - $_SESSION['finalRequestTime'] < $time) {

            if ($requestRemain <= 0) exit('Maximum number of requests exceeded limit');

            $_SESSION['request' . $requestUrl . $session_id] += 1;

        } else {

            unset($_SESSION['request' . $requestUrl . $session_id], $_SESSION['finalRequestTime']);

        }

        header('X-RateLimit-Limit:' . $limit);
        header('X-RateLimit-Remaining:' . $requestRemain);

    }


    /**
     * 检测请求方式
     * @param $method
     * @return mixed
     */
    public function checkMethod($method = null)
    {
        if (empty($method)) return $_SERVER['REQUEST_METHOD'];

        if (strtoupper($method) == $_SERVER['REQUEST_METHOD']) return true;

        return false;
    }

    /**
     * 模拟post进行url请求
     * @param $url
     * @param string $method
     * @param null $post_fields
     * @return array
     */
    public function curlHttpRequest($url, $method = 'GET', $post_fields = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上

        if (strtoupper($method) == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            if (!empty($post_fields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200) {

            echo '======post data======' . '<br/>';
            var_dump($post_fields);
            echo '<br/>';

            echo '======http info======' . '<br/>';
            var_dump(curl_getinfo($ch));
            echo '<br/>';

            echo '======response======' . '<br/>';
            print_r($response);

            curl_close($ch);
            exit;

        }

        curl_close($ch);
        return $response;

    }

    /**
     * 获取客户端IP
     * @return string
     */
    public function getClientIP()
    {
        $ip = "Unknow IP";

        if (getenv("HTTP_CLIENT_IP"))

            $ip = $this->checkIp(getenv('HTTP_CLIENT_IP')) ? getenv('HTTP_CLIENT_IP'): 'Unknow IP';

        else if(getenv("HTTP_X_FORWARDED_FOR"))

            $ip = $this->checkIp(getenv('HTTP_X_FORWARDED_FOR')) ? getenv('HTTP_X_FORWARDED_FOR') : $ip;

        else if(getenv("REMOTE_ADDR"))

            $ip = $this->checkIp(getenv('REMOTE_ADDR')) ? getenv('REMOTE_ADDR') : $ip;

        else $ip = "Unknow IP";

        return $ip;
    }

    /**
     * 检测是否是有效IP
     * @param $str
     * @return bool|int
     */
    public function checkIp($str)
    {
        $ip = explode('.', $str);

        for ($i = 0; $i < count($ip); $i++) {

            if ($ip[$i] > 255) return false;

        }

        return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $str);
    }

    /**
     * @return array
     */
    public function getAgentInfo()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];

        $browserList = ['MSIE', 'Firefox', 'QQBrowser', 'QQ/', 'UCBrowser', 'MicroMessenger', 'Edge', 'Chrome', 'Opera', 'OPR', 'Safari', 'Trident/'];

        $systemList = ['Windows Phone', 'Windows', 'Android', 'iPhone', 'iPad'];

        $browser = 0;//未知
        $system = 0;//未知

        foreach ($browserList as $bro) {
            if (stripos($agent, $bro) !== false) {
                $browser = $bro;
                break;
            }
        }

        foreach ($systemList as $sys) {
            if (stripos($agent, $sys) !== false) {
                $system = $sys;
                break;
            }
        }

        return ['sys' => $system, 'bro' => $browser];
    }

    /**
     * @return mixed
     */
    public function all()
    {
        return $GLOBALS;
    }

    /**
     * @param null $key
     * @return null
     */
    public function server($key = null)
    {
        if (empty($key)) return $_SERVER;

        return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function files($key = null)
    {
        if (empty($key)) return $_FILES;

        return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }

    /**
     * @param null $key
     * @param null $value
     * @return null
     */
    public function session($key = null, $value = null)
    {
        if ($key && empty($value))  return isset($_SESSION[$key]) ? $_SESSION[$key] : null;

        elseif ($key && $value) $_SESSION[$key] = $value;

        else return $_SESSION;
    }

    /**
     * @param $key
     */
    public function sessionRemove($key = null)
    {
        if (empty($key)) session_destroy();

        else unset($_SESSION[$key]);
    }

    /**
     * @param null $key
     * @return null
     */
    public function sessionFlash($key = null)
    {
        $session = $_SESSION;

        if (empty($key)) {
            $this->sessionRemove();
            return $session;
        }

        $this->sessionRemove($key);
        return isset($session[$key]) ? $session[$key] : null;
    }

    /**
     * @param null $key
     * @param null $value
     * @param int $expire
     * @return null
     */
    public function cookie($key = null, $value = null, $expire = 24*60*60)
    {
        if ($key && empty($value))  return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;

        elseif ($key && $value)

            setcookie($key, $value, time() + $expire);

        else return $_COOKIE;
    }

    /**
     * @param $key
     */
    public function cookieRemove($key)
    {
        setcookie($key, '', time() - 3600);
    }

    /**
     * @param null $key
     * @return null
     */
    public function input($key = null)
    {
        if (empty($key)) return $_REQUEST;

        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function post($key = null)
    {
        if (empty($key)) return $_POST;

        return isset($_POST[$key]) ? $_POST[$key] : null;
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function get($key = null)
    {
        if (empty($key)) return $_GET;

        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        echo "Setting '$name' to '$value'\n";
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        return $this->input($name);
    }

}
