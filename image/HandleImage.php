<?php

namespace Image;

/**
 * compress image and add mark for image
 */
class HandleImage
{
    //图片信息
    private $imageInfo;
    //内存中的图片
    private $image;
    //字体内容
    private $content;
    //字体路径
    private $font_url;
    //字体大小
    private $size = 20;
    //字体颜色
    private $color = [255, 255, 255, 10];
    //字体位置
    private $fontPos = ['x' => 20, 'y' => 100];
    //字体旋转角度
    private $rotateAngle = 0;
    //水印图片路径
    private $imageSource;
    //水印图片位置
    private $imagePos = ['x' => 50, 'y' => 50];
    //水印图片透明度
    private $imageAlpha = 50;

    /**
     * Image constructor.
     * @param $imageSrc
     */
    function __construct($imageSrc)
    {
        //获取图片信息
        $info = getimagesize($imageSrc);
        $this->imageInfo = array(
            'width' => $info[0],
            'height' => $info[1],
            'type' => image_type_to_extension($info[2], false),
            'mime' => $info['mime']
        );
        //在内存中建立一个和图片类型一样的图像
        $func = "imagecreatefrom{$this->imageInfo['type']}";
        //把图片复制到内存中
        $this->image = $func($imageSrc);
    }

    /**
     * 压缩图片
     * @param $width
     * @param $height
     * @return $this
     */
    function compress($width, $height)
    {
        //在内存中建立一个固定尺寸的真色彩图片
        $image_thumb = imagecreatetruecolor($width, $height);
        //将原图复制到新建的真色彩图片上，并按照一定比例压缩
        imagecopyresampled($image_thumb, $this->image, 0, 0, 0, 0, $width, $height, $this->imageInfo['width'], $this->imageInfo['height']);
        //销毁原始图片
        imagedestroy($this->image);
        $this->image = $image_thumb;
        return $this;
    }
    /**
     * 获取图片信息
     */
    function getImageInfo()
    {
        return $this->imageInfo;
    }
    /**
     * 获取图片宽度
     */
    function getImageWidth()
    {
        return $this->imageInfo['width'];
    }
    /**
     * 获取图片宽度
     */
    function getImageHeight()
    {
        return $this->imageInfo['height'];
    }
    /**
     * 获取图片后缀名
     */
    function getImageSuffix()
    {
        return $this->imageInfo['type'];
    }
    /**
     * 添加字体水印
     * $content 字体内容
     * $font_url 字体路径
     * $size 	字体大小
     * $color 	字体颜色
     * $local	位置x,y
     * $angle 	旋转角度
     */
    function addFontMark()
    {
        $content = $this->content ? $this->content : '';
        $font_url = $this->font_url ? $this->font_url : '';
        $size = $this->size;
        $color = $this->color;
        $local = $this->fontPos;
        $rotateAngle = $this->rotateAngle;

        $col = imagecolorallocatealpha($this->image, $color[0], $color[1], $color[2], $color[3]);
        imagettftext($this->image, $size, $rotateAngle, $local['x'], $local['y'], $col, $font_url, $content);
    }

    /**
     * 添加字体路径
     * @param $font_url
     * @return $this
     */
    function addFontUrl($font_url)
    {
        $this->font_url = $font_url;
        return $this;
    }

    /**
     * 添加字体内容
     * @param $content
     * @return $this
     */
    function addFontContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * 修改字体大小
     * @param $size
     * @return $this
     */
    function addFontSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * 添加字体颜色
     * @param array $color
     * @return $this
     */
    function addFontColor(array $color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * 修改字体位置
     * @param array $pos
     * @return $this
     */
    function addFontPos(array $pos)
    {
        $this->fontPos = $pos;
        return $this;
    }

    /**
     * 字体旋转角度
     * @param $rotateAngle
     * @return $this
     */
    function addFontRotate($rotateAngle)
    {
        $this->rotateAngle = $rotateAngle;
        return $this;
    }
    /**
     * 添加图片水印
     * $source  图片路径
     * $local	位置x,y
     * $alpha 	透明度
     */
    function addImageMark()
    {
        $source = $this->imageSource ? $this->imageSource : '';
        $ImagePos = $this->imagePos;
        $alpha = $this->imageAlpha;

        $info = getimagesize($source);

        $type = image_type_to_extension($info[2], false);
        $func = "imagecreatefrom{$type}";
        $water = $func($source);

        imagecopymerge($this->image, $water, $ImagePos['x'], $ImagePos['y'], 0, 0, $info[0], $info[1], $alpha);
        imagedestroy($water);
    }

    /**
     * 添加水印图片路径
     * @param $source
     * @return $this
     */
    function addImageSource($source)
    {
        $this->imageSource = $source;
        return $this;
    }

    /**
     * 添加水印图片路径
     * @param array $pos
     * @return $this
     */
    function addImagePos(array $pos)
    {
        $this->imagePos = $pos;
        return $this;
    }

    /**
     * 添加水印图片路径
     * @param $alpha
     * @return $this
     */
    function addImageAlpha($alpha)
    {
        $this->imageAlpha = $alpha;
        return $this;
    }

    /**
     * 在浏览器中输出图片
     */
    function show()
    {
        header('Content-type:' . $this->imageInfo['mime']);
        $func = "image{$this->imageInfo['type']}";
        $func($this->image);
    }

    /**
     * 存储路径,无需写后缀名
     * @param string $imagePath
     * @return mixed
     */
    function save($imagePath = '')
    {
        $time = date('YmdHis') . mt_rand(1000, 9999);
        $imagePath .= $time . '.' . $this->imageInfo['type'];
        $func = "image{$this->imageInfo['type']}";
        $res = $func($this->image, $imagePath);
        return $res;
    }

    /**
     * 压缩并输出base64图片
     */
    function outputBase64()
    {

        ob_start();

        $func = "image{$this->imageInfo['type']}";

        $func($this->image);

        $fileContent = ob_get_contents();

        ob_end_clean();

        return "data:image/{$this->imageInfo['type']};base64," . base64_encode($fileContent);
    }

    /**
     * 销毁图片
     */
    function __destruct()
    {
        imagedestroy($this->image);
        $this->imageInfo = null;
    }
}

