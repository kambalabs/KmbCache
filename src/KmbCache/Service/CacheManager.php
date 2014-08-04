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

use KmbBase\DateTimeFactoryInterface;
use KmbCache\Exception\RuntimeException;
use KmbDomain\Model\EnvironmentInterface;
use KmbPuppetDb\Model\NodeInterface;
use KmbPuppetDb\Service\NodeStatisticsInterface;
use KmbPuppetDb\Service\ReportStatisticsInterface;
use Zend\Cache\Storage\StorageInterface;

/**
 * Class CacheManager
 *
 * @package Cache\Service
 */
class CacheManager implements CacheManagerInterface
{
    /** @var NodeStatisticsInterface */
    protected $nodeStatisticsService;

    /** @var ReportStatisticsInterface */
    protected $reportStatisticsService;

    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var DateTimeFactoryInterface */
    protected $dateTimeFactory;

    /**
     * Refresh cache
     *
     * @param EnvironmentInterface $environment
     * @param bool                 $forceOnPending Allows to refresh cache even if the status is pending.
     * @throws RuntimeException When the cache status is pending (and $forceOnPending is false).
     */
    public function refresh($environment = null, $forceOnPending = false)
    {
        if (!$forceOnPending && $this->getCacheStorage()->getItem($this->statusKey($environment)) == static::PENDING) {
            throw new RuntimeException('Cache refresh is already in progress');
        }

        /** TODO: Query will be ['=', 'facts-environment', $environment->getNormalizedName()] when PuppetDB v4 API will be stable */
        $nodeStatistics = $this->getNodeStatisticsService()->getAllAsArray($environment ? ['=', ['fact', NodeInterface::ENVIRONMENT_FACT], $environment->getNormalizedName()] : null);
        $reportStatistics = $this->getReportStatisticsService()->getAllAsArray($environment ? ['=', 'environment', $environment->getNormalizedName()] : null);

        $this->getCacheStorage()->setItem($this->statusKey($environment), static::PENDING);
        $this->getCacheStorage()->setItem($this->nodeStatisticsKey($environment), $nodeStatistics);
        $this->getCacheStorage()->setItem($this->reportStatisticsKey($environment), $reportStatistics);
        $this->getCacheStorage()->setItem($this->statusKey($environment), static::COMPLETED);
        $this->getCacheStorage()->setItem($this->refreshedAtKey($environment), $this->getDateTimeFactory()->now());
    }

    /**
     * Force refreshing cache
     *
     * @param EnvironmentInterface $environment
     * @throws RuntimeException When the cache status is pending (and $force is false).
     */
    public function forceRefresh($environment = null)
    {
    }

    /**
     * Get the status of the cache (null|pending|completed)
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getCacheStorage()->getItem(static::KEY_CACHE_STATUS);
    }

    /**
     * Get the time of the last refresh
     *
     * @return \DateTime
     */
    public function getRefreshedAt()
    {
        return $this->getCacheStorage()->getItem(static::KEY_REFRESHED_AT);
    }

    /**
     * @return NodeStatisticsInterface
     */
    public function getNodeStatisticsService()
    {
        return $this->nodeStatisticsService;
    }

    /**
     * @param $nodeStatisticsService
     * @return CacheManager
     */
    public function setNodeStatisticsService($nodeStatisticsService)
    {
        $this->nodeStatisticsService = $nodeStatisticsService;
        return $this;
    }

    /**
     * @return ReportStatisticsInterface
     */
    public function getReportStatisticsService()
    {
        return $this->reportStatisticsService;
    }

    /**
     * @param $reportStatisticsService
     * @return CacheManager
     */
    public function setReportStatisticsService($reportStatisticsService)
    {
        $this->reportStatisticsService = $reportStatisticsService;
        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getCacheStorage()
    {
        return $this->cacheStorage;
    }

    /**
     * @param $cacheStorage
     * @return CacheManager
     */
    public function setCacheStorage($cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;
        return $this;
    }

    /**
     * @return DateTimeFactoryInterface
     */
    public function getDateTimeFactory()
    {
        return $this->dateTimeFactory;
    }

    /**
     * @param $dateTimeFactory
     * @return CacheManager
     */
    public function setDateTimeFactory($dateTimeFactory)
    {
        $this->dateTimeFactory = $dateTimeFactory;
        return $this;
    }

    /**
     * @param $key
     * @param EnvironmentInterface $environment
     * @return string
     */
    protected function key($key, $environment)
    {
        return $environment == null ? $key : $key . '.' . $environment->getNormalizedName();
    }

    /**
     * @param $environment
     * @return string
     */
    protected function nodeStatisticsKey($environment)
    {
        return $this->key(static::KEY_NODE_STATISTICS, $environment);
    }

    /**
     * @param $environment
     * @return string
     */
    protected function reportStatisticsKey($environment)
    {
        return $this->key(static::KEY_REPORT_STATISTICS, $environment);
    }

    /**
     * @param $environment
     * @return string
     */
    protected function refreshedAtKey($environment)
    {
        return $this->key(static::KEY_REFRESHED_AT, $environment);
    }

    /**
     * @param $environment
     * @return string
     */
    protected function statusKey($environment)
    {
        return $this->key(static::KEY_CACHE_STATUS, $environment);
    }
}
