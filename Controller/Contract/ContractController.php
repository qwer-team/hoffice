<?php

namespace HOffice\AdminBundle\Controller\Contract;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use HOffice\AdminBundle\Entity\Contract\Contract;
use HOffice\AdminBundle\Form\Contract\ContractType;
use HOffice\AdminBundle\Form\Contract\ContractCreateType;
use HOffice\AdminBundle\Form\Contract\ContractNewType;
use HOffice\AdminBundle\Form\Contract\SearchContractType;
use Itc\AdminBundle\Tools\TranslitGenerator;
use Symfony\Component\Locale\Locale;
use Itc\AdminBundle\Tools\LanguageHelper;



/**
 * Contract\Contract controller.
 *
 * @Route("/contract")
 */
class ContractController extends Controller
{
    /**
     * Lists all Contract\Contract entities.
     *
     * @Route("/{coulonpage}/{page}/{parent_id}", name="contract",
     * requirements={"parent_id" = "\d+", "coulonpage" = "\d+","page" = "\d+"}, 
     * defaults={ "parent_id" = null, "coulonpage" = "100", "page"=1})
     * @Template()
     */
   
    public function indexAction($parent_id = null, $coulonpage = 100, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $locale =  LanguageHelper::getLocale();
        
        $repo = $em->getRepository('HOfficeAdminBundle:Contract\Contract');
        
        $qb = $repo->createQueryBuilder('C')
                        ->select('C, U, A')
                        ->innerJoin('C.user', 'U')
                        ->innerJoin('C.apartment', 'A')
                        ->orderBy('C.kod', 'ASC');    
     
        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $qb,
            $this->get('request')->query->get('page', 1)/*page number*/,
            $coulonpage/*limit per page*/
        );

        $search_form = $this->createForm(new SearchContractType($em, $locale));

