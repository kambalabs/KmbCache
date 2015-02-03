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
use KmbPmProxy\Service\PuppetModuleInterface;
use KmbPmProxy;

class PuppetModuleProxy implements PuppetModuleInterface
{
    /** @var  CacheManagerInterface */
    protected $cacheManager;

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @return KmbPmProxy\Model\PuppetModule[]
     */
    public function getAllInstallableByEnvironment(KmbDomain\Model\EnvironmentInterface $environment)
    {
        return $this->cacheManager->getInstallablePuppetModules($environment);
    }

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @return KmbPmProxy\Model\PuppetModule[]
     */
    public function getAllInstalledByEnvironment(KmbDomain\Model\EnvironmentInterface $environment)
    {
        return $this->cacheManager->getInstalledPuppetModules($environment);
    }

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @param KmbPmProxy\Model\PuppetModule        $module
     * @param string                               $version
     */
    public function installInEnvironment(KmbDomain\Model\EnvironmentInterface $environment, KmbPmProxy\Model\PuppetModule $module, $version)
    {
        $this->cacheManager->installPuppetModule($environment, $module, $version);
    }

    /**
     * @param KmbDomain\Model\EnvironmentInterface $environment
     * @param string                               $name
     * @return KmbPmProxy\Model\PuppetModule
     */
    public function getByEnvironmentAndName(KmbDomain\Model\EnvironmentInterface $environment, $name)
    {
        $puppetModules = $this->getAllInstalledByEnvironment($environment);
        return isset($puppetModules[$name]) ? $puppetModules[$name] : null;
    }

    /**
     * Set CacheManager.
     *
     * @param \KmbCache\Service\CacheManagerInterface $cacheManager
     * @return PuppetModuleProxy
     */
    public function setCacheManager($cacheManager)
    {
        $this->cacheManager = $cacheManager;
        return $this;
    }

    /**
     * Get CacheManager.
     *
     * @return \KmbCache\Service\CacheManagerInterface
     */
    public function getCacheManager()
    {
        return $this->cacheManager;
    }
}
