<?php 
namespace Symfony\Component\HttpFoundation\Session\Storage
{
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\AbstractProxy;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
class PhpBridgeSessionStorage extends NativeSessionStorage
{
public function __construct($handler = null, MetadataBag $metaBag = null)
{
$this->setMetadataBag($metaBag);
$this->setSaveHandler($handler);
}
public function start()
{
if ($this->started) {
return true;
}
$this->loadSession();
return true;
}
public function clear()
{
foreach ($this->bags as $bag) {
$bag->clear();
}
$this->loadSession();
}
}
}
namespace Symfony\Bundle\FrameworkBundle\Templating
{
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
class GlobalVariables
{
protected $container;
public function __construct(ContainerInterface $container)
{
$this->container = $container;
}
public function getUser()
{
if (!$this->container->has('security.token_storage')) {
return;
}
$tokenStorage = $this->container->get('security.token_storage');
if (!$token = $tokenStorage->getToken()) {
return;
}
$user = $token->getUser();
if (!is_object($user)) {
return;
}
return $user;
}
public function getRequest()
{
if ($this->container->has('request_stack')) {
return $this->container->get('request_stack')->getCurrentRequest();
}
}
public function getSession()
{
if ($request = $this->getRequest()) {
return $request->getSession();
}
}
public function getEnvironment()
{
return $this->container->getParameter('kernel.environment');
}
public function getDebug()
{
return (bool) $this->container->getParameter('kernel.debug');
}
}
}
namespace Symfony\Component\Routing\Generator
{
interface ConfigurableRequirementsInterface
{
public function setStrictRequirements($enabled);
public function isStrictRequirements();
}
}
namespace Symfony\Component\Routing\Generator
{
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Psr\Log\LoggerInterface;
class UrlGenerator implements UrlGeneratorInterface, ConfigurableRequirementsInterface
{
protected $routes;
protected $context;
protected $strictRequirements = true;
protected $logger;
protected $decodedChars = array('%2F'=>'/','%40'=>'@','%3A'=>':','%3B'=>';','%2C'=>',','%3D'=>'=','%2B'=>'+','%21'=>'!','%2A'=>'*','%7C'=>'|',
);
public function __construct(RouteCollection $routes, RequestContext $context, LoggerInterface $logger = null)
{
$this->routes = $routes;
$this->context = $context;
$this->logger = $logger;
}
public function setContext(RequestContext $context)
{
$this->context = $context;
}
public function getContext()
{
return $this->context;
}
public function setStrictRequirements($enabled)
{
$this->strictRequirements = null === $enabled ? null : (bool) $enabled;
}
public function isStrictRequirements()
{
return $this->strictRequirements;
}
public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
{
if (null === $route = $this->routes->get($name)) {
throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', $name));
}
$compiledRoute = $route->compile();
return $this->doGenerate($compiledRoute->getVariables(), $route->getDefaults(), $route->getRequirements(), $compiledRoute->getTokens(), $parameters, $name, $referenceType, $compiledRoute->getHostTokens(), $route->getSchemes());
}
protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = array())
{
$variables = array_flip($variables);
$mergedParams = array_replace($defaults, $this->context->getParameters(), $parameters);
if ($diff = array_diff_key($variables, $mergedParams)) {
throw new MissingMandatoryParametersException(sprintf('Some mandatory parameters are missing ("%s") to generate a URL for route "%s".', implode('", "', array_keys($diff)), $name));
}
$url ='';
$optional = true;
$message ='Parameter "{parameter}" for route "{route}" must match "{expected}" ("{given}" given) to generate a corresponding URL.';
foreach ($tokens as $token) {
if ('variable'=== $token[0]) {
if (!$optional || !array_key_exists($token[3], $defaults) || null !== $mergedParams[$token[3]] && (string) $mergedParams[$token[3]] !== (string) $defaults[$token[3]]) {
if (null !== $this->strictRequirements && !preg_match('#^'.$token[2].'$#', $mergedParams[$token[3]])) {
if ($this->strictRequirements) {
throw new InvalidParameterException(strtr($message, array('{parameter}'=> $token[3],'{route}'=> $name,'{expected}'=> $token[2],'{given}'=> $mergedParams[$token[3]])));
}
if ($this->logger) {
$this->logger->error($message, array('parameter'=> $token[3],'route'=> $name,'expected'=> $token[2],'given'=> $mergedParams[$token[3]]));
}
return;
}
$url = $token[1].$mergedParams[$token[3]].$url;
$optional = false;
}
} else {
$url = $token[1].$url;
$optional = false;
}
}
if (''=== $url) {
$url ='/';
}
$url = strtr(rawurlencode($url), $this->decodedChars);
$url = strtr($url, array('/../'=>'/%2E%2E/','/./'=>'/%2E/'));
if ('/..'=== substr($url, -3)) {
$url = substr($url, 0, -2).'%2E%2E';
} elseif ('/.'=== substr($url, -2)) {
$url = substr($url, 0, -1).'%2E';
}
$schemeAuthority ='';
if ($host = $this->context->getHost()) {
$scheme = $this->context->getScheme();
if ($requiredSchemes) {
if (!in_array($scheme, $requiredSchemes, true)) {
$referenceType = self::ABSOLUTE_URL;
$scheme = current($requiredSchemes);
}
}
if ($hostTokens) {
$routeHost ='';
foreach ($hostTokens as $token) {
if ('variable'=== $token[0]) {
if (null !== $this->strictRequirements && !preg_match('#^'.$token[2].'$#i', $mergedParams[$token[3]])) {
if ($this->strictRequirements) {
throw new InvalidParameterException(strtr($message, array('{parameter}'=> $token[3],'{route}'=> $name,'{expected}'=> $token[2],'{given}'=> $mergedParams[$token[3]])));
}
if ($this->logger) {
$this->logger->error($message, array('parameter'=> $token[3],'route'=> $name,'expected'=> $token[2],'given'=> $mergedParams[$token[3]]));
}
return;
}
$routeHost = $token[1].$mergedParams[$token[3]].$routeHost;
} else {
$routeHost = $token[1].$routeHost;
}
}
if ($routeHost !== $host) {
$host = $routeHost;
if (self::ABSOLUTE_URL !== $referenceType) {
$referenceType = self::NETWORK_PATH;
}
}
}
if (self::ABSOLUTE_URL === $referenceType || self::NETWORK_PATH === $referenceType) {
$port ='';
if ('http'=== $scheme && 80 != $this->context->getHttpPort()) {
$port =':'.$this->context->getHttpPort();
} elseif ('https'=== $scheme && 443 != $this->context->getHttpsPort()) {
$port =':'.$this->context->getHttpsPort();
}
$schemeAuthority = self::NETWORK_PATH === $referenceType ?'//': "$scheme://";
$schemeAuthority .= $host.$port;
}
}
if (self::RELATIVE_PATH === $referenceType) {
$url = self::getRelativePath($this->context->getPathInfo(), $url);
} else {
$url = $schemeAuthority.$this->context->getBaseUrl().$url;
}
$extra = array_udiff_assoc(array_diff_key($parameters, $variables), $defaults, function ($a, $b) {
return $a == $b ? 0 : 1;
});
if ($extra && $query = http_build_query($extra,'','&')) {
$url .='?'.strtr($query, array('%2F'=>'/'));
}
return $url;
}
public static function getRelativePath($basePath, $targetPath)
{
if ($basePath === $targetPath) {
return'';
}
$sourceDirs = explode('/', isset($basePath[0]) &&'/'=== $basePath[0] ? substr($basePath, 1) : $basePath);
$targetDirs = explode('/', isset($targetPath[0]) &&'/'=== $targetPath[0] ? substr($targetPath, 1) : $targetPath);
array_pop($sourceDirs);
$targetFile = array_pop($targetDirs);
foreach ($sourceDirs as $i => $dir) {
if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
unset($sourceDirs[$i], $targetDirs[$i]);
} else {
break;
}
}
$targetDirs[] = $targetFile;
$path = str_repeat('../', count($sourceDirs)).implode('/', $targetDirs);
return''=== $path ||'/'=== $path[0]
|| false !== ($colonPos = strpos($path,':')) && ($colonPos < ($slashPos = strpos($path,'/')) || false === $slashPos)
? "./$path" : $path;
}
}
}
namespace Symfony\Component\Routing\Matcher
{
interface RedirectableUrlMatcherInterface
{
public function redirect($path, $route, $scheme = null);
}
}
namespace Symfony\Component\Routing\Matcher
{
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
class UrlMatcher implements UrlMatcherInterface, RequestMatcherInterface
{
const REQUIREMENT_MATCH = 0;
const REQUIREMENT_MISMATCH = 1;
const ROUTE_MATCH = 2;
protected $context;
protected $allow = array();
protected $routes;
protected $request;
protected $expressionLanguage;
protected $expressionLanguageProviders = array();
public function __construct(RouteCollection $routes, RequestContext $context)
{
$this->routes = $routes;
$this->context = $context;
}
public function setContext(RequestContext $context)
{
$this->context = $context;
}
public function getContext()
{
return $this->context;
}
public function match($pathinfo)
{
$this->allow = array();
if ($ret = $this->matchCollection(rawurldecode($pathinfo), $this->routes)) {
return $ret;
}
throw 0 < count($this->allow)
? new MethodNotAllowedException(array_unique($this->allow))
: new ResourceNotFoundException(sprintf('No routes found for "%s".', $pathinfo));
}
public function matchRequest(Request $request)
{
$this->request = $request;
$ret = $this->match($request->getPathInfo());
$this->request = null;
return $ret;
}
public function addExpressionLanguageProvider(ExpressionFunctionProviderInterface $provider)
{
$this->expressionLanguageProviders[] = $provider;
}
protected function matchCollection($pathinfo, RouteCollection $routes)
{
foreach ($routes as $name => $route) {
$compiledRoute = $route->compile();
if (''!== $compiledRoute->getStaticPrefix() && 0 !== strpos($pathinfo, $compiledRoute->getStaticPrefix())) {
continue;
}
if (!preg_match($compiledRoute->getRegex(), $pathinfo, $matches)) {
continue;
}
$hostMatches = array();
if ($compiledRoute->getHostRegex() && !preg_match($compiledRoute->getHostRegex(), $this->context->getHost(), $hostMatches)) {
continue;
}
if ($requiredMethods = $route->getMethods()) {
if ('HEAD'=== $method = $this->context->getMethod()) {
$method ='GET';
}
if (!in_array($method, $requiredMethods)) {
$this->allow = array_merge($this->allow, $requiredMethods);
continue;
}
}
$status = $this->handleRouteRequirements($pathinfo, $name, $route);
if (self::ROUTE_MATCH === $status[0]) {
return $status[1];
}
if (self::REQUIREMENT_MISMATCH === $status[0]) {
continue;
}
return $this->getAttributes($route, $name, array_replace($matches, $hostMatches));
}
}
protected function getAttributes(Route $route, $name, array $attributes)
{
$attributes['_route'] = $name;
return $this->mergeDefaults($attributes, $route->getDefaults());
}
protected function handleRouteRequirements($pathinfo, $name, Route $route)
{
if ($route->getCondition() && !$this->getExpressionLanguage()->evaluate($route->getCondition(), array('context'=> $this->context,'request'=> $this->request))) {
return array(self::REQUIREMENT_MISMATCH, null);
}
$scheme = $this->context->getScheme();
$status = $route->getSchemes() && !$route->hasScheme($scheme) ? self::REQUIREMENT_MISMATCH : self::REQUIREMENT_MATCH;
return array($status, null);
}
protected function mergeDefaults($params, $defaults)
{
foreach ($params as $key => $value) {
if (!is_int($key)) {
$defaults[$key] = $value;
}
}
return $defaults;
}
protected function getExpressionLanguage()
{
if (null === $this->expressionLanguage) {
if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
throw new \RuntimeException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
}
$this->expressionLanguage = new ExpressionLanguage(null, $this->expressionLanguageProviders);
}
return $this->expressionLanguage;
}
}
}
namespace Symfony\Component\Routing\Matcher
{
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
abstract class RedirectableUrlMatcher extends UrlMatcher implements RedirectableUrlMatcherInterface
{
public function match($pathinfo)
{
try {
$parameters = parent::match($pathinfo);
} catch (ResourceNotFoundException $e) {
if ('/'=== substr($pathinfo, -1) || !in_array($this->context->getMethod(), array('HEAD','GET'))) {
throw $e;
}
try {
parent::match($pathinfo.'/');
return $this->redirect($pathinfo.'/', null);
} catch (ResourceNotFoundException $e2) {
throw $e;
}
}
return $parameters;
}
protected function handleRouteRequirements($pathinfo, $name, Route $route)
{
if ($route->getCondition() && !$this->getExpressionLanguage()->evaluate($route->getCondition(), array('context'=> $this->context,'request'=> $this->request))) {
return array(self::REQUIREMENT_MISMATCH, null);
}
$scheme = $this->context->getScheme();
$schemes = $route->getSchemes();
if ($schemes && !$route->hasScheme($scheme)) {
return array(self::ROUTE_MATCH, $this->redirect($pathinfo, $name, current($schemes)));
}
return array(self::REQUIREMENT_MATCH, null);
}
}
}
namespace Symfony\Bundle\FrameworkBundle\Routing
{
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher as BaseMatcher;
class RedirectableUrlMatcher extends BaseMatcher
{
public function redirect($path, $route, $scheme = null)
{
return array('_controller'=>'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction','path'=> $path,'permanent'=> true,'scheme'=> $scheme,'httpPort'=> $this->context->getHttpPort(),'httpsPort'=> $this->context->getHttpsPort(),'_route'=> $route,
);
}
}
}
namespace Symfony\Component\HttpKernel\Controller
{
use Symfony\Component\HttpFoundation\Request;
interface ArgumentResolverInterface
{
public function getArguments(Request $request, $controller);
}
}
namespace Symfony\Component\HttpKernel\Controller
{
use Symfony\Component\HttpFoundation\Request;
interface ControllerResolverInterface
{
public function getController(Request $request);
public function getArguments(Request $request, $controller);
}
}
namespace Symfony\Component\HttpKernel\Controller
{
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
class ControllerResolver implements ArgumentResolverInterface, ControllerResolverInterface
{
private $logger;
public function __construct(LoggerInterface $logger = null)
{
$this->logger = $logger;
}
public function getController(Request $request)
{
if (!$controller = $request->attributes->get('_controller')) {
if (null !== $this->logger) {
$this->logger->warning('Unable to look for the controller as the "_controller" parameter is missing.');
}
return false;
}
if (is_array($controller)) {
return $controller;
}
if (is_object($controller)) {
if (method_exists($controller,'__invoke')) {
return $controller;
}
throw new \InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', get_class($controller), $request->getPathInfo()));
}
if (false === strpos($controller,':')) {
if (method_exists($controller,'__invoke')) {
return $this->instantiateController($controller);
} elseif (function_exists($controller)) {
return $controller;
}
}
$callable = $this->createController($controller);
if (!is_callable($callable)) {
throw new \InvalidArgumentException(sprintf('The controller for URI "%s" is not callable. %s', $request->getPathInfo(), $this->getControllerError($callable)));
}
return $callable;
}
public function getArguments(Request $request, $controller)
{
@trigger_error(sprintf('%s is deprecated as of 3.1 and will be removed in 4.0. Implement the %s and inject it in the HttpKernel instead.', __METHOD__, ArgumentResolverInterface::class), E_USER_DEPRECATED);
if (is_array($controller)) {
$r = new \ReflectionMethod($controller[0], $controller[1]);
} elseif (is_object($controller) && !$controller instanceof \Closure) {
$r = new \ReflectionObject($controller);
$r = $r->getMethod('__invoke');
} else {
$r = new \ReflectionFunction($controller);
}
return $this->doGetArguments($request, $controller, $r->getParameters());
}
protected function doGetArguments(Request $request, $controller, array $parameters)
{
@trigger_error(sprintf('%s is deprecated as of 3.1 and will be removed in 4.0. Implement the %s and inject it in the HttpKernel instead.', __METHOD__, ArgumentResolverInterface::class), E_USER_DEPRECATED);
$attributes = $request->attributes->all();
$arguments = array();
foreach ($parameters as $param) {
if (array_key_exists($param->name, $attributes)) {
if (PHP_VERSION_ID >= 50600 && $param->isVariadic() && is_array($attributes[$param->name])) {
$arguments = array_merge($arguments, array_values($attributes[$param->name]));
} else {
$arguments[] = $attributes[$param->name];
}
} elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
$arguments[] = $request;
} elseif ($param->isDefaultValueAvailable()) {
$arguments[] = $param->getDefaultValue();
} else {
if (is_array($controller)) {
$repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
} elseif (is_object($controller)) {
$repr = get_class($controller);
} else {
$repr = $controller;
}
throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
}
}
return $arguments;
}
protected function createController($controller)
{
if (false === strpos($controller,'::')) {
throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
}
list($class, $method) = explode('::', $controller, 2);
if (!class_exists($class)) {
throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
}
return array($this->instantiateController($class), $method);
}
protected function instantiateController($class)
{
return new $class();
}
private function getControllerError($callable)
{
if (is_string($callable)) {
if (false !== strpos($callable,'::')) {
$callable = explode('::', $callable);
}
if (class_exists($callable) && !method_exists($callable,'__invoke')) {
return sprintf('Class "%s" does not have a method "__invoke".', $callable);
}
if (!function_exists($callable)) {
return sprintf('Function "%s" does not exist.', $callable);
}
}
if (!is_array($callable)) {
return sprintf('Invalid type for controller given, expected string or array, got "%s".', gettype($callable));
}
if (2 !== count($callable)) {
return sprintf('Invalid format for controller, expected array(controller, method) or controller::method.');
}
list($controller, $method) = $callable;
if (is_string($controller) && !class_exists($controller)) {
return sprintf('Class "%s" does not exist.', $controller);
}
$className = is_object($controller) ? get_class($controller) : $controller;
if (method_exists($controller, $method)) {
return sprintf('Method "%s" on class "%s" should be public and non-abstract.', $method, $className);
}
$collection = get_class_methods($controller);
$alternatives = array();
foreach ($collection as $item) {
$lev = levenshtein($method, $item);
if ($lev <= strlen($method) / 3 || false !== strpos($item, $method)) {
$alternatives[] = $item;
}
}
asort($alternatives);
$message = sprintf('Expected method "%s" on class "%s"', $method, $className);
if (count($alternatives) > 0) {
$message .= sprintf(', did you mean "%s"?', implode('", "', $alternatives));
} else {
$message .= sprintf('. Available methods: "%s".', implode('", "', $collection));
}
return $message;
}
}
}
namespace Symfony\Component\HttpKernel\Controller
{
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
final class ArgumentResolver implements ArgumentResolverInterface
{
private $argumentMetadataFactory;
private $argumentValueResolvers;
public function __construct(ArgumentMetadataFactoryInterface $argumentMetadataFactory = null, array $argumentValueResolvers = array())
{
$this->argumentMetadataFactory = $argumentMetadataFactory ?: new ArgumentMetadataFactory();
$this->argumentValueResolvers = $argumentValueResolvers ?: array(
new RequestAttributeValueResolver(),
new RequestValueResolver(),
new DefaultValueResolver(),
new VariadicValueResolver(),
);
}
public function getArguments(Request $request, $controller)
{
$arguments = array();
foreach ($this->argumentMetadataFactory->createArgumentMetadata($controller) as $metadata) {
foreach ($this->argumentValueResolvers as $resolver) {
if (!$resolver->supports($request, $metadata)) {
continue;
}
$resolved = $resolver->resolve($request, $metadata);
if (!$resolved instanceof \Generator) {
throw new \InvalidArgumentException(sprintf('%s::resolve() must yield at least one value.', get_class($resolver)));
}
foreach ($resolved as $append) {
$arguments[] = $append;
}
continue 2;
}
$representative = $controller;
if (is_array($representative)) {
$representative = sprintf('%s::%s()', get_class($representative[0]), $representative[1]);
} elseif (is_object($representative)) {
$representative = get_class($representative);
}
throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $representative, $metadata->getName()));
}
return $arguments;
}
}
}
namespace Symfony\Component\HttpKernel\ControllerMetadata
{
class ArgumentMetadata
{
private $name;
private $type;
private $isVariadic;
private $hasDefaultValue;
private $defaultValue;
public function __construct($name, $type, $isVariadic, $hasDefaultValue, $defaultValue)
{
$this->name = $name;
$this->type = $type;
$this->isVariadic = $isVariadic;
$this->hasDefaultValue = $hasDefaultValue;
$this->defaultValue = $defaultValue;
}
public function getName()
{
return $this->name;
}
public function getType()
{
return $this->type;
}
public function isVariadic()
{
return $this->isVariadic;
}
public function hasDefaultValue()
{
return $this->hasDefaultValue;
}
public function getDefaultValue()
{
if (!$this->hasDefaultValue) {
throw new \LogicException(sprintf('Argument $%s does not have a default value. Use %s::hasDefaultValue() to avoid this exception.', $this->name, __CLASS__));
}
return $this->defaultValue;
}
}
}
namespace Symfony\Component\HttpKernel\ControllerMetadata
{
interface ArgumentMetadataFactoryInterface
{
public function createArgumentMetadata($controller);
}
}
namespace Symfony\Component\HttpKernel\ControllerMetadata
{
final class ArgumentMetadataFactory implements ArgumentMetadataFactoryInterface
{
public function createArgumentMetadata($controller)
{
$arguments = array();
if (is_array($controller)) {
$reflection = new \ReflectionMethod($controller[0], $controller[1]);
} elseif (is_object($controller) && !$controller instanceof \Closure) {
$reflection = (new \ReflectionObject($controller))->getMethod('__invoke');
} else {
$reflection = new \ReflectionFunction($controller);
}
foreach ($reflection->getParameters() as $param) {
$arguments[] = new ArgumentMetadata($param->getName(), $this->getType($param), $this->isVariadic($param), $this->hasDefaultValue($param), $this->getDefaultValue($param));
}
return $arguments;
}
private function isVariadic(\ReflectionParameter $parameter)
{
return PHP_VERSION_ID >= 50600 && $parameter->isVariadic();
}
private function hasDefaultValue(\ReflectionParameter $parameter)
{
return $parameter->isDefaultValueAvailable();
}
private function getDefaultValue(\ReflectionParameter $parameter)
{
return $this->hasDefaultValue($parameter) ? $parameter->getDefaultValue() : null;
}
private function getType(\ReflectionParameter $parameter)
{
if (PHP_VERSION_ID >= 70000) {
return $parameter->hasType() ? (string) $parameter->getType() : null;
}
if ($parameter->isArray()) {
return'array';
}
if ($parameter->isCallable()) {
return'callable';
}
try {
$refClass = $parameter->getClass();
} catch (\ReflectionException $e) {
return str_replace(['Class ',' does not exist'],'', $e->getMessage());
}
return $refClass ? $refClass->getName() : null;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;
class KernelEvent extends Event
{
private $kernel;
private $request;
private $requestType;
public function __construct(HttpKernelInterface $kernel, Request $request, $requestType)
{
$this->kernel = $kernel;
$this->request = $request;
$this->requestType = $requestType;
}
public function getKernel()
{
return $this->kernel;
}
public function getRequest()
{
return $this->request;
}
public function getRequestType()
{
return $this->requestType;
}
public function isMasterRequest()
{
return HttpKernelInterface::MASTER_REQUEST === $this->requestType;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
class FilterControllerEvent extends KernelEvent
{
private $controller;
public function __construct(HttpKernelInterface $kernel, callable $controller, Request $request, $requestType)
{
parent::__construct($kernel, $request, $requestType);
$this->setController($controller);
}
public function getController()
{
return $this->controller;
}
public function setController(callable $controller)
{
$this->controller = $controller;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class FilterResponseEvent extends KernelEvent
{
private $response;
public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, Response $response)
{
parent::__construct($kernel, $request, $requestType);
$this->setResponse($response);
}
public function getResponse()
{
return $this->response;
}
public function setResponse(Response $response)
{
$this->response = $response;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpFoundation\Response;
class GetResponseEvent extends KernelEvent
{
private $response;
public function getResponse()
{
return $this->response;
}
public function setResponse(Response $response)
{
$this->response = $response;
$this->stopPropagation();
}
public function hasResponse()
{
return null !== $this->response;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
class GetResponseForControllerResultEvent extends GetResponseEvent
{
private $controllerResult;
public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, $controllerResult)
{
parent::__construct($kernel, $request, $requestType);
$this->controllerResult = $controllerResult;
}
public function getControllerResult()
{
return $this->controllerResult;
}
public function setControllerResult($controllerResult)
{
$this->controllerResult = $controllerResult;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
class GetResponseForExceptionEvent extends GetResponseEvent
{
private $exception;
public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, \Exception $e)
{
parent::__construct($kernel, $request, $requestType);
$this->setException($e);
}
public function getException()
{
return $this->exception;
}
public function setException(\Exception $exception)
{
$this->exception = $exception;
}
}
}
namespace Symfony\Bundle\FrameworkBundle\Controller
{
use Symfony\Component\HttpKernel\KernelInterface;
class ControllerNameParser
{
protected $kernel;
public function __construct(KernelInterface $kernel)
{
$this->kernel = $kernel;
}
public function parse($controller)
{
$originalController = $controller;
if (3 !== count($parts = explode(':', $controller))) {
throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid "a:b:c" controller string.', $controller));
}
list($bundle, $controller, $action) = $parts;
$controller = str_replace('/','\\', $controller);
$bundles = array();
try {
$allBundles = $this->kernel->getBundle($bundle, false);
} catch (\InvalidArgumentException $e) {
$message = sprintf('The "%s" (from the _controller value "%s") does not exist or is not enabled in your kernel!',
$bundle,
$originalController
);
if ($alternative = $this->findAlternative($bundle)) {
$message .= sprintf(' Did you mean "%s:%s:%s"?', $alternative, $controller, $action);
}
throw new \InvalidArgumentException($message, 0, $e);
}
foreach ($allBundles as $b) {
$try = $b->getNamespace().'\\Controller\\'.$controller.'Controller';
if (class_exists($try)) {
return $try.'::'.$action.'Action';
}
$bundles[] = $b->getName();
$msg = sprintf('The _controller value "%s:%s:%s" maps to a "%s" class, but this class was not found. Create this class or check the spelling of the class and its namespace.', $bundle, $controller, $action, $try);
}
if (count($bundles) > 1) {
$msg = sprintf('Unable to find controller "%s:%s" in bundles %s.', $bundle, $controller, implode(', ', $bundles));
}
throw new \InvalidArgumentException($msg);
}
public function build($controller)
{
if (0 === preg_match('#^(.*?\\\\Controller\\\\(.+)Controller)::(.+)Action$#', $controller, $match)) {
throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid "class::method" string.', $controller));
}
$className = $match[1];
$controllerName = $match[2];
$actionName = $match[3];
foreach ($this->kernel->getBundles() as $name => $bundle) {
if (0 !== strpos($className, $bundle->getNamespace())) {
continue;
}
return sprintf('%s:%s:%s', $name, $controllerName, $actionName);
}
throw new \InvalidArgumentException(sprintf('Unable to find a bundle that defines controller "%s".', $controller));
}
private function findAlternative($nonExistentBundleName)
{
$bundleNames = array_map(function ($b) {
return $b->getName();
}, $this->kernel->getBundles());
$alternative = null;
$shortest = null;
foreach ($bundleNames as $bundleName) {
if (false !== strpos($bundleName, $nonExistentBundleName)) {
return $bundleName;
}
$lev = levenshtein($nonExistentBundleName, $bundleName);
if ($lev <= strlen($nonExistentBundleName) / 3 && ($alternative === null || $lev < $shortest)) {
$alternative = $bundleName;
$shortest = $lev;
}
}
return $alternative;
}
}
}
namespace Symfony\Bundle\FrameworkBundle\Controller
{
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
class ControllerResolver extends BaseControllerResolver
{
protected $container;
protected $parser;
public function __construct(ContainerInterface $container, ControllerNameParser $parser, LoggerInterface $logger = null)
{
$this->container = $container;
$this->parser = $parser;
parent::__construct($logger);
}
protected function createController($controller)
{
if (false === strpos($controller,'::')) {
$count = substr_count($controller,':');
if (2 == $count) {
$controller = $this->parser->parse($controller);
} elseif (1 == $count) {
list($service, $method) = explode(':', $controller, 2);
return array($this->container->get($service), $method);
} elseif ($this->container->has($controller) && method_exists($service = $this->container->get($controller),'__invoke')) {
return $service;
} else {
throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
}
}
return parent::createController($controller);
}
protected function instantiateController($class)
{
if ($this->container->has($class)) {
return $this->container->get($class);
}
$controller = parent::instantiateController($class);
if ($controller instanceof ContainerAwareInterface) {
$controller->setContainer($this->container);
}
return $controller;
}
}
}
namespace Symfony\Component\Security\Core\User
{
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
interface UserProviderInterface
{
public function loadUserByUsername($username);
public function refreshUser(UserInterface $user);
public function supportsClass($class);
}
}
namespace Symfony\Component\Security\Http
{
use Symfony\Component\HttpFoundation\Request;
interface FirewallMapInterface
{
public function getListeners(Request $request);
}
}
namespace Symfony\Bundle\SecurityBundle\Security
{
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
class FirewallMap implements FirewallMapInterface
{
protected $container;
protected $map;
public function __construct(ContainerInterface $container, array $map)
{
$this->container = $container;
$this->map = $map;
}
public function getListeners(Request $request)
{
foreach ($this->map as $contextId => $requestMatcher) {
if (null === $requestMatcher || $requestMatcher->matches($request)) {
return $this->container->get($contextId)->getContext();
}
}
return array(array(), null);
}
}
}
namespace Symfony\Bundle\SecurityBundle\Security
{
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
class FirewallContext
{
private $listeners;
private $exceptionListener;
public function __construct(array $listeners, ExceptionListener $exceptionListener = null)
{
$this->listeners = $listeners;
$this->exceptionListener = $exceptionListener;
}
public function getContext()
{
return array($this->listeners, $this->exceptionListener);
}
}
}
namespace Symfony\Component\HttpFoundation
{
interface RequestMatcherInterface
{
public function matches(Request $request);
}
}
namespace Symfony\Component\HttpFoundation
{
class RequestMatcher implements RequestMatcherInterface
{
private $path;
private $host;
private $methods = array();
private $ips = array();
private $attributes = array();
private $schemes = array();
public function __construct($path = null, $host = null, $methods = null, $ips = null, array $attributes = array(), $schemes = null)
{
$this->matchPath($path);
$this->matchHost($host);
$this->matchMethod($methods);
$this->matchIps($ips);
$this->matchScheme($schemes);
foreach ($attributes as $k => $v) {
$this->matchAttribute($k, $v);
}
}
public function matchScheme($scheme)
{
$this->schemes = array_map('strtolower', (array) $scheme);
}
public function matchHost($regexp)
{
$this->host = $regexp;
}
public function matchPath($regexp)
{
$this->path = $regexp;
}
public function matchIp($ip)
{
$this->matchIps($ip);
}
public function matchIps($ips)
{
$this->ips = (array) $ips;
}
public function matchMethod($method)
{
$this->methods = array_map('strtoupper', (array) $method);
}
public function matchAttribute($key, $regexp)
{
$this->attributes[$key] = $regexp;
}
public function matches(Request $request)
{
if ($this->schemes && !in_array($request->getScheme(), $this->schemes)) {
return false;
}
if ($this->methods && !in_array($request->getMethod(), $this->methods)) {
return false;
}
foreach ($this->attributes as $key => $pattern) {
if (!preg_match('{'.$pattern.'}', $request->attributes->get($key))) {
return false;
}
}
if (null !== $this->path && !preg_match('{'.$this->path.'}', rawurldecode($request->getPathInfo()))) {
return false;
}
if (null !== $this->host && !preg_match('{'.$this->host.'}i', $request->getHost())) {
return false;
}
if (IpUtils::checkIp($request->getClientIp(), $this->ips)) {
return true;
}
return count($this->ips) === 0;
}
}
}
namespace
{
class Twig_Markup implements Countable
{
protected $content;
protected $charset;
public function __construct($content, $charset)
{
$this->content = (string) $content;
$this->charset = $charset;
}
public function __toString()
{
return $this->content;
}
public function count()
{
return function_exists('mb_get_info') ? mb_strlen($this->content, $this->charset) : strlen($this->content);
}
}
}
namespace
{
interface Twig_TemplateInterface
{
const ANY_CALL ='any';
const ARRAY_CALL ='array';
const METHOD_CALL ='method';
public function render(array $context);
public function display(array $context, array $blocks = array());
public function getEnvironment();
}
}
namespace
{
abstract class Twig_Template implements Twig_TemplateInterface
{
protected static $cache = array();
protected $parent;
protected $parents = array();
protected $env;
protected $blocks = array();
protected $traits = array();
public function __construct(Twig_Environment $env)
{
$this->env = $env;
}
abstract public function getTemplateName();
public function getEnvironment()
{
@trigger_error('The '.__METHOD__.' method is deprecated since version 1.20 and will be removed in 2.0.', E_USER_DEPRECATED);
return $this->env;
}
public function getParent(array $context)
{
if (null !== $this->parent) {
return $this->parent;
}
try {
$parent = $this->doGetParent($context);
if (false === $parent) {
return false;
}
if ($parent instanceof self) {
return $this->parents[$parent->getTemplateName()] = $parent;
}
if (!isset($this->parents[$parent])) {
$this->parents[$parent] = $this->loadTemplate($parent);
}
} catch (Twig_Error_Loader $e) {
$e->setTemplateFile(null);
$e->guess();
throw $e;
}
return $this->parents[$parent];
}
protected function doGetParent(array $context)
{
return false;
}
public function isTraitable()
{
return true;
}
public function displayParentBlock($name, array $context, array $blocks = array())
{
$name = (string) $name;
if (isset($this->traits[$name])) {
$this->traits[$name][0]->displayBlock($name, $context, $blocks, false);
} elseif (false !== $parent = $this->getParent($context)) {
$parent->displayBlock($name, $context, $blocks, false);
} else {
throw new Twig_Error_Runtime(sprintf('The template has no parent and no traits defining the "%s" block', $name), -1, $this->getTemplateName());
}
}
public function displayBlock($name, array $context, array $blocks = array(), $useBlocks = true)
{
$name = (string) $name;
if ($useBlocks && isset($blocks[$name])) {
$template = $blocks[$name][0];
$block = $blocks[$name][1];
} elseif (isset($this->blocks[$name])) {
$template = $this->blocks[$name][0];
$block = $this->blocks[$name][1];
} else {
$template = null;
$block = null;
}
if (null !== $template) {
if (!$template instanceof self) {
throw new LogicException('A block must be a method on a Twig_Template instance.');
}
try {
$template->$block($context, $blocks);
} catch (Twig_Error $e) {
if (!$e->getTemplateFile()) {
$e->setTemplateFile($template->getTemplateName());
}
if (false === $e->getTemplateLine()) {
$e->setTemplateLine(-1);
$e->guess();
}
throw $e;
} catch (Exception $e) {
throw new Twig_Error_Runtime(sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, $template->getTemplateName(), $e);
}
} elseif (false !== $parent = $this->getParent($context)) {
$parent->displayBlock($name, $context, array_merge($this->blocks, $blocks), false);
}
}
public function renderParentBlock($name, array $context, array $blocks = array())
{
ob_start();
$this->displayParentBlock($name, $context, $blocks);
return ob_get_clean();
}
public function renderBlock($name, array $context, array $blocks = array(), $useBlocks = true)
{
ob_start();
$this->displayBlock($name, $context, $blocks, $useBlocks);
return ob_get_clean();
}
public function hasBlock($name)
{
return isset($this->blocks[(string) $name]);
}
public function getBlockNames()
{
return array_keys($this->blocks);
}
protected function loadTemplate($template, $templateName = null, $line = null, $index = null)
{
try {
if (is_array($template)) {
return $this->env->resolveTemplate($template);
}
if ($template instanceof self) {
return $template;
}
return $this->env->loadTemplate($template, $index);
} catch (Twig_Error $e) {
if (!$e->getTemplateFile()) {
$e->setTemplateFile($templateName ? $templateName : $this->getTemplateName());
}
if ($e->getTemplateLine()) {
throw $e;
}
if (!$line) {
$e->guess();
} else {
$e->setTemplateLine($line);
}
throw $e;
}
}
public function getBlocks()
{
return $this->blocks;
}
public function getSource()
{
$reflector = new ReflectionClass($this);
$file = $reflector->getFileName();
if (!file_exists($file)) {
return;
}
$source = file($file, FILE_IGNORE_NEW_LINES);
array_splice($source, 0, $reflector->getEndLine());
$i = 0;
while (isset($source[$i]) &&'/* */'=== substr_replace($source[$i],'', 3, -2)) {
$source[$i] = str_replace('*//* ','*/', substr($source[$i], 3, -2));
++$i;
}
array_splice($source, $i);
return implode("\n", $source);
}
public function display(array $context, array $blocks = array())
{
$this->displayWithErrorHandling($this->env->mergeGlobals($context), array_merge($this->blocks, $blocks));
}
public function render(array $context)
{
$level = ob_get_level();
ob_start();
try {
$this->display($context);
} catch (Exception $e) {
while (ob_get_level() > $level) {
ob_end_clean();
}
throw $e;
} catch (Throwable $e) {
while (ob_get_level() > $level) {
ob_end_clean();
}
throw $e;
}
return ob_get_clean();
}
protected function displayWithErrorHandling(array $context, array $blocks = array())
{
try {
$this->doDisplay($context, $blocks);
} catch (Twig_Error $e) {
if (!$e->getTemplateFile()) {
$e->setTemplateFile($this->getTemplateName());
}
if (false === $e->getTemplateLine()) {
$e->setTemplateLine(-1);
$e->guess();
}
throw $e;
} catch (Exception $e) {
throw new Twig_Error_Runtime(sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, $this->getTemplateName(), $e);
}
}
abstract protected function doDisplay(array $context, array $blocks = array());
final protected function getContext($context, $item, $ignoreStrictCheck = false)
{
if (!array_key_exists($item, $context)) {
if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
return;
}
throw new Twig_Error_Runtime(sprintf('Variable "%s" does not exist', $item), -1, $this->getTemplateName());
}
return $context[$item];
}
protected function getAttribute($object, $item, array $arguments = array(), $type = self::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
{
if (self::METHOD_CALL !== $type) {
$arrayItem = is_bool($item) || is_float($item) ? (int) $item : $item;
if ((is_array($object) && array_key_exists($arrayItem, $object))
|| ($object instanceof ArrayAccess && isset($object[$arrayItem]))
) {
if ($isDefinedTest) {
return true;
}
return $object[$arrayItem];
}
if (self::ARRAY_CALL === $type || !is_object($object)) {
if ($isDefinedTest) {
return false;
}
if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
return;
}
if ($object instanceof ArrayAccess) {
$message = sprintf('Key "%s" in object with ArrayAccess of class "%s" does not exist', $arrayItem, get_class($object));
} elseif (is_object($object)) {
$message = sprintf('Impossible to access a key "%s" on an object of class "%s" that does not implement ArrayAccess interface', $item, get_class($object));
} elseif (is_array($object)) {
if (empty($object)) {
$message = sprintf('Key "%s" does not exist as the array is empty', $arrayItem);
} else {
$message = sprintf('Key "%s" for array with keys "%s" does not exist', $arrayItem, implode(', ', array_keys($object)));
}
} elseif (self::ARRAY_CALL === $type) {
if (null === $object) {
$message = sprintf('Impossible to access a key ("%s") on a null variable', $item);
} else {
$message = sprintf('Impossible to access a key ("%s") on a %s variable ("%s")', $item, gettype($object), $object);
}
} elseif (null === $object) {
$message = sprintf('Impossible to access an attribute ("%s") on a null variable', $item);
} else {
$message = sprintf('Impossible to access an attribute ("%s") on a %s variable ("%s")', $item, gettype($object), $object);
}
throw new Twig_Error_Runtime($message, -1, $this->getTemplateName());
}
}
if (!is_object($object)) {
if ($isDefinedTest) {
return false;
}
if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
return;
}
if (null === $object) {
$message = sprintf('Impossible to invoke a method ("%s") on a null variable', $item);
} else {
$message = sprintf('Impossible to invoke a method ("%s") on a %s variable ("%s")', $item, gettype($object), $object);
}
throw new Twig_Error_Runtime($message, -1, $this->getTemplateName());
}
if (self::METHOD_CALL !== $type && !$object instanceof self) { if (isset($object->$item) || array_key_exists((string) $item, $object)) {
if ($isDefinedTest) {
return true;
}
if ($this->env->hasExtension('sandbox')) {
$this->env->getExtension('sandbox')->checkPropertyAllowed($object, $item);
}
return $object->$item;
}
}
$class = get_class($object);
if (!isset(self::$cache[$class]['methods'])) {
if ($object instanceof self) {
$ref = new ReflectionClass($class);
$methods = array();
foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
$methodName = strtolower($refMethod->name);
if ('getenvironment'!== $methodName) {
$methods[$methodName] = true;
}
}
self::$cache[$class]['methods'] = $methods;
} else {
self::$cache[$class]['methods'] = array_change_key_case(array_flip(get_class_methods($object)));
}
}
$call = false;
$lcItem = strtolower($item);
if (isset(self::$cache[$class]['methods'][$lcItem])) {
$method = (string) $item;
} elseif (isset(self::$cache[$class]['methods']['get'.$lcItem])) {
$method ='get'.$item;
} elseif (isset(self::$cache[$class]['methods']['is'.$lcItem])) {
$method ='is'.$item;
} elseif (isset(self::$cache[$class]['methods']['__call'])) {
$method = (string) $item;
$call = true;
} else {
if ($isDefinedTest) {
return false;
}
if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
return;
}
throw new Twig_Error_Runtime(sprintf('Neither the property "%1$s" nor one of the methods "%1$s()", "get%1$s()"/"is%1$s()" or "__call()" exist and have public access in class "%2$s"', $item, get_class($object)), -1, $this->getTemplateName());
}
if ($isDefinedTest) {
return true;
}
if ($this->env->hasExtension('sandbox')) {
$this->env->getExtension('sandbox')->checkMethodAllowed($object, $method);
}
try {
$ret = call_user_func_array(array($object, $method), $arguments);
} catch (BadMethodCallException $e) {
if ($call && ($ignoreStrictCheck || !$this->env->isStrictVariables())) {
return;
}
throw $e;
}
if ($object instanceof Twig_TemplateInterface) {
return $ret ===''?'': new Twig_Markup($ret, $this->env->getCharset());
}
return $ret;
}
}
}
namespace Monolog\Handler
{
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Logger;
class FingersCrossedHandler extends AbstractHandler
{
protected $handler;
protected $activationStrategy;
protected $buffering = true;
protected $bufferSize;
protected $buffer = array();
protected $stopBuffering;
protected $passthruLevel;
public function __construct($handler, $activationStrategy = null, $bufferSize = 0, $bubble = true, $stopBuffering = true, $passthruLevel = null)
{
if (null === $activationStrategy) {
$activationStrategy = new ErrorLevelActivationStrategy(Logger::WARNING);
}
if (!$activationStrategy instanceof ActivationStrategyInterface) {
$activationStrategy = new ErrorLevelActivationStrategy($activationStrategy);
}
$this->handler = $handler;
$this->activationStrategy = $activationStrategy;
$this->bufferSize = $bufferSize;
$this->bubble = $bubble;
$this->stopBuffering = $stopBuffering;
if ($passthruLevel !== null) {
$this->passthruLevel = Logger::toMonologLevel($passthruLevel);
}
if (!$this->handler instanceof HandlerInterface && !is_callable($this->handler)) {
throw new \RuntimeException("The given handler (".json_encode($this->handler).") is not a callable nor a Monolog\Handler\HandlerInterface object");
}
}
public function isHandling(array $record)
{
return true;
}
public function handle(array $record)
{
if ($this->processors) {
foreach ($this->processors as $processor) {
$record = call_user_func($processor, $record);
}
}
if ($this->buffering) {
$this->buffer[] = $record;
if ($this->bufferSize > 0 && count($this->buffer) > $this->bufferSize) {
array_shift($this->buffer);
}
if ($this->activationStrategy->isHandlerActivated($record)) {
if ($this->stopBuffering) {
$this->buffering = false;
}
if (!$this->handler instanceof HandlerInterface) {
$this->handler = call_user_func($this->handler, $record, $this);
if (!$this->handler instanceof HandlerInterface) {
throw new \RuntimeException("The factory callable should return a HandlerInterface");
}
}
$this->handler->handleBatch($this->buffer);
$this->buffer = array();
}
} else {
$this->handler->handle($record);
}
return false === $this->bubble;
}
public function close()
{
if (null !== $this->passthruLevel) {
$level = $this->passthruLevel;
$this->buffer = array_filter($this->buffer, function ($record) use ($level) {
return $record['level'] >= $level;
});
if (count($this->buffer) > 0) {
$this->handler->handleBatch($this->buffer);
$this->buffer = array();
}
}
}
public function reset()
{
$this->buffering = true;
}
public function clear()
{
$this->buffer = array();
$this->reset();
}
}
}
namespace Monolog\Handler
{
use Monolog\Logger;
class FilterHandler extends AbstractHandler
{
protected $handler;
protected $acceptedLevels;
protected $bubble;
public function __construct($handler, $minLevelOrList = Logger::DEBUG, $maxLevel = Logger::EMERGENCY, $bubble = true)
{
$this->handler = $handler;
$this->bubble = $bubble;
$this->setAcceptedLevels($minLevelOrList, $maxLevel);
if (!$this->handler instanceof HandlerInterface && !is_callable($this->handler)) {
throw new \RuntimeException("The given handler (".json_encode($this->handler).") is not a callable nor a Monolog\Handler\HandlerInterface object");
}
}
public function getAcceptedLevels()
{
return array_flip($this->acceptedLevels);
}
public function setAcceptedLevels($minLevelOrList = Logger::DEBUG, $maxLevel = Logger::EMERGENCY)
{
if (is_array($minLevelOrList)) {
$acceptedLevels = array_map('Monolog\Logger::toMonologLevel', $minLevelOrList);
} else {
$minLevelOrList = Logger::toMonologLevel($minLevelOrList);
$maxLevel = Logger::toMonologLevel($maxLevel);
$acceptedLevels = array_values(array_filter(Logger::getLevels(), function ($level) use ($minLevelOrList, $maxLevel) {
return $level >= $minLevelOrList && $level <= $maxLevel;
}));
}
$this->acceptedLevels = array_flip($acceptedLevels);
}
public function isHandling(array $record)
{
return isset($this->acceptedLevels[$record['level']]);
}
public function handle(array $record)
{
if (!$this->isHandling($record)) {
return false;
}
if (!$this->handler instanceof HandlerInterface) {
$this->handler = call_user_func($this->handler, $record, $this);
if (!$this->handler instanceof HandlerInterface) {
throw new \RuntimeException("The factory callable should return a HandlerInterface");
}
}
if ($this->processors) {
foreach ($this->processors as $processor) {
$record = call_user_func($processor, $record);
}
}
$this->handler->handle($record);
return false === $this->bubble;
}
public function handleBatch(array $records)
{
$filtered = array();
foreach ($records as $record) {
if ($this->isHandling($record)) {
$filtered[] = $record;
}
}
$this->handler->handleBatch($filtered);
}
}
}
namespace Monolog\Handler\FingersCrossed
{
interface ActivationStrategyInterface
{
public function isHandlerActivated(array $record);
}
}
namespace Monolog\Handler\FingersCrossed
{
use Monolog\Logger;
class ErrorLevelActivationStrategy implements ActivationStrategyInterface
{
private $actionLevel;
public function __construct($actionLevel)
{
$this->actionLevel = Logger::toMonologLevel($actionLevel);
}
public function isHandlerActivated(array $record)
{
return $record['level'] >= $this->actionLevel;
}
}
}
namespace Doctrine\Common\Lexer
{
abstract class AbstractLexer
{
private $input;
private $tokens = array();
private $position = 0;
private $peek = 0;
public $lookahead;
public $token;
public function setInput($input)
{
$this->input = $input;
$this->tokens = array();
$this->reset();
$this->scan($input);
}
public function reset()
{
$this->lookahead = null;
$this->token = null;
$this->peek = 0;
$this->position = 0;
}
public function resetPeek()
{
$this->peek = 0;
}
public function resetPosition($position = 0)
{
$this->position = $position;
}
public function getInputUntilPosition($position)
{
return substr($this->input, 0, $position);
}
public function isNextToken($token)
{
return null !== $this->lookahead && $this->lookahead['type'] === $token;
}
public function isNextTokenAny(array $tokens)
{
return null !== $this->lookahead && in_array($this->lookahead['type'], $tokens, true);
}
public function moveNext()
{
$this->peek = 0;
$this->token = $this->lookahead;
$this->lookahead = (isset($this->tokens[$this->position]))
? $this->tokens[$this->position++] : null;
return $this->lookahead !== null;
}
public function skipUntil($type)
{
while ($this->lookahead !== null && $this->lookahead['type'] !== $type) {
$this->moveNext();
}
}
public function isA($value, $token)
{
return $this->getType($value) === $token;
}
public function peek()
{
if (isset($this->tokens[$this->position + $this->peek])) {
return $this->tokens[$this->position + $this->peek++];
} else {
return null;
}
}
public function glimpse()
{
$peek = $this->peek();
$this->peek = 0;
return $peek;
}
protected function scan($input)
{
static $regex;
if ( ! isset($regex)) {
$regex = sprintf('/(%s)|%s/%s',
implode(')|(', $this->getCatchablePatterns()),
implode('|', $this->getNonCatchablePatterns()),
$this->getModifiers()
);
}
$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
$matches = preg_split($regex, $input, -1, $flags);
foreach ($matches as $match) {
$type = $this->getType($match[0]);
$this->tokens[] = array('value'=> $match[0],'type'=> $type,'position'=> $match[1],
);
}
}
public function getLiteral($token)
{
$className = get_class($this);
$reflClass = new \ReflectionClass($className);
$constants = $reflClass->getConstants();
foreach ($constants as $name => $value) {
if ($value === $token) {
return $className .'::'. $name;
}
}
return $token;
}
protected function getModifiers()
{
return'i';
}
abstract protected function getCatchablePatterns();
abstract protected function getNonCatchablePatterns();
abstract protected function getType(&$value);
}
}
namespace Doctrine\Common\Annotations
{
use Doctrine\Common\Lexer\AbstractLexer;
final class DocLexer extends AbstractLexer
{
const T_NONE = 1;
const T_INTEGER = 2;
const T_STRING = 3;
const T_FLOAT = 4;
const T_IDENTIFIER = 100;
const T_AT = 101;
const T_CLOSE_CURLY_BRACES = 102;
const T_CLOSE_PARENTHESIS = 103;
const T_COMMA = 104;
const T_EQUALS = 105;
const T_FALSE = 106;
const T_NAMESPACE_SEPARATOR = 107;
const T_OPEN_CURLY_BRACES = 108;
const T_OPEN_PARENTHESIS = 109;
const T_TRUE = 110;
const T_NULL = 111;
const T_COLON = 112;
protected $noCase = array('@'=> self::T_AT,','=> self::T_COMMA,'('=> self::T_OPEN_PARENTHESIS,')'=> self::T_CLOSE_PARENTHESIS,'{'=> self::T_OPEN_CURLY_BRACES,'}'=> self::T_CLOSE_CURLY_BRACES,'='=> self::T_EQUALS,':'=> self::T_COLON,'\\'=> self::T_NAMESPACE_SEPARATOR
);
protected $withCase = array('true'=> self::T_TRUE,'false'=> self::T_FALSE,'null'=> self::T_NULL
);
protected function getCatchablePatterns()
{
return array('[a-z_\\\][a-z0-9_\:\\\]*[a-z_][a-z0-9_]*','(?:[+-]?[0-9]+(?:[\.][0-9]+)*)(?:[eE][+-]?[0-9]+)?','"(?:""|[^"])*+"',
);
}
protected function getNonCatchablePatterns()
{
return array('\s+','\*+','(.)');
}
protected function getType(&$value)
{
$type = self::T_NONE;
if ($value[0] ==='"') {
$value = str_replace('""','"', substr($value, 1, strlen($value) - 2));
return self::T_STRING;
}
if (isset($this->noCase[$value])) {
return $this->noCase[$value];
}
if ($value[0] ==='_'|| $value[0] ==='\\'|| ctype_alpha($value[0])) {
return self::T_IDENTIFIER;
}
$lowerValue = strtolower($value);
if (isset($this->withCase[$lowerValue])) {
return $this->withCase[$lowerValue];
}
if (is_numeric($value)) {
return (strpos($value,'.') !== false || stripos($value,'e') !== false)
? self::T_FLOAT : self::T_INTEGER;
}
return $type;
}
}
}
namespace Doctrine\Common\Annotations
{
interface Reader
{
function getClassAnnotations(\ReflectionClass $class);
function getClassAnnotation(\ReflectionClass $class, $annotationName);
function getMethodAnnotations(\ReflectionMethod $method);
function getMethodAnnotation(\ReflectionMethod $method, $annotationName);
function getPropertyAnnotations(\ReflectionProperty $property);
function getPropertyAnnotation(\ReflectionProperty $property, $annotationName);
}
}
namespace Doctrine\Common\Annotations
{
class FileCacheReader implements Reader
{
private $reader;
private $dir;
private $debug;
private $loadedAnnotations = array();
private $classNameHashes = array();
private $umask;
public function __construct(Reader $reader, $cacheDir, $debug = false, $umask = 0002)
{
if ( ! is_int($umask)) {
throw new \InvalidArgumentException(sprintf('The parameter umask must be an integer, was: %s',
gettype($umask)
));
}
$this->reader = $reader;
$this->umask = $umask;
if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0777 & (~$this->umask), true)) {
throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist and could not be created.', $cacheDir));
}
$this->dir = rtrim($cacheDir,'\\/');
$this->debug = $debug;
}
public function getClassAnnotations(\ReflectionClass $class)
{
if ( ! isset($this->classNameHashes[$class->name])) {
$this->classNameHashes[$class->name] = sha1($class->name);
}
$key = $this->classNameHashes[$class->name];
if (isset($this->loadedAnnotations[$key])) {
return $this->loadedAnnotations[$key];
}
$path = $this->dir.'/'.strtr($key,'\\','-').'.cache.php';
if (!is_file($path)) {
$annot = $this->reader->getClassAnnotations($class);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
if ($this->debug
&& (false !== $filename = $class->getFilename())
&& filemtime($path) < filemtime($filename)) {
@unlink($path);
$annot = $this->reader->getClassAnnotations($class);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
return $this->loadedAnnotations[$key] = include $path;
}
public function getPropertyAnnotations(\ReflectionProperty $property)
{
$class = $property->getDeclaringClass();
if ( ! isset($this->classNameHashes[$class->name])) {
$this->classNameHashes[$class->name] = sha1($class->name);
}
$key = $this->classNameHashes[$class->name].'$'.$property->getName();
if (isset($this->loadedAnnotations[$key])) {
return $this->loadedAnnotations[$key];
}
$path = $this->dir.'/'.strtr($key,'\\','-').'.cache.php';
if (!is_file($path)) {
$annot = $this->reader->getPropertyAnnotations($property);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
if ($this->debug
&& (false !== $filename = $class->getFilename())
&& filemtime($path) < filemtime($filename)) {
@unlink($path);
$annot = $this->reader->getPropertyAnnotations($property);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
return $this->loadedAnnotations[$key] = include $path;
}
public function getMethodAnnotations(\ReflectionMethod $method)
{
$class = $method->getDeclaringClass();
if ( ! isset($this->classNameHashes[$class->name])) {
$this->classNameHashes[$class->name] = sha1($class->name);
}
$key = $this->classNameHashes[$class->name].'#'.$method->getName();
if (isset($this->loadedAnnotations[$key])) {
return $this->loadedAnnotations[$key];
}
$path = $this->dir.'/'.strtr($key,'\\','-').'.cache.php';
if (!is_file($path)) {
$annot = $this->reader->getMethodAnnotations($method);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
if ($this->debug
&& (false !== $filename = $class->getFilename())
&& filemtime($path) < filemtime($filename)) {
@unlink($path);
$annot = $this->reader->getMethodAnnotations($method);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
return $this->loadedAnnotations[$key] = include $path;
}
private function saveCacheFile($path, $data)
{
if (!is_writable($this->dir)) {
throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable. Both, the webserver and the console user need access. You can manage access rights for multiple users with "chmod +a". If your system does not support this, check out the acl package.', $this->dir));
}
$tempfile = tempnam($this->dir, uniqid('', true));
if (false === $tempfile) {
throw new \RuntimeException(sprintf('Unable to create tempfile in directory: %s', $this->dir));
}
$written = file_put_contents($tempfile,'<?php return unserialize('.var_export(serialize($data), true).');');
if (false === $written) {
throw new \RuntimeException(sprintf('Unable to write cached file to: %s', $tempfile));
}
@chmod($tempfile, 0666 & (~$this->umask));
if (false === rename($tempfile, $path)) {
@unlink($tempfile);
throw new \RuntimeException(sprintf('Unable to rename %s to %s', $tempfile, $path));
}
}
public function getClassAnnotation(\ReflectionClass $class, $annotationName)
{
$annotations = $this->getClassAnnotations($class);
foreach ($annotations as $annotation) {
if ($annotation instanceof $annotationName) {
return $annotation;
}
}
return null;
}
public function getMethodAnnotation(\ReflectionMethod $method, $annotationName)
{
$annotations = $this->getMethodAnnotations($method);
foreach ($annotations as $annotation) {
if ($annotation instanceof $annotationName) {
return $annotation;
}
}
return null;
}
public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName)
{
$annotations = $this->getPropertyAnnotations($property);
foreach ($annotations as $annotation) {
if ($annotation instanceof $annotationName) {
return $annotation;
}
}
return null;
}
public function clearLoadedAnnotations()
{
$this->loadedAnnotations = array();
}
}
}
namespace Doctrine\Common\Annotations
{
use SplFileObject;
final class PhpParser
{
public function parseClass(\ReflectionClass $class)
{
if (method_exists($class,'getUseStatements')) {
return $class->getUseStatements();
}
if (false === $filename = $class->getFilename()) {
return array();
}
$content = $this->getFileContent($filename, $class->getStartLine());
if (null === $content) {
return array();
}
$namespace = preg_quote($class->getNamespaceName());
$content = preg_replace('/^.*?(\bnamespace\s+'. $namespace .'\s*[;{].*)$/s','\\1', $content);
$tokenizer = new TokenParser('<?php '. $content);
$statements = $tokenizer->parseUseStatements($class->getNamespaceName());
return $statements;
}
private function getFileContent($filename, $lineNumber)
{
if ( ! is_file($filename)) {
return null;
}
$content ='';
$lineCnt = 0;
$file = new SplFileObject($filename);
while (!$file->eof()) {
if ($lineCnt++ == $lineNumber) {
break;
}
$content .= $file->fgets();
}
return $content;
}
}
}
namespace Doctrine\Common
{
use Doctrine\Common\Lexer\AbstractLexer;
abstract class Lexer extends AbstractLexer
{
}
}
namespace Doctrine\Common\Persistence
{
interface Proxy
{
const MARKER ='__CG__';
const MARKER_LENGTH = 6;
public function __load();
public function __isInitialized();
}
}
namespace Doctrine\Common\Util
{
use Doctrine\Common\Persistence\Proxy;
class ClassUtils
{
public static function getRealClass($class)
{
if (false === $pos = strrpos($class,'\\'.Proxy::MARKER.'\\')) {
return $class;
}
return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
}
public static function getClass($object)
{
return self::getRealClass(get_class($object));
}
public static function getParentClass($className)
{
return get_parent_class( self::getRealClass( $className ) );
}
public static function newReflectionClass($class)
{
return new \ReflectionClass( self::getRealClass( $class ) );
}
public static function newReflectionObject($object)
{
return self::newReflectionClass( self::getClass( $object ) );
}
public static function generateProxyClassName($className, $proxyNamespace)
{
return rtrim($proxyNamespace,'\\') .'\\'.Proxy::MARKER.'\\'. ltrim($className,'\\');
}
}
}
namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter
{
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DateTime;
class DateTimeParamConverter implements ParamConverterInterface
{
public function apply(Request $request, ParamConverter $configuration)
{
$param = $configuration->getName();
if (!$request->attributes->has($param)) {
return false;
}
$options = $configuration->getOptions();
$value = $request->attributes->get($param);
if (!$value && $configuration->isOptional()) {
return false;
}
if (isset($options['format'])) {
$date = DateTime::createFromFormat($options['format'], $value);
if (!$date) {
throw new NotFoundHttpException('Invalid date given.');
}
} else {
if (false === strtotime($value)) {
throw new NotFoundHttpException('Invalid date given.');
}
$date = new DateTime($value);
}
$request->attributes->set($param, $date);
return true;
}
public function supports(ParamConverter $configuration)
{
if (null === $configuration->getClass()) {
return false;
}
return'DateTime'=== $configuration->getClass();
}
}
}
namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter
{
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;
class DoctrineParamConverter implements ParamConverterInterface
{
protected $registry;
public function __construct(ManagerRegistry $registry = null)
{
$this->registry = $registry;
}
public function apply(Request $request, ParamConverter $configuration)
{
$name = $configuration->getName();
$class = $configuration->getClass();
$options = $this->getOptions($configuration);
if (null === $request->attributes->get($name, false)) {
$configuration->setIsOptional(true);
}
if (false === $object = $this->find($class, $request, $options, $name)) {
if (false === $object = $this->findOneBy($class, $request, $options)) {
if ($configuration->isOptional()) {
$object = null;
} else {
throw new \LogicException('Unable to guess how to get a Doctrine instance from the request information.');
}
}
}
if (null === $object && false === $configuration->isOptional()) {
throw new NotFoundHttpException(sprintf('%s object not found.', $class));
}
$request->attributes->set($name, $object);
return true;
}
protected function find($class, Request $request, $options, $name)
{
if ($options['mapping'] || $options['exclude']) {
return false;
}
$id = $this->getIdentifier($request, $options, $name);
if (false === $id || null === $id) {
return false;
}
if (isset($options['repository_method'])) {
$method = $options['repository_method'];
} else {
$method ='find';
}
try {
return $this->getManager($options['entity_manager'], $class)->getRepository($class)->$method($id);
} catch (NoResultException $e) {
return;
}
}
protected function getIdentifier(Request $request, $options, $name)
{
if (isset($options['id'])) {
if (!is_array($options['id'])) {
$name = $options['id'];
} elseif (is_array($options['id'])) {
$id = array();
foreach ($options['id'] as $field) {
$id[$field] = $request->attributes->get($field);
}
return $id;
}
}
if ($request->attributes->has($name)) {
return $request->attributes->get($name);
}
if ($request->attributes->has('id') && !isset($options['id'])) {
return $request->attributes->get('id');
}
return false;
}
protected function findOneBy($class, Request $request, $options)
{
if (!$options['mapping']) {
$keys = $request->attributes->keys();
$options['mapping'] = $keys ? array_combine($keys, $keys) : array();
}
foreach ($options['exclude'] as $exclude) {
unset($options['mapping'][$exclude]);
}
if (!$options['mapping']) {
return false;
}
if (isset($options['id']) && null === $request->attributes->get($options['id'])) {
return false;
}
$criteria = array();
$em = $this->getManager($options['entity_manager'], $class);
$metadata = $em->getClassMetadata($class);
$mapMethodSignature = isset($options['repository_method'])
&& isset($options['map_method_signature'])
&& $options['map_method_signature'] === true;
foreach ($options['mapping'] as $attribute => $field) {
if ($metadata->hasField($field)
|| ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field))
|| $mapMethodSignature) {
$criteria[$field] = $request->attributes->get($attribute);
}
}
if ($options['strip_null']) {
$criteria = array_filter($criteria, function ($value) { return !is_null($value); });
}
if (!$criteria) {
return false;
}
if (isset($options['repository_method'])) {
$repositoryMethod = $options['repository_method'];
} else {
$repositoryMethod ='findOneBy';
}
try {
if ($mapMethodSignature) {
return $this->findDataByMapMethodSignature($em, $class, $repositoryMethod, $criteria);
}
return $em->getRepository($class)->$repositoryMethod($criteria);
} catch (NoResultException $e) {
return;
}
}
private function findDataByMapMethodSignature($em, $class, $repositoryMethod, $criteria)
{
$arguments = array();
$repository = $em->getRepository($class);
$ref = new \ReflectionMethod($repository, $repositoryMethod);
foreach ($ref->getParameters() as $parameter) {
if (array_key_exists($parameter->name, $criteria)) {
$arguments[] = $criteria[$parameter->name];
} elseif ($parameter->isDefaultValueAvailable()) {
$arguments[] = $parameter->getDefaultValue();
} else {
throw new \InvalidArgumentException(sprintf('Repository method "%s::%s" requires that you provide a value for the "$%s" argument.', get_class($repository), $repositoryMethod, $parameter->name));
}
}
return $ref->invokeArgs($repository, $arguments);
}
public function supports(ParamConverter $configuration)
{
if (null === $this->registry || !count($this->registry->getManagers())) {
return false;
}
if (null === $configuration->getClass()) {
return false;
}
$options = $this->getOptions($configuration);
$em = $this->getManager($options['entity_manager'], $configuration->getClass());
if (null === $em) {
return false;
}
return !$em->getMetadataFactory()->isTransient($configuration->getClass());
}
protected function getOptions(ParamConverter $configuration)
{
return array_replace(array('entity_manager'=> null,'exclude'=> array(),'mapping'=> array(),'strip_null'=> false,
), $configuration->getOptions());
}
private function getManager($name, $class)
{
if (null === $name) {
return $this->registry->getManagerForClass($class);
}
return $this->registry->getManager($name);
}
}
}
namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter
{
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
class ParamConverterManager
{
protected $converters = array();
protected $namedConverters = array();
public function apply(Request $request, $configurations)
{
if (is_object($configurations)) {
$configurations = array($configurations);
}
foreach ($configurations as $configuration) {
$this->applyConverter($request, $configuration);
}
}
protected function applyConverter(Request $request, ConfigurationInterface $configuration)
{
$value = $request->attributes->get($configuration->getName());
$className = $configuration->getClass();
if (is_object($value) && $value instanceof $className) {
return;
}
if ($converterName = $configuration->getConverter()) {
if (!isset($this->namedConverters[$converterName])) {
throw new \RuntimeException(sprintf("No converter named '%s' found for conversion of parameter '%s'.",
$converterName, $configuration->getName()
));
}
$converter = $this->namedConverters[$converterName];
if (!$converter->supports($configuration)) {
throw new \RuntimeException(sprintf("Converter '%s' does not support conversion of parameter '%s'.",
$converterName, $configuration->getName()
));
}
$converter->apply($request, $configuration);
return;
}
foreach ($this->all() as $converter) {
if ($converter->supports($configuration)) {
if ($converter->apply($request, $configuration)) {
return;
}
}
}
}
public function add(ParamConverterInterface $converter, $priority = 0, $name = null)
{
if ($priority !== null) {
if (!isset($this->converters[$priority])) {
$this->converters[$priority] = array();
}
$this->converters[$priority][] = $converter;
}
if (null !== $name) {
$this->namedConverters[$name] = $converter;
}
}
public function all()
{
krsort($this->converters);
$converters = array();
foreach ($this->converters as $all) {
$converters = array_merge($converters, $all);
}
return $converters;
}
}
}
namespace Symfony\Component\Form
{
use Symfony\Component\OptionsResolver\OptionsResolver;
interface FormTypeInterface
{
public function buildForm(FormBuilderInterface $builder, array $options);
public function buildView(FormView $view, FormInterface $form, array $options);
public function finishView(FormView $view, FormInterface $form, array $options);
public function configureOptions(OptionsResolver $resolver);
public function getBlockPrefix();
public function getParent();
}
}
namespace Symfony\Component\Form
{
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;
abstract class AbstractType implements FormTypeInterface
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
}
public function buildView(FormView $view, FormInterface $form, array $options)
{
}
public function finishView(FormView $view, FormInterface $form, array $options)
{
}
public function configureOptions(OptionsResolver $resolver)
{
}
public function getBlockPrefix()
{
return StringUtil::fqcnToBlockPrefix(get_class($this));
}
public function getParent()
{
return'Symfony\Component\Form\Extension\Core\Type\FormType';
}
}
}
namespace Sonata\CoreBundle\Form\Type
{
use Sonata\CoreBundle\Form\DataTransformer\BooleanTypeToBooleanTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
class BooleanType extends AbstractType
{
const TYPE_YES = 1;
const TYPE_NO = 2;
public function buildForm(FormBuilderInterface $builder, array $options)
{
if ($options['transform']) {
$builder->addModelTransformer(new BooleanTypeToBooleanTransformer());
}
if ($options['catalogue'] !=='SonataCoreBundle') {
@trigger_error('Option "catalogue" is deprecated since SonataCoreBundle 2.3.10 and will be removed in 3.0. Use option "translation_domain" instead.', E_USER_DEPRECATED);
}
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$choices = array(
self::TYPE_YES =>'label_type_yes',
self::TYPE_NO =>'label_type_no',
);
$defaultOptions = array('transform'=> false,'catalogue'=>'SonataCoreBundle','translation_domain'=> function (Options $options) {
if ($options['catalogue']) {
return $options['catalogue'];
}
return $options['translation_domain'];
},
);
if (method_exists('Symfony\Component\Form\AbstractType','configureOptions')) {
$choices = array_flip($choices);
if (method_exists('Symfony\Component\Form\FormTypeInterface','setDefaultOptions')) {
$defaultOptions['choices_as_values'] = true;
}
}
$defaultOptions['choices'] = $choices;
$resolver->setDefaults($defaultOptions);
}
public function getParent()
{
return method_exists('Symfony\Component\Form\AbstractType','getBlockPrefix') ?'Symfony\Component\Form\Extension\Core\Type\ChoiceType':'choice';
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_boolean';
}
}
}
namespace Sonata\CoreBundle\Form\Type
{
use Sonata\CoreBundle\Form\EventListener\ResizeFormListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
class CollectionType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
$listener = new ResizeFormListener(
$options['type'],
$options['type_options'],
$options['modifiable'],
$options['pre_bind_data_callback']
);
$builder->addEventSubscriber($listener);
}
public function buildView(FormView $view, FormInterface $form, array $options)
{
$view->vars['btn_add'] = $options['btn_add'];
$view->vars['btn_catalogue'] = $options['btn_catalogue'];
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('modifiable'=> false,'type'=>'text','type_options'=> array(),'pre_bind_data_callback'=> null,'btn_add'=>'link_add','btn_catalogue'=>'SonataCoreBundle',
));
}
public function getBlockPrefix()
{
return'sonata_type_collection';
}
public function getName()
{
return $this->getBlockPrefix();
}
}
}
namespace Sonata\CoreBundle\Form\Type
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
class DateRangeType extends AbstractType
{
protected $translator;
public function __construct(TranslatorInterface $translator)
{
$this->translator = $translator;
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
$options['field_options_start'] = array_merge(
array('label'=> $this->translator->trans('date_range_start', array(),'SonataCoreBundle'),
),
$options['field_options_start']
);
$options['field_options_end'] = array_merge(
array('label'=> $this->translator->trans('date_range_end', array(),'SonataCoreBundle'),
),
$options['field_options_end']
);
$builder->add('start', $options['field_type'], array_merge(array('required'=> false), $options['field_options'], $options['field_options_start']));
$builder->add('end', $options['field_type'], array_merge(array('required'=> false), $options['field_options'], $options['field_options_end']));
}
public function getBlockPrefix()
{
return'sonata_type_date_range';
}
public function getName()
{
return $this->getBlockPrefix();
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('field_options'=> array(),'field_options_start'=> array(),'field_options_end'=> array(),'field_type'=>'date',
));
}
}
}
namespace Sonata\CoreBundle\Form\Type
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
class DateTimeRangeType extends AbstractType
{
protected $translator;
public function __construct(TranslatorInterface $translator)
{
$this->translator = $translator;
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
$options['field_options_start'] = array_merge(
array('label'=> $this->translator->trans('date_range_start', array(),'SonataCoreBundle'),
),
$options['field_options_start']
);
$options['field_options_end'] = array_merge(
array('label'=> $this->translator->trans('date_range_end', array(),'SonataCoreBundle'),
),
$options['field_options_end']
);
$builder->add('start', $options['field_type'], array_merge(array('required'=> false), $options['field_options'], $options['field_options_start']));
$builder->add('end', $options['field_type'], array_merge(array('required'=> false), $options['field_options'], $options['field_options_end']));
}
public function getBlockPrefix()
{
return'sonata_type_datetime_range';
}
public function getName()
{
return $this->getBlockPrefix();
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('field_options'=> array(),'field_options_start'=> array(),'field_options_end'=> array(),'field_type'=>'datetime',
));
}
}
}
namespace Sonata\CoreBundle\Form\Type
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
class EqualType extends AbstractType
{
const TYPE_IS_EQUAL = 1;
const TYPE_IS_NOT_EQUAL = 2;
protected $translator;
public function __construct(TranslatorInterface $translator)
{
$this->translator = $translator;
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$choices = array(
self::TYPE_IS_EQUAL => $this->translator->trans('label_type_equals', array(),'SonataCoreBundle'),
self::TYPE_IS_NOT_EQUAL => $this->translator->trans('label_type_not_equals', array(),'SonataCoreBundle'),
);
$defaultOptions = array();
if (method_exists('Symfony\Component\Form\AbstractType','configureOptions')) {
$choices = array_flip($choices);
if (method_exists('Symfony\Component\Form\FormTypeInterface','setDefaultOptions')) {
$defaultOptions['choices_as_values'] = true;
}
}
$defaultOptions['choices'] = $choices;
$resolver->setDefaults($defaultOptions);
}
public function getParent()
{
return method_exists('Symfony\Component\Form\AbstractType','getBlockPrefix') ?'Symfony\Component\Form\Extension\Core\Type\ChoiceType':'choice';
}
public function getBlockPrefix()
{
return'sonata_type_equal';
}
public function getName()
{
return $this->getBlockPrefix();
}
}
}
namespace Sonata\CoreBundle\Form\Type
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
class ImmutableArrayType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
foreach ($options['keys'] as $infos) {
if ($infos instanceof FormBuilderInterface) {
$builder->add($infos);
} else {
list($name, $type, $options) = $infos;
if (is_callable($options)) {
$extra = array_slice($infos, 3);
$options = $options($builder, $name, $type, $extra);
if ($options === null) {
$options = array();
} elseif (!is_array($options)) {
throw new \RuntimeException('the closure must return null or an array');
}
}
$builder->add($name, $type, $options);
}
}
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('keys'=> array(),
));
}
public function getBlockPrefix()
{
return'sonata_type_immutable_array';
}
public function getName()
{
return $this->getBlockPrefix();
}
}
}
namespace Sonata\CoreBundle\Form\Type
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
class TranslatableChoiceType extends AbstractType
{
protected $translator;
public function __construct(TranslatorInterface $translator)
{
@trigger_error('Form type "sonata_type_translatable_choice" is deprecated since SonataCoreBundle 2.2.0 and will be removed in 3.0. Use form type "choice" with "translation_domain" option instead.', E_USER_DEPRECATED);
$this->translator = $translator;
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('catalogue'=>'messages',
));
}
public function buildView(FormView $view, FormInterface $form, array $options)
{
$view->vars['translation_domain'] = $options['catalogue'];
}
public function getParent()
{
return method_exists('Symfony\Component\Form\AbstractType','getBlockPrefix') ?'Symfony\Component\Form\Extension\Core\Type\ChoiceType':'choice';
}
public function getBlockPrefix()
{
return'sonata_type_translatable_choice';
}
public function getName()
{
return $this->getBlockPrefix();
}
}
}
namespace Sonata\BlockBundle\Block
{
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
interface BlockServiceInterface
{
public function execute(BlockContextInterface $blockContext, Response $response = null);
public function getName();
public function setDefaultSettings(OptionsResolverInterface $resolver);
public function load(BlockInterface $block);
public function getJavascripts($media);
public function getStylesheets($media);
public function getCacheKeys(BlockInterface $block);
}
}
namespace Sonata\BlockBundle\Block
{
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\MetadataInterface;
use Sonata\CoreBundle\Validator\ErrorElement;
interface BlockAdminServiceInterface
{
public function buildEditForm(FormMapper $form, BlockInterface $block);
public function buildCreateForm(FormMapper $form, BlockInterface $block);
public function validateBlock(ErrorElement $errorElement, BlockInterface $block);
public function getBlockMetadata($code = null);
}
}
namespace Sonata\BlockBundle\Block
{
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
abstract class AbstractBlockService implements BlockServiceInterface
{
public function setDefaultSettings(OptionsResolverInterface $resolver)
{
$this->configureSettings($resolver);
}
public function configureSettings(OptionsResolver $resolver)
{
}
}
}
namespace Sonata\BlockBundle\Block
{
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
abstract class BaseBlockService extends AbstractBlockService implements BlockAdminServiceInterface
{
protected $name;
protected $templating;
public function __construct($name, EngineInterface $templating)
{
$this->name = $name;
$this->templating = $templating;
}
public function renderResponse($view, array $parameters = array(), Response $response = null)
{
return $this->getTemplating()->renderResponse($view, $parameters, $response);
}
public function renderPrivateResponse($view, array $parameters = array(), Response $response = null)
{
return $this->renderResponse($view, $parameters, $response)
->setTtl(0)
->setPrivate()
;
}
public function getName()
{
return $this->name;
}
public function getTemplating()
{
return $this->templating;
}
public function buildCreateForm(FormMapper $formMapper, BlockInterface $block)
{
$this->buildEditForm($formMapper, $block);
}
public function getCacheKeys(BlockInterface $block)
{
return array('block_id'=> $block->getId(),'updated_at'=> $block->getUpdatedAt() ? $block->getUpdatedAt()->format('U') : strtotime('now'),
);
}
public function prePersist(BlockInterface $block)
{
}
public function postPersist(BlockInterface $block)
{
}
public function preUpdate(BlockInterface $block)
{
}
public function postUpdate(BlockInterface $block)
{
}
public function preRemove(BlockInterface $block)
{
}
public function postRemove(BlockInterface $block)
{
}
public function load(BlockInterface $block)
{
}
public function getJavascripts($media)
{
return array();
}
public function getStylesheets($media)
{
return array();
}
public function execute(BlockContextInterface $blockContext, Response $response = null)
{
return $this->renderResponse($blockContext->getTemplate(), array('block_context'=> $blockContext,'block'=> $blockContext->getBlock(),
), $response);
}
public function buildEditForm(FormMapper $form, BlockInterface $block)
{
}
public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
{
}
public function getBlockMetadata($code = null)
{
return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false,'SonataBlockBundle', array('class'=>'fa fa-file'));
}
}
}
namespace Sonata\BlockBundle\Block\Service
{
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\HttpFoundation\Response;
class EmptyBlockService extends BaseBlockService
{
public function buildEditForm(FormMapper $form, BlockInterface $block)
{
throw new \RuntimeException('Not used, this block renders an empty result if no block document can be found');
}
public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
{
throw new \RuntimeException('Not used, this block renders an empty result if no block document can be found');
}
public function execute(BlockContextInterface $blockContext, Response $response = null)
{
return new Response();
}
}
}
namespace Sonata\BlockBundle\Block\Service
{
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
class RssBlockService extends BaseBlockService
{
public function configureSettings(OptionsResolver $resolver)
{
$resolver->setDefaults(array('url'=> false,'title'=>'Insert the rss title','template'=>'SonataBlockBundle:Block:block_core_rss.html.twig',
));
}
public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
{
$formMapper->add('settings','sonata_type_immutable_array', array('keys'=> array(
array('url','url', array('required'=> false)),
array('title','text', array('required'=> false)),
),
));
}
public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
{
$errorElement
->with('settings[url]')
->assertNotNull(array())
->assertNotBlank()
->end()
->with('settings[title]')
->assertNotNull(array())
->assertNotBlank()
->assertLength(array('max'=> 50))
->end();
}
public function execute(BlockContextInterface $blockContext, Response $response = null)
{
$settings = $blockContext->getSettings();
$feeds = false;
if ($settings['url']) {
$options = array('http'=> array('user_agent'=>'Sonata/RSS Reader','timeout'=> 2,
),
);
$content = @file_get_contents($settings['url'], false, stream_context_create($options));
if ($content) {
try {
$feeds = new \SimpleXMLElement($content);
$feeds = $feeds->channel->item;
} catch (\Exception $e) {
}
}
}
return $this->renderResponse($blockContext->getTemplate(), array('feeds'=> $feeds,'block'=> $blockContext->getBlock(),'settings'=> $settings,
), $response);
}
public function getBlockMetadata($code = null)
{
return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false,'SonataBlockBundle', array('class'=>'fa fa-rss-square',
));
}
}
}
namespace Sonata\BlockBundle\Block\Service
{
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
class MenuBlockService extends BaseBlockService
{
protected $menuProvider;
protected $menus;
public function __construct($name, EngineInterface $templating, MenuProviderInterface $menuProvider, array $menus = array())
{
parent::__construct($name, $templating);
$this->menuProvider = $menuProvider;
$this->menus = $menus;
}
public function execute(BlockContextInterface $blockContext, Response $response = null)
{
$responseSettings = array('menu'=> $this->getMenu($blockContext),'menu_options'=> $this->getMenuOptions($blockContext->getSettings()),'block'=> $blockContext->getBlock(),'context'=> $blockContext,
);
if ('private'=== $blockContext->getSettings('cache_policy')) {
return $this->renderPrivateResponse($blockContext->getTemplate(), $responseSettings, $response);
}
return $this->renderResponse($blockContext->getTemplate(), $responseSettings, $response);
}
public function buildEditForm(FormMapper $form, BlockInterface $block)
{
$form->add('settings','sonata_type_immutable_array', array('keys'=> $this->getFormSettingsKeys(),
));
}
public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
{
if (($name = $block->getSetting('menu_name')) && $name !==''&& !$this->menuProvider->has($name)) {
$errorElement->with('menu_name')
->addViolation('sonata.block.menu.not_existing', array('name'=> $name))
->end();
}
}
public function configureSettings(OptionsResolver $resolver)
{
$resolver->setDefaults(array('title'=> $this->getName(),'cache_policy'=>'public','template'=>'SonataBlockBundle:Block:block_core_menu.html.twig','menu_name'=>'','safe_labels'=> false,'current_class'=>'active','first_class'=> false,'last_class'=> false,'current_uri'=> null,'menu_class'=>'list-group','children_class'=>'list-group-item','menu_template'=> null,
));
}
public function getBlockMetadata($code = null)
{
return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false,'SonataBlockBundle', array('class'=>'fa fa-bars',
));
}
protected function getFormSettingsKeys()
{
return array(
array('title','text', array('required'=> false)),
array('cache_policy','choice', array('choices'=> array('public','private'))),
array('menu_name','choice', array('choices'=> $this->menus,'required'=> false)),
array('safe_labels','checkbox', array('required'=> false)),
array('current_class','text', array('required'=> false)),
array('first_class','text', array('required'=> false)),
array('last_class','text', array('required'=> false)),
array('menu_class','text', array('required'=> false)),
array('children_class','text', array('required'=> false)),
array('menu_template','text', array('required'=> false)),
);
}
protected function getMenu(BlockContextInterface $blockContext)
{
$settings = $blockContext->getSettings();
return $settings['menu_name'];
}
protected function getMenuOptions(array $settings)
{
$mapping = array('current_class'=>'currentClass','first_class'=>'firstClass','last_class'=>'lastClass','safe_labels'=>'allow_safe_labels','menu_template'=>'template',
);
$options = array();
foreach ($settings as $key => $value) {
if (array_key_exists($key, $mapping) && null !== $value) {
$options[$mapping[$key]] = $value;
}
}
return $options;
}
}
}
namespace Sonata\BlockBundle\Block\Service
{
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
class TextBlockService extends BaseBlockService
{
public function execute(BlockContextInterface $blockContext, Response $response = null)
{
return $this->renderResponse($blockContext->getTemplate(), array('block'=> $blockContext->getBlock(),'settings'=> $blockContext->getSettings(),
), $response);
}
public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
{
$formMapper->add('settings','sonata_type_immutable_array', array('keys'=> array(
array('content','textarea', array()),
),
));
}
public function configureSettings(OptionsResolver $resolver)
{
$resolver->setDefaults(array('content'=>'Insert your custom content here','template'=>'SonataBlockBundle:Block:block_core_text.html.twig',
));
}
public function getBlockMetadata($code = null)
{
return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false,'SonataBlockBundle', array('class'=>'fa fa-file-text-o',
));
}
}
}
namespace Sonata\BlockBundle\Exception
{
interface BlockExceptionInterface
{
}
}
namespace Symfony\Component\HttpKernel\Exception
{
interface HttpExceptionInterface
{
public function getStatusCode();
public function getHeaders();
}
}
namespace Symfony\Component\HttpKernel\Exception
{
class HttpException extends \RuntimeException implements HttpExceptionInterface
{
private $statusCode;
private $headers;
public function __construct($statusCode, $message = null, \Exception $previous = null, array $headers = array(), $code = 0)
{
$this->statusCode = $statusCode;
$this->headers = $headers;
parent::__construct($message, $code, $previous);
}
public function getStatusCode()
{
return $this->statusCode;
}
public function getHeaders()
{
return $this->headers;
}
public function setHeaders(array $headers)
{
$this->headers = $headers;
}
}
}
namespace Symfony\Component\HttpKernel\Exception
{
class NotFoundHttpException extends HttpException
{
public function __construct($message = null, \Exception $previous = null, $code = 0)
{
parent::__construct(404, $message, $previous, array(), $code);
}
}
}
namespace Sonata\BlockBundle\Exception
{
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
class BlockNotFoundException extends NotFoundHttpException
{
}
}
namespace Sonata\BlockBundle\Exception\Filter
{
use Sonata\BlockBundle\Model\BlockInterface;
interface FilterInterface
{
public function handle(\Exception $exception, BlockInterface $block);
}
}
namespace Sonata\BlockBundle\Exception\Filter
{
use Sonata\BlockBundle\Model\BlockInterface;
class DebugOnlyFilter implements FilterInterface
{
protected $debug;
public function __construct($debug)
{
$this->debug = $debug;
}
public function handle(\Exception $exception, BlockInterface $block)
{
return $this->debug ? true : false;
}
}
}
namespace Sonata\BlockBundle\Exception\Filter
{
use Sonata\BlockBundle\Model\BlockInterface;
class IgnoreClassFilter implements FilterInterface
{
protected $class;
public function __construct($class)
{
$this->class = $class;
}
public function handle(\Exception $exception, BlockInterface $block)
{
return !$exception instanceof $this->class;
}
}
}
namespace Sonata\BlockBundle\Exception\Filter
{
use Sonata\BlockBundle\Model\BlockInterface;
class KeepAllFilter implements FilterInterface
{
public function handle(\Exception $exception, BlockInterface $block)
{
return true;
}
}
}
namespace Sonata\BlockBundle\Exception\Filter
{
use Sonata\BlockBundle\Model\BlockInterface;
class KeepNoneFilter implements FilterInterface
{
public function handle(\Exception $exception, BlockInterface $block)
{
return false;
}
}
}
namespace Sonata\BlockBundle\Exception\Renderer
{
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
interface RendererInterface
{
public function render(\Exception $exception, BlockInterface $block, Response $response = null);
}
}
namespace Sonata\BlockBundle\Exception\Renderer
{
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
class InlineDebugRenderer implements RendererInterface
{
protected $templating;
protected $template;
protected $forceStyle;
protected $debug;
public function __construct(EngineInterface $templating, $template, $debug, $forceStyle = true)
{
$this->templating = $templating;
$this->template = $template;
$this->debug = $debug;
$this->forceStyle = $forceStyle;
}
public function render(\Exception $exception, BlockInterface $block, Response $response = null)
{
$response = $response ?: new Response();
if (!$this->debug) {
return $response;
}
$flattenException = FlattenException::create($exception);
$code = $flattenException->getStatusCode();
$parameters = array('exception'=> $flattenException,'status_code'=> $code,'status_text'=> isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] :'','logger'=> false,'currentContent'=> false,'block'=> $block,'forceStyle'=> $this->forceStyle,
);
$content = $this->templating->render($this->template, $parameters);
$response->setContent($content);
return $response;
}
}
}
namespace Sonata\BlockBundle\Exception\Renderer
{
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
class InlineRenderer implements RendererInterface
{
protected $templating;
protected $template;
public function __construct(EngineInterface $templating, $template)
{
$this->templating = $templating;
$this->template = $template;
}
public function render(\Exception $exception, BlockInterface $block, Response $response = null)
{
$parameters = array('exception'=> $exception,'block'=> $block,
);
$content = $this->templating->render($this->template, $parameters);
$response = $response ?: new Response();
$response->setContent($content);
return $response;
}
}
}
namespace Sonata\BlockBundle\Exception\Renderer
{
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
class MonkeyThrowRenderer implements RendererInterface
{
public function render(\Exception $banana, BlockInterface $block, Response $response = null)
{
throw $banana;
}
}
}
namespace Sonata\BlockBundle\Form\Type
{
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
class ServiceListType extends AbstractType
{
protected $manager;
public function __construct(BlockServiceManagerInterface $manager)
{
$this->manager = $manager;
}
public function getBlockPrefix()
{
return'sonata_block_service_choice';
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getParent()
{
return'choice';
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$manager = $this->manager;
$resolver->setRequired(array('context',
));
$resolver->setDefaults(array('multiple'=> false,'expanded'=> false,'choices'=> function (Options $options, $previousValue) use ($manager) {
$types = array();
foreach ($manager->getServicesByContext($options['context'], $options['include_containers']) as $code => $service) {
$types[$code] = sprintf('%s - %s', $service->getName(), $code);
}
return $types;
},'preferred_choices'=> array(),'empty_data'=> function (Options $options) {
$multiple = isset($options['multiple']) && $options['multiple'];
$expanded = isset($options['expanded']) && $options['expanded'];
return $multiple || $expanded ? array() :'';
},'empty_value'=> function (Options $options, $previousValue) {
$multiple = isset($options['multiple']) && $options['multiple'];
$expanded = isset($options['expanded']) && $options['expanded'];
return $multiple || $expanded || !isset($previousValue) ? null :'';
},'error_bubbling'=> false,'include_containers'=> false,
));
}
}
}
namespace Sonata\BlockBundle\Model
{
use Doctrine\Common\Collections\Collection;
interface BlockInterface
{
public function setId($id);
public function getId();
public function setName($name);
public function getName();
public function setType($type);
public function getType();
public function setEnabled($enabled);
public function getEnabled();
public function setPosition($position);
public function getPosition();
public function setCreatedAt(\DateTime $createdAt = null);
public function getCreatedAt();
public function setUpdatedAt(\DateTime $updatedAt = null);
public function getUpdatedAt();
public function getTtl();
public function setSettings(array $settings = array());
public function getSettings();
public function setSetting($name, $value);
public function getSetting($name, $default = null);
public function addChildren(BlockInterface $children);
public function getChildren();
public function hasChildren();
public function setParent(BlockInterface $parent = null);
public function getParent();
public function hasParent();
}
}
namespace Sonata\BlockBundle\Model
{
abstract class BaseBlock implements BlockInterface
{
protected $name;
protected $settings;
protected $enabled;
protected $position;
protected $parent;
protected $children;
protected $createdAt;
protected $updatedAt;
protected $type;
protected $ttl;
public function __construct()
{
$this->settings = array();
$this->enabled = false;
$this->children = array();
}
public function __toString()
{
return sprintf('%s ~ #%s', $this->getname(), $this->getId());
}
public function setName($name)
{
$this->name = $name;
}
public function getName()
{
return $this->name;
}
public function setType($type)
{
$this->type = $type;
}
public function getType()
{
return $this->type;
}
public function setSettings(array $settings = array())
{
$this->settings = $settings;
}
public function getSettings()
{
return $this->settings;
}
public function setSetting($name, $value)
{
$this->settings[$name] = $value;
}
public function getSetting($name, $default = null)
{
return isset($this->settings[$name]) ? $this->settings[$name] : $default;
}
public function setEnabled($enabled)
{
$this->enabled = $enabled;
}
public function getEnabled()
{
return $this->enabled;
}
public function setPosition($position)
{
$this->position = $position;
}
public function getPosition()
{
return $this->position;
}
public function setCreatedAt(\DateTime $createdAt = null)
{
$this->createdAt = $createdAt;
}
public function getCreatedAt()
{
return $this->createdAt;
}
public function setUpdatedAt(\DateTime $updatedAt = null)
{
$this->updatedAt = $updatedAt;
}
public function getUpdatedAt()
{
return $this->updatedAt;
}
public function addChildren(BlockInterface $child)
{
$this->children[] = $child;
$child->setParent($this);
}
public function getChildren()
{
return $this->children;
}
public function setParent(BlockInterface $parent = null)
{
$this->parent = $parent;
}
public function getParent()
{
return $this->parent;
}
public function hasParent()
{
return $this->getParent() instanceof self;
}
public function getTtl()
{
if (!$this->getSetting('use_cache', true)) {
return 0;
}
$ttl = $this->getSetting('ttl', 86400);
foreach ($this->getChildren() as $block) {
$blockTtl = $block->getTtl();
$ttl = ($blockTtl < $ttl) ? $blockTtl : $ttl;
}
$this->ttl = $ttl;
return $this->ttl;
}
public function hasChildren()
{
return count($this->children) > 0;
}
}
}
namespace Sonata\BlockBundle\Model
{
class Block extends BaseBlock
{
protected $id;
public function setId($id)
{
$this->id = $id;
}
public function getId()
{
return $this->id;
}
}
}
namespace Sonata\CoreBundle\Model
{
use Doctrine\DBAL\Connection;
interface ManagerInterface
{
public function getClass();
public function findAll();
public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
public function findOneBy(array $criteria, array $orderBy = null);
public function find($id);
public function create();
public function save($entity, $andFlush = true);
public function delete($entity, $andFlush = true);
public function getTableName();
public function getConnection();
}
}
namespace Sonata\CoreBundle\Model
{
use Sonata\DatagridBundle\Pager\PagerInterface;
interface PageableManagerInterface
{
public function getPager(array $criteria, $page, $limit = 10, array $sort = array());
}
}
namespace Sonata\BlockBundle\Model
{
use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\CoreBundle\Model\PageableManagerInterface;
interface BlockManagerInterface extends ManagerInterface, PageableManagerInterface
{
}
}
namespace Sonata\BlockBundle\Model
{
class EmptyBlock extends Block
{
}
}
namespace Sonata\AdminBundle\Admin
{
use Knp\Menu\FactoryInterface as MenuFactoryInterface;
use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorInterface as LegacyValidatorInterface;
interface AdminInterface
{
public function setFormContractor(FormContractorInterface $formContractor);
public function setListBuilder(ListBuilderInterface $listBuilder);
public function getListBuilder();
public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder);
public function getDatagridBuilder();
public function setTranslator(TranslatorInterface $translator);
public function getTranslator();
public function setRequest(Request $request);
public function setConfigurationPool(Pool $pool);
public function setRouteGenerator(RouteGeneratorInterface $routeGenerator);
public function getClass();
public function attachAdminClass(FieldDescriptionInterface $fieldDescription);
public function getDatagrid();
public function setBaseControllerName($baseControllerName);
public function getBaseControllerName();
public function generateObjectUrl($name, $object, array $parameters = array(), $absolute = false);
public function generateUrl($name, array $parameters = array(), $absolute = false);
public function generateMenuUrl($name, array $parameters = array(), $absolute = false);
public function getModelManager();
public function getManagerType();
public function createQuery($context ='list');
public function getFormBuilder();
public function getFormFieldDescription($name);
public function getFormFieldDescriptions();
public function getForm();
public function getRequest();
public function hasRequest();
public function getCode();
public function getBaseCodeRoute();
public function getSecurityInformation();
public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription);
public function getParentFieldDescription();
public function hasParentFieldDescription();
public function trans($id, array $parameters = array(), $domain = null, $locale = null);
public function getRoutes();
public function getRouterIdParameter();
public function getIdParameter();
public function hasRoute($name);
public function hasShowFieldDescription($name);
public function addShowFieldDescription($name, FieldDescriptionInterface $fieldDescription);
public function removeShowFieldDescription($name);
public function addListFieldDescription($name, FieldDescriptionInterface $fieldDescription);
public function removeListFieldDescription($name);
public function hasFilterFieldDescription($name);
public function addFilterFieldDescription($name, FieldDescriptionInterface $fieldDescription);
public function removeFilterFieldDescription($name);
public function getFilterFieldDescriptions();
public function getFilterFieldDescription($name);
public function getList();
public function setSecurityHandler(SecurityHandlerInterface $securityHandler);
public function getSecurityHandler();
public function isGranted($name, $object = null);
public function getUrlsafeIdentifier($entity);
public function getNormalizedIdentifier($entity);
public function id($entity);
public function setValidator($validator);
public function getValidator();
public function getShow();
public function setFormTheme(array $formTheme);
public function getFormTheme();
public function setFilterTheme(array $filterTheme);
public function getFilterTheme();
public function addExtension(AdminExtensionInterface $extension);
public function getExtensions();
public function setMenuFactory(MenuFactoryInterface $menuFactory);
public function getMenuFactory();
public function setRouteBuilder(RouteBuilderInterface $routeBuilder);
public function getRouteBuilder();
public function toString($object);
public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy);
public function getLabelTranslatorStrategy();
public function supportsPreviewMode();
public function addChild(AdminInterface $child);
public function hasChild($code);
public function getChildren();
public function getChild($code);
public function getNewInstance();
public function setUniqid($uniqId);
public function getUniqid();
public function getObject($id);
public function setSubject($subject);
public function getSubject();
public function getListFieldDescription($name);
public function hasListFieldDescription($name);
public function getListFieldDescriptions();
public function getExportFormats();
public function getDataSourceIterator();
public function configure();
public function update($object);
public function create($object);
public function delete($object);
public function preUpdate($object);
public function postUpdate($object);
public function prePersist($object);
public function postPersist($object);
public function preRemove($object);
public function postRemove($object);
public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements);
public function getFilterParameters();
public function hasSubject();
public function validate(ErrorElement $errorElement, $object);
public function showIn($context);
public function createObjectSecurity($object);
public function getParent();
public function setParent(AdminInterface $admin);
public function isChild();
public function getTemplate($name);
public function setTranslationDomain($translationDomain);
public function getTranslationDomain();
public function getFormGroups();
public function setFormGroups(array $formGroups);
public function getFormTabs();
public function setFormTabs(array $formTabs);
public function getShowTabs();
public function setShowTabs(array $showTabs);
public function removeFieldFromFormGroup($key);
public function getShowGroups();
public function setShowGroups(array $showGroups);
public function reorderShowGroup($group, array $keys);
public function addFormFieldDescription($name, FieldDescriptionInterface $fieldDescription);
public function removeFormFieldDescription($name);
public function isAclEnabled();
public function setSubClasses(array $subClasses);
public function hasSubClass($name);
public function hasActiveSubClass();
public function getActiveSubClass();
public function getActiveSubclassCode();
public function getBatchActions();
public function getLabel();
public function getPersistentParameters();
public function getBreadcrumbs($action);
public function setCurrentChild($currentChild);
public function getCurrentChild();
public function getTranslationLabel($label, $context ='', $type ='');
public function buildSideMenu($action, AdminInterface $childAdmin = null);
public function buildTabMenu($action, AdminInterface $childAdmin = null);
public function getObjectMetadata($object);
public function getListModes();
public function setListMode($mode);
public function getListMode();
public function getAccessMapping();
public function checkAccess($action, $object = null);
}
}
namespace Symfony\Component\Security\Acl\Model
{
interface DomainObjectInterface
{
public function getObjectIdentifier();
}
}
namespace Sonata\AdminBundle\Admin
{
use Doctrine\Common\Util\ClassUtils;
use Knp\Menu\FactoryInterface as MenuFactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\CoreBundle\Validator\Constraints\InlineConstraint;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorInterface as LegacyValidatorInterface;
abstract class AbstractAdmin implements AdminInterface, DomainObjectInterface
{
const CONTEXT_MENU ='menu';
const CONTEXT_DASHBOARD ='dashboard';
const CLASS_REGEX ='@
        (?:([A-Za-z0-9]*)\\\)?        # vendor name / app name
        (Bundle\\\)?                  # optional bundle directory
        ([A-Za-z0-9]+?)(?:Bundle)?\\\ # bundle name, with optional suffix
        (
            Entity|Document|Model|PHPCR|CouchDocument|Phpcr|
            Doctrine\\\Orm|Doctrine\\\Phpcr|Doctrine\\\MongoDB|Doctrine\\\CouchDB
        )\\\(.*)@x';
const MOSAIC_ICON_CLASS ='fa fa-th-large fa-fw';
protected $listFieldDescriptions = array();
protected $showFieldDescriptions = array();
protected $formFieldDescriptions = array();
protected $filterFieldDescriptions = array();
protected $maxPerPage = 32;
protected $maxPageLinks = 25;
protected $baseRouteName;
protected $baseRoutePattern;
protected $baseControllerName;
protected $classnameLabel;
protected $translationDomain ='messages';
protected $formOptions = array();
protected $datagridValues = array('_page'=> 1,'_per_page'=> 32,
);
protected $perPageOptions = array(16, 32, 64, 128, 192);
protected $pagerType = Pager::TYPE_DEFAULT;
protected $code;
protected $label;
protected $persistFilters = false;
protected $routes;
protected $subject;
protected $children = array();
protected $parent = null;
protected $baseCodeRoute ='';
protected $parentAssociationMapping = null;
protected $parentFieldDescription;
protected $currentChild = false;
protected $uniqid;
protected $modelManager;
protected $request;
protected $translator;
protected $formContractor;
protected $listBuilder;
protected $showBuilder;
protected $datagridBuilder;
protected $routeBuilder;
protected $datagrid;
protected $routeGenerator;
protected $breadcrumbs = array();
protected $securityHandler = null;
protected $validator = null;
protected $configurationPool;
protected $menu;
protected $menuFactory;
protected $loaded = array('view_fields'=> false,'view_groups'=> false,'routes'=> false,'tab_menu'=> false,
);
protected $formTheme = array();
protected $filterTheme = array();
protected $templates = array();
protected $extensions = array();
protected $labelTranslatorStrategy;
protected $supportsPreviewMode = false;
protected $securityInformation = array();
protected $cacheIsGranted = array();
protected $searchResultActions = array('edit','show');
protected $listModes = array('list'=> array('class'=>'fa fa-list fa-fw',
),'mosaic'=> array('class'=> self::MOSAIC_ICON_CLASS,
),
);
protected $accessMapping = array();
private $class;
private $subClasses = array();
private $list;
private $show;
private $form;
private $filter;
private $cachedBaseRouteName;
private $cachedBaseRoutePattern;
private $formGroups = false;
private $formTabs = false;
private $showGroups = false;
private $showTabs = false;
private $managerType;
private $breadcrumbsBuilder;
public function __construct($code, $class, $baseControllerName)
{
$this->code = $code;
$this->class = $class;
$this->baseControllerName = $baseControllerName;
$this->predefinePerPageOptions();
$this->datagridValues['_per_page'] = $this->maxPerPage;
}
public function getExportFormats()
{
return array('json','xml','csv','xls',
);
}
public function getExportFields()
{
$fields = $this->getModelManager()->getExportFields($this->getClass());
foreach ($this->getExtensions() as $extension) {
if (method_exists($extension,'configureExportFields')) {
$fields = $extension->configureExportFields($this, $fields);
}
}
return $fields;
}
public function getDataSourceIterator()
{
$datagrid = $this->getDatagrid();
$datagrid->buildPager();
return $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
}
public function validate(ErrorElement $errorElement, $object)
{
}
public function initialize()
{
if (!$this->classnameLabel) {
$this->classnameLabel = substr($this->getClass(), strrpos($this->getClass(),'\\') + 1);
}
$this->baseCodeRoute = $this->getCode();
$this->configure();
}
public function configure()
{
}
public function update($object)
{
$this->preUpdate($object);
foreach ($this->extensions as $extension) {
$extension->preUpdate($this, $object);
}
$result = $this->getModelManager()->update($object);
if (null !== $result) {
$object = $result;
}
$this->postUpdate($object);
foreach ($this->extensions as $extension) {
$extension->postUpdate($this, $object);
}
return $object;
}
public function create($object)
{
$this->prePersist($object);
foreach ($this->extensions as $extension) {
$extension->prePersist($this, $object);
}
$result = $this->getModelManager()->create($object);
if (null !== $result) {
$object = $result;
}
$this->postPersist($object);
foreach ($this->extensions as $extension) {
$extension->postPersist($this, $object);
}
$this->createObjectSecurity($object);
return $object;
}
public function delete($object)
{
$this->preRemove($object);
foreach ($this->extensions as $extension) {
$extension->preRemove($this, $object);
}
$this->getSecurityHandler()->deleteObjectSecurity($this, $object);
$this->getModelManager()->delete($object);
$this->postRemove($object);
foreach ($this->extensions as $extension) {
$extension->postRemove($this, $object);
}
}
public function preValidate($object)
{
}
public function preUpdate($object)
{
}
public function postUpdate($object)
{
}
public function prePersist($object)
{
}
public function postPersist($object)
{
}
public function preRemove($object)
{
}
public function postRemove($object)
{
}
public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements)
{
}
public function getFilterParameters()
{
$parameters = array();
if ($this->hasRequest()) {
$filters = $this->request->query->get('filter', array());
if ($this->persistFilters) {
if ($filters == array() && $this->request->query->get('filters') !='reset') {
$filters = $this->request->getSession()->get($this->getCode().'.filter.parameters', array());
} else {
$this->request->getSession()->set($this->getCode().'.filter.parameters', $filters);
}
}
$parameters = array_merge(
$this->getModelManager()->getDefaultSortValues($this->getClass()),
$this->datagridValues,
$filters
);
if (!$this->determinedPerPageValue($parameters['_per_page'])) {
$parameters['_per_page'] = $this->maxPerPage;
}
if ($this->isChild() && $this->getParentAssociationMapping()) {
$name = str_replace('.','__', $this->getParentAssociationMapping());
$parameters[$name] = array('value'=> $this->request->get($this->getParent()->getIdParameter()));
}
}
return $parameters;
}
public function buildDatagrid()
{
if ($this->datagrid) {
return;
}
$filterParameters = $this->getFilterParameters();
if (isset($filterParameters['_sort_by']) && is_string($filterParameters['_sort_by'])) {
if ($this->hasListFieldDescription($filterParameters['_sort_by'])) {
$filterParameters['_sort_by'] = $this->getListFieldDescription($filterParameters['_sort_by']);
} else {
$filterParameters['_sort_by'] = $this->getModelManager()->getNewFieldDescriptionInstance(
$this->getClass(),
$filterParameters['_sort_by'],
array()
);
$this->getListBuilder()->buildField(null, $filterParameters['_sort_by'], $this);
}
}
$this->datagrid = $this->getDatagridBuilder()->getBaseDatagrid($this, $filterParameters);
$this->datagrid->getPager()->setMaxPageLinks($this->maxPageLinks);
$mapper = new DatagridMapper($this->getDatagridBuilder(), $this->datagrid, $this);
$this->configureDatagridFilters($mapper);
if ($this->isChild() && $this->getParentAssociationMapping() && !$mapper->has($this->getParentAssociationMapping())) {
$mapper->add($this->getParentAssociationMapping(), null, array('show_filter'=> false,'label'=> false,'field_type'=>'sonata_type_model_hidden','field_options'=> array('model_manager'=> $this->getModelManager(),
),'operator_type'=>'hidden',
), null, null, array('admin_code'=> $this->getParent()->getCode(),
));
}
foreach ($this->getExtensions() as $extension) {
$extension->configureDatagridFilters($mapper);
}
}
public function getParentAssociationMapping()
{
return $this->parentAssociationMapping;
}
public function getBaseRoutePattern()
{
if (null !== $this->cachedBaseRoutePattern) {
return $this->cachedBaseRoutePattern;
}
if ($this->isChild()) { if (!$this->baseRoutePattern) {
preg_match(self::CLASS_REGEX, $this->class, $matches);
if (!$matches) {
throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', get_class($this)));
}
}
$this->cachedBaseRoutePattern = sprintf('%s/{id}/%s',
$this->getParent()->getBaseRoutePattern(),
$this->baseRoutePattern ?: $this->urlize($matches[5],'-')
);
} elseif ($this->baseRoutePattern) {
$this->cachedBaseRoutePattern = $this->baseRoutePattern;
} else {
preg_match(self::CLASS_REGEX, $this->class, $matches);
if (!$matches) {
throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', get_class($this)));
}
$this->cachedBaseRoutePattern = sprintf('/%s%s/%s',
empty($matches[1]) ?'': $this->urlize($matches[1],'-').'/',
$this->urlize($matches[3],'-'),
$this->urlize($matches[5],'-')
);
}
return $this->cachedBaseRoutePattern;
}
public function getBaseRouteName()
{
if (null !== $this->cachedBaseRouteName) {
return $this->cachedBaseRouteName;
}
if ($this->isChild()) { if (!$this->baseRouteName) {
preg_match(self::CLASS_REGEX, $this->class, $matches);
if (!$matches) {
throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', get_class($this)));
}
}
$this->cachedBaseRouteName = sprintf('%s_%s',
$this->getParent()->getBaseRouteName(),
$this->baseRouteName ?: $this->urlize($matches[5])
);
} elseif ($this->baseRouteName) {
$this->cachedBaseRouteName = $this->baseRouteName;
} else {
preg_match(self::CLASS_REGEX, $this->class, $matches);
if (!$matches) {
throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', get_class($this)));
}
$this->cachedBaseRouteName = sprintf('admin_%s%s_%s',
empty($matches[1]) ?'': $this->urlize($matches[1]).'_',
$this->urlize($matches[3]),
$this->urlize($matches[5])
);
}
return $this->cachedBaseRouteName;
}
public function urlize($word, $sep ='_')
{
return strtolower(preg_replace('/[^a-z0-9_]/i', $sep.'$1', $word));
}
public function getClass()
{
if ($this->hasSubject() && is_object($this->getSubject())) {
return ClassUtils::getClass($this->getSubject());
}
if (!$this->hasActiveSubClass()) {
if (count($this->getSubClasses()) > 0) {
$subject = $this->getSubject();
if ($subject && is_object($subject)) {
return ClassUtils::getClass($subject);
}
}
return $this->class;
}
if ($this->getParentFieldDescription() && $this->hasActiveSubClass()) {
throw new \RuntimeException('Feature not implemented: an embedded admin cannot have subclass');
}
$subClass = $this->getRequest()->query->get('subclass');
return $this->getSubClass($subClass);
}
public function getSubClasses()
{
return $this->subClasses;
}
public function addSubClass($subClass)
{
if (!in_array($subClass, $this->subClasses)) {
$this->subClasses[] = $subClass;
}
}
public function setSubClasses(array $subClasses)
{
$this->subClasses = $subClasses;
}
public function hasSubClass($name)
{
return isset($this->subClasses[$name]);
}
public function hasActiveSubClass()
{
if (count($this->subClasses) > 0 && $this->request) {
return null !== $this->getRequest()->query->get('subclass');
}
return false;
}
public function getActiveSubClass()
{
if (!$this->hasActiveSubClass()) {
return;
}
return $this->getClass();
}
public function getActiveSubclassCode()
{
if (!$this->hasActiveSubClass()) {
return;
}
$subClass = $this->getRequest()->query->get('subclass');
if (!$this->hasSubClass($subClass)) {
return;
}
return $subClass;
}
public function getBatchActions()
{
$actions = array();
if ($this->hasRoute('delete') && $this->isGranted('DELETE')) {
$actions['delete'] = array('label'=>'action_delete','translation_domain'=>'SonataAdminBundle','ask_confirmation'=> true, );
}
$actions = $this->configureBatchActions($actions);
foreach ($this->getExtensions() as $extension) {
if (method_exists($extension,'configureBatchActions')) {
$actions = $extension->configureBatchActions($this, $actions);
}
}
return $actions;
}
public function getRoutes()
{
$this->buildRoutes();
return $this->routes;
}
public function getRouterIdParameter()
{
return $this->isChild() ?'{childId}':'{id}';
}
public function getIdParameter()
{
return $this->isChild() ?'childId':'id';
}
public function hasRoute($name)
{
if (!$this->routeGenerator) {
throw new \RuntimeException('RouteGenerator cannot be null');
}
return $this->routeGenerator->hasAdminRoute($this, $name);
}
public function isCurrentRoute($name, $adminCode = null)
{
if (!$this->hasRequest()) {
return false;
}
$request = $this->getRequest();
$route = $request->get('_route');
if ($adminCode) {
$admin = $this->getConfigurationPool()->getAdminByAdminCode($adminCode);
} else {
$admin = $this;
}
if (!$admin) {
return false;
}
return ($admin->getBaseRouteName().'_'.$name) == $route;
}
public function generateObjectUrl($name, $object, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
{
$parameters['id'] = $this->getUrlsafeIdentifier($object);
return $this->generateUrl($name, $parameters, $absolute);
}
public function generateUrl($name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
{
return $this->routeGenerator->generateUrl($this, $name, $parameters, $absolute);
}
public function generateMenuUrl($name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
{
return $this->routeGenerator->generateMenuUrl($this, $name, $parameters, $absolute);
}
public function setTemplates(array $templates)
{
$this->templates = $templates;
}
public function setTemplate($name, $template)
{
$this->templates[$name] = $template;
}
public function getTemplates()
{
return $this->templates;
}
public function getTemplate($name)
{
if (isset($this->templates[$name])) {
return $this->templates[$name];
}
}
public function getNewInstance()
{
$object = $this->getModelManager()->getModelInstance($this->getClass());
foreach ($this->getExtensions() as $extension) {
$extension->alterNewInstance($this, $object);
}
return $object;
}
public function getFormBuilder()
{
$this->formOptions['data_class'] = $this->getClass();
$formBuilder = $this->getFormContractor()->getFormBuilder(
$this->getUniqid(),
$this->formOptions
);
$this->defineFormBuilder($formBuilder);
return $formBuilder;
}
public function defineFormBuilder(FormBuilderInterface $formBuilder)
{
$mapper = new FormMapper($this->getFormContractor(), $formBuilder, $this);
$this->configureFormFields($mapper);
foreach ($this->getExtensions() as $extension) {
$extension->configureFormFields($mapper);
}
$this->attachInlineValidator();
}
public function attachAdminClass(FieldDescriptionInterface $fieldDescription)
{
$pool = $this->getConfigurationPool();
$adminCode = $fieldDescription->getOption('admin_code');
if ($adminCode !== null) {
$admin = $pool->getAdminByAdminCode($adminCode);
} else {
$admin = $pool->getAdminByClass($fieldDescription->getTargetEntity());
}
if (!$admin) {
return;
}
if ($this->hasRequest()) {
$admin->setRequest($this->getRequest());
}
$fieldDescription->setAssociationAdmin($admin);
}
public function getObject($id)
{
$object = $this->getModelManager()->find($this->getClass(), $id);
foreach ($this->getExtensions() as $extension) {
$extension->alterObject($this, $object);
}
return $object;
}
public function getForm()
{
$this->buildForm();
return $this->form;
}
public function getList()
{
$this->buildList();
return $this->list;
}
public function createQuery($context ='list')
{
if (func_num_args() > 0) {
@trigger_error('The $context argument of '.__METHOD__.' is deprecated since 3.3, to be removed in 4.0.',
E_USER_DEPRECATED
);
}
$query = $this->getModelManager()->createQuery($this->class);
foreach ($this->extensions as $extension) {
$extension->configureQuery($this, $query, $context);
}
return $query;
}
public function getDatagrid()
{
$this->buildDatagrid();
return $this->datagrid;
}
public function buildTabMenu($action, AdminInterface $childAdmin = null)
{
if ($this->loaded['tab_menu']) {
return;
}
$this->loaded['tab_menu'] = true;
$menu = $this->menuFactory->createItem('root');
$menu->setChildrenAttribute('class','nav navbar-nav');
if (method_exists($menu,'setCurrentUri')) {
$menu->setCurrentUri($this->getRequest()->getBaseUrl().$this->getRequest()->getPathInfo());
}
$this->configureTabMenu($menu, $action, $childAdmin);
foreach ($this->getExtensions() as $extension) {
$extension->configureTabMenu($this, $menu, $action, $childAdmin);
}
$this->menu = $menu;
}
public function buildSideMenu($action, AdminInterface $childAdmin = null)
{
return $this->buildTabMenu($action, $childAdmin);
}
public function getSideMenu($action, AdminInterface $childAdmin = null)
{
if ($this->isChild()) {
return $this->getParent()->getSideMenu($action, $this);
}
$this->buildSideMenu($action, $childAdmin);
return $this->menu;
}
public function getRootCode()
{
return $this->getRoot()->getCode();
}
public function getRoot()
{
$parentFieldDescription = $this->getParentFieldDescription();
if (!$parentFieldDescription) {
return $this;
}
return $parentFieldDescription->getAdmin()->getRoot();
}
public function setBaseControllerName($baseControllerName)
{
$this->baseControllerName = $baseControllerName;
}
public function getBaseControllerName()
{
return $this->baseControllerName;
}
public function setLabel($label)
{
$this->label = $label;
}
public function getLabel()
{
return $this->label;
}
public function setPersistFilters($persist)
{
$this->persistFilters = $persist;
}
public function setMaxPerPage($maxPerPage)
{
$this->maxPerPage = $maxPerPage;
}
public function getMaxPerPage()
{
return $this->maxPerPage;
}
public function setMaxPageLinks($maxPageLinks)
{
$this->maxPageLinks = $maxPageLinks;
}
public function getMaxPageLinks()
{
return $this->maxPageLinks;
}
public function getFormGroups()
{
return $this->formGroups;
}
public function setFormGroups(array $formGroups)
{
$this->formGroups = $formGroups;
}
public function removeFieldFromFormGroup($key)
{
foreach ($this->formGroups as $name => $formGroup) {
unset($this->formGroups[$name]['fields'][$key]);
if (empty($this->formGroups[$name]['fields'])) {
unset($this->formGroups[$name]);
}
}
}
public function reorderFormGroup($group, array $keys)
{
$formGroups = $this->getFormGroups();
$formGroups[$group]['fields'] = array_merge(array_flip($keys), $formGroups[$group]['fields']);
$this->setFormGroups($formGroups);
}
public function getFormTabs()
{
return $this->formTabs;
}
public function setFormTabs(array $formTabs)
{
$this->formTabs = $formTabs;
}
public function getShowTabs()
{
return $this->showTabs;
}
public function setShowTabs(array $showTabs)
{
$this->showTabs = $showTabs;
}
public function getShowGroups()
{
return $this->showGroups;
}
public function setShowGroups(array $showGroups)
{
$this->showGroups = $showGroups;
}
public function reorderShowGroup($group, array $keys)
{
$showGroups = $this->getShowGroups();
$showGroups[$group]['fields'] = array_merge(array_flip($keys), $showGroups[$group]['fields']);
$this->setShowGroups($showGroups);
}
public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription)
{
$this->parentFieldDescription = $parentFieldDescription;
}
public function getParentFieldDescription()
{
return $this->parentFieldDescription;
}
public function hasParentFieldDescription()
{
return $this->parentFieldDescription instanceof FieldDescriptionInterface;
}
public function setSubject($subject)
{
$this->subject = $subject;
}
public function getSubject()
{
if ($this->subject === null && $this->request) {
$id = $this->request->get($this->getIdParameter());
$this->subject = $this->getModelManager()->find($this->class, $id);
}
return $this->subject;
}
public function hasSubject()
{
return $this->subject != null;
}
public function getFormFieldDescriptions()
{
$this->buildForm();
return $this->formFieldDescriptions;
}
public function getFormFieldDescription($name)
{
return $this->hasFormFieldDescription($name) ? $this->formFieldDescriptions[$name] : null;
}
public function hasFormFieldDescription($name)
{
return array_key_exists($name, $this->formFieldDescriptions) ? true : false;
}
public function addFormFieldDescription($name, FieldDescriptionInterface $fieldDescription)
{
$this->formFieldDescriptions[$name] = $fieldDescription;
}
public function removeFormFieldDescription($name)
{
unset($this->formFieldDescriptions[$name]);
}
public function getShowFieldDescriptions()
{
$this->buildShow();
return $this->showFieldDescriptions;
}
public function getShowFieldDescription($name)
{
$this->buildShow();
return $this->hasShowFieldDescription($name) ? $this->showFieldDescriptions[$name] : null;
}
public function hasShowFieldDescription($name)
{
return array_key_exists($name, $this->showFieldDescriptions);
}
public function addShowFieldDescription($name, FieldDescriptionInterface $fieldDescription)
{
$this->showFieldDescriptions[$name] = $fieldDescription;
}
public function removeShowFieldDescription($name)
{
unset($this->showFieldDescriptions[$name]);
}
public function getListFieldDescriptions()
{
$this->buildList();
return $this->listFieldDescriptions;
}
public function getListFieldDescription($name)
{
return $this->hasListFieldDescription($name) ? $this->listFieldDescriptions[$name] : null;
}
public function hasListFieldDescription($name)
{
$this->buildList();
return array_key_exists($name, $this->listFieldDescriptions) ? true : false;
}
public function addListFieldDescription($name, FieldDescriptionInterface $fieldDescription)
{
$this->listFieldDescriptions[$name] = $fieldDescription;
}
public function removeListFieldDescription($name)
{
unset($this->listFieldDescriptions[$name]);
}
public function getFilterFieldDescription($name)
{
return $this->hasFilterFieldDescription($name) ? $this->filterFieldDescriptions[$name] : null;
}
public function hasFilterFieldDescription($name)
{
return array_key_exists($name, $this->filterFieldDescriptions) ? true : false;
}
public function addFilterFieldDescription($name, FieldDescriptionInterface $fieldDescription)
{
$this->filterFieldDescriptions[$name] = $fieldDescription;
}
public function removeFilterFieldDescription($name)
{
unset($this->filterFieldDescriptions[$name]);
}
public function getFilterFieldDescriptions()
{
$this->buildDatagrid();
return $this->filterFieldDescriptions;
}
public function addChild(AdminInterface $child)
{
$this->children[$child->getCode()] = $child;
$child->setBaseCodeRoute($this->getCode().'|'.$child->getCode());
$child->setParent($this);
}
public function hasChild($code)
{
return isset($this->children[$code]);
}
public function getChildren()
{
return $this->children;
}
public function getChild($code)
{
return $this->hasChild($code) ? $this->children[$code] : null;
}
public function setParent(AdminInterface $parent)
{
$this->parent = $parent;
}
public function getParent()
{
return $this->parent;
}
public function isChild()
{
return $this->parent instanceof AdminInterface;
}
public function hasChildren()
{
return count($this->children) > 0;
}
public function setUniqid($uniqid)
{
$this->uniqid = $uniqid;
}
public function getUniqid()
{
if (!$this->uniqid) {
$this->uniqid ='s'.uniqid();
}
return $this->uniqid;
}
public function getClassnameLabel()
{
return $this->classnameLabel;
}
public function getPersistentParameters()
{
$parameters = array();
foreach ($this->getExtensions() as $extension) {
$params = $extension->getPersistentParameters($this);
if (!is_array($params)) {
throw new \RuntimeException(sprintf('The %s::getPersistentParameters must return an array', get_class($extension)));
}
$parameters = array_merge($parameters, $params);
}
return $parameters;
}
public function getPersistentParameter($name)
{
$parameters = $this->getPersistentParameters();
return isset($parameters[$name]) ? $parameters[$name] : null;
}
public function getBreadcrumbs($action)
{
@trigger_error('The '.__METHOD__.' method is deprecated since version 3.2 and will be removed in 4.0.'.' Use Sonata\AdminBundle\Admin\BreadcrumbsBuilder::getBreadcrumbs instead.',
E_USER_DEPRECATED
);
return $this->getBreadcrumbsBuilder()->getBreadcrumbs($this, $action);
}
public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
{
@trigger_error('The '.__METHOD__.' method is deprecated since version 3.2 and will be removed in 4.0.',
E_USER_DEPRECATED
);
if (isset($this->breadcrumbs[$action])) {
return $this->breadcrumbs[$action];
}
return $this->breadcrumbs[$action] = $this->getBreadcrumbsBuilder()
->buildBreadcrumbs($this, $action, $menu);
}
final public function getBreadcrumbsBuilder()
{
@trigger_error('The '.__METHOD__.' method is deprecated since version 3.2 and will be removed in 4.0.'.' Use the sonata.admin.breadcrumbs_builder service instead.',
E_USER_DEPRECATED
);
if ($this->breadcrumbsBuilder === null) {
$this->breadcrumbsBuilder = new BreadcrumbsBuilder();
}
return $this->breadcrumbsBuilder;
}
final public function setBreadcrumbsBuilder(BreadcrumbsBuilderInterface $value)
{
@trigger_error('The '.__METHOD__.' method is deprecated since version 3.2 and will be removed in 4.0.'.' Use the sonata.admin.breadcrumbs_builder service instead.',
E_USER_DEPRECATED
);
$this->breadcrumbsBuilder = $value;
return $this;
}
public function setCurrentChild($currentChild)
{
$this->currentChild = $currentChild;
}
public function getCurrentChild()
{
return $this->currentChild;
}
public function getCurrentChildAdmin()
{
foreach ($this->children as $children) {
if ($children->getCurrentChild()) {
return $children;
}
}
return;
}
public function trans($id, array $parameters = array(), $domain = null, $locale = null)
{
$domain = $domain ?: $this->getTranslationDomain();
if (!$this->translator) {
return $id;
}
return $this->translator->trans($id, $parameters, $domain, $locale);
}
public function transChoice($id, $count, array $parameters = array(), $domain = null, $locale = null)
{
$domain = $domain ?: $this->getTranslationDomain();
if (!$this->translator) {
return $id;
}
return $this->translator->transChoice($id, $count, $parameters, $domain, $locale);
}
public function setTranslationDomain($translationDomain)
{
$this->translationDomain = $translationDomain;
}
public function getTranslationDomain()
{
return $this->translationDomain;
}
public function setTranslator(TranslatorInterface $translator)
{
$this->translator = $translator;
}
public function getTranslator()
{
return $this->translator;
}
public function getTranslationLabel($label, $context ='', $type ='')
{
return $this->getLabelTranslatorStrategy()->getLabel($label, $context, $type);
}
public function setRequest(Request $request)
{
$this->request = $request;
foreach ($this->getChildren() as $children) {
$children->setRequest($request);
}
}
public function getRequest()
{
if (!$this->request) {
throw new \RuntimeException('The Request object has not been set');
}
return $this->request;
}
public function hasRequest()
{
return $this->request !== null;
}
public function setFormContractor(FormContractorInterface $formBuilder)
{
$this->formContractor = $formBuilder;
}
public function getFormContractor()
{
return $this->formContractor;
}
public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder)
{
$this->datagridBuilder = $datagridBuilder;
}
public function getDatagridBuilder()
{
return $this->datagridBuilder;
}
public function setListBuilder(ListBuilderInterface $listBuilder)
{
$this->listBuilder = $listBuilder;
}
public function getListBuilder()
{
return $this->listBuilder;
}
public function setShowBuilder(ShowBuilderInterface $showBuilder)
{
$this->showBuilder = $showBuilder;
}
public function getShowBuilder()
{
return $this->showBuilder;
}
public function setConfigurationPool(Pool $configurationPool)
{
$this->configurationPool = $configurationPool;
}
public function getConfigurationPool()
{
return $this->configurationPool;
}
public function setRouteGenerator(RouteGeneratorInterface $routeGenerator)
{
$this->routeGenerator = $routeGenerator;
}
public function getRouteGenerator()
{
return $this->routeGenerator;
}
public function getCode()
{
return $this->code;
}
public function setBaseCodeRoute($baseCodeRoute)
{
$this->baseCodeRoute = $baseCodeRoute;
}
public function getBaseCodeRoute()
{
return $this->baseCodeRoute;
}
public function getModelManager()
{
return $this->modelManager;
}
public function setModelManager(ModelManagerInterface $modelManager)
{
$this->modelManager = $modelManager;
}
public function getManagerType()
{
return $this->managerType;
}
public function setManagerType($type)
{
$this->managerType = $type;
}
public function getObjectIdentifier()
{
return $this->getCode();
}
public function setSecurityInformation(array $information)
{
$this->securityInformation = $information;
}
public function getSecurityInformation()
{
return $this->securityInformation;
}
public function getPermissionsShow($context)
{
switch ($context) {
case self::CONTEXT_DASHBOARD:
case self::CONTEXT_MENU:
default:
return array('LIST');
}
}
public function showIn($context)
{
switch ($context) {
case self::CONTEXT_DASHBOARD:
case self::CONTEXT_MENU:
default:
return $this->isGranted($this->getPermissionsShow($context));
}
}
public function createObjectSecurity($object)
{
$this->getSecurityHandler()->createObjectSecurity($this, $object);
}
public function setSecurityHandler(SecurityHandlerInterface $securityHandler)
{
$this->securityHandler = $securityHandler;
}
public function getSecurityHandler()
{
return $this->securityHandler;
}
public function isGranted($name, $object = null)
{
$key = md5(json_encode($name).($object ?'/'.spl_object_hash($object) :''));
if (!array_key_exists($key, $this->cacheIsGranted)) {
$this->cacheIsGranted[$key] = $this->securityHandler->isGranted($this, $name, $object ?: $this);
}
return $this->cacheIsGranted[$key];
}
public function getUrlsafeIdentifier($entity)
{
return $this->getModelManager()->getUrlsafeIdentifier($entity);
}
public function getNormalizedIdentifier($entity)
{
return $this->getModelManager()->getNormalizedIdentifier($entity);
}
public function id($entity)
{
return $this->getNormalizedIdentifier($entity);
}
public function setValidator($validator)
{
if (!$validator instanceof ValidatorInterface && !$validator instanceof LegacyValidatorInterface) {
throw new \InvalidArgumentException('Argument 1 must be an instance of Symfony\Component\Validator\Validator\ValidatorInterface or Symfony\Component\Validator\ValidatorInterface');
}
$this->validator = $validator;
}
public function getValidator()
{
return $this->validator;
}
public function getShow()
{
$this->buildShow();
return $this->show;
}
public function setFormTheme(array $formTheme)
{
$this->formTheme = $formTheme;
}
public function getFormTheme()
{
return $this->formTheme;
}
public function setFilterTheme(array $filterTheme)
{
$this->filterTheme = $filterTheme;
}
public function getFilterTheme()
{
return $this->filterTheme;
}
public function addExtension(AdminExtensionInterface $extension)
{
$this->extensions[] = $extension;
}
public function getExtensions()
{
return $this->extensions;
}
public function setMenuFactory(MenuFactoryInterface $menuFactory)
{
$this->menuFactory = $menuFactory;
}
public function getMenuFactory()
{
return $this->menuFactory;
}
public function setRouteBuilder(RouteBuilderInterface $routeBuilder)
{
$this->routeBuilder = $routeBuilder;
}
public function getRouteBuilder()
{
return $this->routeBuilder;
}
public function toString($object)
{
if (!is_object($object)) {
return'';
}
if (method_exists($object,'__toString') && null !== $object->__toString()) {
return (string) $object;
}
return sprintf('%s:%s', ClassUtils::getClass($object), spl_object_hash($object));
}
public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy)
{
$this->labelTranslatorStrategy = $labelTranslatorStrategy;
}
public function getLabelTranslatorStrategy()
{
return $this->labelTranslatorStrategy;
}
public function supportsPreviewMode()
{
return $this->supportsPreviewMode;
}
public function setPerPageOptions(array $options)
{
$this->perPageOptions = $options;
}
public function getPerPageOptions()
{
return $this->perPageOptions;
}
public function setPagerType($pagerType)
{
$this->pagerType = $pagerType;
}
public function getPagerType()
{
return $this->pagerType;
}
public function determinedPerPageValue($perPage)
{
return in_array($perPage, $this->perPageOptions);
}
public function isAclEnabled()
{
return $this->getSecurityHandler() instanceof AclSecurityHandlerInterface;
}
public function getObjectMetadata($object)
{
return new Metadata($this->toString($object));
}
public function getListModes()
{
return $this->listModes;
}
public function setListMode($mode)
{
if (!$this->hasRequest()) {
throw new \RuntimeException(sprintf('No request attached to the current admin: %s', $this->getCode()));
}
$this->getRequest()->getSession()->set(sprintf('%s.list_mode', $this->getCode()), $mode);
}
public function getListMode()
{
if (!$this->hasRequest()) {
return'list';
}
return $this->getRequest()->getSession()->get(sprintf('%s.list_mode', $this->getCode()),'list');
}
public function getAccessMapping()
{
return $this->accessMapping;
}
public function checkAccess($action, $object = null)
{
$access = $this->getAccess();
if (!array_key_exists($action, $access)) {
throw new \InvalidArgumentException(sprintf('Action "%s" could not be found in access mapping. Please make sure your action is defined into your admin class accessMapping property.', $action));
}
if (!is_array($access[$action])) {
$access[$action] = array($access[$action]);
}
foreach ($access[$action] as $role) {
if (false === $this->isGranted($role, $object)) {
throw new AccessDeniedException(sprintf('Access Denied to the action %s and role %s', $action, $role));
}
}
}
public function hasAccess($action, $object = null)
{
$access = $this->getAccess();
if (!array_key_exists($action, $access)) {
return false;
}
if (!is_array($access[$action])) {
$access[$action] = array($access[$action]);
}
foreach ($access[$action] as $role) {
if (false === $this->isGranted($role, $object)) {
return false;
}
}
return true;
}
public function configureActionButtons($action, $object = null)
{
$list = array();
if (in_array($action, array('tree','show','edit','delete','list','batch'))) {
$list['create'] = array('template'=>'SonataAdminBundle:Button:create_button.html.twig',
);
}
if (in_array($action, array('show','delete','acl','history')) && $object) {
$list['edit'] = array('template'=>'SonataAdminBundle:Button:edit_button.html.twig',
);
}
if (in_array($action, array('show','edit','acl')) && $object) {
$list['history'] = array('template'=>'SonataAdminBundle:Button:history_button.html.twig',
);
}
if (in_array($action, array('edit','history')) && $object) {
$list['acl'] = array('template'=>'SonataAdminBundle:Button:acl_button.html.twig',
);
}
if (in_array($action, array('edit','history','acl')) && $object) {
$list['show'] = array('template'=>'SonataAdminBundle:Button:show_button.html.twig',
);
}
if (in_array($action, array('show','edit','delete','acl','batch'))) {
$list['list'] = array('template'=>'SonataAdminBundle:Button:list_button.html.twig',
);
}
return $list;
}
public function getActionButtons($action, $object = null)
{
$list = $this->configureActionButtons($action, $object);
foreach ($this->getExtensions() as $extension) {
if (method_exists($extension,'configureActionButtons')) {
$list = $extension->configureActionButtons($this, $list, $action, $object);
}
}
return $list;
}
public function getDashboardActions()
{
$actions = array();
if ($this->hasRoute('create') && $this->isGranted('CREATE')) {
$actions['create'] = array('label'=>'link_add','translation_domain'=>'SonataAdminBundle','template'=>'SonataAdminBundle:CRUD:dashboard__action_create.html.twig','url'=> $this->generateUrl('create'),'icon'=>'plus-circle',
);
}
if ($this->hasRoute('list') && $this->isGranted('LIST')) {
$actions['list'] = array('label'=>'link_list','translation_domain'=>'SonataAdminBundle','url'=> $this->generateUrl('list'),'icon'=>'list',
);
}
return $actions;
}
final public function showMosaicButton($isShown)
{
if ($isShown) {
$this->listModes['mosaic'] = array('class'=> self::MOSAIC_ICON_CLASS);
} else {
unset($this->listModes['mosaic']);
}
}
final public function getSearchResultLink($object)
{
foreach ($this->searchResultActions as $action) {
if ($this->hasRoute($action) && $this->hasAccess($action, $object)) {
return $this->generateObjectUrl($action, $object);
}
}
return;
}
protected function configureFormFields(FormMapper $form)
{
}
protected function configureListFields(ListMapper $list)
{
}
protected function configureDatagridFilters(DatagridMapper $filter)
{
}
protected function configureShowFields(ShowMapper $show)
{
}
protected function configureRoutes(RouteCollection $collection)
{
}
protected function configureBatchActions($actions)
{
return $actions;
}
protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
{
}
protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
{
$this->configureSideMenu($menu, $action, $childAdmin);
}
protected function buildShow()
{
if ($this->show) {
return;
}
$this->show = new FieldDescriptionCollection();
$mapper = new ShowMapper($this->showBuilder, $this->show, $this);
$this->configureShowFields($mapper);
foreach ($this->getExtensions() as $extension) {
$extension->configureShowFields($mapper);
}
}
protected function buildList()
{
if ($this->list) {
return;
}
$this->list = $this->getListBuilder()->getBaseList();
$mapper = new ListMapper($this->getListBuilder(), $this->list, $this);
if (count($this->getBatchActions()) > 0) {
$fieldDescription = $this->getModelManager()->getNewFieldDescriptionInstance($this->getClass(),'batch', array('label'=>'batch','code'=>'_batch','sortable'=> false,'virtual_field'=> true,
));
$fieldDescription->setAdmin($this);
$fieldDescription->setTemplate($this->getTemplate('batch'));
$mapper->add($fieldDescription,'batch');
}
$this->configureListFields($mapper);
foreach ($this->getExtensions() as $extension) {
$extension->configureListFields($mapper);
}
if ($this->hasRequest() && $this->getRequest()->isXmlHttpRequest()) {
$fieldDescription = $this->getModelManager()->getNewFieldDescriptionInstance($this->getClass(),'select', array('label'=> false,'code'=>'_select','sortable'=> false,'virtual_field'=> false,
));
$fieldDescription->setAdmin($this);
$fieldDescription->setTemplate($this->getTemplate('select'));
$mapper->add($fieldDescription,'select');
}
}
protected function buildForm()
{
if ($this->form) {
return;
}
if ($this->isChild() && $this->getParentAssociationMapping()) {
$parent = $this->getParent()->getObject($this->request->get($this->getParent()->getIdParameter()));
$propertyAccessor = $this->getConfigurationPool()->getPropertyAccessor();
$propertyPath = new PropertyPath($this->getParentAssociationMapping());
$object = $this->getSubject();
$value = $propertyAccessor->getValue($object, $propertyPath);
if (is_array($value) || ($value instanceof \Traversable && $value instanceof \ArrayAccess)) {
$value[] = $parent;
$propertyAccessor->setValue($object, $propertyPath, $value);
} else {
$propertyAccessor->setValue($object, $propertyPath, $parent);
}
}
$this->form = $this->getFormBuilder()->getForm();
}
protected function getSubClass($name)
{
if ($this->hasSubClass($name)) {
return $this->subClasses[$name];
}
throw new \RuntimeException(sprintf('Unable to find the subclass `%s` for admin `%s`', $name, get_class($this)));
}
protected function attachInlineValidator()
{
$admin = $this;
if (method_exists($this->validator,'getMetadataFor')) {
$metadata = $this->validator->getMetadataFor($this->getClass());
} else {
$metadata = $this->validator->getMetadataFactory()->getMetadataFor($this->getClass());
}
$metadata->addConstraint(new InlineConstraint(array('service'=> $this,'method'=> function (ErrorElement $errorElement, $object) use ($admin) {
if ($admin->hasSubject() && spl_object_hash($object) !== spl_object_hash($admin->getSubject())) {
return;
}
$admin->validate($errorElement, $object);
foreach ($admin->getExtensions() as $extension) {
$extension->validate($admin, $errorElement, $object);
}
},'serializingWarning'=> true,
)));
}
protected function predefinePerPageOptions()
{
array_unshift($this->perPageOptions, $this->maxPerPage);
$this->perPageOptions = array_unique($this->perPageOptions);
sort($this->perPageOptions);
}
protected function getAccess()
{
$access = array_merge(array('acl'=>'MASTER','export'=>'EXPORT','historyCompareRevisions'=>'EDIT','historyViewRevision'=>'EDIT','history'=>'EDIT','edit'=>'EDIT','show'=>'VIEW','create'=>'CREATE','delete'=>'DELETE','batchDelete'=>'DELETE','list'=>'LIST',
), $this->getAccessMapping());
foreach ($this->extensions as $extension) {
if (method_exists($extension,'getAccessMapping')) {
$access = array_merge($access, $extension->getAccessMapping($this));
}
}
return $access;
}
private function buildRoutes()
{
if ($this->loaded['routes']) {
return;
}
$this->loaded['routes'] = true;
$this->routes = new RouteCollection(
$this->getBaseCodeRoute(),
$this->getBaseRouteName(),
$this->getBaseRoutePattern(),
$this->getBaseControllerName()
);
$this->routeBuilder->build($this, $this->routes);
$this->configureRoutes($this->routes);
foreach ($this->getExtensions() as $extension) {
$extension->configureRoutes($this, $this->routes);
}
}
}
}
namespace Sonata\AdminBundle\Admin
{
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
interface AdminExtensionInterface
{
public function configureFormFields(FormMapper $formMapper);
public function configureListFields(ListMapper $listMapper);
public function configureDatagridFilters(DatagridMapper $datagridMapper);
public function configureShowFields(ShowMapper $showMapper);
public function configureRoutes(AdminInterface $admin, RouteCollection $collection);
public function configureSideMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null);
public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null);
public function validate(AdminInterface $admin, ErrorElement $errorElement, $object);
public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context ='list');
public function alterNewInstance(AdminInterface $admin, $object);
public function alterObject(AdminInterface $admin, $object);
public function getPersistentParameters(AdminInterface $admin);
public function preUpdate(AdminInterface $admin, $object);
public function postUpdate(AdminInterface $admin, $object);
public function prePersist(AdminInterface $admin, $object);
public function postPersist(AdminInterface $admin, $object);
public function preRemove(AdminInterface $admin, $object);
public function postRemove(AdminInterface $admin, $object);
}
}
namespace Sonata\AdminBundle\Admin
{
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
abstract class AbstractAdminExtension implements AdminExtensionInterface
{
public function configureFormFields(FormMapper $formMapper)
{
}
public function configureListFields(ListMapper $listMapper)
{
}
public function configureDatagridFilters(DatagridMapper $datagridMapper)
{
}
public function configureShowFields(ShowMapper $showMapper)
{
}
public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
{
}
public function configureSideMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
{
}
public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
{
$this->configureSideMenu($admin, $menu, $action, $childAdmin);
}
public function validate(AdminInterface $admin, ErrorElement $errorElement, $object)
{
}
public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context ='list')
{
}
public function alterNewInstance(AdminInterface $admin, $object)
{
}
public function alterObject(AdminInterface $admin, $object)
{
}
public function getPersistentParameters(AdminInterface $admin)
{
return array();
}
public function getAccessMapping(AdminInterface $admin)
{
return array();
}
public function configureBatchActions(AdminInterface $admin, array $actions)
{
return $actions;
}
public function configureExportFields(AdminInterface $admin, array $fields)
{
return $fields;
}
public function preUpdate(AdminInterface $admin, $object)
{
}
public function postUpdate(AdminInterface $admin, $object)
{
}
public function prePersist(AdminInterface $admin, $object)
{
}
public function postPersist(AdminInterface $admin, $object)
{
}
public function preRemove(AdminInterface $admin, $object)
{
}
public function postRemove(AdminInterface $admin, $object)
{
}
public function configureActionButtons(AdminInterface $admin, $list, $action, $object)
{
return $list;
}
}
}
namespace Sonata\AdminBundle\Admin
{
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Sonata\AdminBundle\Util\FormViewIterator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
class AdminHelper
{
protected $pool;
public function __construct(Pool $pool)
{
$this->pool = $pool;
}
public function getChildFormBuilder(FormBuilderInterface $formBuilder, $elementId)
{
foreach (new FormBuilderIterator($formBuilder) as $name => $formBuilder) {
if ($name == $elementId) {
return $formBuilder;
}
}
return;
}
public function getChildFormView(FormView $formView, $elementId)
{
foreach (new \RecursiveIteratorIterator(new FormViewIterator($formView), \RecursiveIteratorIterator::SELF_FIRST) as $name => $formView) {
if ($name === $elementId) {
return $formView;
}
}
return;
}
public function getAdmin($code)
{
return $this->pool->getInstance($code);
}
public function appendFormFieldElement(AdminInterface $admin, $subject, $elementId)
{
$formBuilder = $admin->getFormBuilder();
$form = $formBuilder->getForm();
$form->setData($subject);
$form->handleRequest($admin->getRequest());
$childFormBuilder = $this->getChildFormBuilder($formBuilder, $elementId);
if (!$childFormBuilder) {
$propertyAccessor = $this->pool->getPropertyAccessor();
$entity = $admin->getSubject();
$path = $this->getElementAccessPath($elementId, $entity);
$collection = $propertyAccessor->getValue($entity, $path);
if ($collection instanceof \Doctrine\ORM\PersistentCollection || $collection instanceof \Doctrine\ODM\MongoDB\PersistentCollection) {
$entityClassName = $collection->getTypeClass()->getName();
} elseif ($collection instanceof \Doctrine\Common\Collections\Collection) {
$entityClassName = $this->getEntityClassName($admin, explode('.', preg_replace('#\[\d*?\]#','', $path)));
} else {
throw new \Exception('unknown collection class');
}
$collection->add(new $entityClassName());
$propertyAccessor->setValue($entity, $path, $collection);
$fieldDescription = null;
} else {
$fieldDescription = $admin->getFormFieldDescription($childFormBuilder->getName());
try {
$value = $fieldDescription->getValue($form->getData());
} catch (NoValueException $e) {
$value = null;
}
$data = $admin->getRequest()->get($formBuilder->getName());
if (!isset($data[$childFormBuilder->getName()])) {
$data[$childFormBuilder->getName()] = array();
}
$objectCount = count($value);
$postCount = count($data[$childFormBuilder->getName()]);
$fields = array_keys($fieldDescription->getAssociationAdmin()->getFormFieldDescriptions());
$value = array();
foreach ($fields as $name) {
$value[$name] ='';
}
while ($objectCount < $postCount) {
$this->addNewInstance($form->getData(), $fieldDescription);
++$objectCount;
}
$this->addNewInstance($form->getData(), $fieldDescription);
}
$finalForm = $admin->getFormBuilder()->getForm();
$finalForm->setData($subject);
$finalForm->setData($form->getData());
return array($fieldDescription, $finalForm);
}
public function addNewInstance($object, FieldDescriptionInterface $fieldDescription)
{
$instance = $fieldDescription->getAssociationAdmin()->getNewInstance();
$mapping = $fieldDescription->getAssociationMapping();
$method = sprintf('add%s', Inflector::classify($mapping['fieldName']));
if (!method_exists($object, $method)) {
$method = rtrim($method,'s');
if (!method_exists($object, $method)) {
$method = sprintf('add%s', Inflector::classify(Inflector::singularize($mapping['fieldName'])));
if (!method_exists($object, $method)) {
throw new \RuntimeException(sprintf('Please add a method %s in the %s class!', $method, ClassUtils::getClass($object)));
}
}
}
$object->$method($instance);
}
public function camelize($property)
{
@trigger_error(
sprintf('The %s method is deprecated since 3.1 and will be removed in 4.0. '.'Use \Doctrine\Common\Inflector\Inflector::classify() instead.',
__METHOD__
),
E_USER_DEPRECATED
);
return Inflector::classify($property);
}
public function getElementAccessPath($elementId, $entity)
{
$propertyAccessor = $this->pool->getPropertyAccessor();
$idWithoutIdentifier = preg_replace('/^[^_]*_/','', $elementId);
$initialPath = preg_replace('#(_(\d+)_)#','[$2]_', $idWithoutIdentifier);
$parts = explode('_', $initialPath);
$totalPath ='';
$currentPath ='';
foreach ($parts as $part) {
$currentPath .= empty($currentPath) ? $part :'_'.$part;
$separator = empty($totalPath) ?'':'.';
if ($this->pathExists($propertyAccessor, $entity, $totalPath.$separator.$currentPath)) {
$totalPath .= $separator.$currentPath;
$currentPath ='';
}
}
if (!empty($currentPath)) {
throw new \Exception(sprintf('Could not get element id from %s Failing part: %s', $elementId, $currentPath));
}
return $totalPath;
}
protected function getEntityClassName(AdminInterface $admin, $elements)
{
$element = array_shift($elements);
$associationAdmin = $admin->getFormFieldDescription($element)->getAssociationAdmin();
if (count($elements) == 0) {
return $associationAdmin->getClass();
} else {
return $this->getEntityClassName($associationAdmin, $elements);
}
}
private function pathExists(PropertyAccessorInterface $propertyAccessor, $entity, $path)
{
if (method_exists($propertyAccessor,'isReadable')) {
return $propertyAccessor->isReadable($entity, $path);
}
try {
$propertyAccessor->getValue($entity, $path);
return true;
} catch (NoSuchPropertyException $e) {
return false;
} catch (UnexpectedTypeException $e) {
return false;
}
}
}
}
namespace Sonata\AdminBundle\Admin
{
interface FieldDescriptionInterface
{
public function setFieldName($fieldName);
public function getFieldName();
public function setName($name);
public function getName();
public function getOption($name, $default = null);
public function setOption($name, $value);
public function setOptions(array $options);
public function getOptions();
public function setTemplate($template);
public function getTemplate();
public function setType($type);
public function getType();
public function setParent(AdminInterface $parent);
public function getParent();
public function setAssociationMapping($associationMapping);
public function getAssociationMapping();
public function getTargetEntity();
public function setFieldMapping($fieldMapping);
public function getFieldMapping();
public function setParentAssociationMappings(array $parentAssociationMappings);
public function getParentAssociationMappings();
public function setAssociationAdmin(AdminInterface $associationAdmin);
public function getAssociationAdmin();
public function isIdentifier();
public function getValue($object);
public function setAdmin(AdminInterface $admin);
public function getAdmin();
public function mergeOption($name, array $options = array());
public function mergeOptions(array $options = array());
public function setMappingType($mappingType);
public function getMappingType();
public function getLabel();
public function getTranslationDomain();
public function isSortable();
public function getSortFieldMapping();
public function getSortParentAssociationMapping();
public function getFieldValue($object, $fieldName);
}
}
namespace Sonata\AdminBundle\Admin
{
use Doctrine\Common\Inflector\Inflector;
use Sonata\AdminBundle\Exception\NoValueException;
abstract class BaseFieldDescription implements FieldDescriptionInterface
{
protected $name;
protected $type;
protected $mappingType;
protected $fieldName;
protected $associationMapping;
protected $fieldMapping;
protected $parentAssociationMappings;
protected $template;
protected $options = array();
protected $parent = null;
protected $admin;
protected $associationAdmin;
protected $help;
public function setFieldName($fieldName)
{
$this->fieldName = $fieldName;
}
public function getFieldName()
{
return $this->fieldName;
}
public function setName($name)
{
$this->name = $name;
if (!$this->getFieldName()) {
$this->setFieldName(substr(strrchr('.'.$name,'.'), 1));
}
}
public function getName()
{
return $this->name;
}
public function getOption($name, $default = null)
{
return isset($this->options[$name]) ? $this->options[$name] : $default;
}
public function setOption($name, $value)
{
$this->options[$name] = $value;
}
public function setOptions(array $options)
{
if (isset($options['type'])) {
$this->setType($options['type']);
unset($options['type']);
}
if (isset($options['template'])) {
$this->setTemplate($options['template']);
unset($options['template']);
}
if (isset($options['help'])) {
$this->setHelp($options['help']);
unset($options['help']);
}
if (!isset($options['placeholder'])) {
$options['placeholder'] ='short_object_description_placeholder';
}
if (!isset($options['link_parameters'])) {
$options['link_parameters'] = array();
}
$this->options = $options;
}
public function getOptions()
{
return $this->options;
}
public function setTemplate($template)
{
$this->template = $template;
}
public function getTemplate()
{
return $this->template;
}
public function setType($type)
{
$this->type = $type;
}
public function getType()
{
return $this->type;
}
public function setParent(AdminInterface $parent)
{
$this->parent = $parent;
}
public function getParent()
{
return $this->parent;
}
public function getAssociationMapping()
{
return $this->associationMapping;
}
public function getFieldMapping()
{
return $this->fieldMapping;
}
public function getParentAssociationMappings()
{
return $this->parentAssociationMappings;
}
public function setAssociationAdmin(AdminInterface $associationAdmin)
{
$this->associationAdmin = $associationAdmin;
$this->associationAdmin->setParentFieldDescription($this);
}
public function getAssociationAdmin()
{
return $this->associationAdmin;
}
public function hasAssociationAdmin()
{
return $this->associationAdmin !== null;
}
public function getFieldValue($object, $fieldName)
{
if ($this->isVirtual()) {
return;
}
$camelizedFieldName = Inflector::classify($fieldName);
$getters = array();
$parameters = array();
if ($this->getOption('code')) {
$getters[] = $this->getOption('code');
}
if ($this->getOption('parameters')) {
$parameters = $this->getOption('parameters');
}
$getters[] ='get'.$camelizedFieldName;
$getters[] ='is'.$camelizedFieldName;
foreach ($getters as $getter) {
if (method_exists($object, $getter)) {
return call_user_func_array(array($object, $getter), $parameters);
}
}
if (method_exists($object,'__call')) {
return call_user_func_array(array($object,'__call'), array($fieldName, $parameters));
}
if (isset($object->{$fieldName})) {
return $object->{$fieldName};
}
throw new NoValueException(sprintf('Unable to retrieve the value of `%s`', $this->getName()));
}
public function setAdmin(AdminInterface $admin)
{
$this->admin = $admin;
}
public function getAdmin()
{
return $this->admin;
}
public function mergeOption($name, array $options = array())
{
if (!isset($this->options[$name])) {
$this->options[$name] = array();
}
if (!is_array($this->options[$name])) {
throw new \RuntimeException(sprintf('The key `%s` does not point to an array value', $name));
}
$this->options[$name] = array_merge($this->options[$name], $options);
}
public function mergeOptions(array $options = array())
{
$this->setOptions(array_merge_recursive($this->options, $options));
}
public function setMappingType($mappingType)
{
$this->mappingType = $mappingType;
}
public function getMappingType()
{
return $this->mappingType;
}
public static function camelize($property)
{
@trigger_error(
sprintf('The %s method is deprecated since 3.1 and will be removed in 4.0. '.'Use \Doctrine\Common\Inflector\Inflector::classify() instead.',
__METHOD__
),
E_USER_DEPRECATED
);
return Inflector::classify($property);
}
public function setHelp($help)
{
$this->help = $help;
}
public function getHelp()
{
return $this->help;
}
public function getLabel()
{
return $this->getOption('label');
}
public function isSortable()
{
return false !== $this->getOption('sortable', false);
}
public function getSortFieldMapping()
{
return $this->getOption('sort_field_mapping');
}
public function getSortParentAssociationMapping()
{
return $this->getOption('sort_parent_association_mappings');
}
public function getTranslationDomain()
{
return $this->getOption('translation_domain') ?: $this->getAdmin()->getTranslationDomain();
}
public function isVirtual()
{
return false !== $this->getOption('virtual_field', false);
}
}
}
namespace Sonata\AdminBundle\Admin
{
class FieldDescriptionCollection implements \ArrayAccess, \Countable
{
protected $elements = array();
public function add(FieldDescriptionInterface $fieldDescription)
{
$this->elements[$fieldDescription->getName()] = $fieldDescription;
}
public function getElements()
{
return $this->elements;
}
public function has($name)
{
return array_key_exists($name, $this->elements);
}
public function get($name)
{
if ($this->has($name)) {
return $this->elements[$name];
}
throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
}
public function remove($name)
{
if ($this->has($name)) {
unset($this->elements[$name]);
}
}
public function offsetExists($offset)
{
return $this->has($offset);
}
public function offsetGet($offset)
{
return $this->get($offset);
}
public function offsetSet($offset, $value)
{
throw new \RunTimeException('Cannot set value, use add');
}
public function offsetUnset($offset)
{
$this->remove($offset);
}
public function count()
{
return count($this->elements);
}
public function reorder(array $keys)
{
if ($this->has('batch')) {
array_unshift($keys,'batch');
}
$this->elements = array_merge(array_flip($keys), $this->elements);
}
}
}
namespace Sonata\AdminBundle\Block
{
use Sonata\AdminBundle\Admin\Pool;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
class AdminListBlockService extends BaseBlockService
{
protected $pool;
public function __construct($name, EngineInterface $templating, Pool $pool)
{
parent::__construct($name, $templating);
$this->pool = $pool;
}
public function execute(BlockContextInterface $blockContext, Response $response = null)
{
$dashboardGroups = $this->pool->getDashboardGroups();
$settings = $blockContext->getSettings();
$visibleGroups = array();
foreach ($dashboardGroups as $name => $dashboardGroup) {
if (!$settings['groups'] || in_array($name, $settings['groups'])) {
$visibleGroups[] = $dashboardGroup;
}
}
return $this->renderPrivateResponse($this->pool->getTemplate('list_block'), array('block'=> $blockContext->getBlock(),'settings'=> $settings,'admin_pool'=> $this->pool,'groups'=> $visibleGroups,
), $response);
}
public function getName()
{
return'Admin List';
}
public function configureSettings(OptionsResolver $resolver)
{
$resolver->setDefaults(array('groups'=> false,
));
if (method_exists($resolver,'setNormalizer')) {
$resolver->setAllowedTypes('groups', array('bool','array'));
} else {
$resolver->setAllowedTypes(array('groups'=> array('bool','array'),
));
}
}
}
}
namespace Sonata\AdminBundle\Builder
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
interface BuilderInterface
{
public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription);
}
}
namespace Sonata\AdminBundle\Builder
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
interface DatagridBuilderInterface extends BuilderInterface
{
public function addFilter(DatagridInterface $datagrid, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin);
public function getBaseDatagrid(AdminInterface $admin, array $values = array());
}
}
namespace Sonata\AdminBundle\Builder
{
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
interface FormContractorInterface extends BuilderInterface
{
public function __construct(FormFactoryInterface $formFactory);
public function getFormBuilder($name, array $options = array());
public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription);
}
}
namespace Sonata\AdminBundle\Builder
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
interface ListBuilderInterface extends BuilderInterface
{
public function getBaseList(array $options = array());
public function buildField($type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin);
public function addField(FieldDescriptionCollection $list, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin);
}
}
namespace Sonata\AdminBundle\Builder
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;
interface RouteBuilderInterface
{
public function build(AdminInterface $admin, RouteCollection $collection);
}
}
namespace Sonata\AdminBundle\Builder
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
interface ShowBuilderInterface extends BuilderInterface
{
public function getBaseList(array $options = array());
public function addField(FieldDescriptionCollection $list, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin);
}
}
namespace Sonata\AdminBundle\Datagrid
{
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\Form\FormInterface;
interface DatagridInterface
{
public function getPager();
public function getQuery();
public function getResults();
public function buildPager();
public function addFilter(FilterInterface $filter);
public function getFilters();
public function reorderFilters(array $keys);
public function getValues();
public function getColumns();
public function setValue($name, $operator, $value);
public function getForm();
public function getFilter($name);
public function hasFilter($name);
public function removeFilter($name);
public function hasActiveFilters();
public function hasDisplayableFilters();
}
}
namespace Sonata\AdminBundle\Datagrid
{
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
class Datagrid implements DatagridInterface
{
protected $filters = array();
protected $values;
protected $columns;
protected $pager;
protected $bound = false;
protected $query;
protected $formBuilder;
protected $form;
protected $results;
public function __construct(ProxyQueryInterface $query, FieldDescriptionCollection $columns, PagerInterface $pager, FormBuilderInterface $formBuilder, array $values = array())
{
$this->pager = $pager;
$this->query = $query;
$this->values = $values;
$this->columns = $columns;
$this->formBuilder = $formBuilder;
}
public function getPager()
{
return $this->pager;
}
public function getResults()
{
$this->buildPager();
if (!$this->results) {
$this->results = $this->pager->getResults();
}
return $this->results;
}
public function buildPager()
{
if ($this->bound) {
return;
}
foreach ($this->getFilters() as $name => $filter) {
list($type, $options) = $filter->getRenderSettings();
$this->formBuilder->add($filter->getFormName(), $type, $options);
}
$this->formBuilder->add('_sort_by','hidden');
$this->formBuilder->get('_sort_by')->addViewTransformer(new CallbackTransformer(
function ($value) {
return $value;
},
function ($value) {
return $value instanceof FieldDescriptionInterface ? $value->getName() : $value;
}
));
$this->formBuilder->add('_sort_order','hidden');
$this->formBuilder->add('_page','hidden');
$this->formBuilder->add('_per_page','hidden');
$this->form = $this->formBuilder->getForm();
$this->form->submit($this->values);
$data = $this->form->getData();
foreach ($this->getFilters() as $name => $filter) {
$this->values[$name] = isset($this->values[$name]) ? $this->values[$name] : null;
$filter->apply($this->query, $data[$filter->getFormName()]);
}
if (isset($this->values['_sort_by'])) {
if (!$this->values['_sort_by'] instanceof FieldDescriptionInterface) {
throw new UnexpectedTypeException($this->values['_sort_by'],'FieldDescriptionInterface');
}
if ($this->values['_sort_by']->isSortable()) {
$this->query->setSortBy($this->values['_sort_by']->getSortParentAssociationMapping(), $this->values['_sort_by']->getSortFieldMapping());
$this->query->setSortOrder(isset($this->values['_sort_order']) ? $this->values['_sort_order'] : null);
}
}
$maxPerPage = 25;
if (isset($this->values['_per_page'])) {
if (is_array($this->values['_per_page'])) {
if (isset($this->values['_per_page']['value'])) {
$maxPerPage = $this->values['_per_page']['value'];
}
} else {
$maxPerPage = $this->values['_per_page'];
}
}
$this->pager->setMaxPerPage($maxPerPage);
$page = 1;
if (isset($this->values['_page'])) {
if (is_array($this->values['_page'])) {
if (isset($this->values['_page']['value'])) {
$page = $this->values['_page']['value'];
}
} else {
$page = $this->values['_page'];
}
}
$this->pager->setPage($page);
$this->pager->setQuery($this->query);
$this->pager->init();
$this->bound = true;
}
public function addFilter(FilterInterface $filter)
{
$this->filters[$filter->getName()] = $filter;
}
public function hasFilter($name)
{
return isset($this->filters[$name]);
}
public function removeFilter($name)
{
unset($this->filters[$name]);
}
public function getFilter($name)
{
return $this->hasFilter($name) ? $this->filters[$name] : null;
}
public function getFilters()
{
return $this->filters;
}
public function reorderFilters(array $keys)
{
$this->filters = array_merge(array_flip($keys), $this->filters);
}
public function getValues()
{
return $this->values;
}
public function setValue($name, $operator, $value)
{
$this->values[$name] = array('type'=> $operator,'value'=> $value,
);
}
public function hasActiveFilters()
{
foreach ($this->filters as $name => $filter) {
if ($filter->isActive()) {
return true;
}
}
return false;
}
public function hasDisplayableFilters()
{
foreach ($this->filters as $name => $filter) {
$showFilter = $filter->getOption('show_filter', null);
if (($filter->isActive() && $showFilter === null) || ($showFilter === true)) {
return true;
}
}
return false;
}
public function getColumns()
{
return $this->columns;
}
public function getQuery()
{
return $this->query;
}
public function getForm()
{
$this->buildPager();
return $this->form;
}
}
}
namespace Sonata\AdminBundle\Mapper
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\BuilderInterface;
abstract class BaseMapper
{
protected $admin;
protected $builder;
public function __construct(BuilderInterface $builder, AdminInterface $admin)
{
$this->builder = $builder;
$this->admin = $admin;
}
public function getAdmin()
{
return $this->admin;
}
abstract public function get($key);
abstract public function has($key);
abstract public function remove($key);
abstract public function reorder(array $keys);
}
}
namespace Sonata\AdminBundle\Datagrid
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;
class DatagridMapper extends BaseMapper
{
protected $datagrid;
public function __construct(DatagridBuilderInterface $datagridBuilder, DatagridInterface $datagrid, AdminInterface $admin)
{
parent::__construct($datagridBuilder, $admin);
$this->datagrid = $datagrid;
}
public function add($name, $type = null, array $filterOptions = array(), $fieldType = null, $fieldOptions = null, array $fieldDescriptionOptions = array())
{
if (is_array($fieldOptions)) {
$filterOptions['field_options'] = $fieldOptions;
}
if ($fieldType) {
$filterOptions['field_type'] = $fieldType;
}
if ($name instanceof FieldDescriptionInterface) {
$fieldDescription = $name;
$fieldDescription->mergeOptions($filterOptions);
} elseif (is_string($name)) {
if ($this->admin->hasFilterFieldDescription($name)) {
throw new \RuntimeException(sprintf('Duplicate field name "%s" in datagrid mapper. Names should be unique.', $name));
}
if (!isset($filterOptions['field_name'])) {
$filterOptions['field_name'] = substr(strrchr('.'.$name,'.'), 1);
}
$fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
$this->admin->getClass(),
$name,
array_merge($filterOptions, $fieldDescriptionOptions)
);
} else {
throw new \RuntimeException('Unknown field name in datagrid mapper. Field name should be either of FieldDescriptionInterface interface or string.');
}
$this->builder->addFilter($this->datagrid, $type, $fieldDescription, $this->admin);
return $this;
}
public function get($name)
{
return $this->datagrid->getFilter($name);
}
public function has($key)
{
return $this->datagrid->hasFilter($key);
}
final public function keys()
{
return array_keys($this->datagrid->getFilters());
}
public function remove($key)
{
$this->admin->removeFilterFieldDescription($key);
$this->datagrid->removeFilter($key);
return $this;
}
public function reorder(array $keys)
{
$this->datagrid->reorderFilters($keys);
return $this;
}
}
}
namespace Sonata\AdminBundle\Datagrid
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;
class ListMapper extends BaseMapper
{
protected $list;
public function __construct(ListBuilderInterface $listBuilder, FieldDescriptionCollection $list, AdminInterface $admin)
{
parent::__construct($listBuilder, $admin);
$this->list = $list;
}
public function addIdentifier($name, $type = null, array $fieldDescriptionOptions = array())
{
$fieldDescriptionOptions['identifier'] = true;
if (!isset($fieldDescriptionOptions['route']['name'])) {
$routeName = ($this->admin->isGranted('EDIT') && $this->admin->hasRoute('edit')) ?'edit':'show';
$fieldDescriptionOptions['route']['name'] = $routeName;
}
if (!isset($fieldDescriptionOptions['route']['parameters'])) {
$fieldDescriptionOptions['route']['parameters'] = array();
}
return $this->add($name, $type, $fieldDescriptionOptions);
}
public function add($name, $type = null, array $fieldDescriptionOptions = array())
{
if ($name =='_action'&& $type =='actions') {
if (isset($fieldDescriptionOptions['actions']['view'])) {
@trigger_error('Inline action "view" is deprecated since version 2.2.4 and will be removed in 4.0. Use inline action "show" instead.', E_USER_DEPRECATED);
$fieldDescriptionOptions['actions']['show'] = $fieldDescriptionOptions['actions']['view'];
unset($fieldDescriptionOptions['actions']['view']);
}
}
if (in_array($type, array('actions','batch','select'))) {
$fieldDescriptionOptions['virtual_field'] = true;
}
if ($name instanceof FieldDescriptionInterface) {
$fieldDescription = $name;
$fieldDescription->mergeOptions($fieldDescriptionOptions);
} elseif (is_string($name)) {
if ($this->admin->hasListFieldDescription($name)) {
throw new \RuntimeException(sprintf('Duplicate field name "%s" in list mapper. Names should be unique.', $name));
}
$fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
$this->admin->getClass(),
$name,
$fieldDescriptionOptions
);
} else {
throw new \RuntimeException('Unknown field name in list mapper. Field name should be either of FieldDescriptionInterface interface or string.');
}
if (!$fieldDescription->getLabel()) {
$fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(),'list','label'));
}
$this->builder->addField($this->list, $type, $fieldDescription, $this->admin);
return $this;
}
public function get($name)
{
return $this->list->get($name);
}
public function has($key)
{
return $this->list->has($key);
}
public function remove($key)
{
$this->admin->removeListFieldDescription($key);
$this->list->remove($key);
return $this;
}
final public function keys()
{
return array_keys($this->list->getElements());
}
public function reorder(array $keys)
{
$this->list->reorder($keys);
return $this;
}
}
}
namespace Sonata\AdminBundle\Datagrid
{
interface PagerInterface
{
public function init();
public function getMaxPerPage();
public function setMaxPerPage($max);
public function setPage($page);
public function setQuery($query);
public function getResults();
public function setMaxPageLinks($maxPageLinks);
public function getMaxPageLinks();
}
}
namespace Sonata\AdminBundle\Datagrid
{
abstract class Pager implements \Iterator, \Countable, \Serializable, PagerInterface
{
const TYPE_DEFAULT ='default';
const TYPE_SIMPLE ='simple';
protected $page = 1;
protected $maxPerPage = 0;
protected $lastPage = 1;
protected $nbResults = 0;
protected $cursor = 1;
protected $parameters = array();
protected $currentMaxLink = 1;
protected $maxRecordLimit = false;
protected $maxPageLinks = 0;
protected $results = null;
protected $resultsCounter = 0;
protected $query = null;
protected $countColumn = array('id');
public function __construct($maxPerPage = 10)
{
$this->setMaxPerPage($maxPerPage);
}
public function getCurrentMaxLink()
{
return $this->currentMaxLink;
}
public function getMaxRecordLimit()
{
return $this->maxRecordLimit;
}
public function setMaxRecordLimit($limit)
{
$this->maxRecordLimit = $limit;
}
public function getLinks($nbLinks = null)
{
if ($nbLinks == null) {
$nbLinks = $this->getMaxPageLinks();
}
$links = array();
$tmp = $this->page - floor($nbLinks / 2);
$check = $this->lastPage - $nbLinks + 1;
$limit = $check > 0 ? $check : 1;
$begin = $tmp > 0 ? ($tmp > $limit ? $limit : $tmp) : 1;
$i = (int) $begin;
while ($i < $begin + $nbLinks && $i <= $this->lastPage) {
$links[] = $i++;
}
$this->currentMaxLink = count($links) ? $links[count($links) - 1] : 1;
return $links;
}
public function haveToPaginate()
{
return $this->getMaxPerPage() && $this->getNbResults() > $this->getMaxPerPage();
}
public function getCursor()
{
return $this->cursor;
}
public function setCursor($pos)
{
if ($pos < 1) {
$this->cursor = 1;
} else {
if ($pos > $this->nbResults) {
$this->cursor = $this->nbResults;
} else {
$this->cursor = $pos;
}
}
}
public function getObjectByCursor($pos)
{
$this->setCursor($pos);
return $this->getCurrent();
}
public function getCurrent()
{
return $this->retrieveObject($this->cursor);
}
public function getNext()
{
if ($this->cursor + 1 > $this->nbResults) {
return;
} else {
return $this->retrieveObject($this->cursor + 1);
}
}
public function getPrevious()
{
if ($this->cursor - 1 < 1) {
return;
} else {
return $this->retrieveObject($this->cursor - 1);
}
}
public function getFirstIndice()
{
if ($this->page == 0) {
return 1;
} else {
return ($this->page - 1) * $this->maxPerPage + 1;
}
}
public function getLastIndice()
{
if ($this->page == 0) {
return $this->nbResults;
} else {
if ($this->page * $this->maxPerPage >= $this->nbResults) {
return $this->nbResults;
} else {
return $this->page * $this->maxPerPage;
}
}
}
public function getNbResults()
{
return $this->nbResults;
}
public function getFirstPage()
{
return 1;
}
public function getLastPage()
{
return $this->lastPage;
}
public function getPage()
{
return $this->page;
}
public function getNextPage()
{
return min($this->getPage() + 1, $this->getLastPage());
}
public function getPreviousPage()
{
return max($this->getPage() - 1, $this->getFirstPage());
}
public function setPage($page)
{
$this->page = intval($page);
if ($this->page <= 0) {
$this->page = $this->getMaxPerPage() ? 1 : 0;
}
}
public function getMaxPerPage()
{
return $this->maxPerPage;
}
public function setMaxPerPage($max)
{
if ($max > 0) {
$this->maxPerPage = $max;
if ($this->page == 0) {
$this->page = 1;
}
} else {
if ($max == 0) {
$this->maxPerPage = 0;
$this->page = 0;
} else {
$this->maxPerPage = 1;
if ($this->page == 0) {
$this->page = 1;
}
}
}
}
public function getMaxPageLinks()
{
return $this->maxPageLinks;
}
public function setMaxPageLinks($maxPageLinks)
{
$this->maxPageLinks = $maxPageLinks;
}
public function isFirstPage()
{
return 1 == $this->page;
}
public function isLastPage()
{
return $this->page == $this->lastPage;
}
public function getParameters()
{
return $this->parameters;
}
public function getParameter($name, $default = null)
{
return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
}
public function hasParameter($name)
{
return isset($this->parameters[$name]);
}
public function setParameter($name, $value)
{
$this->parameters[$name] = $value;
}
public function current()
{
if (!$this->isIteratorInitialized()) {
$this->initializeIterator();
}
return current($this->results);
}
public function key()
{
if (!$this->isIteratorInitialized()) {
$this->initializeIterator();
}
return key($this->results);
}
public function next()
{
if (!$this->isIteratorInitialized()) {
$this->initializeIterator();
}
--$this->resultsCounter;
return next($this->results);
}
public function rewind()
{
if (!$this->isIteratorInitialized()) {
$this->initializeIterator();
}
$this->resultsCounter = count($this->results);
return reset($this->results);
}
public function valid()
{
if (!$this->isIteratorInitialized()) {
$this->initializeIterator();
}
return $this->resultsCounter > 0;
}
public function count()
{
return $this->getNbResults();
}
public function serialize()
{
$vars = get_object_vars($this);
unset($vars['query']);
return serialize($vars);
}
public function unserialize($serialized)
{
$array = unserialize($serialized);
foreach ($array as $name => $values) {
$this->$name = $values;
}
}
public function getCountColumn()
{
return $this->countColumn;
}
public function setCountColumn(array $countColumn)
{
return $this->countColumn = $countColumn;
}
public function setQuery($query)
{
$this->query = $query;
}
public function getQuery()
{
return $this->query;
}
protected function setNbResults($nb)
{
$this->nbResults = $nb;
}
protected function setLastPage($page)
{
$this->lastPage = $page;
if ($this->getPage() > $page) {
$this->setPage($page);
}
}
protected function isIteratorInitialized()
{
return null !== $this->results;
}
protected function initializeIterator()
{
$this->results = $this->getResults();
$this->resultsCounter = count($this->results);
}
protected function resetIterator()
{
$this->results = null;
$this->resultsCounter = 0;
}
protected function retrieveObject($offset)
{
$queryForRetrieve = clone $this->getQuery();
$queryForRetrieve
->setFirstResult($offset - 1)
->setMaxResults(1);
$results = $queryForRetrieve->execute();
return $results[0];
}
}
}
namespace Sonata\AdminBundle\Datagrid
{
interface ProxyQueryInterface
{
public function __call($name, $args);
public function execute(array $params = array(), $hydrationMode = null);
public function setSortBy($parentAssociationMappings, $fieldMapping);
public function getSortBy();
public function setSortOrder($sortOrder);
public function getSortOrder();
public function getSingleScalarResult();
public function setFirstResult($firstResult);
public function getFirstResult();
public function setMaxResults($maxResults);
public function getMaxResults();
public function getUniqueParameterId();
public function entityJoin(array $associationMappings);
}
}
namespace Sonata\AdminBundle\Exception
{
class ModelManagerException extends \Exception
{
}
}
namespace Sonata\AdminBundle\Exception
{
class NoValueException extends \Exception
{
}
}
namespace Sonata\CoreBundle\Exporter
{
use Exporter\Source\SourceIteratorInterface;
use Exporter\Writer\CsvWriter;
use Exporter\Writer\JsonWriter;
use Exporter\Writer\XlsWriter;
use Exporter\Writer\XmlWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;
class Exporter
{
public function getResponse($format, $filename, SourceIteratorInterface $source)
{
switch ($format) {
case'xls':
$writer = new XlsWriter('php://output');
$contentType ='application/vnd.ms-excel';
break;
case'xml':
$writer = new XmlWriter('php://output');
$contentType ='text/xml';
break;
case'json':
$writer = new JsonWriter('php://output');
$contentType ='application/json';
break;
case'csv':
$writer = new CsvWriter('php://output',',','"','', true, true);
$contentType ='text/csv';
break;
default:
throw new \RuntimeException('Invalid format');
}
$callback = function () use ($source, $writer) {
$handler = \Exporter\Handler::create($source, $writer);
$handler->export();
};
return new StreamedResponse($callback, 200, array('Content-Type'=> $contentType,'Content-Disposition'=> sprintf('attachment; filename="%s"', $filename),
));
}
}
}
namespace Sonata\AdminBundle\Export
{
use Sonata\CoreBundle\Exporter\Exporter as BaseExporter;
class Exporter extends BaseExporter
{
}
}
namespace Sonata\AdminBundle\Filter
{
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
interface FilterInterface
{
const CONDITION_OR ='OR';
const CONDITION_AND ='AND';
public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value);
public function apply($query, $value);
public function getName();
public function getFormName();
public function getLabel();
public function setLabel($label);
public function getDefaultOptions();
public function getOption($name, $default = null);
public function setOption($name, $value);
public function initialize($name, array $options = array());
public function getFieldName();
public function getParentAssociationMappings();
public function getFieldMapping();
public function getAssociationMapping();
public function getFieldOptions();
public function getFieldOption($name, $default = null);
public function setFieldOption($name, $value);
public function getFieldType();
public function getRenderSettings();
public function isActive();
public function setCondition($condition);
public function getCondition();
public function getTranslationDomain();
}
}
namespace Sonata\AdminBundle\Filter
{
abstract class Filter implements FilterInterface
{
protected $name = null;
protected $value = null;
protected $options = array();
protected $condition;
public function initialize($name, array $options = array())
{
$this->name = $name;
$this->setOptions($options);
}
public function getName()
{
return $this->name;
}
public function getFormName()
{
return str_replace('.','__', $this->name);
}
public function getOption($name, $default = null)
{
if (array_key_exists($name, $this->options)) {
return $this->options[$name];
}
return $default;
}
public function setOption($name, $value)
{
$this->options[$name] = $value;
}
public function getFieldType()
{
return $this->getOption('field_type','text');
}
public function getFieldOptions()
{
return $this->getOption('field_options', array('required'=> false));
}
public function getFieldOption($name, $default = null)
{
if (isset($this->options['field_options'][$name]) && is_array($this->options['field_options'])) {
return $this->options['field_options'][$name];
}
return $default;
}
public function setFieldOption($name, $value)
{
$this->options['field_options'][$name] = $value;
}
public function getLabel()
{
return $this->getOption('label');
}
public function setLabel($label)
{
$this->setOption('label', $label);
}
public function getFieldName()
{
$fieldName = $this->getOption('field_name');
if (!$fieldName) {
throw new \RuntimeException(sprintf('The option `field_name` must be set for field: `%s`', $this->getName()));
}
return $fieldName;
}
public function getParentAssociationMappings()
{
return $this->getOption('parent_association_mappings', array());
}
public function getFieldMapping()
{
$fieldMapping = $this->getOption('field_mapping');
if (!$fieldMapping) {
throw new \RuntimeException(sprintf('The option `field_mapping` must be set for field: `%s`', $this->getName()));
}
return $fieldMapping;
}
public function getAssociationMapping()
{
$associationMapping = $this->getOption('association_mapping');
if (!$associationMapping) {
throw new \RuntimeException(sprintf('The option `association_mapping` must be set for field: `%s`', $this->getName()));
}
return $associationMapping;
}
public function setOptions(array $options)
{
$this->options = array_merge(
array('show_filter'=> null,'advanced_filter'=> true),
$this->getDefaultOptions(),
$options
);
}
public function getOptions()
{
return $this->options;
}
public function setValue($value)
{
$this->value = $value;
}
public function getValue()
{
return $this->value;
}
public function isActive()
{
$values = $this->getValue();
return isset($values['value'])
&& false !== $values['value']
&&''!== $values['value'];
}
public function setCondition($condition)
{
$this->condition = $condition;
}
public function getCondition()
{
return $this->condition;
}
public function getTranslationDomain()
{
return $this->getOption('translation_domain');
}
}
}
namespace Sonata\AdminBundle\Filter
{
interface FilterFactoryInterface
{
public function create($name, $type, array $options = array());
}
}
namespace Sonata\AdminBundle\Filter
{
use Symfony\Component\DependencyInjection\ContainerInterface;
class FilterFactory implements FilterFactoryInterface
{
protected $container;
protected $types;
public function __construct(ContainerInterface $container, array $types = array())
{
$this->container = $container;
$this->types = $types;
}
public function create($name, $type, array $options = array())
{
if (!$type) {
throw new \RunTimeException('The type must be defined');
}
$id = isset($this->types[$type]) ? $this->types[$type] : false;
if (!$id) {
throw new \RunTimeException(sprintf('No attached service to type named `%s`', $type));
}
$filter = $this->container->get($id);
if (!$filter instanceof FilterInterface) {
throw new \RunTimeException(sprintf('The service `%s` must implement `FilterInterface`', $id));
}
$filter->initialize($name, $options);
return $filter;
}
}
}
namespace Symfony\Component\Form
{
use Symfony\Component\Form\Exception\TransformationFailedException;
interface DataTransformerInterface
{
public function transform($value);
public function reverseTransform($value);
}
}
namespace Sonata\AdminBundle\Form\DataTransformer
{
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
class ArrayToModelTransformer implements DataTransformerInterface
{
protected $modelManager;
protected $className;
public function __construct(ModelManagerInterface $modelManager, $className)
{
$this->modelManager = $modelManager;
$this->className = $className;
}
public function reverseTransform($array)
{
if ($array instanceof $this->className) {
return $array;
}
$instance = new $this->className();
if (!is_array($array)) {
return $instance;
}
return $this->modelManager->modelReverseTransform($this->className, $array);
}
public function transform($value)
{
return $value;
}
}
}
namespace Sonata\AdminBundle\Form\DataTransformer
{
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\CoreBundle\Model\Adapter\AdapterInterface;
use Symfony\Component\Form\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
class ModelsToArrayTransformer implements DataTransformerInterface
{
protected $modelManager;
protected $class;
protected $choiceList;
public function __construct($choiceList, ModelManagerInterface $modelManager, $class)
{
if (!$choiceList instanceof ModelChoiceList
&& !$choiceList instanceof ModelChoiceLoader
&& !$choiceList instanceof LazyChoiceList) {
throw new RuntimeException('First param passed to ModelsToArrayTransformer should be instance of
                ModelChoiceLoader or ModelChoiceList or LazyChoiceList');
}
$this->choiceList = $choiceList;
$this->modelManager = $modelManager;
$this->class = $class;
}
public function transform($collection)
{
if (null === $collection) {
return array();
}
$array = array();
foreach ($collection as $key => $entity) {
$id = implode(AdapterInterface::ID_SEPARATOR, $this->getIdentifierValues($entity));
$array[] = $id;
}
return $array;
}
public function reverseTransform($keys)
{
if (!is_array($keys)) {
throw new UnexpectedTypeException($keys,'array');
}
$collection = $this->modelManager->getModelCollectionInstance($this->class);
$notFound = array();
foreach ($keys as $key) {
if ($entity = $this->modelManager->find($this->class, $key)) {
$collection[] = $entity;
} else {
$notFound[] = $key;
}
}
if (count($notFound) > 0) {
throw new TransformationFailedException(sprintf('The entities with keys "%s" could not be found', implode('", "', $notFound)));
}
return $collection;
}
private function getIdentifierValues($entity)
{
try {
return $this->modelManager->getIdentifierValues($entity);
} catch (\Exception $e) {
throw new \InvalidArgumentException(sprintf('Unable to retrieve the identifier values for entity %s', ClassUtils::getClass($entity)), 0, $e);
}
}
}
}
namespace Sonata\AdminBundle\Form\DataTransformer
{
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
class ModelToIdTransformer implements DataTransformerInterface
{
protected $modelManager;
protected $className;
public function __construct(ModelManagerInterface $modelManager, $className)
{
$this->modelManager = $modelManager;
$this->className = $className;
}
public function reverseTransform($newId)
{
if (empty($newId) && !in_array($newId, array('0', 0), true)) {
return;
}
return $this->modelManager->find($this->className, $newId);
}
public function transform($entity)
{
if (empty($entity)) {
return;
}
return $this->modelManager->getNormalizedIdentifier($entity);
}
}
}
namespace Sonata\AdminBundle\Form\EventListener
{
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
class MergeCollectionListener implements EventSubscriberInterface
{
protected $modelManager;
public function __construct(ModelManagerInterface $modelManager)
{
$this->modelManager = $modelManager;
}
public static function getSubscribedEvents()
{
return array(
FormEvents::SUBMIT => array('onBind', 10),
);
}
public function onBind(FormEvent $event)
{
$collection = $event->getForm()->getData();
$data = $event->getData();
$event->stopPropagation();
if (!$collection) {
$collection = $data;
} elseif (count($data) === 0) {
$this->modelManager->collectionClear($collection);
} else {
foreach ($collection as $entity) {
if (!$this->modelManager->collectionHasElement($data, $entity)) {
$this->modelManager->collectionRemoveElement($collection, $entity);
} else {
$this->modelManager->collectionRemoveElement($data, $entity);
}
}
foreach ($data as $entity) {
$this->modelManager->collectionAddElement($collection, $entity);
}
}
$event->setData($collection);
}
}
}
namespace Symfony\Component\Form
{
use Symfony\Component\OptionsResolver\OptionsResolver;
interface FormTypeExtensionInterface
{
public function buildForm(FormBuilderInterface $builder, array $options);
public function buildView(FormView $view, FormInterface $form, array $options);
public function finishView(FormView $view, FormInterface $form, array $options);
public function configureOptions(OptionsResolver $resolver);
public function getExtendedType();
}
}
namespace Symfony\Component\Form
{
use Symfony\Component\OptionsResolver\OptionsResolver;
abstract class AbstractTypeExtension implements FormTypeExtensionInterface
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
}
public function buildView(FormView $view, FormInterface $form, array $options)
{
}
public function finishView(FormView $view, FormInterface $form, array $options)
{
}
public function configureOptions(OptionsResolver $resolver)
{
}
}
}
namespace Sonata\AdminBundle\Form\Extension\Field\Type
{
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Exception\NoValueException;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
class FormTypeFieldExtension extends AbstractTypeExtension
{
protected $defaultClasses = array();
protected $options;
public function __construct(array $defaultClasses, array $options)
{
$this->defaultClasses = $defaultClasses;
$this->options = $options;
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
$sonataAdmin = array('name'=> null,'admin'=> null,'value'=> null,'edit'=>'standard','inline'=>'natural','field_description'=> null,'block_name'=> false,'options'=> $this->options,
);
$builder->setAttribute('sonata_admin_enabled', false);
$builder->setAttribute('sonata_help', false);
if ($options['sonata_field_description'] instanceof FieldDescriptionInterface) {
$fieldDescription = $options['sonata_field_description'];
$sonataAdmin['admin'] = $fieldDescription->getAdmin();
$sonataAdmin['field_description'] = $fieldDescription;
$sonataAdmin['name'] = $fieldDescription->getName();
$sonataAdmin['edit'] = $fieldDescription->getOption('edit','standard');
$sonataAdmin['inline'] = $fieldDescription->getOption('inline','natural');
$sonataAdmin['block_name'] = $fieldDescription->getOption('block_name', false);
$sonataAdmin['class'] = $this->getClass($builder);
$builder->setAttribute('sonata_admin_enabled', true);
}
$builder->setAttribute('sonata_admin', $sonataAdmin);
}
public function buildView(FormView $view, FormInterface $form, array $options)
{
$sonataAdmin = $form->getConfig()->getAttribute('sonata_admin');
if ($view->parent && $view->parent->vars['sonata_admin_enabled'] && !$sonataAdmin['admin']) {
$blockPrefixes = $view->vars['block_prefixes'];
$baseName = str_replace('.','_', $view->parent->vars['sonata_admin_code']);
$baseType = $blockPrefixes[count($blockPrefixes) - 2];
$blockSuffix = preg_replace('#^_([a-z0-9]{14})_(.++)$#','$2', array_pop($blockPrefixes));
$blockPrefixes[] = sprintf('%s_%s', $baseName, $baseType);
$blockPrefixes[] = sprintf('%s_%s_%s_%s', $baseName, $baseType, $view->parent->vars['name'], $view->vars['name']);
$blockPrefixes[] = sprintf('%s_%s_%s_%s', $baseName, $baseType, $view->parent->vars['name'], $blockSuffix);
$view->vars['block_prefixes'] = $blockPrefixes;
$view->vars['sonata_admin_enabled'] = true;
$view->vars['sonata_admin'] = array('admin'=> false,'field_description'=> false,'name'=> false,'edit'=>'standard','inline'=>'natural','block_name'=> false,'class'=> false,'options'=> $this->options,
);
$view->vars['sonata_admin_code'] = $view->parent->vars['sonata_admin_code'];
return;
}
$sonataAdminHelp = isset($options['sonata_help']) ? $options['sonata_help'] : null;
if ($sonataAdmin && $form->getConfig()->getAttribute('sonata_admin_enabled', true)) {
$sonataAdmin['value'] = $form->getData();
$blockPrefixes = $view->vars['block_prefixes'];
$baseName = str_replace('.','_', $sonataAdmin['admin']->getCode());
$baseType = $blockPrefixes[count($blockPrefixes) - 2];
$blockSuffix = preg_replace('#^_([a-z0-9]{14})_(.++)$#','$2', array_pop($blockPrefixes));
$blockPrefixes[] = sprintf('%s_%s', $baseName, $baseType);
$blockPrefixes[] = sprintf('%s_%s_%s', $baseName, $sonataAdmin['name'], $baseType);
$blockPrefixes[] = sprintf('%s_%s_%s_%s', $baseName, $sonataAdmin['name'], $baseType, $blockSuffix);
if (isset($sonataAdmin['block_name']) && $sonataAdmin['block_name'] !== false) {
$blockPrefixes[] = $sonataAdmin['block_name'];
}
$view->vars['block_prefixes'] = $blockPrefixes;
$view->vars['sonata_admin_enabled'] = true;
$view->vars['sonata_admin'] = $sonataAdmin;
$view->vars['sonata_admin_code'] = $sonataAdmin['admin']->getCode();
$attr = $view->vars['attr'];
if (!isset($attr['class']) && isset($sonataAdmin['class'])) {
$attr['class'] = $sonataAdmin['class'];
}
$view->vars['attr'] = $attr;
} else {
$view->vars['sonata_admin_enabled'] = false;
}
$view->vars['sonata_help'] = $sonataAdminHelp;
$view->vars['sonata_admin'] = $sonataAdmin;
}
public function getExtendedType()
{
return
method_exists('Symfony\Component\Form\AbstractType','getBlockPrefix') ?'Symfony\Component\Form\Extension\Core\Type\FormType':'form';
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('sonata_admin'=> null,'sonata_field_description'=> null,'label_render'=> true,'sonata_help'=> null,
));
}
public function getValueFromFieldDescription($object, FieldDescriptionInterface $fieldDescription)
{
$value = null;
if (!$object) {
return $value;
}
try {
$value = $fieldDescription->getValue($object);
} catch (NoValueException $e) {
if ($fieldDescription->getAssociationAdmin()) {
$value = $fieldDescription->getAssociationAdmin()->getNewInstance();
}
}
return $value;
}
protected function getClass(FormBuilderInterface $formBuilder)
{
foreach ($this->getTypes($formBuilder) as $type) {
if (!method_exists($type,'getName')) { $name = get_class($type);
} else {
$name = $type->getName();
}
if (isset($this->defaultClasses[$name])) {
return $this->defaultClasses[$name];
}
}
return'';
}
protected function getTypes(FormBuilderInterface $formBuilder)
{
$types = array();
for ($type = $formBuilder->getType(); null !== $type; $type = $type->getParent()) {
array_unshift($types, $type->getInnerType());
}
return $types;
}
}
}
namespace Sonata\AdminBundle\Mapper
{
abstract class BaseGroupedMapper extends BaseMapper
{
protected $currentGroup;
protected $currentTab;
protected $apply;
public function with($name, array $options = array())
{
$defaultOptions = array('collapsed'=> false,'class'=> false,'description'=> false,'translation_domain'=> null,'name'=> $name,'box_class'=>'box box-primary',
);
$code = $name;
if (array_key_exists('tab', $options) && $options['tab']) {
$tabs = $this->getTabs();
if ($this->currentTab) {
if (isset($tabs[$this->currentTab]['auto_created']) && true === $tabs[$this->currentTab]['auto_created']) {
throw new \RuntimeException('New tab was added automatically when you have added field or group. You should close current tab before adding new one OR add tabs before adding groups and fields.');
} else {
throw new \RuntimeException(sprintf('You should close previous tab "%s" with end() before adding new tab "%s".', $this->currentTab, $name));
}
} elseif ($this->currentGroup) {
throw new \RuntimeException(sprintf('You should open tab before adding new group "%s".', $name));
}
if (!isset($tabs[$name])) {
$tabs[$name] = array();
}
$tabs[$code] = array_merge($defaultOptions, array('auto_created'=> false,'groups'=> array(),
), $tabs[$code], $options);
$this->currentTab = $code;
} else {
if ($this->currentGroup) {
throw new \RuntimeException(sprintf('You should close previous group "%s" with end() before adding new tab "%s".', $this->currentGroup, $name));
}
if (!$this->currentTab) {
$this->with('default', array('tab'=> true,'auto_created'=> true,'translation_domain'=> isset($options['translation_domain']) ? $options['translation_domain'] : null,
)); }
if ($this->currentTab !=='default') {
$code = $this->currentTab.'.'.$name; }
$groups = $this->getGroups();
if (!isset($groups[$code])) {
$groups[$code] = array();
}
$groups[$code] = array_merge($defaultOptions, array('fields'=> array(),
), $groups[$code], $options);
$this->currentGroup = $code;
$this->setGroups($groups);
$tabs = $this->getTabs();
}
if ($this->currentGroup && isset($tabs[$this->currentTab]) && !in_array($this->currentGroup, $tabs[$this->currentTab]['groups'])) {
$tabs[$this->currentTab]['groups'][] = $this->currentGroup;
}
$this->setTabs($tabs);
return $this;
}
public function ifTrue($bool)
{
if ($this->apply !== null) {
throw new \RuntimeException('Cannot nest ifTrue or ifFalse call');
}
$this->apply = ($bool === true);
return $this;
}
public function ifFalse($bool)
{
if ($this->apply !== null) {
throw new \RuntimeException('Cannot nest ifTrue or ifFalse call');
}
$this->apply = ($bool === false);
return $this;
}
public function ifEnd()
{
$this->apply = null;
return $this;
}
public function tab($name, array $options = array())
{
return $this->with($name, array_merge($options, array('tab'=> true)));
}
public function end()
{
if ($this->currentGroup !== null) {
$this->currentGroup = null;
} elseif ($this->currentTab !== null) {
$this->currentTab = null;
} else {
throw new \RuntimeException('No open tabs or groups, you cannot use end()');
}
return $this;
}
public function hasOpenTab()
{
return null !== $this->currentTab;
}
abstract protected function getGroups();
abstract protected function getTabs();
abstract protected function setGroups(array $groups);
abstract protected function setTabs(array $tabs);
protected function addFieldToCurrentGroup($fieldName)
{
$currentGroup = $this->getCurrentGroupName();
$groups = $this->getGroups();
$groups[$currentGroup]['fields'][$fieldName] = $fieldName;
$this->setGroups($groups);
return $groups[$currentGroup];
}
protected function getCurrentGroupName()
{
if (!$this->currentGroup) {
$this->with($this->admin->getLabel(), array('auto_created'=> true));
}
return $this->currentGroup;
}
}
}
namespace Sonata\AdminBundle\Form
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;
use Symfony\Component\Form\FormBuilderInterface;
class FormMapper extends BaseGroupedMapper
{
protected $formBuilder;
public function __construct(FormContractorInterface $formContractor, FormBuilderInterface $formBuilder, AdminInterface $admin)
{
parent::__construct($formContractor, $admin);
$this->formBuilder = $formBuilder;
}
public function reorder(array $keys)
{
$this->admin->reorderFormGroup($this->getCurrentGroupName(), $keys);
return $this;
}
public function add($name, $type = null, array $options = array(), array $fieldDescriptionOptions = array())
{
if ($this->apply !== null && !$this->apply) {
return $this;
}
if ($name instanceof FormBuilderInterface) {
$fieldName = $name->getName();
} else {
$fieldName = $name;
}
if (!$name instanceof FormBuilderInterface && strpos($fieldName,'.') !== false && !isset($options['property_path'])) {
$options['property_path'] = $fieldName;
$fieldName = str_replace('.','__', $fieldName);
}
if ($type ==='collection'|| $type ==='Symfony\Component\Form\Extension\Core\Type\CollectionType') {
$type ='sonata_type_native_collection';
}
$label = $fieldName;
$group = $this->addFieldToCurrentGroup($label);
if (!isset($fieldDescriptionOptions['type']) && is_string($type)) {
$fieldDescriptionOptions['type'] = $type;
}
if ($group['translation_domain'] && !isset($fieldDescriptionOptions['translation_domain'])) {
$fieldDescriptionOptions['translation_domain'] = $group['translation_domain'];
}
$fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
$this->admin->getClass(),
$name instanceof FormBuilderInterface ? $name->getName() : $name,
$fieldDescriptionOptions
);
$this->builder->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);
if ($fieldName != $name) {
$fieldDescription->setName($fieldName);
}
$this->admin->addFormFieldDescription($fieldName, $fieldDescription);
if ($name instanceof FormBuilderInterface) {
$this->formBuilder->add($name);
} else {
$options = array_replace_recursive($this->builder->getDefaultOptions($type, $fieldDescription), $options);
if (!isset($options['label_render'])) {
$options['label_render'] = false;
}
if (!isset($options['label'])) {
$options['label'] = $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(),'form','label');
}
$help = null;
if (isset($options['help'])) {
$help = $options['help'];
unset($options['help']);
}
$this->formBuilder->add($fieldDescription->getName(), $type, $options);
if (null !== $help) {
$this->admin->getFormFieldDescription($fieldDescription->getName())->setHelp($help);
}
}
return $this;
}
public function get($name)
{
return $this->formBuilder->get($name);
}
public function has($key)
{
return $this->formBuilder->has($key);
}
final public function keys()
{
return array_keys($this->formBuilder->all());
}
public function remove($key)
{
$this->admin->removeFormFieldDescription($key);
$this->admin->removeFieldFromFormGroup($key);
$this->formBuilder->remove($key);
return $this;
}
public function removeGroup($group, $tab ='default', $deleteEmptyTab = false)
{
$groups = $this->getGroups();
if ($tab !=='default') {
$group = $tab.'.'.$group;
}
if (isset($groups[$group])) {
foreach ($groups[$group]['fields'] as $field) {
$this->remove($field);
}
}
unset($groups[$group]);
$tabs = $this->getTabs();
$key = array_search($group, $tabs[$tab]['groups']);
if (false !== $key) {
unset($tabs[$tab]['groups'][$key]);
}
if ($deleteEmptyTab && count($tabs[$tab]['groups']) == 0) {
unset($tabs[$tab]);
}
$this->setTabs($tabs);
$this->setGroups($groups);
return $this;
}
public function getFormBuilder()
{
return $this->formBuilder;
}
public function create($name, $type = null, array $options = array())
{
return $this->formBuilder->create($name, $type, $options);
}
public function setHelps(array $helps = array())
{
foreach ($helps as $name => $help) {
$this->addHelp($name, $help);
}
return $this;
}
public function addHelp($name, $help)
{
if ($this->admin->hasFormFieldDescription($name)) {
$this->admin->getFormFieldDescription($name)->setHelp($help);
}
return $this;
}
protected function getGroups()
{
return $this->admin->getFormGroups();
}
protected function setGroups(array $groups)
{
$this->admin->setFormGroups($groups);
}
protected function getTabs()
{
return $this->admin->getFormTabs();
}
protected function setTabs(array $tabs)
{
$this->admin->setFormTabs($tabs);
}
}
}
namespace Sonata\AdminBundle\Form\Type
{
use Doctrine\Common\Collections\Collection;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
class AdminType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
$admin = clone $this->getAdmin($options);
if ($admin->hasParentFieldDescription()) {
$admin->getParentFieldDescription()->setAssociationAdmin($admin);
}
if ($options['delete'] && $admin->isGranted('DELETE')) {
if (!array_key_exists('translation_domain', $options['delete_options']['type_options'])) {
$options['delete_options']['type_options']['translation_domain'] = $admin->getTranslationDomain();
}
$builder->add('_delete', $options['delete_options']['type'], $options['delete_options']['type_options']);
}
if ($builder->getData() === null) {
$p = new PropertyAccessor(false, true);
try {
$parentSubject = $admin->getParentFieldDescription()->getAdmin()->getSubject();
if ($parentSubject !== null && $parentSubject !== false) {
if (!method_exists($p,'isReadable')) {
$subjectCollection = $p->getValue(
$parentSubject,
$this->getFieldDescription($options)->getFieldName()
);
if ($subjectCollection instanceof Collection) {
$subject = $subjectCollection->get(trim($options['property_path'],'[]'));
}
} else {
$subject = $p->getValue(
$parentSubject,
$this->getFieldDescription($options)->getFieldName().$options['property_path']
);
}
$builder->setData($subject);
}
} catch (NoSuchIndexException $e) {
}
}
$admin->setSubject($builder->getData());
$admin->defineFormBuilder($builder);
$builder->addModelTransformer(new ArrayToModelTransformer($admin->getModelManager(), $admin->getClass()));
}
public function buildView(FormView $view, FormInterface $form, array $options)
{
$view->vars['btn_add'] = $options['btn_add'];
$view->vars['btn_list'] = $options['btn_list'];
$view->vars['btn_delete'] = $options['btn_delete'];
$view->vars['btn_catalogue'] = $options['btn_catalogue'];
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('delete'=> function (Options $options) {
return $options['btn_delete'] !== false;
},'delete_options'=> array('type'=>'checkbox','type_options'=> array('required'=> false,'mapped'=> false,
),
),'auto_initialize'=> false,'btn_add'=>'link_add','btn_list'=>'link_list','btn_delete'=>'link_delete','btn_catalogue'=>'SonataAdminBundle',
));
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_admin';
}
protected function getFieldDescription(array $options)
{
if (!isset($options['sonata_field_description'])) {
throw new \RuntimeException('Please provide a valid `sonata_field_description` option');
}
return $options['sonata_field_description'];
}
protected function getAdmin(array $options)
{
return $this->getFieldDescription($options)->getAssociationAdmin();
}
}
}
namespace Sonata\AdminBundle\Form\Type\Filter
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
class ChoiceType extends AbstractType
{
const TYPE_CONTAINS = 1;
const TYPE_NOT_CONTAINS = 2;
const TYPE_EQUAL = 3;
protected $translator;
public function __construct(TranslatorInterface $translator)
{
$this->translator = $translator;
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_filter_choice';
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
$choices = array(
self::TYPE_CONTAINS => $this->translator->trans('label_type_contains', array(),'SonataAdminBundle'),
self::TYPE_NOT_CONTAINS => $this->translator->trans('label_type_not_contains', array(),'SonataAdminBundle'),
self::TYPE_EQUAL => $this->translator->trans('label_type_equals', array(),'SonataAdminBundle'),
);
if (!method_exists('Symfony\Component\Form\FormTypeInterface','setDefaultOptions')) {
$choices = array_flip($choices);
}
$operatorChoices = $options['operator_type'] !=='hidden'? array('choices'=> $choices) : array();
$builder
->add('type', $options['operator_type'], array_merge(array('required'=> false), $options['operator_options'], $operatorChoices))
->add('value', $options['field_type'], array_merge(array('required'=> false), $options['field_options']))
;
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('field_type'=>'choice','field_options'=> array(),'operator_type'=>'choice','operator_options'=> array(),
));
}
}
}
namespace Sonata\AdminBundle\Form\Type\Filter
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
class DateRangeType extends AbstractType
{
const TYPE_BETWEEN = 1;
const TYPE_NOT_BETWEEN = 2;
protected $translator;
public function __construct(TranslatorInterface $translator)
{
$this->translator = $translator;
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_filter_date_range';
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
$choices = array(
self::TYPE_BETWEEN => $this->translator->trans('label_date_type_between', array(),'SonataAdminBundle'),
self::TYPE_NOT_BETWEEN => $this->translator->trans('label_date_type_not_between', array(),'SonataAdminBundle'),
);
if (!method_exists('Symfony\Component\Form\FormTypeInterface','setDefaultOptions')) {
$choices = array_flip($choices);
}
$builder
->add('type','choice', array('choices'=> $choices,'required'=> false))
->add('value', $options['field_type'], $options['field_options'])
;
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('field_type'=>'sonata_type_date_range','field_options'=> array('format'=>'yyyy-MM-dd'),
));
}
}
}
namespace Sonata\AdminBundle\Form\Type\Filter
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
class DateTimeRangeType extends AbstractType
{
const TYPE_BETWEEN = 1;
const TYPE_NOT_BETWEEN = 2;
protected $translator;
public function __construct(TranslatorInterface $translator)
{
$this->translator = $translator;
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_filter_datetime_range';
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
$choices = array(
self::TYPE_BETWEEN => $this->translator->trans('label_date_type_between', array(),'SonataAdminBundle'),
self::TYPE_NOT_BETWEEN => $this->translator->trans('label_date_type_not_between', array(),'SonataAdminBundle'),
);
if (!method_exists('Symfony\Component\Form\FormTypeInterface','setDefaultOptions')) {
$choices = array_flip($choices);
}
$builder
->add('type','choice', array('choices'=> $choices,'required'=> false))
->add('value', $options['field_type'], $options['field_options'])
;
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('field_type'=>'sonata_type_datetime_range','field_options'=> array('date_format'=>'yyyy-MM-dd'),
));
}
}
}
namespace Sonata\AdminBundle\Form\Type\Filter
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
class DateTimeType extends AbstractType
{
const TYPE_GREATER_EQUAL = 1;
const TYPE_GREATER_THAN = 2;
const TYPE_EQUAL = 3;
const TYPE_LESS_EQUAL = 4;
const TYPE_LESS_THAN = 5;
const TYPE_NULL = 6;
const TYPE_NOT_NULL = 7;
protected $translator;
public function __construct(TranslatorInterface $translator)
{
$this->translator = $translator;
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_filter_datetime';
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
$choices = array(
self::TYPE_EQUAL => $this->translator->trans('label_date_type_equal', array(),'SonataAdminBundle'),
self::TYPE_GREATER_EQUAL => $this->translator->trans('label_date_type_greater_equal', array(),'SonataAdminBundle'),
self::TYPE_GREATER_THAN => $this->translator->trans('label_date_type_greater_than', array(),'SonataAdminBundle'),
self::TYPE_LESS_EQUAL => $this->translator->trans('label_date_type_less_equal', array(),'SonataAdminBundle'),
self::TYPE_LESS_THAN => $this->translator->trans('label_date_type_less_than', array(),'SonataAdminBundle'),
self::TYPE_NULL => $this->translator->trans('label_date_type_null', array(),'SonataAdminBundle'),
self::TYPE_NOT_NULL => $this->translator->trans('label_date_type_not_null', array(),'SonataAdminBundle'),
);
if (!method_exists('Symfony\Component\Form\FormTypeInterface','setDefaultOptions')) {
$choices = array_flip($choices);
}
$builder
->add('type','choice', array('choices'=> $choices,'required'=> false))
->add('value', $options['field_type'], array_merge(array('required'=> false), $options['field_options']))
;
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('field_type'=>'datetime','field_options'=> array('date_format'=>'yyyy-MM-dd'),
));
}
}
}
namespace Sonata\AdminBundle\Form\Type\Filter
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Optionsresolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
class DateType extends AbstractType
{
const TYPE_GREATER_EQUAL = 1;
const TYPE_GREATER_THAN = 2;
const TYPE_EQUAL = 3;
const TYPE_LESS_EQUAL = 4;
const TYPE_LESS_THAN = 5;
const TYPE_NULL = 6;
const TYPE_NOT_NULL = 7;
protected $translator;
public function __construct(TranslatorInterface $translator)
{
$this->translator = $translator;
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_filter_date';
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
$choices = array(
self::TYPE_EQUAL => $this->translator->trans('label_date_type_equal', array(),'SonataAdminBundle'),
self::TYPE_GREATER_EQUAL => $this->translator->trans('label_date_type_greater_equal', array(),'SonataAdminBundle'),
self::TYPE_GREATER_THAN => $this->translator->trans('label_date_type_greater_than', array(),'SonataAdminBundle'),
self::TYPE_LESS_EQUAL => $this->translator->trans('label_date_type_less_equal', array(),'SonataAdminBundle'),
self::TYPE_LESS_THAN => $this->translator->trans('label_date_type_less_than', array(),'SonataAdminBundle'),
self::TYPE_NULL => $this->translator->trans('label_date_type_null', array(),'SonataAdminBundle'),
self::TYPE_NOT_NULL => $this->translator->trans('label_date_type_not_null', array(),'SonataAdminBundle'),
);
if (!method_exists('Symfony\Component\Form\FormTypeInterface','setDefaultOptions')) {
$choices = array_flip($choices);
}
$builder
->add('type','choice', array('choices'=> $choices,'required'=> false))
->add('value', $options['field_type'], array_merge(array('required'=> false), $options['field_options']))
;
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('field_type'=>'date','field_options'=> array('date_format'=>'yyyy-MM-dd'),
));
}
}
}
namespace Sonata\AdminBundle\Form\Type\Filter
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
class DefaultType extends AbstractType
{
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_filter_default';
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
$builder
->add('type', $options['operator_type'], array_merge(array('required'=> false), $options['operator_options']))
->add('value', $options['field_type'], array_merge(array('required'=> false), $options['field_options']))
;
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('operator_type'=>'hidden','operator_options'=> array(),'field_type'=>'text','field_options'=> array(),
));
}
}
}
namespace Sonata\AdminBundle\Form\Type\Filter
{
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
class NumberType extends AbstractType
{
const TYPE_GREATER_EQUAL = 1;
const TYPE_GREATER_THAN = 2;
const TYPE_EQUAL = 3;
const TYPE_LESS_EQUAL = 4;
const TYPE_LESS_THAN = 5;
protected $translator;
public function __construct(TranslatorInterface $translator)
{
$this->translator = $translator;
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_filter_number';
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
$choices = array(
self::TYPE_EQUAL => $this->translator->trans('label_type_equal', array(),'SonataAdminBundle'),
self::TYPE_GREATER_EQUAL => $this->translator->trans('label_type_greater_equal', array(),'SonataAdminBundle'),
self::TYPE_GREATER_THAN => $this->translator->trans('label_type_greater_than', array(),'SonataAdminBundle'),
self::TYPE_LESS_EQUAL => $this->translator->trans('label_type_less_equal', array(),'SonataAdminBundle'),
self::TYPE_LESS_THAN => $this->translator->trans('label_type_less_than', array(),'SonataAdminBundle'),
);
if (!method_exists('Symfony\Component\Form\FormTypeInterface','setDefaultOptions')) {
$choices = array_flip($choices);
}
$builder
->add('type','choice', array('choices'=> $choices,'required'=> false))
->add('value', $options['field_type'], array_merge(array('required'=> false), $options['field_options']))
;
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('field_type'=>'number','field_options'=> array(),
));
}
}
}
namespace Sonata\AdminBundle\Form\Type
{
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
class ModelReferenceType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
$builder->addModelTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']));
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('compound'=> false,'model_manager'=> null,'class'=> null,
));
}
public function getParent()
{
return'text';
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_model_reference';
}
}
}
namespace Sonata\AdminBundle\Form\Type
{
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Form\DataTransformer\LegacyModelsToArrayTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Form\EventListener\MergeCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
class ModelType extends AbstractType
{
protected $propertyAccessor;
public function __construct(PropertyAccessorInterface $propertyAccessor)
{
$this->propertyAccessor = $propertyAccessor;
}
public function buildForm(FormBuilderInterface $builder, array $options)
{
if ($options['multiple']) {
if (array_key_exists('choice_loader', $options) && $options['choice_loader'] !== null) { $builder->addViewTransformer(new ModelsToArrayTransformer(
$options['choice_loader'],
$options['model_manager'],
$options['class']), true);
} else {
$builder->addViewTransformer(new LegacyModelsToArrayTransformer($options['choice_list']), true);
}
$builder
->addEventSubscriber(new MergeCollectionListener($options['model_manager']))
;
} else {
$builder
->addViewTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']), true)
;
}
}
public function buildView(FormView $view, FormInterface $form, array $options)
{
$view->vars['btn_add'] = $options['btn_add'];
$view->vars['btn_list'] = $options['btn_list'];
$view->vars['btn_delete'] = $options['btn_delete'];
$view->vars['btn_catalogue'] = $options['btn_catalogue'];
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$options = array();
$propertyAccessor = $this->propertyAccessor;
if (interface_exists('Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface')) { $options['choice_loader'] = function (Options $options, $previousValue) use ($propertyAccessor) {
if ($previousValue && count($choices = $previousValue->getChoices())) {
return $choices;
}
return new ModelChoiceLoader(
$options['model_manager'],
$options['class'],
$options['property'],
$options['query'],
$options['choices'],
$propertyAccessor
);
};
} else {
$options['choice_list'] = function (Options $options, $previousValue) use ($propertyAccessor) {
if ($previousValue && count($choices = $previousValue->getChoices())) {
return $choices;
}
return new ModelChoiceList(
$options['model_manager'],
$options['class'],
$options['property'],
$options['query'],
$options['choices'],
$propertyAccessor
);
};
}
$resolver->setDefaults(array_merge($options, array('compound'=> function (Options $options) {
if (isset($options['multiple']) && $options['multiple']) {
if (isset($options['expanded']) && $options['expanded']) {
return true;
}
return false;
}
if (isset($options['expanded']) && $options['expanded']) {
return true;
}
return false;
},'template'=>'choice','multiple'=> false,'expanded'=> false,'model_manager'=> null,'class'=> null,'property'=> null,'query'=> null,'choices'=> array(),'preferred_choices'=> array(),'btn_add'=>'link_add','btn_list'=>'link_list','btn_delete'=>'link_delete','btn_catalogue'=>'SonataAdminBundle',
)));
}
public function getParent()
{
return'choice';
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_model';
}
}
}
namespace Sonata\AdminBundle\Form\Type
{
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
class ModelTypeList extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
$builder
->resetViewTransformers()
->addViewTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']));
}
public function buildView(FormView $view, FormInterface $form, array $options)
{
if (isset($view->vars['sonata_admin'])) {
$view->vars['sonata_admin']['edit'] ='list';
}
$view->vars['btn_add'] = $options['btn_add'];
$view->vars['btn_list'] = $options['btn_list'];
$view->vars['btn_delete'] = $options['btn_delete'];
$view->vars['btn_catalogue'] = $options['btn_catalogue'];
}
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
$this->configureOptions($resolver);
}
public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults(array('model_manager'=> null,'class'=> null,'btn_add'=>'link_add','btn_list'=>'link_list','btn_delete'=>'link_delete','btn_catalogue'=>'SonataAdminBundle',
));
}
public function getParent()
{
return'text';
}
public function getName()
{
return $this->getBlockPrefix();
}
public function getBlockPrefix()
{
return'sonata_type_model_list';
}
}
}
namespace Sonata\AdminBundle\Guesser
{
use Sonata\AdminBundle\Model\ModelManagerInterface;
interface TypeGuesserInterface
{
public function guessType($class, $property, ModelManagerInterface $modelManager);
}
}
namespace Sonata\AdminBundle\Guesser
{
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Guess\Guess;
class TypeGuesserChain implements TypeGuesserInterface
{
protected $guessers = array();
public function __construct(array $guessers)
{
foreach ($guessers as $guesser) {
if (!$guesser instanceof TypeGuesserInterface) {
throw new UnexpectedTypeException($guesser,'Sonata\AdminBundle\Guesser\TypeGuesserInterface');
}
if ($guesser instanceof self) {
$this->guessers = array_merge($this->guessers, $guesser->guessers);
} else {
$this->guessers[] = $guesser;
}
}
}
public function guessType($class, $property, ModelManagerInterface $modelManager)
{
return $this->guess(function ($guesser) use ($class, $property, $modelManager) {
return $guesser->guessType($class, $property, $modelManager);
});
}
private function guess(\Closure $closure)
{
$guesses = array();
foreach ($this->guessers as $guesser) {
if ($guess = $closure($guesser)) {
$guesses[] = $guess;
}
}
return Guess::getBestGuess($guesses);
}
}
}
namespace Sonata\AdminBundle\Model
{
interface AuditManagerInterface
{
public function setReader($serviceId, array $classes);
public function hasReader($class);
public function getReader($class);
}
}
namespace Sonata\AdminBundle\Model
{
use Symfony\Component\DependencyInjection\ContainerInterface;
class AuditManager implements AuditManagerInterface
{
protected $classes = array();
protected $readers = array();
protected $container;
public function __construct(ContainerInterface $container)
{
$this->container = $container;
}
public function setReader($serviceId, array $classes)
{
$this->readers[$serviceId] = $classes;
}
public function hasReader($class)
{
foreach ($this->readers as $classes) {
if (in_array($class, $classes)) {
return true;
}
}
return false;
}
public function getReader($class)
{
foreach ($this->readers as $readerId => $classes) {
if (in_array($class, $classes)) {
return $this->container->get($readerId);
}
}
throw new \RuntimeException(sprintf('The class "%s" does not have any reader manager', $class));
}
}
}
namespace Sonata\AdminBundle\Model
{
interface AuditReaderInterface
{
public function find($className, $id, $revision);
public function findRevisionHistory($className, $limit = 20, $offset = 0);
public function findRevision($classname, $revision);
public function findRevisions($className, $id);
public function diff($className, $id, $oldRevision, $newRevision);
}
}
namespace Sonata\AdminBundle\Model
{
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
interface ModelManagerInterface
{
public function getNewFieldDescriptionInstance($class, $name, array $options = array());
public function create($object);
public function update($object);
public function delete($object);
public function findBy($class, array $criteria = array());
public function findOneBy($class, array $criteria = array());
public function find($class, $id);
public function batchDelete($class, ProxyQueryInterface $queryProxy);
public function getParentFieldDescription($parentAssociationMapping, $class);
public function createQuery($class, $alias ='o');
public function getModelIdentifier($class);
public function getIdentifierValues($model);
public function getIdentifierFieldNames($class);
public function getNormalizedIdentifier($model);
public function getUrlsafeIdentifier($model);
public function getModelInstance($class);
public function getModelCollectionInstance($class);
public function collectionRemoveElement(&$collection, &$element);
public function collectionAddElement(&$collection, &$element);
public function collectionHasElement(&$collection, &$element);
public function collectionClear(&$collection);
public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid);
public function getDefaultSortValues($class);
public function modelReverseTransform($class, array $array = array());
public function modelTransform($class, $instance);
public function executeQuery($query);
public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null);
public function getExportFields($class);
public function getPaginationParameters(DatagridInterface $datagrid, $page);
public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx);
}
}
namespace Sonata\AdminBundle\Route
{
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;
class AdminPoolLoader extends Loader
{
const ROUTE_TYPE_NAME ='sonata_admin';
protected $pool;
protected $adminServiceIds = array();
protected $container;
public function __construct(Pool $pool, array $adminServiceIds, ContainerInterface $container)
{
$this->pool = $pool;
$this->adminServiceIds = $adminServiceIds;
$this->container = $container;
}
public function supports($resource, $type = null)
{
return $type === self::ROUTE_TYPE_NAME;
}
public function load($resource, $type = null)
{
$collection = new SymfonyRouteCollection();
foreach ($this->adminServiceIds as $id) {
$admin = $this->pool->getInstance($id);
foreach ($admin->getRoutes()->getElements() as $code => $route) {
$collection->add($route->getDefault('_sonata_name'), $route);
}
$reflection = new \ReflectionObject($admin);
if (file_exists($reflection->getFileName())) {
$collection->addResource(new FileResource($reflection->getFileName()));
}
}
$reflection = new \ReflectionObject($this->container);
if (file_exists($reflection->getFileName())) {
$collection->addResource(new FileResource($reflection->getFileName()));
}
return $collection;
}
}
}
namespace Sonata\AdminBundle\Route
{
use Sonata\AdminBundle\Admin\AdminInterface;
interface RouteGeneratorInterface
{
public function generateUrl(AdminInterface $admin, $name, array $parameters = array(), $absolute = false);
public function generateMenuUrl(AdminInterface $admin, $name, array $parameters = array(), $absolute = false);
public function generate($name, array $parameters = array(), $absolute = false);
public function hasAdminRoute(AdminInterface $admin, $name);
}
}
namespace Sonata\AdminBundle\Route
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
class DefaultRouteGenerator implements RouteGeneratorInterface
{
private $router;
private $cache;
private $caches = array();
private $loaded = array();
public function __construct(RouterInterface $router, RoutesCache $cache)
{
$this->router = $router;
$this->cache = $cache;
}
public function generate($name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
{
return $this->router->generate($name, $parameters, $absolute);
}
public function generateUrl(AdminInterface $admin, $name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
{
$arrayRoute = $this->generateMenuUrl($admin, $name, $parameters, $absolute);
return $this->router->generate($arrayRoute['route'], $arrayRoute['routeParameters'], $arrayRoute['routeAbsolute']);
}
public function generateMenuUrl(AdminInterface $admin, $name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
{
if ($admin->isChild() && $admin->hasRequest() && $admin->getRequest()->attributes->has($admin->getParent()->getIdParameter())) {
if (isset($parameters['id'])) {
$parameters[$admin->getIdParameter()] = $parameters['id'];
unset($parameters['id']);
}
$parameters[$admin->getParent()->getIdParameter()] = $admin->getRequest()->attributes->get($admin->getParent()->getIdParameter());
}
if ($admin->hasParentFieldDescription()) {
$parameters = array_merge($parameters, $admin->getParentFieldDescription()->getOption('link_parameters', array()));
$parameters['uniqid'] = $admin->getUniqid();
$parameters['code'] = $admin->getCode();
$parameters['pcode'] = $admin->getParentFieldDescription()->getAdmin()->getCode();
$parameters['puniqid'] = $admin->getParentFieldDescription()->getAdmin()->getUniqid();
}
if ($name =='update'|| substr($name, -7) =='|update') {
$parameters['uniqid'] = $admin->getUniqid();
$parameters['code'] = $admin->getCode();
}
if ($admin->hasRequest()) {
$parameters = array_merge($admin->getPersistentParameters(), $parameters);
}
$code = $this->getCode($admin, $name);
if (!array_key_exists($code, $this->caches)) {
throw new \RuntimeException(sprintf('unable to find the route `%s`', $code));
}
return array('route'=> $this->caches[$code],'routeParameters'=> $parameters,'routeAbsolute'=> $absolute,
);
}
public function hasAdminRoute(AdminInterface $admin, $name)
{
return array_key_exists($this->getCode($admin, $name), $this->caches);
}
private function getCode(AdminInterface $admin, $name)
{
$this->loadCache($admin);
if ($admin->isChild()) {
return $admin->getBaseCodeRoute().'.'.$name;
}
if (array_key_exists($name, $this->caches)) {
return $name;
}
if (strpos($name,'.')) {
return $admin->getCode().'|'.$name;
}
return $admin->getCode().'.'.$name;
}
private function loadCache(AdminInterface $admin)
{
if ($admin->isChild()) {
$this->loadCache($admin->getParent());
} else {
if (in_array($admin->getCode(), $this->loaded)) {
return;
}
$this->caches = array_merge($this->cache->load($admin), $this->caches);
$this->loaded[] = $admin->getCode();
}
}
}
}
namespace Sonata\AdminBundle\Route
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Model\AuditManagerInterface;
class PathInfoBuilder implements RouteBuilderInterface
{
protected $manager;
public function __construct(AuditManagerInterface $manager)
{
$this->manager = $manager;
}
public function build(AdminInterface $admin, RouteCollection $collection)
{
$collection->add('list');
$collection->add('create');
$collection->add('batch');
$collection->add('edit', $admin->getRouterIdParameter().'/edit');
$collection->add('delete', $admin->getRouterIdParameter().'/delete');
$collection->add('show', $admin->getRouterIdParameter().'/show');
$collection->add('export');
if ($this->manager->hasReader($admin->getClass())) {
$collection->add('history', $admin->getRouterIdParameter().'/history');
$collection->add('history_view_revision', $admin->getRouterIdParameter().'/history/{revision}/view');
$collection->add('history_compare_revisions', $admin->getRouterIdParameter().'/history/{base_revision}/{compare_revision}/compare');
}
if ($admin->isAclEnabled()) {
$collection->add('acl', $admin->getRouterIdParameter().'/acl');
}
if ($admin->getParent()) {
return;
}
foreach ($admin->getChildren() as $children) {
$collection->addCollection($children->getRoutes());
}
}
}
}
namespace Sonata\AdminBundle\Route
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Model\AuditManagerInterface;
class QueryStringBuilder implements RouteBuilderInterface
{
protected $manager;
public function __construct(AuditManagerInterface $manager)
{
$this->manager = $manager;
}
public function build(AdminInterface $admin, RouteCollection $collection)
{
$collection->add('list');
$collection->add('create');
$collection->add('batch');
$collection->add('edit');
$collection->add('delete');
$collection->add('show');
$collection->add('export');
if ($this->manager->hasReader($admin->getClass())) {
$collection->add('history','/audit-history');
$collection->add('history_view_revision','/audit-history-view');
$collection->add('history_compare_revisions','/audit-history-compare');
}
if ($admin->isAclEnabled()) {
$collection->add('acl', $admin->getRouterIdParameter().'/acl');
}
if ($admin->getParent()) {
return;
}
foreach ($admin->getChildren() as $children) {
$collection->addCollection($children->getRoutes());
}
}
}
}
namespace Sonata\AdminBundle\Route
{
use Symfony\Component\Routing\Route;
class RouteCollection
{
protected $elements = array();
protected $baseCodeRoute;
protected $baseRouteName;
protected $baseControllerName;
protected $baseRoutePattern;
public function __construct($baseCodeRoute, $baseRouteName, $baseRoutePattern, $baseControllerName)
{
$this->baseCodeRoute = $baseCodeRoute;
$this->baseRouteName = $baseRouteName;
$this->baseRoutePattern = $baseRoutePattern;
$this->baseControllerName = $baseControllerName;
}
public function add($name, $pattern = null, array $defaults = array(), array $requirements = array(), array $options = array(), $host ='', array $schemes = array(), array $methods = array(), $condition ='')
{
$pattern = $this->baseRoutePattern.'/'.($pattern ?: $name);
$code = $this->getCode($name);
$routeName = $this->baseRouteName.'_'.$name;
if (!isset($defaults['_controller'])) {
$defaults['_controller'] = $this->baseControllerName.':'.$this->actionify($code);
}
if (!isset($defaults['_sonata_admin'])) {
$defaults['_sonata_admin'] = $this->baseCodeRoute;
}
$defaults['_sonata_name'] = $routeName;
$this->elements[$this->getCode($name)] = function () use (
$pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition) {
return new Route($pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
};
return $this;
}
public function getCode($name)
{
if (strrpos($name,'.') !== false) {
return $name;
}
return $this->baseCodeRoute.'.'.$name;
}
public function addCollection(RouteCollection $collection)
{
foreach ($collection->getElements() as $code => $route) {
$this->elements[$code] = $route;
}
return $this;
}
public function getElements()
{
foreach ($this->elements as $name => $element) {
$this->elements[$name] = $this->resolve($element);
}
return $this->elements;
}
public function has($name)
{
return array_key_exists($this->getCode($name), $this->elements);
}
public function get($name)
{
if ($this->has($name)) {
$code = $this->getCode($name);
$this->elements[$code] = $this->resolve($this->elements[$code]);
return $this->elements[$code];
}
throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
}
public function remove($name)
{
unset($this->elements[$this->getCode($name)]);
return $this;
}
public function clearExcept($routeList)
{
if (!is_array($routeList)) {
$routeList = array($routeList);
}
$routeCodeList = array();
foreach ($routeList as $name) {
$routeCodeList[] = $this->getCode($name);
}
$elements = $this->elements;
foreach ($elements as $key => $element) {
if (!in_array($key, $routeCodeList)) {
unset($this->elements[$key]);
}
}
return $this;
}
public function clear()
{
$this->elements = array();
return $this;
}
public function actionify($action)
{
if (($pos = strrpos($action,'.')) !== false) {
$action = substr($action, $pos + 1);
}
if (strpos($this->baseControllerName,':') === false) {
$action .='Action';
}
return lcfirst(str_replace(' ','', ucwords(strtr($action,'_-','  '))));
}
public function getBaseCodeRoute()
{
return $this->baseCodeRoute;
}
public function getBaseControllerName()
{
return $this->baseControllerName;
}
public function getBaseRouteName()
{
return $this->baseRouteName;
}
public function getBaseRoutePattern()
{
return $this->baseRoutePattern;
}
private function resolve($element)
{
if (is_callable($element)) {
return call_user_func($element);
}
return $element;
}
}
}
namespace Symfony\Component\Security\Acl\Permission
{
interface PermissionMapInterface
{
public function getMasks($permission, $object);
public function contains($permission);
}
}
namespace Sonata\AdminBundle\Security\Acl\Permission
{
use Symfony\Component\Security\Acl\Permission\PermissionMapInterface;
class AdminPermissionMap implements PermissionMapInterface
{
const PERMISSION_VIEW ='VIEW';
const PERMISSION_EDIT ='EDIT';
const PERMISSION_CREATE ='CREATE';
const PERMISSION_DELETE ='DELETE';
const PERMISSION_UNDELETE ='UNDELETE';
const PERMISSION_LIST ='LIST';
const PERMISSION_EXPORT ='EXPORT';
const PERMISSION_OPERATOR ='OPERATOR';
const PERMISSION_MASTER ='MASTER';
const PERMISSION_OWNER ='OWNER';
private $map = array(
self::PERMISSION_VIEW => array(
MaskBuilder::MASK_VIEW,
MaskBuilder::MASK_LIST,
MaskBuilder::MASK_EDIT,
MaskBuilder::MASK_OPERATOR,
MaskBuilder::MASK_MASTER,
MaskBuilder::MASK_OWNER,
),
self::PERMISSION_EDIT => array(
MaskBuilder::MASK_EDIT,
MaskBuilder::MASK_OPERATOR,
MaskBuilder::MASK_MASTER,
MaskBuilder::MASK_OWNER,
),
self::PERMISSION_CREATE => array(
MaskBuilder::MASK_CREATE,
MaskBuilder::MASK_OPERATOR,
MaskBuilder::MASK_MASTER,
MaskBuilder::MASK_OWNER,
),
self::PERMISSION_DELETE => array(
MaskBuilder::MASK_DELETE,
MaskBuilder::MASK_OPERATOR,
MaskBuilder::MASK_MASTER,
MaskBuilder::MASK_OWNER,
),
self::PERMISSION_UNDELETE => array(
MaskBuilder::MASK_UNDELETE,
MaskBuilder::MASK_OPERATOR,
MaskBuilder::MASK_MASTER,
MaskBuilder::MASK_OWNER,
),
self::PERMISSION_LIST => array(
MaskBuilder::MASK_LIST,
MaskBuilder::MASK_OPERATOR,
MaskBuilder::MASK_MASTER,
MaskBuilder::MASK_OWNER,
),
self::PERMISSION_EXPORT => array(
MaskBuilder::MASK_EXPORT,
MaskBuilder::MASK_OPERATOR,
MaskBuilder::MASK_MASTER,
MaskBuilder::MASK_OWNER,
),
self::PERMISSION_OPERATOR => array(
MaskBuilder::MASK_OPERATOR,
MaskBuilder::MASK_MASTER,
MaskBuilder::MASK_OWNER,
),
self::PERMISSION_MASTER => array(
MaskBuilder::MASK_MASTER,
MaskBuilder::MASK_OWNER,
),
self::PERMISSION_OWNER => array(
MaskBuilder::MASK_OWNER,
),
);
public function getMasks($permission, $object)
{
if (!isset($this->map[$permission])) {
return;
}
return $this->map[$permission];
}
public function contains($permission)
{
return isset($this->map[$permission]);
}
}
}
namespace Symfony\Component\Security\Acl\Permission
{
interface MaskBuilderInterface
{
public function set($mask);
public function get();
public function add($mask);
public function remove($mask);
public function reset();
public function resolveMask($code);
}
}
namespace Symfony\Component\Security\Acl\Permission
{
abstract class AbstractMaskBuilder implements MaskBuilderInterface
{
protected $mask;
public function __construct($mask = 0)
{
$this->set($mask);
}
public function set($mask)
{
if (!is_int($mask)) {
throw new \InvalidArgumentException('$mask must be an integer.');
}
$this->mask = $mask;
return $this;
}
public function get()
{
return $this->mask;
}
public function add($mask)
{
$this->mask |= $this->resolveMask($mask);
return $this;
}
public function remove($mask)
{
$this->mask &= ~$this->resolveMask($mask);
return $this;
}
public function reset()
{
$this->mask = 0;
return $this;
}
}
}
namespace Symfony\Component\Security\Acl\Permission
{
class MaskBuilder extends AbstractMaskBuilder
{
const MASK_VIEW = 1; const MASK_CREATE = 2; const MASK_EDIT = 4; const MASK_DELETE = 8; const MASK_UNDELETE = 16; const MASK_OPERATOR = 32; const MASK_MASTER = 64; const MASK_OWNER = 128; const MASK_IDDQD = 1073741823;
const CODE_VIEW ='V';
const CODE_CREATE ='C';
const CODE_EDIT ='E';
const CODE_DELETE ='D';
const CODE_UNDELETE ='U';
const CODE_OPERATOR ='O';
const CODE_MASTER ='M';
const CODE_OWNER ='N';
const ALL_OFF ='................................';
const OFF ='.';
const ON ='*';
public function getPattern()
{
$pattern = self::ALL_OFF;
$length = strlen($pattern);
$bitmask = str_pad(decbin($this->mask), $length,'0', STR_PAD_LEFT);
for ($i = $length - 1; $i >= 0; --$i) {
if ('1'=== $bitmask[$i]) {
try {
$pattern[$i] = self::getCode(1 << ($length - $i - 1));
} catch (\Exception $e) {
$pattern[$i] = self::ON;
}
}
}
return $pattern;
}
public static function getCode($mask)
{
if (!is_int($mask)) {
throw new \InvalidArgumentException('$mask must be an integer.');
}
$reflection = new \ReflectionClass(get_called_class());
foreach ($reflection->getConstants() as $name => $cMask) {
if (0 !== strpos($name,'MASK_') || $mask !== $cMask) {
continue;
}
if (!defined($cName ='static::CODE_'.substr($name, 5))) {
throw new \RuntimeException('There was no code defined for this mask.');
}
return constant($cName);
}
throw new \InvalidArgumentException(sprintf('The mask "%d" is not supported.', $mask));
}
public function resolveMask($code)
{
if (is_string($code)) {
if (!defined($name = sprintf('static::MASK_%s', strtoupper($code)))) {
throw new \InvalidArgumentException(sprintf('The code "%s" is not supported', $code));
}
return constant($name);
}
if (!is_int($code)) {
throw new \InvalidArgumentException('$code must be an integer.');
}
return $code;
}
}
}
namespace Sonata\AdminBundle\Security\Acl\Permission
{
use Symfony\Component\Security\Acl\Permission\MaskBuilder as BaseMaskBuilder;
class MaskBuilder extends BaseMaskBuilder
{
const MASK_LIST = 4096; const MASK_EXPORT = 8192;
const CODE_LIST ='L';
const CODE_EXPORT ='E';
}
}
namespace Sonata\AdminBundle\Security\Handler
{
use Sonata\AdminBundle\Admin\AdminInterface;
interface SecurityHandlerInterface
{
public function isGranted(AdminInterface $admin, $attributes, $object = null);
public function getBaseRole(AdminInterface $admin);
public function buildSecurityInformation(AdminInterface $admin);
public function createObjectSecurity(AdminInterface $admin, $object);
public function deleteObjectSecurity(AdminInterface $admin, $object);
}
}
namespace Sonata\AdminBundle\Security\Handler
{
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
interface AclSecurityHandlerInterface extends SecurityHandlerInterface
{
public function setAdminPermissions(array $permissions);
public function getAdminPermissions();
public function setObjectPermissions(array $permissions);
public function getObjectPermissions();
public function getObjectAcl(ObjectIdentityInterface $objectIdentity);
public function findObjectAcls(\Traversable $oids, array $sids = array());
public function addObjectOwner(AclInterface $acl, UserSecurityIdentity $securityIdentity = null);
public function addObjectClassAces(AclInterface $acl, array $roleInformation = array());
public function createAcl(ObjectIdentityInterface $objectIdentity);
public function updateAcl(AclInterface $acl);
public function deleteAcl(ObjectIdentityInterface $objectIdentity);
public function findClassAceIndexByRole(AclInterface $acl, $role);
public function findClassAceIndexByUsername(AclInterface $acl, $username);
}
}
namespace Sonata\AdminBundle\Security\Handler
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;
class AclSecurityHandler implements AclSecurityHandlerInterface
{
protected $tokenStorage;
protected $authorizationChecker;
protected $aclProvider;
protected $superAdminRoles;
protected $adminPermissions;
protected $objectPermissions;
protected $maskBuilderClass;
public function __construct($tokenStorage, $authorizationChecker, MutableAclProviderInterface $aclProvider, $maskBuilderClass, array $superAdminRoles)
{
if (!$tokenStorage instanceof TokenStorageInterface && !$tokenStorage instanceof SecurityContextInterface) {
throw new \InvalidArgumentException('Argument 1 should be an instance of Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface or Symfony\Component\Security\Core\SecurityContextInterface');
}
if (!$authorizationChecker instanceof AuthorizationCheckerInterface && !$authorizationChecker instanceof SecurityContextInterface) {
throw new \InvalidArgumentException('Argument 2 should be an instance of Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface or Symfony\Component\Security\Core\SecurityContextInterface');
}
$this->tokenStorage = $tokenStorage;
$this->authorizationChecker = $authorizationChecker;
$this->aclProvider = $aclProvider;
$this->maskBuilderClass = $maskBuilderClass;
$this->superAdminRoles = $superAdminRoles;
}
public function setAdminPermissions(array $permissions)
{
$this->adminPermissions = $permissions;
}
public function getAdminPermissions()
{
return $this->adminPermissions;
}
public function setObjectPermissions(array $permissions)
{
$this->objectPermissions = $permissions;
}
public function getObjectPermissions()
{
return $this->objectPermissions;
}
public function isGranted(AdminInterface $admin, $attributes, $object = null)
{
if (!is_array($attributes)) {
$attributes = array($attributes);
}
try {
return $this->authorizationChecker->isGranted($this->superAdminRoles) || $this->authorizationChecker->isGranted($attributes, $object);
} catch (AuthenticationCredentialsNotFoundException $e) {
return false;
}
}
public function getBaseRole(AdminInterface $admin)
{
return'ROLE_'.str_replace('.','_', strtoupper($admin->getCode())).'_%s';
}
public function buildSecurityInformation(AdminInterface $admin)
{
$baseRole = $this->getBaseRole($admin);
$results = array();
foreach ($admin->getSecurityInformation() as $role => $permissions) {
$results[sprintf($baseRole, $role)] = $permissions;
}
return $results;
}
public function createObjectSecurity(AdminInterface $admin, $object)
{
$objectIdentity = ObjectIdentity::fromDomainObject($object);
$acl = $this->getObjectAcl($objectIdentity);
if (is_null($acl)) {
$acl = $this->createAcl($objectIdentity);
}
$user = $this->tokenStorage->getToken()->getUser();
$securityIdentity = UserSecurityIdentity::fromAccount($user);
$this->addObjectOwner($acl, $securityIdentity);
$this->addObjectClassAces($acl, $this->buildSecurityInformation($admin));
$this->updateAcl($acl);
}
public function deleteObjectSecurity(AdminInterface $admin, $object)
{
$objectIdentity = ObjectIdentity::fromDomainObject($object);
$this->deleteAcl($objectIdentity);
}
public function getObjectAcl(ObjectIdentityInterface $objectIdentity)
{
try {
$acl = $this->aclProvider->findAcl($objectIdentity);
} catch (AclNotFoundException $e) {
return;
}
return $acl;
}
public function findObjectAcls(\Traversable $oids, array $sids = array())
{
try {
$acls = $this->aclProvider->findAcls(iterator_to_array($oids), $sids);
} catch (NotAllAclsFoundException $e) {
$acls = $e->getPartialResult();
} catch (AclNotFoundException $e) { $acls = new \SplObjectStorage();
}
return $acls;
}
public function addObjectOwner(AclInterface $acl, UserSecurityIdentity $securityIdentity = null)
{
if (false === $this->findClassAceIndexByUsername($acl, $securityIdentity->getUsername())) {
$acl->insertObjectAce($securityIdentity, constant("$this->maskBuilderClass::MASK_OWNER"));
}
}
public function addObjectClassAces(AclInterface $acl, array $roleInformation = array())
{
$builder = new $this->maskBuilderClass();
foreach ($roleInformation as $role => $permissions) {
$aceIndex = $this->findClassAceIndexByRole($acl, $role);
$hasRole = false;
foreach ($permissions as $permission) {
if (in_array($permission, $this->getObjectPermissions())) {
$builder->add($permission);
$hasRole = true;
}
}
if ($hasRole) {
if ($aceIndex === false) {
$acl->insertClassAce(new RoleSecurityIdentity($role), $builder->get());
} else {
$acl->updateClassAce($aceIndex, $builder->get());
}
$builder->reset();
} elseif ($aceIndex !== false) {
$acl->deleteClassAce($aceIndex);
}
}
}
public function createAcl(ObjectIdentityInterface $objectIdentity)
{
return $this->aclProvider->createAcl($objectIdentity);
}
public function updateAcl(AclInterface $acl)
{
$this->aclProvider->updateAcl($acl);
}
public function deleteAcl(ObjectIdentityInterface $objectIdentity)
{
$this->aclProvider->deleteAcl($objectIdentity);
}
public function findClassAceIndexByRole(AclInterface $acl, $role)
{
foreach ($acl->getClassAces() as $index => $entry) {
if ($entry->getSecurityIdentity() instanceof RoleSecurityIdentity && $entry->getSecurityIdentity()->getRole() === $role) {
return $index;
}
}
return false;
}
public function findClassAceIndexByUsername(AclInterface $acl, $username)
{
foreach ($acl->getClassAces() as $index => $entry) {
if ($entry->getSecurityIdentity() instanceof UserSecurityIdentity && $entry->getSecurityIdentity()->getUsername() === $username) {
return $index;
}
}
return false;
}
}
}
namespace Sonata\AdminBundle\Security\Handler
{
use Sonata\AdminBundle\Admin\AdminInterface;
class NoopSecurityHandler implements SecurityHandlerInterface
{
public function isGranted(AdminInterface $admin, $attributes, $object = null)
{
return true;
}
public function getBaseRole(AdminInterface $admin)
{
return'';
}
public function buildSecurityInformation(AdminInterface $admin)
{
return array();
}
public function createObjectSecurity(AdminInterface $admin, $object)
{
}
public function deleteObjectSecurity(AdminInterface $admin, $object)
{
}
}
}
namespace Sonata\AdminBundle\Security\Handler
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;
class RoleSecurityHandler implements SecurityHandlerInterface
{
protected $authorizationChecker;
protected $superAdminRoles;
public function __construct($authorizationChecker, array $superAdminRoles)
{
if (!$authorizationChecker instanceof AuthorizationCheckerInterface && !$authorizationChecker instanceof SecurityContextInterface) {
throw new \InvalidArgumentException('Argument 1 should be an instance of Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface or Symfony\Component\Security\Core\SecurityContextInterface');
}
$this->authorizationChecker = $authorizationChecker;
$this->superAdminRoles = $superAdminRoles;
}
public function isGranted(AdminInterface $admin, $attributes, $object = null)
{
if (!is_array($attributes)) {
$attributes = array($attributes);
}
foreach ($attributes as $pos => $attribute) {
$attributes[$pos] = sprintf($this->getBaseRole($admin), $attribute);
}
try {
return $this->authorizationChecker->isGranted($this->superAdminRoles)
|| $this->authorizationChecker->isGranted($attributes, $object);
} catch (AuthenticationCredentialsNotFoundException $e) {
return false;
}
}
public function getBaseRole(AdminInterface $admin)
{
return'ROLE_'.str_replace('.','_', strtoupper($admin->getCode())).'_%s';
}
public function buildSecurityInformation(AdminInterface $admin)
{
return array();
}
public function createObjectSecurity(AdminInterface $admin, $object)
{
}
public function deleteObjectSecurity(AdminInterface $admin, $object)
{
}
}
}
namespace Sonata\AdminBundle\Show
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;
class ShowMapper extends BaseGroupedMapper
{
protected $list;
public function __construct(ShowBuilderInterface $showBuilder, FieldDescriptionCollection $list, AdminInterface $admin)
{
parent::__construct($showBuilder, $admin);
$this->list = $list;
}
public function add($name, $type = null, array $fieldDescriptionOptions = array())
{
if ($this->apply !== null && !$this->apply) {
return $this;
}
$fieldKey = ($name instanceof FieldDescriptionInterface) ? $name->getName() : $name;
$this->addFieldToCurrentGroup($fieldKey);
if ($name instanceof FieldDescriptionInterface) {
$fieldDescription = $name;
$fieldDescription->mergeOptions($fieldDescriptionOptions);
} elseif (is_string($name)) {
if (!$this->admin->hasShowFieldDescription($name)) {
$fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
$this->admin->getClass(),
$name,
$fieldDescriptionOptions
);
} else {
throw new \RuntimeException(sprintf('Duplicate field name "%s" in show mapper. Names should be unique.', $name));
}
} else {
throw new \RuntimeException('invalid state');
}
if (!$fieldDescription->getLabel()) {
$fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(),'show','label'));
}
$fieldDescription->setOption('safe', $fieldDescription->getOption('safe', false));
$this->builder->addField($this->list, $type, $fieldDescription, $this->admin);
return $this;
}
public function get($name)
{
return $this->list->get($name);
}
public function has($key)
{
return $this->list->has($key);
}
public function remove($key)
{
$this->admin->removeShowFieldDescription($key);
$this->list->remove($key);
return $this;
}
final public function keys()
{
return array_keys($this->list->getElements());
}
public function reorder(array $keys)
{
$this->admin->reorderShowGroup($this->getCurrentGroupName(), $keys);
return $this;
}
protected function getGroups()
{
return $this->admin->getShowGroups();
}
protected function setGroups(array $groups)
{
$this->admin->setShowGroups($groups);
}
protected function getTabs()
{
return $this->admin->getShowTabs();
}
protected function setTabs(array $tabs)
{
$this->admin->setShowTabs($tabs);
}
}
}
namespace Sonata\AdminBundle\Translator
{
interface LabelTranslatorStrategyInterface
{
public function getLabel($label, $context ='', $type ='');
}
}
namespace Sonata\AdminBundle\Translator
{
class BCLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
public function getLabel($label, $context ='', $type ='')
{
if ($context =='breadcrumb') {
return sprintf('%s.%s_%s', $context, $type, strtolower($label));
}
return ucfirst(strtolower($label));
}
}
}
namespace Sonata\AdminBundle\Translator
{
class FormLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
public function getLabel($label, $context ='', $type ='')
{
return ucfirst(strtolower($label));
}
}
}
namespace Sonata\AdminBundle\Translator
{
class NativeLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
public function getLabel($label, $context ='', $type ='')
{
$label = str_replace(array('_','.'),' ', $label);
$label = strtolower(preg_replace('~(?<=\\w)([A-Z])~','_$1', $label));
return trim(ucwords(str_replace('_',' ', $label)));
}
}
}
namespace Sonata\AdminBundle\Translator
{
class NoopLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
public function getLabel($label, $context ='', $type ='')
{
return $label;
}
}
}
namespace Sonata\AdminBundle\Translator
{
class UnderscoreLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
public function getLabel($label, $context ='', $type ='')
{
$label = str_replace('.','_', $label);
return sprintf('%s.%s_%s', $context, $type, strtolower(preg_replace('~(?<=\\w)([A-Z])~','_$1', $label)));
}
}
}
namespace Sonata\AdminBundle\Util
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Model\AclInterface;
interface AdminAclManipulatorInterface
{
public function configureAcls(OutputInterface $output, AdminInterface $admin);
public function addAdminClassAces(OutputInterface $output, AclInterface $acl, AclSecurityHandlerInterface $securityHandler, array $roleInformation = array());
}
}
namespace Sonata\AdminBundle\Util
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\AclInterface;
class AdminAclManipulator implements AdminAclManipulatorInterface
{
protected $maskBuilderClass;
public function __construct($maskBuilderClass)
{
$this->maskBuilderClass = $maskBuilderClass;
}
public function configureAcls(OutputInterface $output, AdminInterface $admin)
{
$securityHandler = $admin->getSecurityHandler();
if (!$securityHandler instanceof AclSecurityHandlerInterface) {
$output->writeln(sprintf('Admin `%s` is not configured to use ACL : <info>ignoring</info>', $admin->getCode()));
return;
}
$objectIdentity = ObjectIdentity::fromDomainObject($admin);
$newAcl = false;
if (is_null($acl = $securityHandler->getObjectAcl($objectIdentity))) {
$acl = $securityHandler->createAcl($objectIdentity);
$newAcl = true;
}
$output->writeln(sprintf(' > install ACL for %s', $admin->getCode()));
$configResult = $this->addAdminClassAces($output, $acl, $securityHandler, $securityHandler->buildSecurityInformation($admin));
if ($configResult) {
$securityHandler->updateAcl($acl);
} else {
$output->writeln(sprintf('   - %s , no roles and permissions found', ($newAcl ?'skip':'removed')));
$securityHandler->deleteAcl($objectIdentity);
}
}
public function addAdminClassAces(OutputInterface $output, AclInterface $acl, AclSecurityHandlerInterface $securityHandler, array $roleInformation = array())
{
if (count($securityHandler->getAdminPermissions()) > 0) {
$builder = new $this->maskBuilderClass();
foreach ($roleInformation as $role => $permissions) {
$aceIndex = $securityHandler->findClassAceIndexByRole($acl, $role);
$roleAdminPermissions = array();
foreach ($permissions as $permission) {
if (in_array($permission, $securityHandler->getAdminPermissions())) {
$builder->add($permission);
$roleAdminPermissions[] = $permission;
}
}
if (count($roleAdminPermissions) > 0) {
if ($aceIndex === false) {
$acl->insertClassAce(new RoleSecurityIdentity($role), $builder->get());
$action ='add';
} else {
$acl->updateClassAce($aceIndex, $builder->get());
$action ='update';
}
if (!is_null($output)) {
$output->writeln(sprintf('   - %s role: %s, permissions: %s', $action, $role, json_encode($roleAdminPermissions)));
}
$builder->reset();
} elseif ($aceIndex !== false) {
$acl->deleteClassAce($aceIndex);
if (!is_null($output)) {
$output->writeln(sprintf('   - remove role: %s', $role));
}
}
}
return true;
} else {
return false;
}
}
}
}
namespace Sonata\AdminBundle\Util
{
use Symfony\Component\Form\FormBuilderInterface;
class FormBuilderIterator extends \RecursiveArrayIterator
{
protected static $reflection;
protected $formBuilder;
protected $keys = array();
protected $prefix;
protected $iterator;
public function __construct(FormBuilderInterface $formBuilder, $prefix = false)
{
$this->formBuilder = $formBuilder;
$this->prefix = $prefix ? $prefix : $formBuilder->getName();
$this->iterator = new \ArrayIterator(self::getKeys($formBuilder));
}
public function rewind()
{
$this->iterator->rewind();
}
public function valid()
{
return $this->iterator->valid();
}
public function key()
{
$name = $this->iterator->current();
return sprintf('%s_%s', $this->prefix, $name);
}
public function next()
{
$this->iterator->next();
}
public function current()
{
return $this->formBuilder->get($this->iterator->current());
}
public function getChildren()
{
return new self($this->formBuilder->get($this->iterator->current()), $this->current());
}
public function hasChildren()
{
return count(self::getKeys($this->current())) > 0;
}
private static function getKeys(FormBuilderInterface $formBuilder)
{
return array_keys($formBuilder->all());
}
}
}
namespace Sonata\AdminBundle\Util
{
use Symfony\Component\Form\FormView;
class FormViewIterator implements \RecursiveIterator
{
protected $iterator;
public function __construct(FormView $formView)
{
$this->iterator = $formView->getIterator();
}
public function getChildren()
{
return new self($this->current());
}
public function hasChildren()
{
return count($this->current()->children) > 0;
}
public function current()
{
return $this->iterator->current();
}
public function next()
{
$this->iterator->next();
}
public function key()
{
return $this->current()->vars['id'];
}
public function valid()
{
return $this->iterator->valid();
}
public function rewind()
{
$this->iterator->rewind();
}
}
}
namespace Sonata\AdminBundle\Util
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
interface ObjectAclManipulatorInterface
{
public function batchConfigureAcls(OutputInterface $output, AdminInterface $admin, UserSecurityIdentity $securityIdentity = null);
}
}
namespace Sonata\AdminBundle\Util
{
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
abstract class ObjectAclManipulator implements ObjectAclManipulatorInterface
{
public function configureAcls(OutputInterface $output, AdminInterface $admin, \Traversable $oids, UserSecurityIdentity $securityIdentity = null)
{
$countAdded = 0;
$countUpdated = 0;
$securityHandler = $admin->getSecurityHandler();
if (!$securityHandler instanceof AclSecurityHandlerInterface) {
$output->writeln(sprintf('Admin `%s` is not configured to use ACL : <info>ignoring</info>', $admin->getCode()));
return array(0, 0);
}
$acls = $securityHandler->findObjectAcls($oids);
foreach ($oids as $oid) {
if ($acls->contains($oid)) {
$acl = $acls->offsetGet($oid);
++$countUpdated;
} else {
$acl = $securityHandler->createAcl($oid);
++$countAdded;
}
if (!is_null($securityIdentity)) {
$securityHandler->addObjectOwner($acl, $securityIdentity);
}
$securityHandler->addObjectClassAces($acl, $securityHandler->buildSecurityInformation($admin));
try {
$securityHandler->updateAcl($acl);
} catch (\Exception $e) {
$output->writeln(sprintf('Error saving ObjectIdentity (%s, %s) ACL : %s <info>ignoring</info>', $oid->getIdentifier(), $oid->getType(), $e->getMessage()));
}
}
return array($countAdded, $countUpdated);
}
}
}