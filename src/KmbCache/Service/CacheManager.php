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
use KmbDomain\Model\EnvironmentInterface;
use KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface;
use KmbPuppetDb\Query\Query;
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
    const STATUS_SUFFIX = '.status';
    const REFRESHED_AT_SUFFIX = '.refreshedAt';
    const KEY_NODE_STATISTICS = 'nodeStatistics';
    const KEY_REPORT_STATISTICS = 'reportStatistics';
    const PENDING = 'pending';
    const COMPLETED = 'completed';
    const EXPIRATION_TIME = '5 minutes';

    /** @var NodeStatisticsInterface */
    protected $nodeStatisticsService;

    /** @var ReportStatisticsInterface */
    protected $reportStatisticsService;

    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var DateTimeFactoryInterface */
    protected $dateTimeFactory;

    /** @var QuerySuffixBuilderInterface */
    protected $querySuffixBuilder;

    /** @var EnvironmentsQueryBuilderInterface */
    protected $nodesEnvironmentsQueryBuilder;

    /** @var EnvironmentsQueryBuilderInterface */
    protected $reportsEnvironmentsQueryBuilder;

    /** @var \KmbPermission\Service\EnvironmentInterface */
    protected $permissionEnvironmentService;

    /**
     * Refresh cache if necessary.
     *
     * @param EnvironmentInterface $environment
     */
    public function refreshExpiredCache($environment = null)
    {
        $environments = $this->permissionEnvironmentService->getAllReadable($environment);

        $query = $this->getNodesEnvironmentsQueryBuilder()->build($environments);
        $this->refresh(static::KEY_NODE_STATISTICS, $query, function ($query) {
            return $this->getNodeStatisticsService()->getAllAsArray($query);
        });

        $query = $this->getReportsEnvironmentsQueryBuilder()->build($environments);
        $this->refresh(static::KEY_REPORT_STATISTICS, $query, function ($query) {
            return $this->getReportStatisticsService()->getAllAsArray($query);
        });
    }

    /**
     * @param $key
     * @return string
     */
    public static function statusKeyFor($key)
    {
        return $key . static::STATUS_SUFFIX;
    }

    /**
     * @param $key
     * @return string
     */
    public static function refreshedAtKeyFor($key)
    {
        return $key . static::REFRESHED_AT_SUFFIX;
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
     * Set QuerySuffixBuilder.
     *
     * @param \KmbCache\Service\QuerySuffixBuilderInterface $querySuffixBuilder
     * @return CacheManager
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
     * Set NodesEnvironmentsQueryBuilder.
     *
     * @param \KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface $nodesEnvironmentsQueryBuilder
     * @return CacheManager
     */
    public function setNodesEnvironmentsQueryBuilder($nodesEnvironmentsQueryBuilder)
    {
        $this->nodesEnvironmentsQueryBuilder = $nodesEnvironmentsQueryBuilder;
        return $this;
    }

    /**
     * Get NodesEnvironmentsQueryBuilder.
     *
     * @return \KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface
     */
    public function getNodesEnvironmentsQueryBuilder()
    {
        return $this->nodesEnvironmentsQueryBuilder;
    }

    /**
     * Set ReportsEnvironmentsQueryBuilder.
     *
     * @param \KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface $reportsEnvironmentsQueryBuilder
     * @return CacheManager
     */
    public function setReportsEnvironmentsQueryBuilder($reportsEnvironmentsQueryBuilder)
    {
        $this->reportsEnvironmentsQueryBuilder = $reportsEnvironmentsQueryBuilder;
        return $this;
    }

    /**
     * Get ReportsEnvironmentsQueryBuilder.
     *
     * @return \KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface
     */
    public function getReportsEnvironmentsQueryBuilder()
    {
        return $this->reportsEnvironmentsQueryBuilder;
    }

    /**
     * Set PermissionEnvironmentService.
     *
     * @param \KmbPermission\Service\EnvironmentInterface $permissionEnvironmentService
     * @return CacheManager
     */
    public function setPermissionEnvironmentService($permissionEnvironmentService)
    {
        $this->permissionEnvironmentService = $permissionEnvironmentService;
        return $this;
    }

    /**
     * Get PermissionEnvironmentService.
     *
     * @return \KmbPermission\Service\EnvironmentInterface
     */
    public function getPermissionEnvironmentService()
    {
        return $this->permissionEnvironmentService;
    }

    /**
     * @param string   $key
     * @param Query    $query
     * @param callback $getRealDataCallback
     */
    protected function refresh($key, $query, $getRealDataCallback)
    {
        $cacheStorage = $this->getCacheStorage();
        $suffix = $this->getQuerySuffixBuilder()->build($query);

        $status = $cacheStorage->getItem($this->statusKeyFor($key . $suffix));
        $refreshedAt = $cacheStorage->getItem($this->refreshedAtKeyFor($key . $suffix));
        if (
            $status !== static::PENDING &&
            (
                $refreshedAt == null ||
                $this->getDateTimeFactory()->now() > $refreshedAt->add(\DateInterval::createFromDateString(self::EXPIRATION_TIME))
            )
        ) {
            $cacheStorage->setItem($this->statusKeyFor($key . $suffix), static::PENDING);
            $data = $getRealDataCallback($query);
            $cacheStorage->setItem($key . $suffix, $data);
            $cacheStorage->setItem($this->statusKeyFor($key . $suffix), static::COMPLETED);
            $cacheStorage->setItem($this->refreshedAtKeyFor($key . $suffix), $this->getDateTimeFactory()->now());
        }
    }
}
