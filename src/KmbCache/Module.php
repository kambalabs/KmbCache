<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/kambalabs for the sources repositories
 *
 * This file is part of Kamba.
 *
 * Kamba is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Kamba is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kamba.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbCache;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\BasePath;
use Zend\View\HelperPluginManager;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, BootstrapListenerInterface
{
    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {
        /** @var EventManagerInterface $eventManager */
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, function (MvcEvent $event) {
            /** @var HelperPluginManager $viewHelperManager */
            $viewHelperManager = $event->getApplication()->getServiceManager()->get('ViewHelperManager');
            /** @var BasePath $basePath */
            $basePath = $viewHelperManager->get('basePath');
            $viewHelperManager->get('inlineScript')->prependFile($basePath() . '/js/kmb.cache.js');
        });
    }

    public function getConfig()
    {
        return include dirname(dirname(__DIR__)) . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__,
                ],
            ],
        ];
    }
}
