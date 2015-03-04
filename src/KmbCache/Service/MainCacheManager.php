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

class MainCacheManager
{
    /** @var AbstractCacheManager[] */
    protected $cacheManagers = [];

    /**
     * Refresh cache if necessary.
     *
     * @param EnvironmentInterface $environment
     * @param bool                 $forceRefresh
     * @return bool
     */
    public function refreshExpiredCache($environment = null, $forceRefresh = false)
    {
        $hasBeenRefreshed = false;
        foreach ($this->cacheManagers as $cacheManager) {
            $hasBeenRefreshed =  $cacheManager->refreshExpiredCache($environment, $forceRefresh) || $hasBeenRefreshed;
        }
        return $hasBeenRefreshed;
    }

    /**
     * Force cache refresh.
     *
     * @param EnvironmentInterface $environment
     * @return bool
     */
    public function forceRefreshCache($environment = null)
    {
        return $this->refreshExpiredCache($environment, true);
    }

    /**
     * Set CacheManagers.
     *
     * @param \KmbCache\Service\AbstractCacheManager[] $cacheManagers
     * @return MainCacheManager
     */
    public function setCacheManagers($cacheManagers)
    {
        $this->cacheManagers = $cacheManagers;
        return $this;
    }

    /**
     * @param string               $key
     * @param AbstractCacheManager $cacheManager
     * @return MainCacheManager
     */
    public function addCacheManager($key, $cacheManager)
    {
        $this->cacheManagers[$key] = $cacheManager;
        return $this;
    }

    /**
     * Get CacheManagers.
     *
     * @return \KmbCache\Service\AbstractCacheManager[]
     */
    public function getCacheManagers()
    {
        return $this->cacheManagers;
    }

    /**
     * Get CacheManager.
     *
     * @param string $key
     * @return \KmbCache\Service\AbstractCacheManager
     */
    public function getCacheManager($key)
    {
        return array_key_exists($key, $this->cacheManagers) ? $this->cacheManagers[$key] : null;
    }
}
