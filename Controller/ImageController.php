<?php

namespace Hoyes\ImageManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ImageInterface;

use Hoyes\ImageManagerBundle\Service\ImageCache;
use Hoyes\ImageManagerBundle\Entity\Image;

class ImageController extends Controller
{
    public function indexAction($hash, $ext, $width, $height, $crop)
    {

        $repo = $this->getDoctrine()->getRepository('HoyesImageManagerBundle:Image');

        $image = $repo->findOneBy(array('hash' => $hash, 'extension' => $ext));

        if (!$image) {
            throw $this->createNotFoundException('Invalid hash');
        }


        /** @var $cache ImageCache */
        $cache = $this->get('hoyes_image_manager.image_cache');
        $filepath = $cache->ensure($image, $width, $height, $crop);
        $filename = $cache->makeFilename($image->getHash(), $width, $height, $crop);

        $response = new Response();
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', $image->getType());

        $response->setPublic();
        $response->setMaxAge(63072000);
        $response->setSharedMaxAge(63072000);

        $response->setContent(file_get_contents($filepath));
        return $response;
    }

    public function postAction(Request $request, $width = 250, $height = 200)
    {
        $image = new Image();

        /** @var $file File */
        $file = $request->files->get('file');
        /** @var $imagine ImagineInterface */
        $imagine = $this->get('hoyes_image_manager.imagine');
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('HoyesImageManagerBundle:Image');


        $hash = md5(file_get_contents($file->getPathname()));

        if (!($image = $repo->findOneByHash($hash))) {

            $data_path = $this->container->getParameter('hoyes_image_manager.data_dir');
            $ext = $file->guessExtension();
            $image_file = $imagine->open($file->getPathname());
            $size = $image_file->getSize();

            $max_width = $this->container->getParameter('hoyes_image_manager.max_width');
            $max_height = $this->container->getParameter('hoyes_image_manager.max_height');
            if ($size->getWidth() > $max_width || $size->getHeight() > $max_height) {
                $image_file = $image_file->thumbnail(new \Imagine\Image\Box($max_width, $max_height),
                            ImageInterface::THUMBNAIL_INSET);
                $size = $image_file->getSize();
            }

            $image = new Image;
            $image->setExtension($ext)
                ->setHash($hash)
                ->setWidth($size->getWidth())
                ->setHeight($size->getHeight())
                ->setType($file->getMimeType())
            ;

            $em->persist($image);
            $em->flush();

            $image_file->save($data_path.DIRECTORY_SEPARATOR.$hash, array('format' => $ext));

        }

        /** @var $cache ImageCache */
        $cache = $this->get('hoyes_image_manager.image_cache');
        list($thumb_width, $thumb_height) = $cache->changeDimensions($image, $width, $height, false);
        $url = $this->generateUrl('hoyes_image_manager_image_both', array(
            'hash' => $hash, 'width' => $width, 'height' => $height, 'ext' => $image->getExtension(),
        ));

        $full_url = $this->generateUrl('hoyes_image_manager_image_both', array(
            'hash' => $hash, 'width' => 1024, 'height' => 768, 'ext' => $image->getExtension(),
        ));

        $data = array(
            'url' => $url,
            'full_url' => $full_url,
            'width' => $thumb_width,
            'height' => $thumb_height,
            'id' => $image->getId(),
        );

        $response = new JsonResponse();
        $response->setData($data);
        return $response;
    }
}
