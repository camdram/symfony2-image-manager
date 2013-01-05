<?php
namespace Hoyes\ImageManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Doctrine\Common\Persistence\ObjectManager;

use Hoyes\ImageManagerBundle\Service\Encryptor;
use Hoyes\ImageManagerBundle\Form\DataTransformer\ImageTransformer;

class ImageType extends AbstractType
{
    /**
     * @var \Hoyes\ImageManagerBundle\Service\Encryptor
     */
    private $encryptor;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;

    private $post_route;

    public function __construct(Encryptor $encryptor, ObjectManager $om, $post_route)
    {
        $this->encryptor = $encryptor;
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
        $view->vars['session'] = urlencode($this->encryptor->encrypt(session_id()));
        $view->vars['preview_div'] = $options['preview_div'];
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
        return 'hidden';
    }

    public function getName()
    {
        return 'image_upload';
    }

    public function encrypt($string)
    {
        $crypt = new Encrypt($this->token);
        return $crypt->encrypt($string);
    }
}
