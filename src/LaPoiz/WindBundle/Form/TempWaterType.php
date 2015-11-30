<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TempWaterType extends AbstractType
{
  public function getName()
  {
	return 'laPoiz_windBundle_tempWater';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
      $builder->add('tempWaterURL', 'text', array('label' => 'ULR pour la T°C de l\'eau (http://www.meteocity.com/plage/)'));
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\Spot',
            'csrf_protection' => false,
            'attr' => array('id' => 'tempWater_form')
  		));
  }

 }