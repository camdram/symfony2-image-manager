<?php
namespace Hoyes\ImageManagerBundle\Service;

use Imagine\Image\ImagineInterface;
use Imagine\Image\ImageInterface;

use Hoyes\ImageManagerBundle\Entity\Image;

class ImageCache
{
    private $cache_dir;

    private $data_dir;

    private $imagine;

    public function __construct($cache_dir, $data_dir, ImagineInterface $imagine)
    {
        $this->cache_dir = $cache_dir;
        $this->data_dir = $data_dir;
        $this->imagine = $imagine;
    }

    public function makeFilename($hash, $width, $height, $crop)
    {
        $path = $hash.'_'.$width.'_'.$height;
        if ($crop) $path .= '_c';
        return $path;
    }

    public function getFilePath($hash, $width, $height, $crop)
    {
        return $this->cache_dir.DIRECTORY_SEPARATOR.$this->makeFilename($hash, $width, $height, $crop);
    }

    private function exists(Image $image, $width, $height, $crop)
    {
        return file_exists($this->getFilepath($image->getHash(), $width, $height, $crop));
    }

    private function getResizeDimensions(Image $image, $width, $height, $fit)
    {
        if (is_null($width) && is_null($height)) {
            return array($image->getWidth(), $image->getHeight());
        }
        elseif (is_null($width)) {
            $ratio = $height / $image->getHeight();
        }
        elseif (is_null($height)) {
            $ratio = $width / $image->getWidth();
        }
        else {
            $ratios = array(
                $width / $image->getWidth(),
                $height / $image->getHeight(),
            );
            if ($fit) {
                $ratio = max($ratios);
            } else {
                $ratio = min($ratios);
            }
        }

        if ($ratio > 1.0) $ratio = 1.0;

        $new_width = $image->getWidth() * $ratio;
        $new_height = $image->getHeight() * $ratio;
        return array(round($new_width), round($new_height));
    }

    public function changeDimensions(Image $image, $width, $height, $crop)
    {
        if ($crop) {
            return array($width, $height);
        }
        else {
            return $this->getResizeDimensions($image, $width, $height, false);
        }
    }



    public function add(Image $image, $width, $height, $crop)
    {
        list($new_width, $new_height) = $this->getResizeDimensions($image, $width, $height, $crop);
        $img = $this->imagine->open($this->data_dir.DIRECTORY_SEPARATOR.$image->getHash());
        $type = $crop ? ImageInterface::THUMBNAIL_INSET : ImageInterface::THUMBNAIL_OUTBOUND;

        $img = $img->thumbnail(new \Imagine\Image\Box($new_width, $new_height), $type);

        if ($crop) {
            $x = ($img->getSize()->getWidth() - $width) / 2;
            $y = ($img->getSize()->getHeight() - $height) / 2;
            $img->crop(new \Imagine\Image\Point($x, $y), new \Imagine\Image\Box($width, $height));
        }

        $img->save($this->getFilePath($image->getHash(), $width, $height, $crop), array('format' => 'jpg'));
    }

    public function ensure(Image $image, $width, $height, $crop)
    {
        if (!$this->exists($image, $width, $height, $crop)) {
            $this->add($image, $width, $height, $crop);
        }
        return $this->getFilePath($image->getHash(), $width, $height, $crop);
    }

}