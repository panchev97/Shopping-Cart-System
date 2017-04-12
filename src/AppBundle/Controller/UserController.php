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

           return $this->redirectToRoute('products');
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
    public function editProfileAction($id, Request $request)
    {

        $user = $this->getDoctrine()
                     ->getRepository('AppBundle:User')
                     ->find($id);

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Get Data..
            $username = $form['username']->getData();
            $name = $form['name']->getData();

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('AppBundle:User')
                        ->find($id);

            $password = $this->get('security.password_encoder')
                             ->encodePassword($user, $user->getPassword());

            $user->setUsername($username);
            $user->setName($name);
            $user->setPassword($password);

            $em->flush();

            return $this->redirectToRoute('products');
        }

        return $this->render('user/profile.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
