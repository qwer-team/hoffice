<?php

namespace HOffice\AdminBundle\Form\Service;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('kod')
            ->add('name')
            ->add('price')
            ->add('price1')
            //->add('unit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HOffice\AdminBundle\Entity\Service\Service'
        ));
    }

    public function getName()
    {
        return 'hoffice_adminbundle_service_servicetype';
    }
}
