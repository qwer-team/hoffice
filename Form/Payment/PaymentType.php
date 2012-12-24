<?php

namespace HOffice\AdminBundle\Form\Payment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('N')
            ->add('date', 'genemu_jquerydate')
            ->add('status')
//            ->add('oa1')
//            ->add('oa2')
//            ->add('txt1')
//            ->add('txt2')
            ->add('summa1')
//            ->add('summa2')
//            ->add('summa3')
//            ->add('ucor')
//            ->add('dtcor', 'genemu_jquerydate')
            ->add('contract')
            ->add('invoice')
            ->add('pdtype')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HOffice\AdminBundle\Entity\Payment\Payment'
        ));
    }

    public function getName()
    {
        return 'hoffice_adminbundle_payment_paymenttype';
    }
}
