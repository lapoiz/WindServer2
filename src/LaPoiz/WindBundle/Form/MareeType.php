<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MareeType extends AbstractType
{
  public function getName()
  {
	return 'laPoiz_windBundle_maree';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('mareeURL');

    $builder->add('mareeRestriction', 'collection', array(
            'type' => new MareeRestrictionType(),
            'label' => 'Restriction',
            'allow_add' => true,
            'allow_delete' => true
            //'by_reference' => false,
        ));


  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\Spot',
            'csrf_protection' => false,
            'attr' => array('id' => 'maree_form')
  		));
  }

 }