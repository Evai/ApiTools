<?php

namespace DataEncrypt;
/**
 * Class RSA
 */
class RSACrypt
{

    private $publicKey;

    private $privateKey;

    private $publicKeyPath;

    private $privateKeyPath;

    /**
     * RSACrypt constructor.
     * @param null $keyPath
     * @param $bits
     */
    public function __construct($keyPath = null, $bits = 2048)
    {
        if (empty($keyPath)) return;

        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => $bits,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        // Create the private and public key
        $resource = openssl_pkey_new($config);

        // Extract the private key from $res to $priKey
        openssl_pkey_export($resource, $this->privateKey);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($resource);

        $this->publicKey = $pubKey['key'];

        $this->publicKeyPath = $keyPath . 'rsa_public_key.pem';

        $this->privateKeyPath = $keyPath . 'rsa_private_key.pem';

        if (!is_dir($keyPath)) mkdir($keyPath, 0777, true);

        if (file_exists($keyPath)) return;

        file_put_contents($this->publicKeyPath, $this->publicKey);

        file_put_contents($this->privateKeyPath, $this->privateKey);

    }

    /**
     * 设置公钥
     * @param $pubKey
     */
    public function setPublicKey($pubKey)
    {
        $this->publicKey = $pubKey;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param $priKey
     */
    public function setPrivateKey($priKey)
    {
        $this->privateKey = $priKey;
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param $pubKeyPath
     */
    public function setPublicKeyPath($pubKeyPath)
    {
        $this->publicKeyPath = $pubKeyPath;
    }

    /**
     * @return string
     */
    public function getPublicKeyPath()
    {
        return $this->publicKeyPath;
    }

    /**
     * @param $priKeyPath
     */
    public function setPrivateKeyPath($priKeyPath)
    {
        $this->privateKeyPath = $priKeyPath;
    }

    /**
     * @return string
     */
    public function getPrivateKeyPath()
    {
        return $this->privateKeyPath;
    }

    /**
     * 公钥加密
     * @param $data
     * @return mixed
     */
    public function publicEncrypt($data)
    {
        $publicKey = $this->checkPublicKey();

        openssl_public_encrypt($data, $encrypted, $publicKey);

        openssl_free_key($publicKey);

        return base64_encode($encrypted);
    }

    /**
     * 公钥解密
     * @param $data
     * @return mixed
     */
    public function publicDecrypt($data)
    {
        $publicKey = $this->checkPublicKey();

        openssl_public_decrypt(base64_decode($data), $decrypted, $publicKey);

        openssl_free_key($publicKey);

        return $decrypted;
    }

    /**
     * 私钥加密
     * @param $data
     * @return mixed
     */
    public function privateEncrypt($data)
    {
        $privateKey = $this->checkPrivateKey();

        openssl_private_encrypt($data, $encrypted, $privateKey);

        openssl_free_key($privateKey);

        return base64_encode($encrypted);
    }

    /**
     * 私钥解密
     * @param $data
     * @return mixed
     */
    public function privateDecrypt($data)
    {
        $privateKey = $this->checkPrivateKey();

        openssl_private_decrypt(base64_decode($data), $decrypted, $privateKey);

        openssl_free_key($privateKey);

        return $decrypted;
    }

    /**
     * 公钥校验
     * @param array $data
     * @return bool
     */
    public function rsaCheckSignPublic(array $data)
    {
        $sign = $data['sign'];

        unset($data['sign']);

        $str = $this->toUrlParams($data);

        return $this->rsaPublicKeyVerify($str, $sign);
    }

    /**
     * 序列化参数
     * @param array $data
     * @return string
     */
    public function toUrlParams(array $data)
    {
        ksort($data);

        $str = '';

        foreach ($data as $key => $value)
        {
            $str .= $key . '=' . $value . '&';
        }

        $str = rtrim($str, '&');

        unset ($key, $value);

        return $str;
    }

    /**
     * 校验公钥签名
     * @param $str
     * @param $sign
     * @return bool
     */
    public function rsaPublicKeyVerify($str, $sign)
    {

        $publicKey = $this->checkPublicKey();

        $result = (bool) openssl_verify($str, base64_decode($sign), $publicKey);

        openssl_free_key($publicKey);

        return $result;
    }

    /**
     * 读取公钥
     * @return resource|string
     */
    private function checkPublicKey()
    {
        if($this->publicKeyPath){

            //读取公钥文件
            $pubKey = @file_get_contents($this->publicKeyPath);

            $pubKey or exit('public key is not exists!');

            //转换为openssl格式密钥
            $res = openssl_pkey_get_public($pubKey);

        } elseif ($this->publicKey) {

            //初始化公钥
            $pubKey = str_replace("-----BEGIN PUBLIC KEY-----", "", $this->publicKey);
            $pubKey = str_replace("-----END PUBLIC KEY-----", "", $pubKey);
            $pubKey = str_replace("\n", "", $pubKey);

            $pubKey = "-----BEGIN PUBLIC KEY-----" . PHP_EOL .
                wordwrap($pubKey, 64, "\n", true) . PHP_EOL .
                "-----END PUBLIC KEY-----";

            //转换为openssl格式密钥
            $res = openssl_pkey_get_public($pubKey);

        } else {

            exit('public key is not exists!');

        }

        return $res;
    }

    /**
     * 读取私钥
     * @return resource|string
     */
    private function checkPrivateKey()
    {
        if($this->privateKeyPath){

            //读取私钥文件
            $priKey = @file_get_contents($this->privateKeyPath);

            $priKey or exit('private key is not exists!');

            //转换为openssl格式密钥
            $res = openssl_get_privatekey($priKey);

        } elseif ($this->privateKey) {

            //初始化私钥
            $priKey = str_replace("-----BEGIN PRIVATE KEY-----", "", $this->privateKey);
            $priKey = str_replace("-----END PRIVATE KEY-----", "", $priKey);
            $priKey = str_replace("\n", "", $priKey);

            $priKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";

            //转换为openssl格式密钥
            $res = openssl_get_privatekey($priKey);

        } else {

            exit('private key is not exists!');

        }

        return $res;
    }

}


