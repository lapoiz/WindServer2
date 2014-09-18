<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DataWindPrevType extends AbstractType
{
    public function getName()
    {
        return 'dataWindPrevForm';
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url')
            ->add('slotTime','integer',array('label' => 'Nombre de prevision par jour'))
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
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'LaPoiz\WindBundle\Entity\DataWindPrev',
                'attr' => array('id' => 'addSite_form'),
                'csrf_protection' => false
        ));
    }

}
