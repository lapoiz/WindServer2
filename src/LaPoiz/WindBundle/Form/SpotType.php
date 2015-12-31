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

    $builder->add('description','genemu_tinymce');

    $builder->add('localisationDescription','genemu_tinymce');

    $builder->add('gpsLong','number',array('label'=>'GPS long'));
    $builder->add('gpsLat','number',array('label'=>'GPS lat'));

    $builder->add('infoOrientation','genemu_tinymce');
    $builder->add('infoMaree','genemu_tinymce');

    $builder->add('windOrientation', 'collection', array('type' => new OrientationWindType(), 'label'=>' '));

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