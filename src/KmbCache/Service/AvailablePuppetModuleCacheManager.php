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

use KmbDomain\Model\EnvironmentInterface;
use KmbPmProxy\Service\PuppetModuleInterface;

class AvailablePuppetModuleCacheManager extends AbstractCacheManager
{
    /** @var  PuppetModuleInterface */
    protected $puppetModuleService;

    /**
     * @param mixed $context
     * @return mixed
     */
    public function getDataFromRealService($context = null)
    {
        return $this->puppetModuleService->getAllAvailable();
    }

    /**
     * @param mixed $context
     * @return mixed
     */
    public function getData($context = null)
    {
        return parent::getData();
    }

    /**
     * @param EnvironmentInterface $environment
     * @param bool                 $forceRefresh
     * @return bool
     */
    public function refreshExpiredCache($environment = null, $forceRefresh = false)
    {
        return parent::refreshExpiredCache(null, $forceRefresh);
    }

    /**
     * Set PuppetModuleService.
     *
     * @param PuppetModuleInterface $puppetModuleService
     * @return AvailablePuppetModuleCacheManager
     */
    public function setPuppetModuleService($puppetModuleService)
    {
        $this->puppetModuleService = $puppetModuleService;
        return $this;
    }

    /**
     * Get PuppetModuleService.
     *
     * @return PuppetModuleInterface
     */
    public function getPuppetModuleService()
    {
        return $this->puppetModuleService;
    }
}
