<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/16
 * Time: 14:44
 */
date_default_timezone_set('PRC');
require_once('./ZmopClient.php');
require_once('../lotusphp_runtime/Logger/Logger.php');
require_once('../ZmopSdk.php');
require_once '../../class/RSACrypt.php';

/**
 * Class ZhimaScoreGet
 */
class Zhima
{
    //芝麻信用网关地址
    public $gatewayUrl = "https://zmopenapi.zmxy.com.cn/openapi.do";
    //商户私钥文件
    public $privateKeyFile = "../rsa_private_key.pem";
    //芝麻公钥文件
    public $zmPublicKeyFile = "../zhima_rsa_public_key.pem";
    //数据编码格式
    public $charset = "UTF-8";
    //芝麻分配给商户的 appId
    public $appId = "1001725";

    /**
     * 行业关注查询
     * @param $transId
     * @param $openId
     * @return string
     */
    public function ZhimaCreditWatchlistiiGet($transId, $openId){
        require_once('./request/ZhimaCreditWatchlistiiGetRequest.php');
        $client = new ZmopClient($this->gatewayUrl,$this->appId,$this->charset,$this->privateKeyFile,$this->zmPublicKeyFile);
        $request = new ZhimaCreditWatchlistiiGetRequest();
        $request->setPlatform("zmop");
        $request->setProductCode("w1010100100000000022");// 必要参数
        $request->setTransactionId($transId);// 必要参数
        $request->setOpenId($openId);// 必要参数
        $response = $client->execute($request);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 跳转授权页面
     * @param $name
     * @param $identity
     */
    public function ZhimaAuthInfoAuthorize($name, $identity){

        require_once('./request/ZhimaAuthInfoAuthorizeRequest.php');
        $client = new ZmopClient($this->gatewayUrl,$this->appId,$this->charset,$this->privateKeyFile,$this->zmPublicKeyFile);
        $request = new ZhimaAuthInfoAuthorizeRequest();

        $request->setChannel("app");
        $request->setPlatform("zmop");
        $request->setIdentityType("2");// 必要参数
        $request->setIdentityParam("{\"name\":\"$name\",\"certType\":\"IDENTITY_CARD\",\"certNo\":\"$identity\"}");// 必要参数
        $request->setBizParams("{\"auth_code\":\"M_H5\",\"channelType\":\"app\",\"state\":\"123\"}");//

        $url = $client->generatePageRedirectInvokeUrl($request);
        //echo $url;
        header("Location:".$url);
    }

    /**
     * 返回值 {"success":true,"biz_no":"ZM201701193000000866100598780020","zm_score":"738"}
     * {"success":false,"error_code":"ZMCREDIT.openid_parameter_invalid","error_message":"open_id参数错误"}
     * 获取芝麻信用评分
     * @param $transId
     * @param $openId
     * @return string
     */
    public function ZhimaCreditScoreGet($transId, $openId){
        require_once('./request/ZhimaCreditScoreGetRequest.php');
        $client = new ZmopClient($this->gatewayUrl,$this->appId,$this->charset,$this->privateKeyFile,$this->zmPublicKeyFile);
        $request = new ZhimaCreditScoreGetRequest();
        $request->setPlatform("zmop");
        $request->setTransactionId($transId);// 必要参数
        $request->setProductCode("w1010100100000000001");// 必要参数
        $request->setOpenId($openId);// 必要参数
        $response = $client->execute($request);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 返回值 {"success":true,"authorized":true,"open_id":"268803856945229820387160610"}
     * {"success":false,"error_code":"ZMCSP.zm_account_not_existed","error_message":""}
     * 查询用户是否已授权
     * @param $name
     * @param $identity
     * @return string
     */
    public function ZhimaAuthInfoAuthquery($name, $identity){
        require_once './request/ZhimaAuthInfoAuthqueryRequest.php';
        $client = new ZmopClient($this->gatewayUrl,$this->appId,$this->charset,$this->privateKeyFile,$this->zmPublicKeyFile);
        $request = new ZhimaAuthInfoAuthqueryRequest();
        $request->setChannel("apppc");
        $request->setPlatform("zmop");
        $request->setIdentityType("2");// 必要参数
        $request->setIdentityParam("{\"certNo\":\"$identity\",\"name\":\"$name\",\"certType\":\"IDENTITY_CARD\"}");// 必要参数
        $response = $client->execute($request);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

}

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
$encryptData = isset($_REQUEST['encryptData']) ? $_REQUEST['encryptData'] : '';
$openId = isset($_REQUEST['openId']) ? $_REQUEST['openId'] : '';

if ('ZhimaAuthInfoAuthorize' == $act) {
    //跳转授权页面

    $rsa = new \RSACrypt();

    $rsa->setPrivateKeyPath('../../rsa_private_key.pem');
    $decrypted = $rsa->privateDecrypt(base64_decode($encryptData));

    if (empty($decrypted)) {
        exit('网络错误，请稍后再试');
    }
    session_start();
    $_SESSION['zhima_info'] = $decrypted;
    $data = json_decode($decrypted);

    $zm = new Zhima();
    $zm->ZhimaAuthInfoAuthorize($data->name, $data->identity);

} elseif ('ZhimaAuthInfoAuthquery' == $act) {
    //查询用户是否已授权
    $rsa = new \RSACrypt();

    $rsa->setPrivateKeyPath('../../rsa_private_key.pem');
    $decrypted = $rsa->privateDecrypt(base64_decode($encryptData));

    if (empty($decrypted)) {
        exit('网络错误，请稍后再试');
    }

    $data = json_decode($decrypted);

    $zm = new Zhima();
    echo $zm->ZhimaAuthInfoAuthquery($data->name, $data->identity);

} elseif ('ZhimaCreditWatchlistiiGet' == $act) {
    //行业关注查询
    /*$time = date('YmdHis').str_pad(floor(microtime()*1000), 3, 0, STR_PAD_LEFT);
    $inc = str_pad(mt_rand(1,9999999999999), 13, 0, STR_PAD_LEFT);
    $transId = $time.$inc;
    $openId = '268803856945229820387160610';
    $zm = new Zhima();
    $zm->ZhimaCreditWatchlistiiGet($transId, $openId);*/

} elseif ('ZhimaCreditScoreGet' == $act) {
    //获取芝麻信用评分
    $transId = isset($_COOKIE['transId'.$openId]) ? $_COOKIE['transId'.$openId] : '';

    if (empty($transId)) {
        $time = date('YmdHis').str_pad(floor(microtime()*1000), 3, 0, STR_PAD_LEFT);
        $inc = str_pad(mt_rand(1,9999999999999), 13, 0, STR_PAD_LEFT);
        $transId = $time.$inc;
        setcookie('transId'.$openId, $transId, time()+24*60*60);
    }
    $zm = new Zhima();
    echo $zm->ZhimaCreditScoreGet($transId, $openId);
} else {
    exit('参数错误');
}

