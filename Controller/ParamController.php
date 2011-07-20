<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Param;
use NineThousand\Bundle\NineThousandJobqueueBundle\Form\ParamType;

/**
 * Param controller.
 *
 */
class ParamController extends Controller
{
    /**
     * Lists all Param entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('NineThousandJobqueueBundle:Param')->findAll();

        return $this->render('NineThousandJobqueueBundle:Param:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Param entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('NineThousandJobqueueBundle:Param')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Param entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NineThousandJobqueueBundle:Param:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Param entity.
     *
     */
    public function newAction()
    {
        $entity = new Param();
        $form   = $this->createForm(new ParamType(), $entity);

        return $this->render('NineThousandJobqueueBundle:Param:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Param entity.
     *
     */
    public function createAction()
    {
        $entity  = new Param();
        $request = $this->getRequest();
        $form    = $this->createForm(new ParamType(), $entity);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('jobqueue_param_show', array('id' => $entity->getId())));
                
            }
        }

        return $this->render('NineThousandJobqueueBundle:Param:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Param entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('NineThousandJobqueueBundle:Param')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Param entity.');
        }

        $editForm = $this->createForm(new ParamType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NineThousandJobqueueBundle:Param:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Param entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('NineThousandJobqueueBundle:Param')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Param entity.');
        }

        $editForm   = $this->createForm(new ParamType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('jobqueue_param_edit', array('id' => $id)));
            }
        }

        return $this->render('NineThousandJobqueueBundle:Param:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Param entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity = $em->getRepository('NineThousandJobqueueBundle:Param')->find($id);

                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find Param entity.');
                }

                $em->remove($entity);
                $em->flush();
            }
        }

        return $this->redirect($this->generateUrl('jobqueue_param'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
