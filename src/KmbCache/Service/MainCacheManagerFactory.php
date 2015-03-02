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

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MainCacheManagerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $mainCacheManager = new MainCacheManager();
        $logger = $serviceLocator->get('Logger');
        $cacheStorage = $serviceLocator->get('CacheService');
        $dateTimeFactory = $serviceLocator->get('DateTimeFactory');

        $config = $serviceLocator->get('Config');
        if (array_key_exists('cache_manager', $config)) {
            foreach ($config['cache_manager'] as $key => $cacheManagerConfig) {
                if (array_key_exists('service', $cacheManagerConfig)) {
                    /** @var AbstractCacheManager $cacheManager */
                    $cacheManager = $serviceLocator->get($cacheManagerConfig['service']);
                    if ($cacheManager instanceof AbstractCacheManager) {
                        $cacheManager->setLogger($logger);
                        $cacheManager->setCacheStorage($cacheStorage);
                        $cacheManager->setDateTimeFactory($dateTimeFactory);
                        $cacheManager->setKey($key);
                        $cacheManager->setDescription(array_key_exists('description', $cacheManagerConfig) ? $cacheManagerConfig['description'] : $key);
                        if ($cacheManager->getSuffixBuilder() == null) {
                            $cacheManager->setSuffixBuilder(new DefaultSuffixBuilder());
                        }
                        if ($cacheManager->getDataContextBuilder() == null) {
                            $cacheManager->setDataContextBuilder(new DefaultDataContextBuilder());
                        }
                        $mainCacheManager->addCacheManager($key, $cacheManager);
                    } else {
                        $logger->warn("Invalid configuration for cache manager $key : service should extends KmbCache\\Service\\AbstractCacheManager");
                    }
                } else {
                    $logger->warn("Invalid configuration for cache manager $key : missing service");
                }
            }
        }
        return $mainCacheManager;
    }
}
