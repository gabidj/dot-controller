<?php
/**
 * @see https://github.com/dotkernel/dot-controller/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-controller/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Controller\Factory;

use Dot\Controller\Plugin\PluginManager;
use Dot\Controller\Plugin\TemplatePlugin;
use Dot\Controller\Plugin\UrlHelperPlugin;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class PluginManagerFactory
 * @package Dot\Controller\Factory
 */
class PluginManagerFactory
{
    /**
     * @param ContainerInterface $container
     * @return PluginManager
     */
    public function __invoke(ContainerInterface $container)
    {
        $pluginManager = new PluginManager($container, $container->get('config')['dot_controller']['plugin_manager']);

        //register the built in plugins, if the required component is present
        if ($container->has(UrlHelper::class)) {
            $pluginManager->setFactory('url', function (ContainerInterface $container) {
                return new UrlHelperPlugin($container->get(UrlHelper::class));
            });
        }

        if ($container->has(TemplateRendererInterface::class)) {
            $pluginManager->setFactory('template', function (ContainerInterface $container) {
                return new TemplatePlugin($container->get(TemplateRendererInterface::class));
            });
        }

        return $pluginManager;
    }
}
