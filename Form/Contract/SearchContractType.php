<?php

namespace HOffice\AdminBundle\Form\Contract;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class SearchContractType extends AbstractType
{
    private $locale;
    private $house_id;
    /**
     *
     * @var Doctrine\ORM\EntityManager 
     */
    private $em;
    
    public function __construct($em, $locale, $house_id = NULL )
    {
        $this->locale = $locale;
        $this->house_id = $house_id;
        $this->em = $em;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $qb = $this->em->getRepository('HOfficeAdminBundle:House\Apartment')
                                    ->createQueryBuilder('a')
                                    ->where("a.house = :house_id")
                                    ->setParameter("house_id", $this->house_id)
                                    ->orderBy('a.name', 'ASC');      
        
        $builder
            ->add('text', 'text', array('required'=>NULL))
            ->add('serial_number', 'integer', array('required'=>NULL))
            ->add('user_id', 'hidden',
                    array('required'=>NULL, 
                        'attr' => array(
                            'class' => 'search_user_id' )))
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
                            'class' => 'search_house_id' )))                    
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
                        )))  ;
            if($this->house_id){
               $builder->add('apartment_id', 'entity', array(
                                'required' => NULL,
                                'class' => 'HOfficeAdminBundle:House\Apartment',
                                'property' => 'name',
                                'query_builder' => $qb,
                                'attr' => array(
                                    'class' => 'search_apartment_id' )
                                )) ;
            }  
            else 
            {
                $builder->add('apartment_id', 'choice', array(
                                'required' => NULL,
                                'attr' => array(
                                    'class' => 'search_apartment_id' ),
                                )) ;
            }
    }

    public function getName()
    {
        return 'itc_documentsbundle_searchcontracttype';
    }
}
