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

use KmbDomain\Model\EnvironmentInterface;
use KmbPuppetDb\Query\QueryBuilderInterface;

class NodeStatisticsDataContextBuilder implements DataContextBuilderInterface
{
    /** @var  \KmbPermission\Service\EnvironmentInterface */
    protected $permissionEnvironmentService;

    /** @var  QueryBuilderInterface */
    protected $nodesEnvironmentsQueryBuilder;

    /**
     * @param EnvironmentInterface $environment
     * @return mixed
     */
    public function build(EnvironmentInterface $environment = null)
    {
        $environments = $this->getPermissionEnvironmentService()->getAllReadable($environment);
        return $this->getNodesEnvironmentsQueryBuilder()->build($environments);
    }

    /**
     * Set NodesEnvironmentsQueryBuilder.
     *
     * @param \KmbPuppetDb\Query\QueryBuilderInterface $nodesEnvironmentsQueryBuilder
     * @return NodeStatisticsDataContextBuilder
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
     * @return NodeStatisticsDataContextBuilder
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
}
