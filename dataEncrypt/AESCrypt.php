<?php

/**
 * Class AESCrypt
 */
class AESCrypt
{
    private $key;
    private $iv;
    private $method = 'aes-128-cbc';

    /**
     * AESCrypt constructor.
     * @param $key
     * @param $iv
     */
    function __construct($key, $iv)
    {

        if (empty($key) || empty($iv)) {

            return null;

        } elseif (!$this->checkKey($key)) {

            exit('key is not correct!');

        }

        $this->key = $key;

        $this->iv = $iv;

    }

    /**
     * 加密
     * @param $data
     * @return string
     */
    public function encrypt($data)
    {

        $encrypted = openssl_encrypt($data, $this->method, $this->key, true, $this->iv);

        return base64_encode($encrypted);

    }

    /**
     * 解密
     * @param $data
     * @return string
     */
    public function decrypt($data)
    {

        $decrypted = openssl_decrypt(base64_decode($data), $this->method, $this->key, true, $this->iv);

        return $decrypted;

    }

    /**
     * 验证是否是字母和数字的组合
     * @param $key
     * @return bool
     */
    private function checkKey($key)
    {

        if (!preg_match("/^(?![^a-zA-Z]+$)(?!\d+$).{32}$/", $key)) {

            return false;

        }

        return true;
    }

}

/**
 * DEMO
 */

$key = md5('hello');
$iv = '1234567887654321';
$data = "I'm AES encrypt data";
$aes = new AESCrypt($key, $iv);

$encrypted = $aes->encrypt($data); //AES encrypt
echo $encrypted . '<br/>';

$decrypted = $aes->decrypt($encrypted); //AES decrypt
echo $decrypted;


