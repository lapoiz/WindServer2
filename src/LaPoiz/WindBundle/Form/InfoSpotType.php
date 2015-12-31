<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InfoSpotType extends AbstractType
{
  public function getName()
  {
	return 'infoSpotForm';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('titre')
            ->add('url')
            ->add('commentaire','genemu_tinymce');
  }

  public function configureOptions(OptionsResolver  $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\InfoSpot',
            'csrf_protection' => false,
            'attr' => array('id' => 'infoSpot_form')
  		));
  }

 }