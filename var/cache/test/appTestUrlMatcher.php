<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appTestUrlMatcher.
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appTestUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
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

        if (0 === strpos($pathinfo, '/_')) {
            // _wdt
            if (0 === strpos($pathinfo, '/_wdt') && preg_match('#^/_wdt/(?P<token>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_wdt')), array (  '_controller' => 'web_profiler.controller.profiler:toolbarAction',));
            }

            if (0 === strpos($pathinfo, '/_profiler')) {
                // _profiler_home
                if (rtrim($pathinfo, '/') === '/_profiler') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', '_profiler_home');
                    }

                    return array (  '_controller' => 'web_profiler.controller.profiler:homeAction',  '_route' => '_profiler_home',);
                }

                if (0 === strpos($pathinfo, '/_profiler/search')) {
                    // _profiler_search
                    if ($pathinfo === '/_profiler/search') {
                        return array (  '_controller' => 'web_profiler.controller.profiler:searchAction',  '_route' => '_profiler_search',);
                    }

                    // _profiler_search_bar
                    if ($pathinfo === '/_profiler/search_bar') {
                        return array (  '_controller' => 'web_profiler.controller.profiler:searchBarAction',  '_route' => '_profiler_search_bar',);
                    }

                }

                // _profiler_info
                if (0 === strpos($pathinfo, '/_profiler/info') && preg_match('#^/_profiler/info/(?P<about>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_info')), array (  '_controller' => 'web_profiler.controller.profiler:infoAction',));
                }

                // _profiler_phpinfo
                if ($pathinfo === '/_profiler/phpinfo') {
                    return array (  '_controller' => 'web_profiler.controller.profiler:phpinfoAction',  '_route' => '_profiler_phpinfo',);
                }

                // _profiler_search_results
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/search/results$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_search_results')), array (  '_controller' => 'web_profiler.controller.profiler:searchResultsAction',));
                }

                // _profiler
                if (preg_match('#^/_profiler/(?P<token>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler')), array (  '_controller' => 'web_profiler.controller.profiler:panelAction',));
                }

                // _profiler_router
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/router$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_router')), array (  '_controller' => 'web_profiler.controller.router:panelAction',));
                }

                // _profiler_exception
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/exception$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_exception')), array (  '_controller' => 'web_profiler.controller.exception:showAction',));
                }

                // _profiler_exception_css
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/exception\\.css$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_exception_css')), array (  '_controller' => 'web_profiler.controller.exception:cssAction',));
                }

            }

            // _twig_error_test
            if (0 === strpos($pathinfo, '/_error') && preg_match('#^/_error/(?P<code>\\d+)(?:\\.(?P<_format>[^/]++))?$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_twig_error_test')), array (  '_controller' => 'twig.controller.preview_error:previewErrorPageAction',  '_format' => 'html',));
            }

        }

        if (0 === strpos($pathinfo, '/acme/test')) {
            // create
            if ($pathinfo === '/acme/test/default/create') {
                return array (  '_controller' => 'Acme\\TestBundle\\Controller\\Web\\DefaultController::createAction',  '_route' => 'create',);
            }

            if (0 === strpos($pathinfo, '/acme/test/acme/test/api/products')) {
                // acme_test_api_product_new
                if ($pathinfo === '/acme/test/acme/test/api/products') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_acme_test_api_product_new;
                    }

                    return array (  '_controller' => 'Acme\\TestBundle\\Controller\\Api\\ProductController::newAction',  '_route' => 'acme_test_api_product_new',);
                }
                not_acme_test_api_product_new:

                // show
                if ($pathinfo === '/acme/test/acme/test/api/products/show') {
                    return array (  '_controller' => 'Acme\\TestBundle\\Controller\\Api\\ProductController::showAction',  '_route' => 'show',);
                }

                // update
                if ($pathinfo === '/acme/test/acme/test/api/products/update') {
                    return array (  '_controller' => 'Acme\\TestBundle\\Controller\\Api\\ProductController::updateAction',  '_route' => 'update',);
                }

            }

            if (0 === strpos($pathinfo, '/acme/test/default')) {
                // AcmeTestBundle
                if ($pathinfo === '/acme/test/default/index') {
                    return array (  '_controller' => 'Acme\\TestBundle\\Controller\\Web\\DefaultController::indexAction',  '_route' => 'AcmeTestBundle',);
                }

                // AcmeTestBundle_create
                if (rtrim($pathinfo, '/') === '/acme/test/default/create') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'AcmeTestBundle_create');
                    }

                    return array (  '_controller' => 'Acme\\TestBundle\\Controller\\Web\\DefaultController::createAction',  '_route' => 'AcmeTestBundle_create',);
                }

                // AcmeTestBundle_show
                if (0 === strpos($pathinfo, '/acme/test/default/show') && preg_match('#^/acme/test/default/show/(?P<productId>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'AcmeTestBundle_show')), array (  '_controller' => 'Acme\\TestBundle\\Controller\\Web\\DefaultController::showAction',));
                }

            }

            // AcmeTestBundle_update
            if (0 === strpos($pathinfo, '/acme/test/update') && preg_match('#^/acme/test/update/(?P<productId>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => 'AcmeTestBundle_update')), array (  '_controller' => 'Acme\\TestBundle\\Controller\\Web\\DefaultController::updateAction',));
            }

            if (0 === strpos($pathinfo, '/acme/test/api/products')) {
                // AcmeTestBundle_api_new
                if ($pathinfo === '/acme/test/api/products') {
                    return array (  '_controller' => 'Acme\\TestBundle\\Controller\\Api\\ProductController::newAction',  '_route' => 'AcmeTestBundle_api_new',);
                }

                // AcmeTestBundle_api_show
                if ($pathinfo === '/acme/test/api/products/show') {
                    return array (  '_controller' => 'Acme\\TestBundle\\Controller\\Api\\ProductController::showAction',  '_route' => 'AcmeTestBundle_api_show',);
                }

            }

        }

        // homepage
        if ($pathinfo === '/default/index') {
            return array (  '_controller' => 'AppBundle\\Controller\\Web\\DefaultController::indexAction',  '_route' => 'homepage',);
        }

        // lucky_number
        if ($pathinfo === '/lucky/number') {
            return array (  'count' => false,  '_controller' => 'AppBundle\\Controller\\Web\\LuckyController::numberAction',  '_route' => 'lucky_number',);
        }

        if (0 === strpos($pathinfo, '/a')) {
            if (0 === strpos($pathinfo, '/api')) {
                // lucky_number_api
                if ($pathinfo === '/api/lucky/number') {
                    return array (  '_controller' => 'AppBundle\\Controller\\Web\\LuckyController::apiNumberAction',  '_route' => 'lucky_number_api',);
                }

                // app_api_programmer_new
                if ($pathinfo === '/api/programmers') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_app_api_programmer_new;
                    }

                    return array (  '_controller' => 'AppBundle\\Controller\\Api\\ProgrammerController::newAction',  '_route' => 'app_api_programmer_new',);
                }
                not_app_api_programmer_new:

            }

            if (0 === strpos($pathinfo, '/admin')) {
                // sonata_admin_redirect
                if (rtrim($pathinfo, '/') === '/admin') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'sonata_admin_redirect');
                    }

                    return array (  '_controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::redirectAction',  'route' => 'sonata_admin_dashboard',  'permanent' => 'true',  '_route' => 'sonata_admin_redirect',);
                }

                // sonata_admin_dashboard
                if ($pathinfo === '/admin/dashboard') {
                    return array (  '_controller' => 'Sonata\\AdminBundle\\Controller\\CoreController::dashboardAction',  '_route' => 'sonata_admin_dashboard',);
                }

                if (0 === strpos($pathinfo, '/admin/core')) {
                    // sonata_admin_retrieve_form_element
                    if ($pathinfo === '/admin/core/get-form-field-element') {
                        return array (  '_controller' => 'sonata.admin.controller.admin:retrieveFormFieldElementAction',  '_route' => 'sonata_admin_retrieve_form_element',);
                    }

                    // sonata_admin_append_form_element
                    if ($pathinfo === '/admin/core/append-form-field-element') {
                        return array (  '_controller' => 'sonata.admin.controller.admin:appendFormFieldElementAction',  '_route' => 'sonata_admin_append_form_element',);
                    }

                    // sonata_admin_short_object_information
                    if (0 === strpos($pathinfo, '/admin/core/get-short-object-description') && preg_match('#^/admin/core/get\\-short\\-object\\-description(?:\\.(?P<_format>html|json))?$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'sonata_admin_short_object_information')), array (  '_controller' => 'sonata.admin.controller.admin:getShortObjectDescriptionAction',  '_format' => 'html',));
                    }

                    // sonata_admin_set_object_field_value
                    if ($pathinfo === '/admin/core/set-object-field-value') {
                        return array (  '_controller' => 'sonata.admin.controller.admin:setObjectFieldValueAction',  '_route' => 'sonata_admin_set_object_field_value',);
                    }

                }

                // sonata_admin_search
                if ($pathinfo === '/admin/search') {
                    return array (  '_controller' => 'Sonata\\AdminBundle\\Controller\\CoreController::searchAction',  '_route' => 'sonata_admin_search',);
                }

                // sonata_admin_retrieve_autocomplete_items
                if ($pathinfo === '/admin/core/get-autocomplete-items') {
                    return array (  '_controller' => 'sonata.admin.controller.admin:retrieveAutocompleteItemsAction',  '_route' => 'sonata_admin_retrieve_autocomplete_items',);
                }

            }

        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
