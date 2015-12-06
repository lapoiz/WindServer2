<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegionType extends AbstractType
{
  public function getName()
  {
	return 'region';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('nom');
    $builder->add('numDisplay');
    $builder->add('description','genemu_tinymce');
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\Region',
            'csrf_protection' => false,
            'attr' => array('id' => 'region_form')
  		));
  }

 }