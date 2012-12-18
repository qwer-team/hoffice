<?php

namespace HOffice\AdminBundle\Form\Contract;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Itc\AdminBundle\Tools\LanguageHelper;
use Doctrine\ORM\EntityRepository;
use HOffice\AdminBundle\Form\Contract\ContractType;

class ContractCreateType extends ContractType
{
    private $choiceId;
    private $choiseName;
    
    public function __construct($em, $choice = null )
    {
        parent::__construct($em);
        
        if(!is_null($choice))
        {
            $this->choiceId = $choice->getId();
            $this->choiseName = $choice->getName();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder->add('user_id', 'hidden',
                    array('attr' => array(
                          'class' => 'search_user_id' )))
                    ->add('apartment_id', 'choice', array(
                                'required' => NULL,
                                'choices' => 
                                    array($this->choiceId => $this->choiseName),
                                'attr' => array(
                                    'class' => 'search_apartment_id' )
                    )) ;
    }
}
