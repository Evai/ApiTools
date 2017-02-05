<?php

spl_autoload_register(function ($class) {
    include $class . '.php';
});

use Common\Common;
use DataEncrypt\AESCrypt;
use Request\Request;
use Upload\UploadFiles;
use Image\HandleImage;

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

    public function response()
    {
        $request = new Request();
        $name = $this->validateParam('name', $request->name, 'Evai', '3-12', true);
        $mobile = $this->validateParam('mobile', $request->get('mobile'), '', 11, true);
        $email = $this->validateParam('email', $request->post('email'), 'evai@gmail.com');
        $user = ['name' => $name, 'mobile'=> $mobile];
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
        echo $file->uploadFile('.'); //upload file
        $file = new uploadFiles($base64);
        echo $file->uploadBase64('.'); //upload base64 image
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

    public function compress()
    {
        $image = new HandleImage('./Image/demo.jpg'); //The image path
        $content = 'Font Mark'; //mark content
        $font_url = './Image/font/arial.ttf'; //mark font
        $size = 24; //font size
        $color = [255, 255, 255, 20]; //font color
        $pos = ['x' => 20, 'y' => 100]; //font position
        $rotate = 10; //font rotate
        $image->addFontContent($content)
            ->addFontUrl($font_url)
            ->addFontSize($size)
            ->addFontColor($color)
            ->addFontRotate($rotate)
            ->addFontPos($pos)
            ->addFontMark();
        /*$width = $image->getImageWidth() * 0.5;
        $height = $image->getImageHeight() * 0.5;
        $image->compress($width, $height);*/
        /**
         * add image mark
         */
        $info = getimagesize('./Image/demo.jpg');
        header('Content-Type: image/jpeg');
        //exit(var_dump($info));
        //在内存中建立一个和图片类型一样的图像

        imagecreatefromjpeg('./Image/demo.jpg');
        //把图片复制到内存中
        /*$image->addImageSource('./Image/car.png')
            ->addImagePos(['x' => 10, 'y' => 10])
            ->addImageAlpha(100)
            ->addImageMark();*/
        //$image->show();
        //var_dump($image->getImageInfo());
        /**
         * compress image and save image
         */
       /* $width = $image->getImageWidth() * 0.5;
        $height = $image->getImageHeight() * 0.5;
        $image->compress($width, $height);

        $image->save('../'); //save path*/
    }

}
echo '<pre/>';

$demo = new Demo();
//$demo->AESDemo();
//$demo->response();
//$demo->uploadImg();
//$demo->session();
//$demo->cookie();
$demo->compress();