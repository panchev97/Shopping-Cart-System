<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());

            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

           return $this->redirectToRoute('our_login');
        }
        return $this->render('user/register.html.twig', array(
            'registerForm' => $form->createView()
        ));
    }

    /**
     * @Route("/user/{id}", name="user_profile")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function myProfileAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $user = $this->get('security.context')->getToken()->getUser();
        $entity = $em->getRepository('AcmeSecurityBundle:User')->find($user->id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $form = $this->createForm(new ProfileType(), $entity);

        $request = $this->getRequest();

        if ($request->getMethod() === 'POST')
        {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('profile'));
            }

            $em->refresh($user); // Add this line
        }

        return $this->render('', array(
            'entity'      => $entity,
            'form'   => $form->createView(),
        ));
    }
}
