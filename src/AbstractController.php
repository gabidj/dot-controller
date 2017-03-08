<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-controller
 * @author: n3vrax
 * Date: 9/5/2016
 * Time: 8:24 PM
 */

declare(strict_types = 1);

namespace Dot\Controller;

use Dot\Controller\Event\DispatchControllerEventsTrait;
use Dot\Controller\Plugin\PluginInterface;
use Dot\Controller\Plugin\PluginManager;
use Dot\Controller\Plugin\PluginManagerAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\EventManager\EventManagerAwareInterface;

/**
 * Class AbstractController
 * @package Dot\Controller
 */
abstract class AbstractController implements
    PluginManagerAwareInterface,
    EventManagerAwareInterface
{
    use DispatchControllerEventsTrait;

    /** @var  PluginManager */
    protected $pluginManager;

    /** @var  ServerRequestInterface */
    protected $request;

    /** @var  ResponseInterface */
    protected $response;

    /** @var  callable */
    protected $next;

    /** @var bool */
    protected $debug = false;

    /**
     * Transform an "action" token into a method name
     *
     * @param  string $action
     * @return string
     */
    public static function getMethodFromAction(string $action): string
    {
        $method = str_replace(['.', '-', '_'], ' ', $action);
        $method = ucwords($method);
        $method = str_replace(' ', '', $method);
        $method = lcfirst($method);
        $method .= 'Action';
        return $method;
    }

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
    ): ResponseInterface {
        $this->request = $request;
        $this->response = $response;
        $this->next = $next;

        return $this->dispatch();
    }

    abstract public function dispatch(): ResponseInterface;

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return callable
     */
    public function getNext(): callable
    {
        return $this->next;
    }

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
    public function __call(string $method, array $params)
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
     * @param  array $options Options to pass to plugin constructor (if not already instantiated)
     * @return PluginInterface|callable
     */
    public function plugin(string $name, array $options = []): PluginInterface
    {
        return $this->getPluginManager()->get($name, $options);
    }

    /**
     * @return PluginManager
     */
    public function getPluginManager(): PluginManager
    {
        return $this->pluginManager;
    }

    /**
     * @param PluginManager $pluginManager
     */
    public function setPluginManager(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }
}
