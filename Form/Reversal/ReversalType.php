<?php

namespace HOffice\AdminBundle\Form\Reversal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Itc\DocumentsBundle\Form\Pd\PdlReversalType;
use HOffice\AdminBundle\Form\Reversal\TagType;

class ReversalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('N')
            ->add('date')
            ->add('status')
//            ->add('oa1')
//            ->add('oa2')
//            ->add('txt1')
//            ->add('txt2')
            ->add('summa1')
            ->add('summa2')
            ->add('summa3')
//            ->add('ucor')
//            ->add('dtcor')
//            ->add('contract_id')
            ->add('pdtype')
            ->add('contract')
            ->add( "pdlines", 'collection', 
                array(
                    'type'         => new PdlReversalType(),
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => true,
                ) 
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HOffice\AdminBundle\Entity\Reversal\Reversal'
        ));
    }

    public function getName()
    {
        return 'hoffice_adminbundle_reversal_reversaltype';
    }
}
