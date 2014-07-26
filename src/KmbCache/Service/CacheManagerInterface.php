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
use KmbPuppetDb\Service\NodeStatisticsInterface;
use KmbPuppetDb\Service\ReportStatisticsInterface;

interface CacheManagerInterface
{
    const PENDING = 'pending';
    const COMPLETED = 'completed';

    /**
     * Refresh cache
     *
     * @param bool $forceOnPending Allows to refresh cache even if the status is pending.
     * @throws RuntimeException When the cache status is pending (and $force is false).
     */
    public function refresh($forceOnPending = false);

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
     * Get an item from the cache
     *
     * @param $key
     * @return mixed Data on success, null on failure
     */
    public function getItem($key);

    /**
     * @return NodeStatisticsInterface
     */
    public function getNodeStatisticsService();

    /**
     * @return ReportStatisticsInterface
     */
    public function getReportStatisticsService();
}
