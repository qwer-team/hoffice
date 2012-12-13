<?php
namespace HOffice\AdminBundle\Entity\House;
use Doctrine\ORM\EntityRepository;
use HOffice\AdminBundle\Entity\House\Apartment;
use HOffice\AdminBundle\Entity\House\House;

class ApartmentRepository extends EntityRepository
{
    public function selApart($houseId, $coulonpage)
    {
        $qb = $this->_em->createQueryBuilder()
                        ->select("M")
                        ->from("HOfficeAdminBundle:House\Apartment", "M");
        if($houseId != null)
        {
            $qb->where('M.houseId = :houseId')
               ->setParameter('houseId', $houseId);           
        }
        $qb->orderBy('M.kod');
        return $qb;
    }
//    
//    public function checkFor($houseId){
//        $qb = $this->_em->createQueryBuilder()
//                        ->select("M")
//                        ->from("HOfficeAdminBundle:House\Apartment", "M")
//                        ->where('M.houseId = :houseId')
//                        ->setParameter('houseId', $houseId); 
//        
//        return  $qb;           
//    }
}