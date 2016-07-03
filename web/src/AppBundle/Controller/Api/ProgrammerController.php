<?php
/**
 * Created by PhpStorm.
 * User: dhiraj
 * Date: 26/6/16
 * Time: 7:06 PM
 */
namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgrammerController extends Controller
{
    /**
     * @Route("/api/programmers")
     * @Method("POST")
     * @return Response
     */
    public function newAction(Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);



        return new Response($body);
    }


}