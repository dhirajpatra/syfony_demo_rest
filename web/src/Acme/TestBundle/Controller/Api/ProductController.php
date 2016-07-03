<?php
/**
 * Created by PhpStorm.
 * User: dhiraj
 * Date: 27/6/16
 * Time: 9:36 PM
 */

namespace Acme\TestBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Acme\TestBundle\Entity\Product;

class ProductController extends Controller
{
    /**
     * @Route("/acme/test/api/products")
     * @Method("POST")
     * @return Response
     */
    public function newAction(Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice($data['price']);
        $product->setDescription($data['description']);

        $em = $this->getDoctrine()->getManager();

        // tells Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $em->flush();

        return new Response('Saved new product with id '.$product->getId());
    }

    /**
     * @Route("/acme/test/api/products/show", name="show")
     */
    public function showAction(Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $productId = $data['id'];

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
     * @Route("/acme/test/api/products/update", name="update")
     */
    public function updateAction(Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $productId = $data['id'];

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