<?php
namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TestGetMareeType extends AbstractType
{
  public function getName()
  {
	return 'testGetMaree';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('spot','entity',
        array(  'label'=>'Spot',
                'class' => 'LaPoiz\\WindBundle\\Entity\\Spot',
                'property' => 'nom',
                'multiple' => false
        ));
      $builder
          ->add('action','form_actions', [
              'buttons' => [
                  'tester' => ['type' => 'submit', 'options' => ['label' => 'Va chercher']]]
          ]);
  }
 }