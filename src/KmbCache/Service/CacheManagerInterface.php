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

use KmbCache\Exception\RuntimeException;
use KmbDomain\Model\EnvironmentInterface;
use KmbPuppetDb\Service\NodeStatisticsInterface;
use KmbPuppetDb\Service\ReportStatisticsInterface;

interface CacheManagerInterface
{
    const KEY_CACHE_STATUS = 'cacheStatus';
    const KEY_REFRESHED_AT = 'refreshedAt';
    const KEY_NODE_STATISTICS = 'nodeStatistics';
    const KEY_REPORT_STATISTICS = 'reportStatistics';
    const PENDING = 'pending';
    const COMPLETED = 'completed';

    /**
     * Refresh cache
     *
     * @param EnvironmentInterface $environment
     * @param bool $forceOnPending Allows to refresh cache even if the status is pending.
     * @throws RuntimeException When the cache status is pending (and $force is false).
     */
    public function refresh($environment = null, $forceOnPending = false);

    /**
     * Force refreshing cache
     *
     * @param EnvironmentInterface $environment
     * @throws RuntimeException When the cache status is pending (and $force is false).
     */
    public function forceRefresh($environment = null);

    /**
     * Get the status of the cache (null|pending|completed)
     *
     * @return string
     */
    public function getStatus();

    /**
     * Get the time of the last refresh
     *
     * @return \DateTime
     */
    public function getRefreshedAt();

    /**
     * @return NodeStatisticsInterface
     */
    public function getNodeStatisticsService();

    /**
     * @return ReportStatisticsInterface
     */
    public function getReportStatisticsService();
}
