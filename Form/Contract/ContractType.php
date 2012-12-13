<?php

namespace HOffice\AdminBundle\Form\Contract;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Itc\AdminBundle\Tools\LanguageHelper;


class ContractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
/*        $qb = $this->em->getRepository( 'ItcAdminBundle:User' )
                                            ->createQueryBuilder( 'U' );
        
        if( $options["attr"]["new"] && ! $this->update ){
            $qb->select("P")->where("P.id is null");
        }
        if( !$this->update && ! $options["attr"]["new"])
            $qb = $qb->select( "P" )->Join( "P.products",  "T")
                                ->where( "T.id = {$this->id}" );
  */      
        $builder
            ->add('parent_id')
            ->add('kod')
            ->add('serial_number')
            ->add('registered')
            ->add('user', null, array('required'=>NULL))
            ->add('apartment', null, array('required'=>NULL))
            ->add('sale')
        ;
        $languages = $this->getLanguages();     
         
        foreach($languages as $k => $lang ){
            
            $builder->add( $lang.'Translation.title', 'text', 
                                        array("label" => "Title"
                                             ));
        }
        
    }
    private function getLanguages()
    {
        $locale = LanguageHelper::getLocale();
        $lngs = LanguageHelper::getLanguages();
        $languages = !\is_null($lngs)? $lngs: array($locale);
        return $languages;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HOffice\AdminBundle\Entity\Contract\Contract'
        ));
    }

    public function getName()
    {
        return 'itc_documentsbundle_contract_contracttype';
    }
}
