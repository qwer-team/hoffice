<?php

namespace HOffice\AdminBundle\Form\Reversal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReversalPdlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add( 'N' )
            ->add( 'oa1', 'hidden' )
            ->add('summa1')
//            ->add('summa2')
//            ->add('summa3')
//            ->add('summa4')
//            ->add('summa5')
//            ->add('summa6')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Itc\DocumentsBundle\Entity\Pd\Pdl'
        ));
    }

    public function getName()
    {
        return 'itc_documentsbundle_pd_pdltype';
    }
}
