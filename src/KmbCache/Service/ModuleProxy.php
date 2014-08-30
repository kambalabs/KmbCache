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
namespace KmbCache\Service;

use KmbDomain;
use KmbPmProxy;
use KmbPmProxy\Service\ModuleInterface;
use Zend\Cache\Storage\StorageInterface;

class ModuleProxy implements ModuleInterface
{
    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var ModuleInterface */
    protected $pmProxyModuleService;

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @return KmbPmProxy\Model\Module[]
     */
    public function getAllByEnvironment(KmbDomain\Model\EnvironmentInterface $environment)
    {
        $key = CacheManager::KEY_MODULES . $environment->getNormalizedName();
        if ($this->cacheStorage->hasItem($key)) {
            return $this->cacheStorage->getItem($key);
        }
        return $this->pmProxyModuleService->getAllByEnvironment($environment);
    }

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @param string                               $name
     * @return KmbPmProxy\Model\Module
     */
    public function getByEnvironmentAndName(KmbDomain\Model\EnvironmentInterface $environment, $name)
    {
        $modules = $this->getAllByEnvironment($environment);
        return isset($modules[$name]) ? $modules[$name] : null;
    }

    /**
     * Set CacheStorage.
     *
     * @param \Zend\Cache\Storage\StorageInterface $cacheStorage
     * @return ModuleProxy
     */
    public function setCacheStorage($cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;
        return $this;
    }

    /**
     * Get CacheStorage.
     *
     * @return \Zend\Cache\Storage\StorageInterface
     */
    public function getCacheStorage()
    {
        return $this->cacheStorage;
    }

    /**
     * Set PmProxyModuleService.
     *
     * @param \KmbPmProxy\Service\ModuleInterface $pmProxyModuleService
     * @return ModuleProxy
     */
    public function setPmProxyModuleService($pmProxyModuleService)
    {
        $this->pmProxyModuleService = $pmProxyModuleService;
        return $this;
    }

    /**
     * Get PmProxyModuleService.
     *
     * @return \KmbPmProxy\Service\ModuleInterface
     */
    public function getPmProxyModuleService()
    {
        return $this->pmProxyModuleService;
    }
}
