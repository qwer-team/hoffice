<?php

namespace HOffice\AdminBundle\Form\Contract;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Itc\AdminBundle\Tools\LanguageHelper;
use Doctrine\ORM\EntityRepository;


class ContractType extends AbstractType
{
    private $house_id;
    /**
     *
     * @var Doctrine\ORM\EntityManager 
     */
    private $em;
    
    public function __construct($em = NULL, $house_id = NULL )
    {
        $this->house_id = $house_id;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if($this->house_id)
        $qb = $this->em->getRepository('HOfficeAdminBundle:House\Apartment')
                                    ->createQueryBuilder('a')
                                    ->where("a.house = :house_id")
                                    ->setParameter("house_id", $this->house_id)
                                    ->orderBy('a.name', 'ASC');      
        
        $builder
//            ->add('parent_id')
            ->add('kod')
            ->add('serial_number')
            ->add('registered')
//            ->add('user', null, array('required'=>NULL))
//            ->add('apartment', null, array('required'=>NULL))
            ->add('sale')
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
                            )));
/*            ->add('house_id', 'hidden',
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
*/          if(false){
               $builder->add('apartment', 'entity', array(
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
                                    'class' => 'search_apartment_id' )
                                )) ;
            }
                
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
