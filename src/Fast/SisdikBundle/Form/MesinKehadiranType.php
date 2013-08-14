<?php

namespace Fast\SisdikBundle\Form;
use Fast\SisdikBundle\Entity\Sekolah;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MesinKehadiranType extends AbstractType
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $sekolah = $user->getSekolah();

        $em = $this->container->get('doctrine')->getManager();

        $builder
                ->add('sekolah', new EntityHiddenType($em),
                        array(
                                'required' => true, 'class' => 'FastSisdikBundle:Sekolah',
                                'data' => $sekolah->getId(),
                        ))
                ->add('alamatIp', null,
                        array(
                                'label' => 'label.ipaddress',
                                'attr' => array(
                                    'class' => 'medium'
                                )
                        ))
                ->add('commkey', null,
                        array(
                                'label' => 'label.commkey',
                                'attr' => array(
                                    'class' => 'mini'
                                )
                        ))
                ->add('aktif', null,
                        array(
                            'label' => 'label.active', 'required' => false, 'label_render' => false,
                        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver
                ->setDefaults(
                        array(
                            'data_class' => 'Fast\SisdikBundle\Entity\MesinKehadiran'
                        ));
    }

    public function getName() {
        return 'fast_sisdikbundle_mesinkehadirantype';
    }
}
