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
namespace KmbCache\Controller;

use KmbCache\Exception\RuntimeException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $cacheManager = $this->getServiceLocator()->get('KmbCache\Service\CacheManager');
        /** @var \DateTime $refreshedAt */
        $refreshedAt = $cacheManager->getRefreshedAt();
        if ($refreshedAt) {
            $refreshedAt = $refreshedAt->format(\DateTime::RFC1123);
        }
        return new JsonModel([
            'refreshed_at' => $refreshedAt,
            'status' => $cacheManager->getStatus(),
        ]);
    }

    public function refreshAction()
    {
        try {
            $cacheManager = $this->getServiceLocator()->get('KmbCache\Service\CacheManager');
            $cacheManager->refresh();
        } catch (RuntimeException $exception) {
            $this->getResponse()->setStatusCode(409);
            return new JsonModel(['message' => $exception->getMessage()]);
        } catch (\Exception $exception) {
            $this->getResponse()->setStatusCode(500);
            echo $exception->getTraceAsString();
            return new JsonModel(['message' => $exception->getMessage()]);
        }

        return new JsonModel(['message' => 'OK']);
    }
}
