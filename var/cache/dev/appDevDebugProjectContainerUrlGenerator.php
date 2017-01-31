<?php

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Psr\Log\LoggerInterface;

/**
 * appDevDebugProjectContainerUrlGenerator
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appDevDebugProjectContainerUrlGenerator extends Symfony\Component\Routing\Generator\UrlGenerator
{
    private static $declaredRoutes;

    /**
     * Constructor.
     */
    public function __construct(RequestContext $context, LoggerInterface $logger = null)
    {
        $this->context = $context;
        $this->logger = $logger;
        if (null === self::$declaredRoutes) {
            self::$declaredRoutes = array(
        '_wdt' => array (  0 =>   array (    0 => 'token',  ),  1 =>   array (    '_controller' => 'web_profiler.controller.profiler:toolbarAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'token',    ),    1 =>     array (      0 => 'text',      1 => '/_wdt',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_profiler_home' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'web_profiler.controller.profiler:homeAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/_profiler/',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_profiler_search' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'web_profiler.controller.profiler:searchAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/_profiler/search',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_profiler_search_bar' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'web_profiler.controller.profiler:searchBarAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/_profiler/search_bar',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_profiler_info' => array (  0 =>   array (    0 => 'about',  ),  1 =>   array (    '_controller' => 'web_profiler.controller.profiler:infoAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'about',    ),    1 =>     array (      0 => 'text',      1 => '/_profiler/info',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_profiler_phpinfo' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'web_profiler.controller.profiler:phpinfoAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/_profiler/phpinfo',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_profiler_search_results' => array (  0 =>   array (    0 => 'token',  ),  1 =>   array (    '_controller' => 'web_profiler.controller.profiler:searchResultsAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/search/results',    ),    1 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'token',    ),    2 =>     array (      0 => 'text',      1 => '/_profiler',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_profiler' => array (  0 =>   array (    0 => 'token',  ),  1 =>   array (    '_controller' => 'web_profiler.controller.profiler:panelAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'token',    ),    1 =>     array (      0 => 'text',      1 => '/_profiler',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_profiler_router' => array (  0 =>   array (    0 => 'token',  ),  1 =>   array (    '_controller' => 'web_profiler.controller.router:panelAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/router',    ),    1 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'token',    ),    2 =>     array (      0 => 'text',      1 => '/_profiler',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_profiler_exception' => array (  0 =>   array (    0 => 'token',  ),  1 =>   array (    '_controller' => 'web_profiler.controller.exception:showAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/exception',    ),    1 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'token',    ),    2 =>     array (      0 => 'text',      1 => '/_profiler',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_profiler_exception_css' => array (  0 =>   array (    0 => 'token',  ),  1 =>   array (    '_controller' => 'web_profiler.controller.exception:cssAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/exception.css',    ),    1 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'token',    ),    2 =>     array (      0 => 'text',      1 => '/_profiler',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        '_twig_error_test' => array (  0 =>   array (    0 => 'code',    1 => '_format',  ),  1 =>   array (    '_controller' => 'twig.controller.preview_error:previewErrorPageAction',    '_format' => 'html',  ),  2 =>   array (    'code' => '\\d+',  ),  3 =>   array (    0 =>     array (      0 => 'variable',      1 => '.',      2 => '[^/]++',      3 => '_format',    ),    1 =>     array (      0 => 'variable',      1 => '/',      2 => '\\d+',      3 => 'code',    ),    2 =>     array (      0 => 'text',      1 => '/_error',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'acme_store_default_index' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Acme\\StoreBundle\\Controller\\DefaultController::indexAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/acme/store/',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'acme_store_default_create' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Acme\\StoreBundle\\Controller\\DefaultController::createAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/acme/store/create',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'acme_store_default_show' => array (  0 =>   array (    0 => 'id',  ),  1 =>   array (    '_controller' => 'Acme\\StoreBundle\\Controller\\DefaultController::showAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'id',    ),    1 =>     array (      0 => 'text',      1 => '/acme/store/show',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'create' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Acme\\TestBundle\\Controller\\Web\\DefaultController::createAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/acme/test/default/create',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'acme_test_api_product_new' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Acme\\TestBundle\\Controller\\Api\\ProductController::newAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/acme/test/acme/test/api/products',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'show' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Acme\\TestBundle\\Controller\\Api\\ProductController::showAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/acme/test/acme/test/api/products/show',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'update' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Acme\\TestBundle\\Controller\\Api\\ProductController::updateAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/acme/test/acme/test/api/products/update',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'AcmeTestBundle' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Acme\\TestBundle\\Controller\\Web\\DefaultController::indexAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/acme/test/default/index',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'AcmeTestBundle_create' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Acme\\TestBundle\\Controller\\Web\\DefaultController::createAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/acme/test/default/create/',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'AcmeTestBundle_show' => array (  0 =>   array (    0 => 'productId',  ),  1 =>   array (    '_controller' => 'Acme\\TestBundle\\Controller\\Web\\DefaultController::showAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'productId',    ),    1 =>     array (      0 => 'text',      1 => '/acme/test/default/show',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'AcmeTestBundle_update' => array (  0 =>   array (    0 => 'productId',  ),  1 =>   array (    '_controller' => 'Acme\\TestBundle\\Controller\\Web\\DefaultController::updateAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'productId',    ),    1 =>     array (      0 => 'text',      1 => '/acme/test/update',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'AcmeTestBundle_api_new' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Acme\\TestBundle\\Controller\\Api\\ProductController::newAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/acme/test/api/products',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'AcmeTestBundle_api_show' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Acme\\TestBundle\\Controller\\Api\\ProductController::showAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/acme/test/api/products/show',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'homepage' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'AppBundle\\Controller\\Web\\DefaultController::indexAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/default/index',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'lucky_number' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'AppBundle\\Controller\\Web\\LuckyController::numberAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/lucky/number',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'lucky_number_api' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'AppBundle\\Controller\\Web\\LuckyController::apiNumberAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/api/lucky/number',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'app_api_programmer_new' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'AppBundle\\Controller\\Api\\ProgrammerController::newAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/api/programmers',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'sonata_admin_redirect' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::redirectAction',    'route' => 'sonata_admin_dashboard',    'permanent' => 'true',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/admin/',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'sonata_admin_dashboard' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Sonata\\AdminBundle\\Controller\\CoreController::dashboardAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/admin/dashboard',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'sonata_admin_retrieve_form_element' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'sonata.admin.controller.admin:retrieveFormFieldElementAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/admin/core/get-form-field-element',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'sonata_admin_append_form_element' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'sonata.admin.controller.admin:appendFormFieldElementAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/admin/core/append-form-field-element',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'sonata_admin_short_object_information' => array (  0 =>   array (    0 => '_format',  ),  1 =>   array (    '_controller' => 'sonata.admin.controller.admin:getShortObjectDescriptionAction',    '_format' => 'html',  ),  2 =>   array (    '_format' => 'html|json',  ),  3 =>   array (    0 =>     array (      0 => 'variable',      1 => '.',      2 => 'html|json',      3 => '_format',    ),    1 =>     array (      0 => 'text',      1 => '/admin/core/get-short-object-description',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'sonata_admin_set_object_field_value' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'sonata.admin.controller.admin:setObjectFieldValueAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/admin/core/set-object-field-value',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'sonata_admin_search' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Sonata\\AdminBundle\\Controller\\CoreController::searchAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/admin/search',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'sonata_admin_retrieve_autocomplete_items' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'sonata.admin.controller.admin:retrieveAutocompleteItemsAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/admin/core/get-autocomplete-items',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
    );
        }
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if (!isset(self::$declaredRoutes[$name])) {
            throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', $name));
        }

        list($variables, $defaults, $requirements, $tokens, $hostTokens, $requiredSchemes) = self::$declaredRoutes[$name];

        return $this->doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);
    }
}
