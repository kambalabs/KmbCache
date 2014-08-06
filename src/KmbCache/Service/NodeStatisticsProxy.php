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

use KmbPuppetDb\Model;
use KmbPuppetDb\Query\Query;
use KmbPuppetDb\Service;
use Zend\Cache\Storage\StorageInterface;

class NodeStatisticsProxy implements Service\NodeStatisticsInterface
{
    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var Service\NodeStatisticsInterface */
    protected $nodeStatisticsService;

    /** @var QuerySuffixBuilderInterface */
    protected $querySuffixBuilder;

    /**
     * Get unchanged nodes count.
     *
     * @param Query|array $query
     * @return int
     */
    public function getUnchangedCount($query = null)
    {
        return $this->getStatistic('unchangedCount', $query);
    }

    /**
     * Get changed nodes count.
     *
     * @param Query|array $query
     * @return int
     */
    public function getChangedCount($query = null)
    {
        return $this->getStatistic('changedCount', $query);
    }

    /**
     * Get failed nodes count.
     *
     * @param Query|array $query
     * @return int
     */
    public function getFailedCount($query = null)
    {
        return $this->getStatistic('failedCount', $query);
    }

    /**
     * Get nodes count.
     *
     * @param Query|array $query
     * @return int
     */
    public function getNodesCount($query = null)
    {
        return $this->getStatistic('nodesCount', $query);
    }

    /**
     * Get nodes count grouped by Operating System.
     *
     * @param Query|array $query
     * @return array
     */
    public function getNodesCountByOS($query = null)
    {
        return $this->getStatistic('nodesCountByOS', $query);
    }

    /**
     * Get nodes percentage grouped by Operating System.
     *
     * @param Query|array $query
     * @return array
     */
    public function getNodesPercentageByOS($query = null)
    {
        return $this->getStatistic('nodesPercentageByOS', $query);
    }

    /**
     * Get OS count.
     *
     * @param Query|array $query
     * @return int
     */
    public function getOSCount($query = null)
    {
        return $this->getStatistic('osCount', $query);
    }

    /**
     * Get recently rebooted nodes.
     *
     * @param Query|array $query
     * @return array
     */
    public function getRecentlyRebootedNodes($query = null)
    {
        return $this->getStatistic('recentlyRebootedNodes', $query);
    }

    /**
     * Get all statistics.
     *
     * @param Query|array $query
     * @return array
     */
    public function getAllAsArray($query = null)
    {
        $key = CacheManager::KEY_NODE_STATISTICS . $this->getQuerySuffixBuilder()->build($query);
        if ($this->getCacheStorage()->hasItem($key)) {
            return $this->getCacheStorage()->getItem($key);
        }

        return $this->getNodeStatisticsService()->getAllAsArray($query);
    }

    /**
     * @return Service\NodeStatisticsInterface
     */
    public function getNodeStatisticsService()
    {
        return $this->nodeStatisticsService;
    }

    /**
     * @param $nodeStatisticsService
     * @return NodeStatisticsProxy
     */
    public function setNodeStatisticsService($nodeStatisticsService)
    {
        $this->nodeStatisticsService = $nodeStatisticsService;
        return $this;
    }

    /**
     * Set CacheStorage.
     *
     * @param \Zend\Cache\Storage\StorageInterface $cacheStorage
     * @return NodeStatisticsProxy
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
     * Set QuerySuffixBuilder.
     *
     * @param \KmbCache\Service\QuerySuffixBuilderInterface $querySuffixBuilder
     * @return NodeStatisticsProxy
     */
    public function setQuerySuffixBuilder($querySuffixBuilder)
    {
        $this->querySuffixBuilder = $querySuffixBuilder;
        return $this;
    }

    /**
     * Get QuerySuffixBuilder.
     *
     * @return \KmbCache\Service\QuerySuffixBuilderInterface
     */
    public function getQuerySuffixBuilder()
    {
        return $this->querySuffixBuilder;
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
}
