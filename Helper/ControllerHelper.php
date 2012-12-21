<?php

namespace Main\SiteBundle\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Itc\AdminBundle\Tools\LanguageHelper;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ControllerHelper
 *
 * @author root
 */
class ControllerHelper extends Controller{

/************************ Вспомогательные методы ******************************/
    /**
     * Поиск сущности по роутингу
     * @param string $entityName - сущьность с транслитом описана в массиве
     * пример $this->menu;
     * @param string $translit - транслит для поиска
     * @return результат запроса
     */
    protected function getEntityRouting( $entityName, $routing, 
                                            array $wheres = NULL, 
                                            array $parameters = NULL,
                                            array $orderby = NULL ){
            $wheres[] = "M.routing = :routing";
            $parameters['routing'] = $routing;

        return $this->getEntities( $entityName, $wheres, $parameters, $orderby );
    }
    /**
     * Поиск сущности по транслиту
     * @param string $entities - сущьность с транслитом описана в массиве
     * пример $this->menu;
     * @param string $translit - транслит для поиска
     * @return результат запроса
     */
    protected function getEntityTranslit( $entityName, $translit, 
                                            array $wheres = NULL, 
                                            array $parameters = NULL,
                                            array $orderby = NULL ){

        if( LanguageHelper::getLocale() == LanguageHelper::getDefaultLocale() ){

            $wheres[] = "M.translit = :translit";
            $parameters['translit'] = $translit;

        } else {

            $wheres[] = "T.value    = :translit";
            $wheres[] = "T.property = :property";
            
            $parameters['translit'] = $translit;
            $parameters['property'] = "translit";
        }

        return $this->getEntities( $entityName, $wheres, $parameters, $orderby );
    }
    /**
     * Вытягивет сущьность по критериям
     * 
     * !!! Переводимые поля должны быть T.
     * !!! Непереводимые M.
     * 
     * Можно прописать или вытягивать переводимые/непереводимые поля в массив, 
     * но это потом...
     * 
     * @param type $entities - сущьность с транслитом описана в массиве
     * пример $this->menu;
     * 
     * @param array $wheres - массив с поиском [] = "M.locale = :locale" без AND;
     * $qb->where( implode( ' AND ', $wheres ) );
     * 
     * @param array $parameters - парметры поиска, обязательное условие
     * array( ['locale'] => $locale, ... )
     * 
     * @return $qb->getQuery();
     */
    protected function getEntities( $entityName, array $wheres     = NULL, 
                                                 array $parameters = NULL, 
                                                 array $orderby    = NULL ){

        if( is_array( $entityName ) )
            list( $entity, $translation ) = $entityName;
        else 
            $entity = $entityName;

        $em            = $this->getDoctrine()->getManager();
        $locale        = LanguageHelper::getLocale();

        if( $locale == LanguageHelper::getDefaultLocale() ){

            foreach( $wheres as $v ){
                $w[] = str_replace( "T.", "M.", $v );
            }

            $wheres = $w;

            $qb = $em->getRepository( $entity )
                     ->createQueryBuilder( 'M' )
                     ->select( 'M' );

        } else {

            $wheres[] = "T.locale = :locale";
            $parameters['locale'] = $locale;

            $qb = $em->getRepository( $entity )
                     ->createQueryBuilder( 'M' )
                     ->select( 'M' )
                     ->join( "M.translations", 'T' );
        }

        if( $wheres !== NULL ){

            $qb->where( implode( ' AND ', $wheres ) );
            $qb->setParameters( $parameters );

        }

        if( $orderby !== NULL ){

            list( $sort, $order ) = $orderby;
            $qb->orderBy( $sort, $order );
        }

        return $qb->getQuery();

    }

    protected function getLocale()
    {
        $locale = $this->getRequest()->getLocale();
        return $locale;
    }
     /**
     * есть в ITC
     * @return type
     */
    protected function getRoutes()
    {
        $router = $this->container->get( 'router' );
        
        $routes = array();

        foreach ( $router->getRouteCollection()->all() as $name => $route ){
           $routes[] = $name;
          
        }
        return $routes;
    }

