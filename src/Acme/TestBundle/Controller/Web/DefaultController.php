<?php

namespace Acme\TestBundle\Controller\Web;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Acme\TestBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{
    /**
     * @Route("/default/index", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('AcmeTestBundle:Web\Default:index.html.twig');
    }
    
    /**
     * @Route("/default/create", name="create")
     */
    public function createAction()
    { 
        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(19.99);
        $product->setDescription('Ergonomic and stylish!');
    
        $em = $this->getDoctrine()->getManager();
    
        // tells Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($product);
    
        // actually executes the queries (i.e. the INSERT query)
        $em->flush();
    
        return new Response('Saved new product with id '.$product->getId());
    }
    
    /**
     * @Route("/acme/test/show/{$productId}", name="show")
     */
    public function showAction($productId)
    {
        $product = $this->getDoctrine()
            ->getRepository('AcmeTestBundle:Product')
            ->find($productId);
    
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$productId
            );
        }
        
        return new Response('Product details '.$product->getId() . ' ' . $product->getName() .'  '.$product->getDescription());
        // ... do something, like pass the $product object into a template
    }
    
    /**
     * @Route("/acme/test/update/{$productId}", name="update")
     */
    public function updateAction($productId)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AcmeTestBundle:Product')->find($productId);
    
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$productId
            );
        }
    
        $product->setName('New product name!');
        $em->flush();
    
        return $this->redirectToRoute('homepage');
    }
}
