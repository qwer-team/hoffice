<?php

namespace HOffice\AdminBundle\Form\Invoice;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pdtype_id')
            ->add('N')
            ->add('date')
            ->add('status')
            ->add('oa1')
            ->add('oa2')
            ->add('txt1')
            ->add('txt2')
            ->add('summa1')
            ->add('summa2')
            ->add('summa3')
            ->add('ucor')
            ->add('dtcor')
            ->add('pdtype')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HOffice\AdminBundle\Entity\Invoice\Invoice'
        ));
    }

    public function getName()
    {
        return 'hoffice_adminbundle_invoice_invoicetype';
    }
}
