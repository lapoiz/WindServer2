<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrientationWindType extends AbstractType
{
  public function getName()
  {
	return 'orientationWind';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('orientation','hidden'); // use in id of the input
    $builder->add('state','hidden', array('required' => false));
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\WindOrientation'
  		));
  }
 }