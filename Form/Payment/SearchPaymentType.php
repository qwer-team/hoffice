<?php

namespace HOffice\AdminBundle\Form\Payment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Itc\AdminBundle\Tools\LanguageHelper;

class SearchPaymentType extends AbstractType {

    private $locale;

    public function __construct(){

        $this->locale = LanguageHelper::getLocale();

    }

    public function buildForm( FormBuilderInterface $builder, array $options ){

        $required = array( 'required' => NULL );
        $arra     = array( 'widget' => 'single_text', 'required' => NULL );

        $builder->add( 'text', 'text', $required )
                ->add( 'from', 'genemu_jquerydate', $arra )
                ->add( 'to',   'genemu_jquerydate', $arra );

    }

    public function getName(){

        return 'itc_documentsbundle_searchpaymenttype';

    }

}
