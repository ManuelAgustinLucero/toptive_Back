<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Traits\FormErrorValidator;

/**
 * Post controller.
 *
 * @Route("posts")
 */
class PostController extends Controller
{
    use FormErrorValidator;

    /**
     * Lists all post entities.
     *
     * @Route("/", name="post_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle:Post')->findAll();
        $posts = $this->get('jms_serializer')->serialize($posts, 'json', SerializationContext::create()->setGroups(array('post_index')));
        return new Response($posts);
    }

    /**
     * Creates a new post entity.
     *
     * @Route("/create", name="post_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(),true);
        $post = new Post();
        $form = $this->createForm('AppBundle\Form\PostType', $post);
        $form->submit($data);

        if(!$form->isValid()) {
            $errors = $this->getErrors($form);
            $validation = [
                'type' => 'validation',
                'description' => 'data validation',
                'code'=> 400,
                'errors' => $errors
            ];
            return new JsonResponse($validation);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();
        $post = $this->get('jms_serializer')->serialize($post, 'json', SerializationContext::create()->setGroups(array('post_index')));
        return new Response($post);

    }

    /**
     * Finds and displays a post entity.
     *
     * @Route("/{id}", name="post_show")
     * @Method("GET")
     */
    public function showAction(Post $post)
    {
        $post = $this->get('jms_serializer')->serialize($post, 'json', SerializationContext::create()->setGroups(array('post_index')));
        return new Response($post);
    }

    /**
     * Displays a form to edit an existing post entity.
     *
     * @Route("/{id}/edit", name="post_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Post $post)
    {
        $data = json_decode($request->getContent(),true);

        $editForm = $this->createForm('AppBundle\Form\PostType', $post);
        $editForm->handleRequest($request);
        $editForm->submit($data);
        $this->getDoctrine()->getManager()->flush();
        $post = $this->get('jms_serializer')->serialize($post, 'json', SerializationContext::create()->setGroups(array('post_index')));
        return new Response($post);
    }

    /**
     * Deletes a post entity.
     *
     * @Route("/{id}/delete", name="post_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        return new JsonResponse("Deleted",200);
    }


}
