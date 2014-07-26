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
use KmbPuppetDb\Service;

class NodeStatisticsProxy implements Service\NodeStatisticsInterface
{
    /**
     * @var CacheManagerInterface
     */
    protected $cacheManager;

    /**
     * Get unchanged nodes count.
     *
     * @return int
     */
    public function getUnchangedCount()
    {
        return $this->getStatistic('unchangedCount');
    }

    /**
     * Get changed nodes count.
     *
     * @return int
     */
    public function getChangedCount()
    {
        return $this->getStatistic('changedCount');
    }

    /**
     * Get failed nodes count.
     *
     * @return int
     */
    public function getFailedCount()
    {
        return $this->getStatistic('failedCount');
    }

    /**
     * Get nodes count.
     *
     * @return int
     */
    public function getNodesCount()
    {
        return $this->getStatistic('nodesCount');
    }

    /**
     * Get nodes count grouped by Operating System.
     *
     * @return array
     */
    public function getNodesCountByOS()
    {
        return $this->getStatistic('nodesCountByOS');
    }

    /**
     * Get nodes percentage grouped by Operating System.
     *
     * @return array
     */
    public function getNodesPercentageByOS()
    {
        return $this->getStatistic('nodesPercentageByOS');
    }

    /**
     * Get OS count.
     *
     * @return int
     */
    public function getOSCount()
    {
        return $this->getStatistic('osCount');
    }

    /**
     * Get recently rebooted nodes.
     *
     * @return array
     */
    public function getRecentlyRebootedNodes()
    {
        return $this->getStatistic('recentlyRebootedNodes');
    }

    /**
     * Get all statistics.
     *
     * @return array
     */
    public function getAllAsArray()
    {
        return $this->getCacheManager()->getItem('nodesStatistics');
    }

    /**
     * @return CacheManagerInterface
     */
    public function getCacheManager()
    {
        return $this->cacheManager;
    }

    /**
     * @param $cacheManager
     * @return NodeStatisticsProxy
     */
    public function setCacheManager($cacheManager)
    {
        $this->cacheManager = $cacheManager;
        return $this;
    }

    /**
     * @param $statistic
     * @return mixed
     */
    protected function getStatistic($statistic)
    {
        $allStatistics = $this->getAllAsArray();
        return $allStatistics[$statistic];
    }
}
