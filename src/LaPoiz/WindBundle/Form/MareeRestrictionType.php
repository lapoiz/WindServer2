<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MareeRestrictionType extends AbstractType
{
  public function getName()
  {
	return 'laPoiz_windBundle_mareeRestriction';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
      $builder->add('hauteurMax','integer');
      $builder->add('hauteurMin','integer');
      /*$builder->add('state','text');*/
      $builder->add('state','choice', array(
              'choices' => array('OK'=>'OK', 'warn'=>'warn', 'KO'=>'KO')
          )
      );
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\MareeRestriction'
  		));
  }
 }