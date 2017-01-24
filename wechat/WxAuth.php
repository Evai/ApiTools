<?php

/**
 * 微信授权相关接口
 *
 * Class WxAuth
 */

class WxAuth {

    //公众号的appId和appSecret
    private $appId;
    private $appSecret;
    public $debug = false;

    /**
     * 获取微信授权链接，获取用户的基本信息
     * 如果用户同意授权，页面将跳转至 redirect_uri/?code=CODE&state=STATE。
     * 若用户禁止授权，则重定向后不会带上code参数，仅会带上state参数redirect_uri?state=STATE
     * @param string $redirect_uri 跳转地址
     * @param string $state 参数
     * @return string
     */
    public function authorize_user_info($redirect_uri, $state = '321')
    {

        $redirect_uri = urlencode($redirect_uri);

        $auth_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";

        header('Location:' . $auth_url);

    }

    /**
     * 获取微信授权链接，只获取进入页面的用户的openid
     * 如果用户同意授权，页面将跳转至 redirect_uri/?state=STATE。
     * @param string $redirect_uri 跳转地址
     * @param string $state 参数
     * @return string
     */
    public function get_authorize_url_base($redirect_uri = '', $state = '')
    {

        $redirect_uri = urlencode($redirect_uri);

        if(!empty($state)) $state = "&state={$state}";

        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base{$state}#wechat_redirect";

    }

    /**
     * 获取授权token
     *
     * @param string $code 通过get_authorize_url_xxxx获取到的code
     * 正确时返回的JSON数据包如下：
     *	{
     *   "access_token":"ACCESS_TOKEN",
     *   "expires_in":7200,
     *   "refresh_token":"REFRESH_TOKEN",
     *   "openid":"OPENID",
     *   "scope":"SCOPE"
     *  }
     * 参数	描述
     * access_token	网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
     * expires_in	access_token接口调用凭证超时时间，单位（秒）
     * refresh_token	用户刷新access_token
     * openid	用户唯一标识，请注意，在未关注公众号时，用户访问公众号的网页，也会产生一个用户和公众号唯一的OpenID
     * scope	用户授权的作用域，使用逗号（,）分隔
     *
     * @error :
     * {"errcode":40029,"errmsg":"invalid code"}
     * @return bool|mixed
     */
    public function get_access_token($code)
    {

        $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->appId . '&secret=' . $this->appSecret . '&code=' . $code . '&grant_type=authorization_code';

        $token_data = $this->curl_http($token_url);

        if($token_data[0] == 200)
        {
            return json_decode($token_data[1]);
        }

        return false;
    }

    /**
     * 获取授权后的微信用户信息
     * @param string $access_token
     * @param string $open_id
     * @return bool|mixed
     */
    public function get_user_info($access_token, $open_id)
    {
        $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
        $info_data = $this->curl_http($info_url, 'GET');

        if($info_data[0] == 200)
        {
            return json_decode($info_data[1]);
        }

        return FALSE;
    }

    /**
     * 检验授权凭证（access_token）是否有效
     * @param string $access_token
     * @param string $open_id
     * @return bool
     */
    public function check_access_token($access_token, $open_id)
    {
        $check_url = 'https://api.weixin.qq.com/sns/auth?access_token=' . $access_token . '&openid=' . $open_id;
        $check_data = $this->curl_http($check_url, 'GET');

        if($check_data[0] == 200)
        {
            $res = json_decode($check_data[1]);

            if ($res->errcode == 0 && $res->errmsg == 'ok') return true;
        }

        return false;
    }

    /**
     * 用refresh_token刷新access_token
     * @param string $refresh_token
     * @return bool|mixed
     */
    public function refresh_access_token($refresh_token)
    {

        $refresh_url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=' . $this->appId . '&grant_type=refresh_token&refresh_token=' . $refresh_token;

        $refresh_data = $this->curl_http($refresh_url, 'GET');

        if($refresh_data[0] == 200)
        {
            return json_decode($refresh_data[1]);
        }

        return false;

    }

    /**
     * @param $url
     * @param $method
     * @param null $post_fields
     * @return array
     */
    public function curl_http($url, $method = 'GET', $post_fields = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            if (!empty($post_fields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            }
        }

        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($post_fields);

            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ch));

            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ch);
        return [$http_code, $response];
    }

    /**
     * 检查access_token是否有效并获取用户信息
     * @param $access_data
     * @return array|bool|mixed
     */
    private function filter_data($access_data)
    {
        if (isset($access_data->access_token)) {

            //检验授权凭证（access_token）是否有效
            if ($this->check_access_token($access_data->access_token, $access_data->openid)) {
                //拉取用户信息(需scope为 snsapi_userinfo)
                $user_info = $this->get_user_info($access_data->access_token, $access_data->openid);

                return $user_info;

            }

        }

        return false;
    }

    /**
     * @param $code
     * @return array|bool|mixed
     */
    public function execute($code)
    {

        $access_data = $this->get_access_token($code);

        return $this->filter_data($access_data);
    }

}

/**
 * DEMO
 */
$wx = new WxAuth();
$redirect_url = 'your redirect url';
//第一步：获取微信授权链接，进行跳转，用户同意授权，获取code
$auth_url = $wx->authorize_user_info($redirect_url);

//第二步：通过code换取网页授权access_token
$code = $_REQUEST['code'];
$state = $_REQUEST['state'];

$user_info = $wx->execute($code);

if (empty($user_info)) {
    //如果失败，重新跳转到授权页面
    $auth_url = $wx->authorize_user_info($redirect_url);
} else {
    //成功则返回用户的基本信息，然后处理业务逻辑
    
}





