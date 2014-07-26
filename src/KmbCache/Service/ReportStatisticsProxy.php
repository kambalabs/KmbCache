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

use KmbPuppetDb\Service\ReportStatisticsInterface;

class ReportStatisticsProxy implements ReportStatisticsInterface
{
    /**
     * @var CacheManagerInterface
     */
    protected $cacheManager;

    /**
     * Get all statistics as array.
     *
     * @return array
     */
    public function getAllAsArray()
    {
        return $this->getCacheManager()->getItem('reportsStatistics');
    }

    /**
     * Get success count.
     *
     * @return int
     */
    public function getSuccessCount()
    {
        return $this->getStatistic('success');
    }

    /**
     * Get failures count.
     *
     * @return int
     */
    public function getFailuresCount()
    {
        return $this->getStatistic('failures');
    }

    /**
     * Get skips count.
     *
     * @return int
     */
    public function getSkipsCount()
    {
        return $this->getStatistic('skips');
    }

    /**
     * Get noops count.
     *
     * @return int
     */
    public function getNoopsCount()
    {
        return $this->getStatistic('noops');
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
     * @return ReportStatisticsProxy
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
