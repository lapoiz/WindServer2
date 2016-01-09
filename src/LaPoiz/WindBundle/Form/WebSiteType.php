<?php

namespace LaPoiz\WindBundle\Form;

use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebSiteType extends AbstractType
{
    public function getName()
    {
        return 'websiteForm';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $websiteArray = array();
        $listWebSiteAvailable=WebsiteGetData::getListWebsiteAvailable();

        foreach ($listWebSiteAvailable as $website) {
            $websiteArray[$website]=$website;
        }

        $builder
            ->add('url')
            //->add('nom', WebSiteChoiceType::class)
            ->add('nom', ChoiceType::class, array(
                'choices' => $websiteArray))
            ->add('logo')
        ;
    }
    public function configureOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'LaPoiz\WindBundle\Entity\WebSite',
            'csrf_protection' => false,
            'attr' => array('id' => 'infoWebsite_form')
        ));
    }
}