        return array(
            'entities'  => $entities,
            'locale'    => $locale,
            'parent_id' => $parent_id,
            'coulonpage' => $coulonpage,
            'search_form' => $search_form->createView(),
        );
    }
    /**
     * @Route("/{coulonpage}/search", name="contract_search",
     * requirements={"coulonpage" = "\d+"}, 
     * defaults={"coulonpage" = "100"})
     * @Template("HOfficeAdminBundle:Contract\Contract:index.html.twig")
     */
    public function searchAction(Request $request, $coulonpage = 100)
    {
        $locale =  LanguageHelper::getLocale();
        $em = $this->getDoctrine()->getManager();        
        
        $postData = $request->request->get('itc_documentsbundle_searchcontracttype');
        $house_id = $postData['house_id'];
        
        $search_form = $this->createForm(new SearchContractType($em, $locale, $house_id));
        $search_form->bind($request);
        $deleteForm = array();   
        $visibleForm = array();
        $changeKodForm = array();        
        $data = $search_form->getData();
        
        $repo = $em->getRepository('HOfficeAdminBundle:Contract\Contract');
        
        $qb = $repo->createQueryBuilder('M')
                        ->select('M')
                        ->innerJoin('M.user', 'U');

        if(null !== $data["house_id"])
        {
            $qb->innerJoin('M.apartment', 'F', 
                    'WITH', 'F.house = :house')
               ->setParameter('house', $data["house_id"]);
        }
        
        if(null !== $data["text"])
        {
            $qb->orWhere('M.id = :id')
                ->setParameter('id', $data["text"])
                ->orWhere('M.title LIKE :title')
                ->setParameter('title', "%".$data["text"]."%")
                ;
        }
        if(null !== $data["user_id"])
        {
            $qb->andWhere('M.user = :user')
               ->setParameter('user', $data["user_id"]);
        }
        if(isset($data["apartment_id"]))
        {
            $qb->andWhere('M.apartment = :apartment')
               ->setParameter('apartment', $data["apartment_id"]);
        }

        $paginator = $this->get('knp_paginator');
        
        $entities = $paginator->paginate(
            $qb,
            $this->get('request')->query->get('page', 1),
            100
        );
        foreach ($entities as $entity){
            
            $deleteForm[$entity->getId()] = $this->createDeleteForm($entity->getId())
                            ->createView();
/*            $visibleForm[$entity->getId()] = $this->createVisibleForm($entity)
                            ->createView();
           $changeKodForm[$entity->getId()] = 
                    $this->createChangeKodForm($entity->getKod(), $coulonpage, 1)
                            ->createView();
  */      }
  
        return array(
            'entities'  => $entities,
            'locale'    => $locale,
            'parent_id' => null,
            'chmap'     => array(),
            'search_form' => $search_form->createView(),
            'delete_form' => $deleteForm,
//            'visible_form' => $visibleForm,
            'coulonpage' => $coulonpage,
//            'change_kod_form' => $changeKodForm,
        );
    }

    /**
     * Finds and displays a Contract\Contract entity.
     *
     * @Route("/{id}/show", name="contract_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Contract\Contract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contract\Contract entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Contract\Contract entity.
     *
     * @Route("/new/{parent_id}", name="contract_new",
     * requirements={"parent_id" = "\d+"}, defaults={ "parent_id" = null})
     * @Template()
     */
    public function newAction($parent_id)
    {
        $em = $this->getDoctrine()->getManager();                
        $languages  = LanguageHelper::getLanguages();
        $locale =  LanguageHelper::getLocale();
        $entity = new Contract();
        
        $entity->setKod($this->getKodeForContract($parent_id));
        $form   = $this->createForm(new ContractNewType($em), $entity
                    );

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'locale' => $locale,
            'languages' => $languages,
        );
    }

    /**
     * Creates a new Contract\Contract entity.
     *
     * @Route("/create", name="contract_create")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Contract\Contract:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Contract();
        $em = $this->getDoctrine()->getManager();                        
        $languages  = LanguageHelper::getLanguages();
        $locale =  LanguageHelper::getLocale();
        $postData = $request->request->get('itc_documentsbundle_contract_contracttype');
        //print_r($postData);echo "<br />";
        $apartment = $em->getRepository('HOfficeAdminBundle:House\Apartment')
                      ->find($postData['apartment_id']);
        $form = $this->createForm(new ContractCreateType($em, $apartment), 
                                    $entity );
        $user = $em->getRepository('ItcAdminBundle:User')
                      ->find($postData['user_id']);
        $form->bind($request);
        $data = $form->getData();
        //print_r($data);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $entity->setUser($user);
  
            $entity->setApartment($apartment);
            
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('contract_edit', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'locale' => $locale,
            'languages' => $languages,
            'user'      => $user,
        );
    }

    /**
     * Displays a form to edit an existing Contract\Contract entity.
     *
     * @Route("/{id}/edit", name="contract_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $languages  = LanguageHelper::getLanguages();
        $locale =  LanguageHelper::getLocale();

        $entity = $em->getRepository('HOfficeAdminBundle:Contract\Contract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contract\Contract entity.');
        }

        $editForm = $this->createForm(new ContractType($em), $entity );
        $deleteForm = $this->createDeleteForm($id);

        $user = $entity->getUser();
        $apartment = $entity->getApartment();

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'locale' => $locale,
            'languages' => $languages,
            'user' => $user,
            'apartment' => $apartment
        );
    }

    /**
     * Edits an existing Contract\Contract entity.
     *
     * @Route("/{id}/update", name="contract_update")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Contract\Contract:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $languages  = LanguageHelper::getLanguages();
        $locale =  LanguageHelper::getLocale();

        $entity = $em->getRepository('HOfficeAdminBundle:Contract\Contract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contract\Contract entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ContractType($em), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('contract_edit', array('id' => $id)));
        }
        $user = $entity->getUser();
        $apartment = $entity->getApartment();

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'locale' => $locale,
            'languages' => $languages,
            'user' => $user,
            'apartment' => $apartment
        );
    }

    /**
     * Deletes a Contract\Contract entity.
     *
     * @Route("/{id}/delete", name="contract_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HOfficeAdminBundle:Contract\Contract')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Contract\Contract entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('contract'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    private function getKodeForContract($parent_id)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('HOfficeAdminBundle:Contract\Contract')
                                        ->createQueryBuilder('M')
                                        ->select('max(coalesce(M.kod,0)) + 1 kod');
        
        $kod = $queryBuilder->getQuery()->getSingleScalarResult();
        return is_null($kod) ? 1 : $kod ;
    }
    
}
