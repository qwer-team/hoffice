<?php

namespace HOffice\AdminBundle\Form\Invoice;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use HOffice\AdminBundle\Form\Invoice;
class EditInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$qb = $this->em->getRepository('HOfficeAdminBundle:House\Apartment')
                                    ->createQueryBuilder('a')
                                    ->where("a.house = :house_id")
                                    ->setParameter("house_id", $this->house_id)
                                    ->orderBy('a.name', 'ASC'); */
        $disable = array( 'disabled'=>'disabled' );
        $builder
            ->add('N')
            ->add('date','date', $disable )
            ->add('status')
            ->add('contract_id','integer',$disable)
            //->add('oa2')
            //->add('txt1')
            //->add('txt2')
            ->add('summa1')
            //->add('summa2')
            //->add('summa3')
            ->add('ucor',null, $disable )
            //->add('dtcor','date', $disable )
            //->add('pdtype',null, $disable )
            ->add( "pdlines", 'collection', 
                array(
                    'type'         => new \Itc\DocumentsBundle\Form\Pd\PdlReversalType(),
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => true,
                ))  
        ;
    }

    public function getName()
    {
        return 'itc_documentsbundle_editinvoicetype';
    }
}
