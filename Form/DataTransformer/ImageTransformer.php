<?php
namespace Hoyes\ImageManagerBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class ImageTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }


    public function transform($issue)
    {
        return $issue;
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $image = $this->om
            ->getRepository('HoyesImageManagerBundle:Image')
            ->findOneById($id)
        ;

        if (null === $image) {
            throw new TransformationFailedException(sprintf(
                'An image with number "%s" does not exist!',
                $id
            ));
        }

        return $image;
    }
}