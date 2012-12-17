<?php

namespace HOffice\AdminBundle\Form\Contract;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Itc\AdminBundle\Tools\LanguageHelper;
use Doctrine\ORM\EntityRepository;


class ContractType extends AbstractType
{
    /**
     *
     * @var Doctrine\ORM\EntityManager 
     */
    private $em;
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('kod')
            ->add('serial_number')
            ->add('registered')
            ->add('sale', 'text', array('required'=>NULL));
                        

        $languages = $this->getLanguages();     
         
        foreach($languages as $k => $lang ){
            
            $builder->add( $lang.'Translation.title', 'text', 
                                        array("label" => "Title"
                                             ));
        }
        
    }
    protected function getLanguages()
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
