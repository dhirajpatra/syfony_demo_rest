<?php
// src/AppBundle/Controller/LuckyController.php
namespace AppBundle\Controller\Web;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LuckyController extends Controller
{
    /**
     * @Route("/lucky/number", name="lucky_number")
     */
    public function numberAction($count = FALSE)
    {
        $count = (!isset($count) || $count == '')?rand(5, 10):$count;

        $numbers = array();
        for ($i = 0; $i < $count; $i++) {
            $numbers[] = rand(0, 100);
        }
        $numbersList = implode(', ', $numbers);

        $html = $this->container->get('templating')->render(
            'lucky/number.html.twig',
            array('luckyNumberList' => $numbersList)
        );

        return new Response($html);
    }
    
    /**
     * @Route("/api/lucky/number", name="lucky_number_api")
     */
    public function apiNumberAction()
    {
        $data = array(
            'lucky_number' => rand(0, 100),
        );
    
        return new Response(
            json_encode($data),
            200,
            array('Content-Type' => 'application/json')
        );
    }
}