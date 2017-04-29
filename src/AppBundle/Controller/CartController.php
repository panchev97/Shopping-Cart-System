<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\CartProduct;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class CartController extends Controller
{

    /**
     * @Security("has_role('ROLE_USER') or has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     * @Route("/cart/add", name="cart_add")
     */
    public function addToCartAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $id_cart = $session->get('id_cart', false);
        $currentUserId = $this->get('security.token_storage')->getToken()->getUser();

        if (!$id_cart) {
           $cart = new Cart();
           $cart->setUserId($currentUserId);
           $cart->setDateCreated(new \DateTime());
           $cart->setDateUpdated(new \DateTime());

           $manager->persist($cart);
           $manager->flush();

           $session->set('id_cart', $cart->getId());
       }

       $cart = $this->getDoctrine()->getRepository('AppBundle:Cart')->find($session->get('id_cart', false));
       $products = $request->get('products', []);

        foreach ($products as $id_product) {
            $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id_product);

            if ($product) {
                $cartProduct = $this
                                   ->getDoctrine()
                                   ->getRepository('AppBundle:CartProduct')
                                   ->findOneBy([
                                        'cart' => $cart,
                                       'product' => $product
                                       ]);

                if (!$cartProduct) {
                    $cartProduct = new CartProduct();
                    $cartProduct->setCart($cart);
                    $cartProduct->setProduct($product);
                    $cartProduct->setQuantity(1);
                } else {
                    $cartProduct->setQuantity($cartProduct->getQuantity() + 1);
                }

                $manager->persist($cartProduct);
            }
       }
        $cart->setDateUpdated(new \DateTime());
        $manager->persist($cart);
        $manager->flush();

        return $this->redirectToRoute('cart_list');
    }
    /**
     * @Route("products/remove")
     * @Security("has_role('ROLE_USER') or has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     */
    public function ClearCartAction()
    {
        $session = $this->get('session');
        $session->clear();
        return $this->redirectToRoute('cart_list');
    }

    /**
     * @Route("/cart/show/", name="cart_list")
     * @Security("has_role('ROLE_USER') or has_role('ROLE_EDITOR') or has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listCartProductsAction()
    {
        $session = $this->get('session');
        $cart = $this->getDoctrine()->getRepository('AppBundle:Cart')->find($session->get('id_cart', false));

        if (!$cart) {
            return $this->render('cart/empty.cart.view.html.twig');
        }
       return $this->render('cart/cart.view.html.twig', array(
           'cart' => $cart
       ));
    }
}
