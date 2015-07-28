<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactType extends AbstractType
{
  public function getName()
  {
	return 'contactForm';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('username','text',array('label'  => 'Utilisateur'));

    $builder->add('mail','email',array('label'  => 'Adresse mail'));

    //$builder->add('comment','genemu_tinymce',array('label'  => 'Commentaire sur vous et le spot'));
    $builder->add('comment','hidden');

  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\Contact',
            'attr' => array('id' => 'contact_form')
  		));
  }

 }