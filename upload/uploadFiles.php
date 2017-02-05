<?php

namespace Upload;
/**
 * Class uploadFiles
 */
class UploadFiles
{
    /**
     * @var int
     */
    private $maxSize = 0;
    /**
     * @var array
     */
    private $allowMime = ['image/jpeg', 'image/png'];
    /**
     * @var array
     */
    private $allowExt = ['jpeg', 'jpg', 'png'];
    /**
     * @var null
     */
    private $fileInfo;
    /**
     * @var
     */
    private $error;
    /**
     * @var
     */
    private $ext;
    /**
     * @var bool
     */
    private $imgFlag = true;

    private $base64_img;

    /**
     * uploadFiles constructor.
     * @param $fileInfo
     */
    public function __construct($fileInfo)
    {

        if (is_array($fileInfo)) {

            $this->fileInfo = $fileInfo;

        } else if (is_string($fileInfo)) {

            $this->base64_img = $fileInfo;

        }

    }

    /**
     * @return mixed
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * @param $size
     * @return $this
     */
    public function setMaxSize($size)
    {
        $this->maxSize = $size;
        return $this;
    }

    /**
     * @param array $mime
     * @return $this
     */
    public function setAllowMine(array $mime)
    {
        $this->allowMime = $mime;
        return $this;
    }

    /**
     * @param array $ext
     * @return $this
     */
    public function setAllowExt(array $ext)
    {
        $this->allowExt = $ext;
        return $this;
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setImageFlag($bool)
    {
        $this->imgFlag = $bool ?: false;
        return $this;
    }

    /**
     * check file error
     * @return boolean
     */
    protected function checkError()
    {

        if (!is_null($this->fileInfo)) {

            if ($this->fileInfo['error'] > 0) {

                switch ($this->fileInfo['error']) {
                    case 1:
                        $this->error = 'The file exceeds your upload_max_filesize ini directive (limit is ' . $this->getMaxFileSize() . ' KiB).';
                        break;
                    case 2:
                        $this->error = 'The file exceeds the upload limit defined in your form(MAX_FILE_SIZE).';
                        break;
                    case 3:
                        $this->error = 'The file was only partially uploaded.';
                        break;
                    case 4:
                        $this->error = 'No file was uploaded.';
                        break;
                    case 6:
                        $this->error = 'File could not be uploaded: missing temporary directory.';
                        break;
                    case 7:
                        $this->error = 'The file could not be written on disk.';
                        break;
                    case 8:
                        $this->error = 'File upload was stopped by a PHP extension.';
                        break;

                }

                return false;

            }

            return true;

        }

        $this->error = 'The file is not uploaded';

        return false;

    }

    /**
     * check file size
     * @return boolean
     */
    protected function checkSize()
    {

        if ( $this->maxSize > 0 && $this->fileInfo['size'] > $this->maxSize ) {

            $this->error = 'The file is out of range ' . $this->maxSize / 1024 . 'KB';

            return false;

        }

        return true;

    }

    /**
     * @return string
     */
    public function getExt()
    {
        return strtolower(pathinfo($this->fileInfo['name'], PATHINFO_EXTENSION));
    }

    /**
     * @return string
     */
    public function getOriginName()
    {
        return pathinfo($this->fileInfo['name'], PATHINFO_FILENAME);
    }
    /**
     * check file extension
     * @return boolean
     */
    protected function checkExt()
    {

        $this->ext = $this->getExt();

        if (!in_array($this->ext, $this->allowExt)) {

            $this->error = 'Not allowed file extension.';

            return false;

        }

        return true;

    }
    /**
     * check file type
     * @return boolean
     */
    protected function checkMime()
    {

        if(!in_array($this->fileInfo['type'], $this->allowMime)) {

            $this->error = 'Not allowed file type.';

            return false;

        }

        return true;

    }

    /**
     * check file is a image
     * @return bool
     */
    protected function checkTrueImg()
    {

        if($this->imgFlag){

            if(!getimagesize($this->fileInfo['tmp_name'])){

                $this->error = 'The file is not image.';

                return false;

            }

        }

        return true;

    }

    /**
     * check file upload method
     * @return boolean
     */
    protected function checkHttpPost()
    {

        if (!is_uploaded_file($this->fileInfo['tmp_name'])) {

            $this->error = 'The file is not upload for HTTP POST method.';

            return false;

        }

        return true;

    }

    /**
     * check upload path
     * @param $uploadPath
     */
    protected function checkUploadPath($uploadPath)
    {

        if (!file_exists($uploadPath)) {

            mkdir($uploadPath, 0755, true);

        }

    }

    /**
     * generate unique string
     * @return string
     */
    protected function generateName()
    {
        return date('Y-m-d-H-i-s-') . uniqid() . '-' . $this->getOriginName();

    }

    /**
     * Returns the maximum size of an uploaded file as configured in php.ini.
     *
     * @return int The maximum size of an uploaded file in bytes
     */
    private function getMaxFileSize()
    {
        $iniMax = strtolower(ini_get('upload_max_filesize'));

        if ('' === $iniMax) {
            return PHP_INT_MAX;
        }

        $max = ltrim($iniMax, '+');
        if (0 === strpos($max, '0x')) {
            $max = intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = intval($max, 8);
        } else {
            $max = (int) $max;
        }

        switch (substr($iniMax, -1)) {
            case 't': $max *= 1024;
            case 'g': $max *= 1024;
            case 'm': $max *= 1024;
            case 'k': $max *= 1024;
        }

        return $max;
    }

    /**
     * upload file
     * @param $uploadPath
     * @param null $fileName
     * @return null|string
     */
    public function uploadFile($uploadPath, $fileName = null)
    {

        if ($this->checkError() && $this->checkSize() && $this->checkExt() && $this->checkMime() && $this->checkTrueImg() && $this->checkHttpPost()) {

            $this->checkUploadPath($uploadPath);

            $fileName = $fileName ? $fileName : $uploadPath . '/' . $this->generateName() . '.' . $this->getExt();


            if (@move_uploaded_file($this->fileInfo['tmp_name'], $fileName)) {

                return [true, $fileName];

            }

            $this->error = 'Could not move the file.';

        }

        return [false, $this->error];

    }

    /**
     * base64图片上传
     * @param $uploadPath
     * @return string
     */
    public function uploadBase64($uploadPath)
    {

        $base64_img = trim($this->base64_img);

        $this->checkUploadPath($uploadPath);

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)) {

            $type = $result[2];

            if (in_array($type, $this->allowExt)) {

                $fileName = $uploadPath . '/' . date('Y-m-d-H-i-s-') . uniqid() . '.' . $type;

                if (@file_put_contents($fileName, base64_decode(str_replace($result[1], '', $base64_img)))) {

                    return [true, $fileName];

                }

                return [false, 'could not save image.'];
            }

           return [false, 'Not allowed image type.'];

        }

        return [false, 'Incorrect base64 image.'];

    }


}

