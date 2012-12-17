<?php

namespace HOffice\AdminBundle\Form\Payment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Itc\AdminBundle\Tools\LanguageHelper;
use Itc\AdminBundle\ItcAdminBundle;

class SearchPaymentType extends AbstractType {

/*
    public function buildForm( FormBuilderInterface $builder, array $options ){

        $required = array( 'required' => NULL );
        $arra     = array( 'widget' => 'single_text', 'required' => NULL );

        $builder->add( 'text', 'text', $required )
                ->add( 'from', 'genemu_jquerydate', $arra )
                ->add( 'to',   'genemu_jquerydate', $arra );

    }
 * 
 */
    private $locale;
    private $house_id;
    /**
     *
     * @var Doctrine\ORM\EntityManager 
     */
    private $em;
    
    public function __construct( $house_id = NULL )
    {
        $this->locale = LanguageHelper::getLocale();
        $this->house_id = $house_id;
        $this->em = ItcAdminBundle::getContainer()
                    ->get("doctrine")
                    ->getEntityManager();
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $required = array( 'required' => NULL );
        $arra     = array( 'required' => NULL );

        $qb = $this->em->getRepository('HOfficeAdminBundle:House\Apartment')
                                    ->createQueryBuilder('a')
                                    ->where("a.house = :house_id")
                                    ->setParameter("house_id", $this->house_id)
                                    ->orderBy('a.name', 'ASC');      
        
        $builder
            ->add('text', 'text', $required )
            ->add( 'from', 'genemu_jquerydate', $arra )
            ->add( 'to',   'genemu_jquerydate', $arra )
            ->add('user_id', 'hidden',
                    array('required'=>NULL, 
                        'attr' => array(
                            'class' => 'search_user_id' 
            )))
            ->add('user', 'text',
                    array('required'=>NULL, 
                        'attr' => array(
                            'class' => 'entity_search w250px',
                            'data-link' => ".search_user_id",
                            'data-type-link' => "input",
                            'data-route' => "ajax_search_user"
            )))
            ->add('house_id', 'hidden',
                    array('required'=>NULL, 
                        'attr' => array(
                            'class' => 'search_house_id' 
            )))                    
            ->add('house', 'text',
                    array('required'=>NULL, 
                        'attr' => array(
                            'class' => 'entity_search w250px',
                            'data-link' => ".search_house_id",
                            'data-type-link' => "input",
                            'data-route' => "ajax_search_house",
                            'data-after-search' => '.loadApartametData'
                            )))
            ->add('load_apartament_data', 'hidden', 
                    array('required'=>NULL, 
                        'attr' => array(
                            'class' => 'loadApartametData',
                            'data-link' => ".search_apartment_id",
                            'data-route' => "ajax_search_apartment"
            )));

            if(isset( $this->house_id ) ){

                $builder->add( 'apartment_id', 'entity', array(
                               'required' => NULL,
                               'class' => 'HOfficeAdminBundle:House\Apartment',
                               'property' => 'name',
                               'query_builder' => $qb,
                               'attr' => array(
                                    'class' => 'search_apartment_id' 
                               )
                )) ;

            } else {

                $builder->add('apartment_id', 'choice', array(
                                'required' => NULL,
                                'attr' => array(
                                    'class' => 'search_apartment_id' 
                                ),
                )) ;

            }
    }

    public function getName(){

        return 'itc_documentsbundle_searchpaymenttype';

    }

}
