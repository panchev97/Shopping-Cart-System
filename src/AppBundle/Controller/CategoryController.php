<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends Controller
{

    /**
     * @Route("/category/add", name="add_category")
     * @Security("has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCategoryAction(Request $request)
    {

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            $this->redirectToRoute('products');
        }

        return $this->render('category/category.view.html.twig', array(
            'categoryForm' => $form->createView()
        ));
    }

    /**
     * @Route("/categories/show", name="list_categories")
     * @Security("has_role('ROLE_USER') or has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     */
    public function listCategoriesAction()
    {
        $categories = $this
                          ->getDoctrine()
                          ->getRepository('AppBundle:Category')
                          ->findAll();

        return $this->render('category/show.categories.view.html.twig', array(
            'categories' => $categories
        ));
    }

    /**
     * @Route("/categories/{id}", name="each_category_products")
     * @Security("has_role('ROLE_USER') or has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     */
    public function listEachCategoryProducts($id)
    {
        $category = $this
                        ->getDoctrine()
                        ->getRepository('AppBundle:Category')
                        ->find($id);

        $products = $category->getProducts();

        return $this->render('category/show.category.products.html.twig', array(
            'products' => $products
        ));
    }


    /**
     * @Route("categories/show/{id}")
     * @param $id
     * @Security("has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCategoryAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em
                       ->getRepository('AppBundle:Category')
                       ->find($id);

        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('list_categories');
    }


}
