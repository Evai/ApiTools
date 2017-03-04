<?php

use Common\Common;
use DataEncrypt\AESCrypt;
use DataEncrypt\RSACrypt;
use Request\Request;
use Upload\UploadFiles;
use Image\HandleImage;
use WeChat\WxAuth;
use GeoHash\GeoHash;

/**
 * Class Demo
 */
class Demo
{
    use Common;

    public function AESDemo()
    {
        $key = md5('hello');
        $iv = '1234567887654321';
        $data = "I'm AES encrypt data";
        $aes = new AESCrypt($key, $iv);

        $encrypted = $aes->encrypt($data); //AES encrypt
        echo $encrypted . '<br/>';

        $decrypted = $aes->decrypt($encrypted); //AES decrypt
        echo $decrypted;
    }

    public function RSADemo()
    {
        $keyPath = './DataEncrypt/rsaKey/'; //if you don't have public key and private key, then you can creating it.
        $rsa = new RSACrypt($keyPath);
        $encryptData = "I'm RSA encryptData";
        $encrypted = $rsa->publicEncrypt($encryptData); //output encrypt base64
        echo $encrypted;
        echo '<br/>';
        $decrypted = $rsa->privateDecrypt($encrypted); //output decrypt base64
        echo $decrypted;

        //also you can ...
        $rsa = new RSACrypt(); //if you have rsa key file
        $rsa->setPrivateKeyPath('your private key path');
        $rsa->setPublicKeyPath('your public key path');
        //or
        $pubKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAt7iiFbB++U/6+Cyy5EmT
                osbj8pStRUfcCJ1SIwJUF4oe9dCt4KGMjh9QSMFgIcdDFYDyk1yb3a40BiSLkIsi
                L5auf+LuVYH7yMCaE144NpadEe9oYTixdSifgTOYdUhEHGxc3xATwIA4A8GMCSYL
                f3yNfqjf7jbRr/RzCNWVPss0Iyg8bE9eCoVsF6GEl6PeQ4t6TmVSLPZIHuib/GYg
                FDeoozLAQDQ85HoAoIPqRoVx29vLhtzsH7x6RqHXk0tKZDI/oP+JY3ppxDsqXGAI
                zPXmQBKotQD9JvWGvHUPcVlc3VPJNsw9kyQ/jTkumw3V76UBJy8j32u9iNNKsjBw
                PQIDAQAB';
        $priKey = 'your private key';

        $rsa->setPrivateKey($priKey);
        $rsa->setPublicKey($pubKey);
    }

    public function response()
    {
        $request = new Request();
        $name = $this->validateParam('name', $request->name, 'Evai', '3-12', true);
        $mobile = $this->validateParam('mobile', $request->get('mobile'), '17777777777', 11, true);
        $email = $this->validateParam('email', $request->post('email'), 'evaichen0@gmail.com');
        $user = compact('name', 'mobile');
        echo $this->returnJson(0, 'success', compact('user', 'email'));
    }

    public function uploadImg()
    {
        $request = new Request();
        $form = <<<EOF
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="file" name="file" onchange="upload(event)" multiple/>
                <input type="hidden" name="base64" id="base64" />
                <input type="submit" value="上传文件"/>
            </form>
            <script>
            function upload(e) {
                var render = new FileReader();
                var file = e.target.files[0];
                render.addEventListener('load', function (e) {
                    document.getElementById('base64').value = e.target.result;
                }, false);
                render.readAsDataURL(file);
            }
            </script>
EOF;

        echo $form;

        $file = $request->files('file');
        $base64 = $request->base64;
        $file = new UploadFiles($file);
        /*$file->setMaxSize(1024*1024*3) //文件上传大小限制
            ->setAllowExt(['png', 'jpeg', 'jpg']) //文件后缀名限制
            ->setAllowMine(['image/png', 'image/jpeg']) //文件类型限制
            ->setImageFlag(false);*/ //检测是否是有效图片
        var_dump($file->uploadFile('.')); //upload file
        $file = new uploadFiles($base64);
        var_dump($file->uploadBase64('.')); //upload base64 image
    }

    public function session()
    {
        $request = new Request();

        $request->session('name', 'Evai');
        $request->session('mobile', '12312312');
        var_dump($request->session('name'));
        var_dump($request->session());
        //var_dump($request->sessionRemove());
        //var_dump($request->sessionFlash());
    }

    public function cookie()
    {
        $request = new Request();
        $request->cookie('user', 'Bob', 60); //current time + 60s
        var_dump($request->cookie()); //all cookie
        var_dump($request->cookie('user')); // cookie of user
        //$request->cookieRemove();
    }

    public function addMark()
    {
        $image = new HandleImage('./Image/demo.jpg'); //The image path
        $content = 'Font Mark'; //mark content
        $font_url = './Image/font/arial.ttf'; //mark font
        $size = 24; //font size
        $color = [255, 255, 255, 20]; //font color
        $pos = ['x' => 20, 'y' => 100]; //font position
        $rotate = 10; //font rotate
        //add font mark
        $image->addFontContent($content)
            ->addFontUrl($font_url)
            ->addFontSize($size)
            ->addFontColor($color)
            ->addFontRotate($rotate)
            ->addFontPos($pos)
            ->addFontMark();
        //add image mark
        $image->addImageSource('./Image/car.png')
            ->addImagePos(['x' => 10, 'y' => 10])
            ->addImageAlpha(100)
            ->addImageMark();
        $image->show();
        var_dump($image->save('./')); //save path
    }

    public function compress()
    {
        $image = new HandleImage('./Image/demo.jpg');//or base64 image
        $width = $image->getImageWidth() * 0.8;
        $height = $image->getImageHeight() * 0.8;
        $image->compress($width, $height);

        var_dump( $image->save('./')); //save path
    }

    public function wxAuth()
    {
        $appId = 'your appid';
        $appSecret = 'your appsecret';
        $wx = new WxAuth($appId, $appSecret);

        $redirect_url = 'your redirect url';
        //第一步：获取微信授权链接，进行跳转，用户同意授权，获取code
        $wx->authorize_user_info($redirect_url, $state = '321');

        //第二步：通过code换取网页授权access_token,这一步是第一步的回调地址url来获取数据的
        $code = $_REQUEST['code'];
        $state = $_REQUEST['state'];

        if ($state != '321') exit('Incorrect state!');

        $user_info = $wx->execute($code);

        if (empty($user_info)) {
            //如果失败，重新跳转到授权页面
            $wx->authorize_user_info($redirect_url, $state = '321');
        } else {
            //成功则返回用户的基本信息，然后处理业务逻辑
            var_dump($user_info);
        }
    }

    public function geoHash()
    {
        $geoHash = new GeoHash();
        //得到这点的hash值
        $hash = $geoHash->encode(30.98123848, 120.31683691);
        //取前缀，前缀约长范围越小
        $prefix = substr($hash, 0, 6);
        print_r($geoHash->decode($hash));
        //取出相邻八个区域
        $neighbors = $geoHash->neighbors($prefix);
        array_push($neighbors, $prefix);

        print_r($neighbors);
    }

}