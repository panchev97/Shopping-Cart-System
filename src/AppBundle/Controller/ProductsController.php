<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Form\EditProducType;
use AppBundle\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ProductsController extends Controller
{
    /**
     * @Route("/products/show", name="products")
     * @Security("has_role('ROLE_USER') or has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listProductsAction(Request $request)
    {
        $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findAll();


        $paginator = $this->get('knp_paginator');

        $result = $paginator->paginate(
            $products,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 4)
        );

        return $this->render('products/products.html.twig', array(
            'products' => $result,
        ));
    }

    /**
     * @Route("/products/add", name="create_products")
     * @Security("has_role('ROLE_USER') or has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createProductAction(Request $request)
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $file = $product->getImageForm();

                $filename = md5($product->getName());

                $file->move(
                    $this->get('kernel')->getRootDir() . '/../web/images/products/',
                    $filename
                );

                $product->setImage($filename);
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $product->setUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->redirectToRoute('products');

            return $this->redirectToRoute('products');
        }

        return $this->render('products/create.product.html.twig', array(
            'productForm' => $form->createView()
        ));
    }

    /**
     * @Route("products/details/{id}", name="product_details")
     * @param id
     * @Security("has_role('ROLE_USER') or has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailsAction($id)
        {
        $product = $this->getDoctrine()
                        ->getRepository('AppBundle:Product')
                        ->find($id);

        return $this->render('products/details.product.html.twig', array(
            'product' => $product
        ));
    }

    /**
     * @Route("/products/edit/{id}")
     * @param $id
     * @param Request $request
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_EDITOR')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editProductAction($id, Request $request)
    {
        $product = $this->getDoctrine()
                        ->getRepository('AppBundle:Product')
                        ->find($id);

        $product->setName($product->getName());
        $product->setPrice($product->getPrice());
        $product->setDescription($product->getDescription());

        $form = $this->createForm(EditProducType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Get Data..
            $name = $form['name']->getData();
            $price = $form['price']->getData();
            $description = $form['description']->getData();

            /** @var UploadedFile $file */
            $file = $product->getImageForm();

            $filename = md5($product->getName());

            $file->move(
                $this->get('kernel')->getRootDir() . '/../web/images/products/',
                $filename
            );

            $product->setImage($filename);

            $em = $this->getDoctrine()->getManager();
            $product = $em->getRepository('AppBundle:Product')->find($id);

            $product->setName($name);
            $product->setPrice($price);
            $product->setDescription($description);

            $em->flush();

            return $this->redirectToRoute('products');
        }
        return $this->render('products/edit.product.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("products/delete/{id}")
     * @param $id
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_EDITOR')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteProductAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AppBundle:Product')
                      ->find($id);

        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('products');
    }

    /**
     * @Route("/user/products/{id}")
     * @param $id
     * @Security("has_role('ROLE_USER') or has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showEveryUserProductsAction($id)
    {

        $user= $this
                        ->getDoctrine()
                        ->getRepository('AppBundle:User')
                        ->find($id);


        $getUserProducts = $user->getProducts();

        return $this->render('products/every.user.products.html.twig', array(
            'products' => $getUserProducts
        ));
    }

    /**
     * @Route("/my/products/{id}")
     * @Security("has_role('ROLE_USER') or has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCurrentUserProductsAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $currentUserProducts = $user->getProducts();

        return $this->render('products/show.current.user.products.html.twig', array(
            'products' => $currentUserProducts
        ));
    }

}
