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
namespace KmbCache\Proxy;

use KmbCache\Service\CacheManagerInterface;
use KmbPmProxy\Service\PuppetModule;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PuppetModuleProxyFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $proxy = new PuppetModuleProxy();

        /** @var CacheManagerInterface $availableModulesCacheManager */
        $availableModulesCacheManager = $serviceLocator->get('KmbCache\Service\AvailablePuppetModuleCacheManager');
        $proxy->setAvailableModulesCacheManager($availableModulesCacheManager);

        /** @var CacheManagerInterface $installableModulesCacheManager */
        $installableModulesCacheManager = $serviceLocator->get('KmbCache\Service\InstallablePuppetModuleCacheManager');
        $proxy->setInstallableModulesCacheManager($installableModulesCacheManager);

        /** @var CacheManagerInterface $installedModulesCacheManager */
        $installedModulesCacheManager = $serviceLocator->get('KmbCache\Service\InstalledPuppetModuleCacheManager');
        $proxy->setInstalledModulesCacheManager($installedModulesCacheManager);

        /** @var PuppetModule $moduleService */
        $moduleService = $serviceLocator->get('KmbPmProxy\Service\PuppetModule');
        $proxy->setModuleService($moduleService);

        return $proxy;
    }
}
