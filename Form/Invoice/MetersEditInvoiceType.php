<?php

namespace HOffice\AdminBundle\Form\Invoice;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use HOffice\AdminBundle\Form\Invoice;
class MetersEditInvoiceType  extends AbstractType
{

    private $serviceM;
    private $closed;
    
    public function __construct( $serviceM = NULL, $closed)
    {
        $this->serviceM = $serviceM;
        $this->closed= $closed;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       // $builder
       //     ->add('summa1', 'hidden', array('required'=>NULL));
       
        if($this->serviceM)
        {
            if($this->closed)
            $builder
                ->add('summa2','integer', array( 'disabled'=>'disabled' ));
            else{
        }           $builder
                ->add('summa2','integer');
         
        }
                
        
        
//        
//        
//            $builder
//                    ->add( "pdlines", 'collection', 
//                array(
//                    'type'         => new Itc\DocumentsBundle\Form\Pd\PdlReversalType,
//                    'allow_add'    => true,
//                    'allow_delete' => true,
//                    'by_reference' => true,
//                ) );
            
            
//        ->add('id') 	->add('user_id') 	->add('apartment_id') 	->add('kod') 	->add('serial_number') 	->add('title') 	->add('registered') 	->add('sale');
//            ->add('N')
//            ->add('date','date', $disable )
//            ->add('status')
//            ->add('contract_id',null, $disable)
//            ->add('oa2')
//            ->add('txt1')
//            ->add('txt2')
//            ->add('summa1',null, $disable )
//            ->add('summa2')
//            ->add('summa3')
//            ->add('name_ucor',null, $disable )
//            ->add('dtcor','date', $disable );

    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Itc\DocumentsBundle\Entity\Pd\Pdl'
        ));
    }

    public function getName()
    {
        return 'itc_documentsbundle_meterseditinvoicetype';
    }
}