<?php

/**
 * Class Request
 */

class Request

{
    private $resArr = ['code' => -1, 'msg' => 'request error'];

    /**
     * Request constructor.
     */
    public function __construct()
    {

    }

    /**
     * 请求次数限制
     * @param int $limit  次数限制
     * @param int $range  时间限制(单位：秒)
     * @throws Exception
     */
    public function requestLimit($limit = 60, $range = 60)
    {
        session_start();
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

        if ($RequestTime - $_SESSION['finalRequestTime'] < $range) {

            if ($requestRemain <= 0)
            {
                throw new \Exception('Maximum number of requests exceeded limit');
            }

            $_SESSION['request' . $requestUrl . $session_id] += 1;

        } else {

            unset($_SESSION['request' . $requestUrl . $session_id], $_SESSION['finalRequestTime']);

        }

        header('X-RateLimit-Limit:' . $limit);
        header('X-RateLimit-Remaining:' . $requestRemain);

    }

    /**
     * 验证字段
     * @param string $msg
     * @param null $param
     * @param string $default
     * @param int $length
     * @param bool $checkEmpty
     * @return int|null|string
     */
    public function getArgs($msg = '参数名称', $param = null, $default = '', $length = 0, $checkEmpty = false)
    {
        if(empty($param)) {

            if ($checkEmpty) $this->isEmpty($msg, $default);

            return $default;

        }

        if (!is_string($param)) {

            exit($this->returnResponse(-400, $msg . '字段类型错误，请用 String 类型', 'none'));

        }

        $param = trim($param);

        if ($length > 0 && strlen($param) > $length) {

            exit($this->returnResponse(-401, $msg . '字段名称过长,请不要超过' . $length . '个字节', 'none'));

        }

        $param = stripcslashes($param);

        $param = is_numeric($default) ? intval($param) : $param;

        if ($checkEmpty) $this->isEmpty($msg, $param);

        return $param;

    }

    /**
     * 检测值是否为空
     * @param $msg
     * @param $param
     */
    public function isEmpty($msg, $param)
    {
        if(empty($param)) {

            exit($this->returnResponse(-300, $msg . ' 字段名称不能为空', 'none'));

        }
    }

    /**
     * 请求返回值 {"code":0, "msg":"success", "data":{["user":{},"banner":[]]}}
     * @param int $code
     * @param string $msg
     * @param array $res
     * @return string
     */
    public function returnResponse($code = 0, $msg = 'success', $res = [])
    {

        $this->resArr['code'] = $code;
        $this->resArr['msg'] = $msg;

        if ($res) {

            if (!is_array($res)) {

                $res = compact('res');

            }

            foreach ($res as $k => $v)
            {

                if (!empty($v)) $this->resArr['data'][$k] = $v;

            }

        }

        return json_encode($this->resArr, JSON_UNESCAPED_UNICODE);
    }

}

$req = new Request();
$user = ['user_id'=>1, 'mobile'=> 133333333333, 'name' => 'evai'];
$banner = true;
$data = compact('user', 'banner');
echo $req->returnResponse(200, 'success', $data);
