<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SpotType extends AbstractType
{
  public function getName()
  {
	return 'spot';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('nom');
    $builder->add('description','textarea', array('attr' => array('class' => 'ckeditor',)));
    $builder->add('isKitePractice');
    $builder->add('isWindsurfPractice');
    $builder->add('googleMapURL','text',array('label'=>'URL (googleMap)'));
    $builder->add('localisationDescription','textarea',array('label'=>'Description','attr' => array('class' => 'ckeditor',)));
    $builder->add('gpsLong','number',array('label'=>'GPS long'));
    $builder->add('gpsLat','number',array('label'=>'GPS lat'));
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\Spot',
            'csrf_protection' => false,
            'attr' => array('id' => 'spot_form')
  		));
  }

 }