<?php

namespace Common;
/**
 * Class Common
 */
trait Common
{
    private $resArr = ['code' => -1, 'msg' => 'request error'];

    /**
     * 检测手机号
     * @param $mobile
     * @return bool
     */
    public function checkMobile($mobile)
    {
        if (!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile)) {
            return false;
        }
        return true;
    }

    /**
     * 检测是否是邮箱
     * @param $email
     * @return mixed
     */
    public function checkEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
        {
            return false;
        }
        return true;
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @return string
     */
    public function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $str = '';
        for ($i = 0; $i < $length; $i++)
        {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    /**
     * 检测是否是有效url
     * @param $url
     * @return bool
     */
    public function checkUrl($url)
    {
        if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%
=~_|]/i", $url)) {
            return false;
        }
        return true;
    }

    /**
     * 输出xml字符
     * @param $strData
     * @return bool|string
     */
    public function toXml(array $strData)
    {
        if(count($strData) <= 0) return false;

        $xml = "<xml>";
        foreach ($strData as $key => $val)
        {
            if (is_numeric($val)){
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }else{
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 解析XML数据
     * @param $xml
     * @return mixed
     * @throws \Exception
     */
    public function fromXml($xml)
    {
        if(!$xml){
            throw new \Exception("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    /**
     * 数组转化为url参数
     * @param array $postData
     * @return string
     */
    public function toUrlParams(array $postData)
    {
        if(count($postData) <= 0) return false;

        ksort($postData);
        $str = '';

        foreach ($postData as $k => $v)
        {
            $str .= $k . '=' . $v . '&';
        }

        if (get_magic_quotes_gpc()) $str = stripslashes($str);
        $buff = trim($str, "&");
        return $buff;
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
    public function validateParam($msg = 'param name', $param = null, $default = '', $length = 0, $checkEmpty = false)
    {

        if(empty($param)) $param = $default;

        if (!is_string($param)) {

            exit($this->returnJson(-401, $msg . '字段类型错误，请用 String 类型'));

        }

        $param = trim($param);

        if (0 != $length) {

            if (!is_array($length)) {

                $length = explode('-', $length);

            }

            if (count($length) == 1) {

                if (strlen($param) != $length[0]) exit($this->returnJson(-401, $msg . '字段名长度必须为' . $length[0] . '个字节'));

            } else {

                $min = $length[0];
                $max = $length[1];

                if ($min > strlen($param)) {

                    exit($this->returnJson(-401, $msg . '字段名不能少于' . $min . '个字节'));

                } elseif ($max < strlen($param)) {

                    exit($this->returnJson(-401, $msg . '字段名不能超过' . $max . '个字节'));

                }

            }

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

            exit($this->returnJson(-400, $msg . '字段名称不能为空'));

        }
    }

    /**
     * 请求返回值 {"code":0, "msg":"success", "data":{["user":{},"banner":[]]}}
     * @param int $code
     * @param string $msg
     * @param array $res
     * @return string
     */
    private function setResponse($code = 0, $msg = 'success', $res = [])
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

    /**
     * @param int $code
     * @param string $msg
     * @param array $res
     * @return string
     */
    public function returnJson($code = 0, $msg = 'success', $res = [])
    {
        return $this->setResponse($code, $msg, $res);
    }

    /**
     * @param int $code
     * @param string $msg
     * @param array $res
     * @return string
     */
    public function returnJsonp($code = 0, $msg = 'success', $res = [])
    {

        $dataType = isset($_GET['dataType']) ? $_GET['dataType']  : '';
        $callback = isset($_GET['callback']) ? $_GET['callback'] : '';

        if ($dataType == 'jsonp' && $callback) {

            return $callback . '(' . $this->setResponse($code, $msg, $res) . ')';

        }

        return json_encode($this->resArr, JSON_UNESCAPED_UNICODE);

    }

    /**
     * 删除文件
     * @param string $path
     * @return bool
     */
    function removeFile($path = '')
    {
        if ($path && strpos($path, 'uploads' !== false))
        {
            $path = stristr($path, 'uploads');
            if (@unlink($path)) return true;
        }
        return false;
    }

    /**
     * 生成唯一字符串
     * @param string $type
     * @param string $extra
     * @return mixed
     */
    public function generateUnique($type = 'md5', $extra = '')
    {
        return $type(uniqid(md5(microtime(true).$extra),true));
    }

    /**
     * @return string
     */
    public function generateOrderNum()
    {
        $time = date('YmdHis') . str_pad(floor(microtime() * 1000), 3, 0, STR_PAD_LEFT);
        $inc = str_pad(mt_rand(1, 9999999999999), 13, 0, STR_PAD_LEFT);
        return $time.$inc;
    }

    /**
     * 计算两点地理坐标之间的距离
     * @param $lat1 起点纬度
     * @param $lng1 起点经度
     * @param $lat2 终点纬度
     * @param $lng2 终点经度
     * @param int $decimal 精度 保留小数位数
     * @return string
     */
    function getDistance($lat1, $lng1, $lat2, $lng2, $decimal = 2){

        if (empty($lat1) || empty($lng1)) {
            return '';
        }

        $earthRadius = 6367000; //approximate radius of earth in meters

        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;

        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;


        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;

        if(round($calculatedDistance) >= 1000) {

            return round($calculatedDistance / 1000, $decimal) . 'km';

        }

        return round($calculatedDistance) . 'm';

    }

    /**
     * 获取某个段时间
     * @param string $time
     * @param string $timezone
     * @return false|int
     */
    function getTodayAnytime($time = '00:00', $timezone = 'Asia/Shanghai')
    {
        $d = new \DateTime($time, new \DateTimeZone($timezone));
        return strtotime($d->format("Y-m-d H:i:s"));
    }

}