    protected function getController( $name ){

        return $this->container->get( 'router' )
                    ->getRouteCollection()
                    ->get( $name )
                    ->getDefault("_controller");
    }
    
        
    private $param = array();


    /**
     * сброс набора параметров
     */
    protected function resetParam(){
        
        $this->param = array();
        
    }
    
    /**
     * 
     * @param collection $entities
     * @return deleteform collection
     */
    protected function getDeleteForm( $entities )
    {

        $deleteForm = array();
        
        foreach ( $entities as $entity ){

            $id = $entity->getId();
            $deleteForm[$id] = $this->createDeleteForm( $id )
                                    ->createView();

        }

        return $deleteForm;

    }

    /**
     * @param integer $id
     * @param string $entityName
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     * @throws type
     */
    protected function deleteEntityById( $id, $entityName, Request $request ){

        $form = $this->createDeleteForm( $id );
        $form->bind( $request );

        if ( $form->isValid() ){

             $em = $this->getDoctrine()->getManager();
              $entity = $em->getRepository( $entityName )->find( $id );

             if ( ! $entity ) {
                 throw $this->createNotFoundException('Unable to find Menu entity.');
             }

             $em->remove( $entity );
             $em->flush();

        } else {
             print_r( $form->getErrors() );
        }

        return array(

            'entity' => $entity,

        );

    }

    /**
     * @param int $id
     * @return object form
     */
    private function createDeleteForm( $id ){

        return $this->createFormBuilder( array( 'id' => $id ) )
            ->add('id', 'hidden')
            ->getForm()
        ;
    }

    /**
     * 
     * (down)Есть минус $this->param надо очищать! $this->resetParam(){}
     * Функция набирает qb where и parameters, с какими-то более мение 
     * универсальными характеристиками
     * 
     * @param array   $parameters = array( field => value, ... )
     * @param string  $entityName = название сущьности
     * @param object  $qb         = query builder
     * 
     * Надстройки
     * @param string  $w          = соединитель для where
     * @param string  $r          = знак
     * @param string  $alias      = алиас для таблицы
     * @param boolean $reset      = сброс $this->param (up)
     * @return object $qb         = query builder
     */
    protected function searchHelper( array $parameters, $entityName, $qb,
                                     $values, $reset = false, &$param ){
        
        list( $w, $r, $alias ) = $values;
        if( $reset ) $this->resetParam ();
        
        if( empty ( $parameters ) ) return $qb;
        
        foreach( $parameters as $kparam => $parameter ){

            if( isset( $parameter ) ){

                $kv = $kparam;
                $j = 0;

                while( isset( $this->param[$kv] ) ) $kv = $kparam."_".$j++;

                $this->param[$kv] = $parameter;

                $wheres[] = "{$alias}.{$kparam} {$r} :{$kv}";

            } else {

                $wheres[] = "{$alias}.{$kparam} IS NULL";

            }

        }

        $where = implode( " {$w} ", $wheres );

        if( empty( $qb ) ){

            $em = $this->getDoctrine()->getManager();        
            $repo = $em->getRepository( $entityName );

            $qb = $repo->createQueryBuilder( $alias )
                       ->select( $alias );
            $qb->where( $where );

        } else {

            $ww = $w."Where";
            $qb->$ww( $where );

        }

        $qb->setParameters( $this->param );
        
        return $qb;

    }
    
    /**
     * Поиск по дате, упрощение
     * 
     * @param string $enName
     * @param array $data
     * @param object $qb - queryBuilder
     * @return null
     */
    protected function getDateQb( $enName, array $data, $qb = array() ){

        if( empty( $data ) ) return NULL;
        
        $d = array( 
                "from" => ">=", 
                "to"   => "<=" 
        );

        foreach( $d as $ft => $z ){

            if( isset( $data[$ft] ) && ! is_null( $data[$ft] ) ) {

                $values = array("AND", $z, "P");
                $param['date'] = $data[$ft];
                $qb = $this->searchHelper( $param, $enName, $qb, $values );
                unset( $param );

            }

        }
        
        return $qb;

    }
}

?>
