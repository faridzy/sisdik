<?php

namespace Fast\SisdikBundle\Form;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Fast\SisdikBundle\Entity\Sekolah;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SiswaImportType extends AbstractType
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $sekolah = $user->getSekolah();

        $em = $this->container->get('doctrine')->getManager();
        if (is_object($sekolah) && $sekolah instanceof Sekolah) {
            $querybuilder1 = $em->createQueryBuilder()->select('t')
                    ->from('FastSisdikBundle:Tahunmasuk', 't')->where('t.sekolah = :sekolah')
                    ->orderBy('t.tahun', 'DESC')->setParameter('sekolah', $sekolah);
            $builder
                    ->add('tahunmasuk', 'entity',
                            array(
                                    'class' => 'FastSisdikBundle:Tahunmasuk',
                                    'label' => 'label.yearentry.entry', 'multiple' => false,
                                    'expanded' => false, 'property' => 'tahun', 'required' => true,
                                    'query_builder' => $querybuilder1,
                                    'attr' => array(
                                        'class' => 'medium'
                                    ),
                            ));

            $querybuilder2 = $em->createQueryBuilder()->select('t')
                    ->from('FastSisdikBundle:Gelombang', 't')->where('t.sekolah = :sekolah')
                    ->orderBy('t.urutan', 'ASC')->setParameter('sekolah', $sekolah);
            $builder
                    ->add('gelombang', 'entity',
                            array(
                                    'class' => 'FastSisdikBundle:Gelombang',
                                    'label' => 'label.admissiongroup.entry', 'multiple' => false,
                                    'expanded' => false, 'property' => 'nama', 'required' => true,
                                    'query_builder' => $querybuilder2,
                                    'attr' => array(
                                        'class' => 'medium'
                                    ),
                            ));
        }

        $builder
                ->add('delimiter', 'choice',
                        array(
                                'label' => 'label.fielddelimiter',
                                'choices' => array(
                                        ';' => 'semicolon [ ; ]', ',' => 'comma [ , ]',
                                        '|' => 'pipe [ | ]', ':' => 'colon [ : ]'
                                ),
                                'attr' => array(
                                    'class' => 'medium'
                                ),
                        ))
                ->add('file', 'file',
                        array(
                                'required' => true,
                                'attr' => array(
                                    'class' => 'medium'
                                ),
                        ));
    }

    //     public function setDefaultOptions(OptionsResolverInterface $resolver) {
    //         $resolver
    //                 ->setDefaults(
    //                         array(
    //                             'data_class' => 'Fast\SisdikBundle\Entity\Tahun'
    //                         ));
    //     }

    public function getName() {
        return 'fast_sisdikbundle_siswaimporttype';
    }
}
