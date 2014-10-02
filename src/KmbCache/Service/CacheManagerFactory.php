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

use KmbPermission\Service\EnvironmentInterface;
use KmbPmProxy\Service\ModuleInterface;
use KmbPuppetDb\Query\QueryBuilderInterface;
use KmbPuppetDb\Service;
use Zend\Log\Logger;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheManagerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cacheManager = new CacheManager();
        $cacheManager->setCacheStorage($serviceLocator->get('CacheService'));

        // Cache manager needs real node statistics service
        $nodeStatisticsService = $serviceLocator->get('KmbPuppetDb\Service\NodeStatistics');
        $cacheManager->setNodeStatisticsService($nodeStatisticsService);

        /** @var QuerySuffixBuilderInterface $querySuffixBuilder */
        $querySuffixBuilder = $serviceLocator->get('KmbCache\Service\QuerySuffixBuilder');
        $cacheManager->setQuerySuffixBuilder($querySuffixBuilder);

        /** @var QueryBuilderInterface $nodesEnvironmentQueryBuilder */
        $nodesEnvironmentQueryBuilder = $serviceLocator->get('KmbPuppetDb\Query\NodesEnvironmentsQueryBuilder');
        $cacheManager->setNodesEnvironmentsQueryBuilder($nodesEnvironmentQueryBuilder);

        /** @var EnvironmentInterface $permissionEnvironmentService */
        $permissionEnvironmentService = $serviceLocator->get('KmbPermission\Service\Environment');
        $cacheManager->setPermissionEnvironmentService($permissionEnvironmentService);

        /** @var ModuleInterface $moduleService */
        $moduleService = $serviceLocator->get('KmbPmProxy\Service\Module');
        $cacheManager->setPmProxyModuleService($moduleService);

        /** @var Logger $logger */
        $logger = $serviceLocator->get('Logger');
        $cacheManager->setLogger($logger);

        $cacheManager->setDateTimeFactory($serviceLocator->get('DateTimeFactory'));
        return $cacheManager;
    }
}
