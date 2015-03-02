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
use Zend\Cache\Storage\StorageInterface;
use Zend\Log\Logger;

abstract class AbstractCacheManager implements CacheManagerInterface
{
    const STATUS_SUFFIX = '.status';
    const REFRESHED_AT_SUFFIX = '.refreshedAt';
    const PENDING = 'pending';
    const NEED_REFRESH = 'needRefresh';
    const COMPLETED = 'completed';
    const EXPIRATION_TIME = '5 minutes';

    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var DateTimeFactoryInterface */
    protected $dateTimeFactory;

    /** @var Logger */
    protected $logger;

    /** @var  string */
    protected $key;

    /** @var  SuffixBuilderInterface */
    protected $suffixBuilder;

    /** @var  DataContextBuilderInterface */
    protected $dataContextBuilder;

    /** @var  string */
    protected $description;

    /**
     * @param mixed $context
     * @return mixed
     */
    abstract public function getDataFromRealService($context = null);

    /**
     * @param mixed $context
     * @return mixed
     */
    public function getData($context = null)
    {
        $this->refreshExpiredCacheForContext($context);
        return unserialize($this->cacheStorage->getItem($this->getKey($context)));
    }

    /**
     * @param EnvironmentInterface $environment
     * @param bool                 $forceRefresh
     * @return bool
     */
    public function refreshExpiredCache($environment = null, $forceRefresh = false)
    {
        return $this->refreshExpiredCacheForContext($this->getDataContextBuilder()->build($environment), $forceRefresh);
    }

    /**
     * @param EnvironmentInterface $environment
     * @return bool
     */
    public function forceRefreshCache($environment = null)
    {
        return $this->refreshExpiredCache($environment, true);
    }

    /**
     * Refresh cache if necessary.
     *
     * @param mixed $context
     * @param bool  $forceRefresh
     * @return bool
     */
    protected function refreshExpiredCacheForContext($context = null, $forceRefresh = false)
    {
        $key = $this->getKey($context);
        if ($forceRefresh || $this->needRefresh($key)) {
            $this->logger->debug("Refreshing cache for $key ...");
            $this->cacheStorage->setItem($this->statusKeyFor($key), static::PENDING);
            $this->cacheStorage->setItem($key, serialize($this->getDataFromRealService($context)));
            $this->cacheStorage->setItem($this->statusKeyFor($key), static::COMPLETED);
            $this->cacheStorage->setItem($this->refreshedAtKeyFor($key), serialize($this->getDateTimeFactory()->now()));
            $this->logger->debug("Cache for $key has been refreshed !");
            return true;
        }
        return false;
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function needRefresh($key)
    {
        $status = $this->cacheStorage->getItem(static::statusKeyFor($key));
        if ($status == static::NEED_REFRESH || ($status == static::PENDING && !$this->cacheStorage->hasItem($key))) {
            return true;
        }

        $refreshedAt = unserialize($this->cacheStorage->getItem(static::refreshedAtKeyFor($key)));
        $expirationTime = $refreshedAt ? $refreshedAt->add(\DateInterval::createFromDateString(self::EXPIRATION_TIME)) : null;
        if ($status !== static::PENDING && $this->getDateTimeFactory()->now() > $expirationTime) {
            return true;
        }

        return false;
    }

    /**
     * Set Key.
     *
     * @param string $key
     * @return AbstractCacheManager
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param mixed $context
     * @return string
     */
    public function getKey($context = null)
    {
        return $this->key . $this->getSuffixBuilder()->build($context);
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
     * Set CacheStorage.
     *
     * @param \Zend\Cache\Storage\StorageInterface $cacheStorage
     * @return AbstractCacheManager
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
     * Set Logger.
     *
     * @param \Zend\Log\Logger $logger
     * @return AbstractCacheManager
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
     * Set DateTimeFactory.
     *
     * @param \KmbBase\DateTimeFactoryInterface $dateTimeFactory
     * @return AbstractCacheManager
     */
    public function setDateTimeFactory($dateTimeFactory)
    {
        $this->dateTimeFactory = $dateTimeFactory;
        return $this;
    }

    /**
     * Get DateTimeFactory.
     *
     * @return \KmbBase\DateTimeFactoryInterface
     */
    public function getDateTimeFactory()
    {
        return $this->dateTimeFactory;
    }

    /**
     * Set SuffixBuilder.
     *
     * @param \KmbCache\Service\SuffixBuilderInterface $suffixBuilder
     * @return AbstractCacheManager
     */
    public function setSuffixBuilder($suffixBuilder)
    {
        $this->suffixBuilder = $suffixBuilder;
        return $this;
    }

    /**
     * Get SuffixBuilder.
     *
     * @return \KmbCache\Service\SuffixBuilderInterface
     */
    public function getSuffixBuilder()
    {
        return $this->suffixBuilder;
    }

    /**
     * Set DataContextBuilder.
     *
     * @param \KmbCache\Service\DataContextBuilderInterface $dataContextBuilder
     * @return AbstractCacheManager
     */
    public function setDataContextBuilder($dataContextBuilder)
    {
        $this->dataContextBuilder = $dataContextBuilder;
        return $this;
    }

    /**
     * Get DataContextBuilder.
     *
     * @return \KmbCache\Service\DataContextBuilderInterface
     */
    public function getDataContextBuilder()
    {
        return $this->dataContextBuilder;
    }

    /**
     * Set Description.
     *
     * @param string $description
     * @return AbstractCacheManager
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get Description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
