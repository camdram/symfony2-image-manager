<?php
namespace Hoyes\ImageManagerBundle\Twig\Extension;

use Symfony\Component\Routing\RouterInterface;
use Hoyes\ImageManagerBundle\Entity\Image;
use Hoyes\ImageManagerBundle\Service\ImageCache;

class ImageExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Hoyes\ImageManagerBundle\Service\ImageCache
     */
    private $cache;

    public function __construct(RouterInterface $router, ImageCache $cache)
    {
        $this->router = $router;
        $this->cache = $cache;
    }

    public function getFunctions()
    {
        return array(
            'render_image' => new \Twig_Function_Method($this, 'renderImage', array('is_safe' => array('html'))),
            'image_url' => new \Twig_Function_Method($this, 'getImageUrl'),
        );
    }

    public function renderImage(Image $image, $width = null, $height = null, $crop = false, $attrs = array())
    {
        $attrs['src'] = $this->getImageUrl($image, $width, $height, $crop);
        if (!$crop) {
            list($width, $height) = $this->cache->changeDimensions($image, $width, $height, false);
        }
        $attrs['width'] = round($width);
        $attrs['height'] = round($height);

        $tag = '<img';
        foreach ($attrs as $key => $val) {
            $tag .= ' '.$key.'="'.$val.'"';
        }
        $tag .= '/>';
        return $tag;

    }

    public function getImageUrl(Image $image, $width = null, $height = null, $crop = false)
    {
        $hash = $image->getHash();

        if (!$width && !$height) {
            return $this->router->generate('hoyes_image_manager_image', array(
                'ext' => $image->getExtension(), 'hash' => $hash
            ));
        }
        elseif ($width && !$height) {
            return $this->router->generate('hoyes_image_manager_image_width', array(
                'ext' => $image->getExtension(), 'hash' => $hash, 'width' => $width
            ));
        }
        elseif (!$width && $height) {
            return $this->router->generate('hoyes_image_manager_image_height', array(
                'ext' => $image->getExtension(), 'hash' => $hash, 'height' => $height
            ));
        }
        elseif ($width && $height && !$crop) {
            return $this->router->generate('hoyes_image_manager_image_both', array(
                'ext' => $image->getExtension(), 'hash' => $hash, 'width' => $width, 'height' => $height
            ));
        }
        else {
            return $this->router->generate('hoyes_image_manager_image_crop', array(
                'ext' => $image->getExtension(), 'hash' => $hash, 'width' => $width, 'height' => $height
            ));
        }
    }

    public function getName()
    {
        return 'HoyesImageExtension';
    }
}