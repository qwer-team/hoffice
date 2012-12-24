<?php

namespace HOffice\AdminBundle\Entity\Reversal;

use Doctrine\ORM\EntityRepository;

/**
 * ReversalRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReversalRepository extends EntityRepository
{
    
    function leftPdlines(){

        $entities = $this->_em
                         ->getRepository('HOfficeAdminBundle:Reversal\Reversal')
                         ->createQueryBuilder( 'R' )
                         ->select( "R, P")
                         ->Leftjoin( "R.pdlines", "P")
                         ->getQuery()
                         ->execute();
        return $entities;

    }
}
