<?php
namespace Hoyes\ImageManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Doctrine\Common\Persistence\ObjectManager;

use Hoyes\ImageManagerBundle\Form\DataTransformer\ImageTransformer;

class ImageType extends AbstractType
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;

    private $post_route;

    public function __construct(ObjectManager $om, $post_route)
    {
        $this->om = $om;
        $this->post_route = $post_route;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ImageTransformer($this->om));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['post_route'] = $this->post_route;
        $view->vars['copy_target'] = $options['copy_target'];
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Hoyes\ImageManagerBundle\Entity\Image',
            'preview_div' => 'image_upload_preview',
            'copy_target' => null,
        ));
    }

    public function getParent()
    {
        return 'file';
    }

    public function getName()
    {
        return 'image_upload';
    }

}
