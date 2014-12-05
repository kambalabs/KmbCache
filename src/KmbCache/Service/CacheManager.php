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
use KmbPmProxy\Model\PuppetModule;
use KmbPmProxy\Service\PuppetModuleInterface;
use KmbPuppetDb\Query\Query;
use KmbPuppetDb\Query\QueryBuilderInterface;
use KmbPuppetDb\Service\NodeStatisticsInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\Log\Logger;

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
    const KEY_PUPPET_MODULES = 'modules.';
    const PENDING = 'pending';
    const COMPLETED = 'completed';
    const EXPIRATION_TIME = '5 minutes';

    /** @var NodeStatisticsInterface */
    protected $nodeStatisticsService;

    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var DateTimeFactoryInterface */
    protected $dateTimeFactory;

    /** @var QuerySuffixBuilderInterface */
    protected $querySuffixBuilder;

    /** @var QueryBuilderInterface */
    protected $nodesEnvironmentsQueryBuilder;

    /** @var \KmbPermission\Service\EnvironmentInterface */
    protected $permissionEnvironmentService;

    /** @var PuppetModuleInterface */
    protected $pmProxyPuppetModuleService;

    /** @var Logger */
    protected $logger;

    /**
     * @param array|Query $query
     * @return bool
     */
    public function refreshNodeStatisticsIfExpired($query)
    {
        $suffix = $this->getQuerySuffixBuilder()->build($query);
        $refreshNodes = $this->refresh(static::KEY_NODE_STATISTICS . $suffix, function () use ($query) {
            return $this->getNodeStatisticsService()->getAllAsArray($query);
        });
        return $refreshNodes;
    }

    /**
     * @param array|Query $query
     * @return array
     */
    public function getNodeStatistics($query = null)
    {
        $this->refreshNodeStatisticsIfExpired($query);
        return $this->cacheStorage->getItem(static::KEY_NODE_STATISTICS . $this->getQuerySuffixBuilder()->build($query));
    }

    /**
     * @param EnvironmentInterface $environment
     * @return bool
     */
    public function refreshPuppetModulesIfExpired($environment)
    {
        if ($environment != null) {
            return $this->refresh(static::KEY_PUPPET_MODULES . $environment->getNormalizedName(), function () use ($environment) {
                return $this->getPmProxyPuppetModuleService()->getAllInstalledByEnvironment($environment);
            });
        }
        return false;
    }

    /**
     * @param EnvironmentInterface $environment
     * @return PuppetModule[]
     */
    public function getPuppetModules($environment = null)
    {
        $this->refreshPuppetModulesIfExpired($environment);
        return $this->cacheStorage->getItem(static::KEY_PUPPET_MODULES . $environment->getNormalizedName());
    }

    /**
     * Refresh cache if necessary.
     * Return true if some cache has been refreshed.
     *
     * @param EnvironmentInterface $environment
     * @return bool
     */
    public function refreshExpiredCache($environment = null)
    {
        $environments = $this->permissionEnvironmentService->getAllReadable($environment);
        $query = $this->getNodesEnvironmentsQueryBuilder()->build($environments);

        $nodesRefreshed = $this->refreshNodeStatisticsIfExpired($query);
        $modulesRefreshed = $this->refreshPuppetModulesIfExpired($environment);

        return $nodesRefreshed || $modulesRefreshed;
    }

    /**
     * Clear cache.
     *
     * @param EnvironmentInterface $environment
     */
    public function clearCache($environment = null)
    {
        $environments = $this->permissionEnvironmentService->getAllReadable($environment);
        $query = $this->getNodesEnvironmentsQueryBuilder()->build($environments);

        $this->clearCacheFor(static::KEY_NODE_STATISTICS . $this->getQuerySuffixBuilder()->build($query));

        if ($environment != null) {
            $this->clearCacheFor(static::KEY_PUPPET_MODULES . $environment->getNormalizedName());
        }
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
     * Set PmProxyModuleService.
     *
     * @param \KmbPmProxy\Service\PuppetModuleInterface $pmProxyPuppetModuleService
     * @return CacheManager
     */
    public function setPmProxyPuppetModuleService($pmProxyPuppetModuleService)
    {
        $this->pmProxyPuppetModuleService = $pmProxyPuppetModuleService;
        return $this;
    }

    /**
     * Get PmProxyModuleService.
     *
     * @return \KmbPmProxy\Service\PuppetModuleInterface
     */
    public function getPmProxyPuppetModuleService()
    {
        return $this->pmProxyPuppetModuleService;
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
     * @param \KmbPuppetDb\Query\QueryBuilderInterface $nodesEnvironmentsQueryBuilder
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
     * @return \KmbPuppetDb\Query\QueryBuilderInterface
     */
    public function getNodesEnvironmentsQueryBuilder()
    {
        return $this->nodesEnvironmentsQueryBuilder;
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
     * Set Logger.
     *
     * @param \Zend\Log\Logger $logger
     * @return CacheManager
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get Logger.
     *
     * @return \Zend\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string   $key
     * @param callback $getRealDataCallback
     * @return bool
     */
    protected function refresh($key, $getRealDataCallback)
    {
        $status = $this->cacheStorage->getItem($this->statusKeyFor($key));
        $refreshedAt = $this->cacheStorage->getItem($this->refreshedAtKeyFor($key));
        if (
            $status !== static::PENDING &&
            (
                $refreshedAt == null ||
                $this->getDateTimeFactory()->now() > $refreshedAt->add(\DateInterval::createFromDateString(self::EXPIRATION_TIME))
            )
        ) {
            $this->logger->debug("Refreshing cache for $key ...");
            $this->cacheStorage->setItem($this->statusKeyFor($key), static::PENDING);
            $data = $getRealDataCallback();
            $this->cacheStorage->setItem($key, $data);
            $this->cacheStorage->setItem($this->statusKeyFor($key), static::COMPLETED);
            $this->cacheStorage->setItem($this->refreshedAtKeyFor($key), $this->getDateTimeFactory()->now());
            $this->logger->debug("Cache for $key has been refreshed !");
            return true;
        }
        return false;
    }

    /**
     * @param $key
     */
    protected function clearCacheFor($key)
    {
        $this->logger->debug('Removing cache for ' . $key);
        $this->cacheStorage->removeItem($key);
        $this->cacheStorage->removeItem($this->statusKeyFor($key));
        $this->cacheStorage->removeItem($this->refreshedAtKeyFor($key));
    }
}
