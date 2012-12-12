<?php

namespace HOffice\AdminBundle\Form\House;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ApartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('kod')
            ->add('name')
            ->add('floor')
            ->add('rooms')
            ->add('q_w_meters')
            ->add('s_live')
            ->add('s_all')
            ->add('s_balcony')
            ->add('s_wo_balcony')
            ->add('house')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HOffice\AdminBundle\Entity\House\Apartment'
        ));
    }

    public function getName()
    {
        return 'itc_documentsbundle_house_apartmenttype';
    }
}
