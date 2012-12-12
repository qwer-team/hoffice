<?php
namespace HOffice\AdminBundle\Entity\House;
use Doctrine\ORM\EntityRepository;
use HOffice\AdminBundle\Entity\House\House;


class HouseRepository extends EntityRepository
{
    public function getAll()
    {
        $entities = $this->_em->getRepository('HOfficeAdminBundle:House\House')
                         ->findAll();  
        if (!$entities) {
            throw $this->createNotFoundException('Unable to find House\House entity.');
        }
        return $entities;
    }
}