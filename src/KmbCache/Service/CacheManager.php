<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/multimediabs/kamba for the canonical source repository
 *
 * This file is part of kamba.
 *
 * kamba is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * kamba is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with kamba.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbCache\Service;

use KmbCache\Exception\RuntimeException;
use KmbCore\DateTimeFactoryInterface;
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
    /**
     * @var NodeStatisticsInterface
     */
    protected $nodeStatisticsService;

    /**
     * @var ReportStatisticsInterface
     */
    protected $reportStatisticsService;

    /**
     * @var StorageInterface
     */
    protected $cacheStorage;

    /**
     * @var DateTimeFactoryInterface
     */
    protected $dateTimeFactory;

    /**
     * Refresh cache
     *
     * @param bool $forceOnPending Allows to refresh cache even if the status is pending.
     * @throws RuntimeException When the cache status is pending (and $forceOnPending is false).
     */
    public function refresh($forceOnPending = false)
    {
        if (!$forceOnPending && $this->getCacheStorage()->getItem('cacheStatus') == static::PENDING) {
            throw new RuntimeException('Cache refresh is already in progress');
        }
        $this->getCacheStorage()->setItem('cacheStatus', static::PENDING);
        $this->getCacheStorage()->setItem('nodesStatistics', $this->getNodeStatisticsService()->getAllAsArray());
        $this->getCacheStorage()->setItem('reportsStatistics', $this->getReportStatisticsService()->getAllAsArray());
        $this->getCacheStorage()->setItem('cacheStatus', static::COMPLETED);
        $this->getCacheStorage()->setItem('refreshedAt', $this->getDateTimeFactory()->now());
    }

    /**
     * Get the status of the cache (null|pending|completed)
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getCacheStorage()->getItem('cacheStatus');
    }

    /**
     * Get the time of the last refresh
     *
     * @return \DateTime
     */
    public function getRefreshedAt()
    {
        return $this->getCacheStorage()->getItem('refreshedAt');
    }

    /**
     * Get an item from the cache
     *
     * @param $key
     * @return mixed Data on success, null on failure
     */
    public function getItem($key)
    {
        if (!$this->getCacheStorage()->hasItem($key)) {
            $this->refresh(true);
        }
        return $this->getCacheStorage()->getItem($key);
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
}
