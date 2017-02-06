<?php

date_default_timezone_set('PRC');

spl_autoload_register(function ($class) {
    include $class . '.php';
});
/**
 * DEMO
 */
//$demo = new Demo();
//$demo->AESDemo();
//$demo->RSADemo();
//$demo->response();
//$demo->uploadImg();
//$demo->session();
//$demo->cookie();
//$demo->compress();
//$demo->wxAuth();
//$demo->geoHash();
//echo $demo->getTodayAnytime('now', 'PRC');