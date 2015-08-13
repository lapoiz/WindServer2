<?php

namespace LaPoiz\WindBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

class DataWindPrevType extends AbstractType
{
    public function getName() {
        return 'dataWindPrevForm';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('url')
            ->add('website','entity',
                array('class' => 'LaPoizWindBundle:WebSite',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('webSite')
                            ->where('webSite.nom != :windGuruPro')
                            ->setParameter('windGuruPro',WebsiteGetData::windguruProName);
                    },
                    'property' => 'nom',
                    'multiple' => false))
            ->add('spot','entity',
                array('class' => 'LaPoizWindBundle:Spot',
                    'property' => 'nom',
                    'multiple' => false,
                    'read_only' => true))
        ;
    }

    /*
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('url')
            ->add('website','entity', 
                array('class' => 'LaPoizWindBundle:WebSite',
                  'property' => 'nom',
                  'multiple' => false))
            ->add('spot','entity', 
                array('class' => 'LaPoizWindBundle:Spot',
                  'property' => 'nom',
                  'multiple' => false,
                  'read_only' => true))
        ;
    }*/

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'LaPoiz\WindBundle\Entity\DataWindPrev',
                'attr' => array('id' => 'dataWindPrev_form'),
                'csrf_protection' => false
        ));
    }

}
