<?php
/**
 * @copyright: DotKernel
 * @package: dot-controller
 * @author: n3vrax
 * Date: 8/15/2016
 * Time: 5:56 PM
 */

namespace DotKernel\DotController;

use DotKernel\DotController\Plugin\PluginManager;
use DotKernel\DotController\Plugin\PluginManagerAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractController
 * @package DotKernel\DotController
 */
abstract class AbstractController implements PluginManagerAwareInterface
{
    /** @var  PluginManager */
    protected $pluginManager;

    /** @var  ServerRequestInterface */
    protected $request;

    /** @var  ResponseInterface */
    protected $response;

    /** @var  callable */
    protected $next;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->next = $next;

        return $this->dispatch();

    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return callable
     */
    public function getNext()
    {
        return $this->next;
    }

    public abstract function dispatch();

    /**
     * Method overloading: return/call plugins
     *
     * If the plugin is a functor, call it, passing the parameters provided.
     * Otherwise, return the plugin instance.
     *
     * @param  string $method
     * @param  array $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        $plugin = $this->plugin($method);
        if (is_callable($plugin)) {
            return call_user_func_array($plugin, $params);
        }
        return $plugin;
    }

    /**
     * Get plugin instance
     *
     * @param  string $name Name of plugin to return
     * @param  null|array $options Options to pass to plugin constructor (if not already instantiated)
     * @return mixed
     */
    public function plugin($name, array $options = null)
    {
        return $this->getPluginManager()->get($name, $options);
    }

    /**
     * @return PluginManager
     */
    public function getPluginManager()
    {
        return $this->pluginManager;
    }

    /**
     * @param PluginManager $pluginManager
     * @return $this
     */
    public function setPluginManager(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
        return $this;
    }

    /**
     * Transform an "action" token into a method name
     *
     * @param  string $action
     * @return string
     */
    public static function getMethodFromAction($action)
    {
        $method = str_replace(['.', '-', '_'], ' ', $action);
        $method = ucwords($method);
        $method = str_replace(' ', '', $method);
        $method = lcfirst($method);
        $method .= 'Action';
        return $method;
    }


}