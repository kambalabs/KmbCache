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
use KmbDomain;
use KmbPmProxy;
use KmbPmProxy\Service\PuppetModuleInterface;

class PuppetModuleProxy implements PuppetModuleInterface
{
    /** @var  PuppetModuleInterface */
    protected $moduleService;

    /** @var  CacheManagerInterface */
    protected $availableModulesCacheManager;

    /** @var  CacheManagerInterface */
    protected $installableModulesCacheManager;

    /** @var  CacheManagerInterface */
    protected $installedModulesCacheManager;

    /**
     * @return KmbPmProxy\Model\PuppetModule[]
     */
    public function getAllAvailable()
    {
        return $this->availableModulesCacheManager->getData();
    }

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @return KmbPmProxy\Model\PuppetModule[]
     */
    public function getAllInstallableByEnvironment(KmbDomain\Model\EnvironmentInterface $environment)
    {
        return $this->installableModulesCacheManager->getData($environment);
    }

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @return KmbPmProxy\Model\PuppetModule[]
     */
    public function getAllInstalledByEnvironment(KmbDomain\Model\EnvironmentInterface $environment)
    {
        return $this->installedModulesCacheManager->getData($environment);
    }

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @param KmbPmProxy\Model\PuppetModule        $module
     * @param string                               $version
     */
    public function installInEnvironment(KmbDomain\Model\EnvironmentInterface $environment, KmbPmProxy\Model\PuppetModule $module, $version)
    {
        $this->moduleService->installInEnvironment($environment, $module, $version);
        $this->availableModulesCacheManager->forceRefreshCache();
        $this->installableModulesCacheManager->forceRefreshCache($environment);
        $this->installedModulesCacheManager->forceRefreshCache($environment);
    }

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @param KmbPmProxy\Model\PuppetModule        $module
     */
    public function removeFromEnvironment(KmbDomain\Model\EnvironmentInterface $environment, KmbPmProxy\Model\PuppetModule $module)
    {
        $this->moduleService->removeFromEnvironment($environment, $module);
        $this->availableModulesCacheManager->forceRefreshCache();
        $this->installableModulesCacheManager->forceRefreshCache($environment);
        $this->installedModulesCacheManager->forceRefreshCache($environment);
    }

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @param string                               $name
     * @return KmbPmProxy\Model\PuppetModule
     */
    public function getInstalledByEnvironmentAndName(KmbDomain\Model\EnvironmentInterface $environment, $name)
    {
        $puppetModules = $this->getAllInstalledByEnvironment($environment);
        return isset($puppetModules[$name]) ? $puppetModules[$name] : null;
    }

    /**
     * Set AvailableModuleCacheManager.
     *
     * @param \KmbCache\Service\CacheManagerInterface $availableModulesCacheManager
     * @return PuppetModuleProxy
     */
    public function setAvailableModulesCacheManager($availableModulesCacheManager)
    {
        $this->availableModulesCacheManager = $availableModulesCacheManager;
        return $this;
    }

    /**
     * Get CacheManager.
     *
     * @return \KmbCache\Service\CacheManagerInterface
     */
    public function getAvailableModulesCacheManager()
    {
        return $this->availableModulesCacheManager;
    }

    /**
     * Set InstallableModulesCacheManager.
     *
     * @param \KmbCache\Service\CacheManagerInterface $installableModulesCacheManager
     * @return PuppetModuleProxy
     */
    public function setInstallableModulesCacheManager($installableModulesCacheManager)
    {
        $this->installableModulesCacheManager = $installableModulesCacheManager;
        return $this;
    }

    /**
     * Get InstallableModulesCacheManager.
     *
     * @return \KmbCache\Service\CacheManagerInterface
     */
    public function getInstallableModulesCacheManager()
    {
        return $this->installableModulesCacheManager;
    }

    /**
     * Set InstalledModulesCacheManager.
     *
     * @param \KmbCache\Service\CacheManagerInterface $installedModulesCacheManager
     * @return PuppetModuleProxy
     */
    public function setInstalledModulesCacheManager($installedModulesCacheManager)
    {
        $this->installedModulesCacheManager = $installedModulesCacheManager;
        return $this;
    }

    /**
     * Get InstalledModulesCacheManager.
     *
     * @return \KmbCache\Service\CacheManagerInterface
     */
    public function getInstalledModulesCacheManager()
    {
        return $this->installedModulesCacheManager;
    }

    /**
     * Set ModuleService.
     *
     * @param \KmbPmProxy\Service\PuppetModuleInterface $moduleService
     * @return PuppetModuleProxy
     */
    public function setModuleService($moduleService)
    {
        $this->moduleService = $moduleService;
        return $this;
    }

    /**
     * Get ModuleService.
     *
     * @return \KmbPmProxy\Service\PuppetModuleInterface
     */
    public function getModuleService()
    {
        return $this->moduleService;
    }
}
