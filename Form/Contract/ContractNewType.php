<?php

namespace HOffice\AdminBundle\Form\Contract;
use HOffice\AdminBundle\Form\Contract\ContractType;
/**
 * Description of ContractNewType
 *
 * @author root
 */
class ContractNewType extends ContractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('user_id', 'hidden',
                    array('attr' => array(
                          'class' => 'search_user_id' )))
                    ->add('apartment_id', 'choice', array(
                                'required' => NULL,
    //                            'choices' => array($this->choice => ""),
                                'attr' => array(
                                    'class' => 'search_apartment_id' )
                    ));
    }

}