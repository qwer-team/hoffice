<?php

namespace HOffice\AdminBundle\Form\Invoice;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Itc\DocumentsBundle\Form\Pd\PdlReversalType;

class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$qb = $this->em->getRepository('HOfficeAdminBundle:House\Apartment')
                                    ->createQueryBuilder('a')
                                    ->where("a.house = :house_id")
                                    ->setParameter("house_id", $this->house_id)
                                    ->orderBy('a.name', 'ASC'); */
        $disable = array();
        $builder
            ->add('pdtype')
            ->add('N')
            ->add('date')
            ->add('status', null, $disable )
            ->add('contract')
            ->add('oa2')
            ->add('txt1')
            ->add('txt2')
            ->add('summa1')
            ->add('summa2')
            ->add('summa3')
            ->add('ucor')
            ->add('dtcor')
            ->add('pdtype', null, $disable )    
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
