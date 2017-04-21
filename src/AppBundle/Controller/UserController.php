<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

            $user->setRole($user->getDefaultRole());

            /** @var UploadedFile $file */
            $file = $user->getImageForm();

            $filename = md5($user->getName());

            $file->move(
                $this->get('kernel')->getRootDir() . '/../web/images/users/',
                $filename
            );

            $user->setImage($filename);

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
        return $this->render('user/profile.html.twig');
    }

    /**
     * @Route("/user/edit/{id}", name="edit_profile")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editProfileAction($id, Request $request)
    {
        return $this->render('user/edit.profile.html.twig');
    }

    /**
     * @Route("/users/show", name="users_show")
     */
    public function listUsersAction()
    {
        $users = $this
                     ->getDoctrine()
                     ->getRepository('AppBundle:User')
                     ->findAll();

        return $this->render('user/users.show.html.twig', array(
            'users' => $users
        ));
    }
}