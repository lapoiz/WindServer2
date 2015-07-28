<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType
{
  public function getName()
  {
	return 'commentForm';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('username','hidden');
    $builder->add('mail','hidden');

    $builder->add('comment','genemu_tinymce',array('label'  => 'Un commentaire ?'));
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\Contact',
            'attr' => array('id' => 'contact_form')
  		));
  }

 }