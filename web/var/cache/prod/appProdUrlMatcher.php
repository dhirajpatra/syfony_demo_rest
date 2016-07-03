<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appProdUrlMatcher.
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appProdUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);
        $context = $this->context;
        $request = $this->request;

        // homepage
        if (rtrim($pathinfo, '/') === '') {
            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', 'homepage');
            }

            return array (  '_controller' => 'AppBundle\\Controller\\DefaultController::indexAction',  '_route' => 'homepage',);
        }

        // app_lucky_number
        if ($pathinfo === '/lucky/number') {
            return array (  'count' => false,  '_controller' => 'AppBundle\\Controller\\LuckyController::numberAction',  '_route' => 'app_lucky_number',);
        }

        // app_lucky_apinumber
        if ($pathinfo === '/api/lucky/number') {
            return array (  '_controller' => 'AppBundle\\Controller\\LuckyController::apiNumberAction',  '_route' => 'app_lucky_apinumber',);
        }

        // lucky_number
        if (0 === strpos($pathinfo, '/lucky/number') && preg_match('#^/lucky/number/(?P<count>[^/]++)$#s', $pathinfo, $matches)) {
            return $this->mergeDefaults(array_replace($matches, array('_route' => 'lucky_number')), array (  '_controller' => 'AppBundle\\Controller\\LuckyController::numberAction',));
        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
