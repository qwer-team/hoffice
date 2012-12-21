<?php

namespace HOffice\AdminBundle\Form\Invoice;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

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
            ->add('contract_id','integer', $disable)
            //->add('oa2')
            //->add('txt1')
            //->add('txt2')
            ->add('summa1',null, $disable )
            //->add('summa2')
            //->add('summa3')
            ->add('name_ucor',null, $disable )
            ->add('dtcor','date', $disable )
            //->add('pdtype',null, $disable )
                
        ;
    }

    public function getName()
    {
        return 'itc_documentsbundle_editinvoicetype';
    }
}
