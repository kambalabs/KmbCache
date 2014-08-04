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

use KmbPuppetDb\Query;
use KmbPuppetDb\Service;
use Zend\Cache\Storage\StorageInterface;

class ReportStatisticsProxy implements Service\ReportStatisticsInterface
{
    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var Service\ReportStatisticsInterface */
    protected $reportStatisticsService;

    /**
     * Get all statistics as array.
     *
     * @param Query|array $query
     * @return array
     */
    public function getAllAsArray($query = null)
    {
        $key = null;
        if ($query == null) {
            $key = CacheManagerInterface::KEY_REPORT_STATISTICS;
        } elseif ($this->isEnvironmentQuery($query)) {
            $key = CacheManagerInterface::KEY_REPORT_STATISTICS . '.' . $query[2];
        }

        if ($key != null && $this->getCacheStorage()->hasItem($key)) {
            return $this->getCacheStorage()->getItem($key);
        }

        return $this->getReportStatisticsService()->getAllAsArray($query);
    }

    /**
     * Get success count.
     *
     * @param Query|array $query
     * @return int
     */
    public function getSuccessCount($query = null)
    {
        return $this->getStatistic('success', $query);
    }

    /**
     * Get failures count.
     *
     * @param Query|array $query
     * @return int
     */
    public function getFailuresCount($query = null)
    {
        return $this->getStatistic('failures', $query);
    }

    /**
     * Get skips count.
     *
     * @param Query|array $query
     * @return int
     */
    public function getSkipsCount($query = null)
    {
        return $this->getStatistic('skips', $query);
    }

    /**
     * Get noops count.
     *
     * @param Query|array $query
     * @return int
     */
    public function getNoopsCount($query = null)
    {
        return $this->getStatistic('noops', $query);
    }

    /**
     * Set CacheStorage.
     *
     * @param \Zend\Cache\Storage\StorageInterface $cacheStorage
     * @return ReportStatisticsProxy
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
     * Set ReportStatisticsService.
     *
     * @param \KmbPuppetDb\Service\ReportStatisticsInterface $reportStatisticsService
     * @return ReportStatisticsProxy
     */
    public function setReportStatisticsService($reportStatisticsService)
    {
        $this->reportStatisticsService = $reportStatisticsService;
        return $this;
    }

    /**
     * Get ReportStatisticsService.
     *
     * @return \KmbPuppetDb\Service\ReportStatisticsInterface
     */
    public function getReportStatisticsService()
    {
        return $this->reportStatisticsService;
    }

    /**
     * @param             $statistic
     * @param Query|array $query
     * @return mixed
     */
    protected function getStatistic($statistic, $query = null)
    {
        $allStatistics = $this->getAllAsArray($query);
        return $allStatistics[$statistic];
    }

    /**
     * @param $query
     * @return bool
     */
    protected function isEnvironmentQuery($query)
    {
        return count($query) === 3 && $query[0] === '=' && $query[1] == 'environment' && !empty($query[2]);
    }
}
