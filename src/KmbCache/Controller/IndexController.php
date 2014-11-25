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

use KmbAuthentication\Controller\AuthenticatedControllerInterface;
use KmbCache\Service\CacheManagerInterface;
use KmbDomain\Model\EnvironmentInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Exception;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController implements AuthenticatedControllerInterface
{
    public function refreshExpiredAction()
    {
        $serviceManager = $this->getServiceLocator();

        /** @var EnvironmentInterface $environment */
        $environment = $serviceManager->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));

        /** @var CacheManagerInterface $cacheManager */
        $cacheManager = $serviceManager->get('KmbCache\Service\CacheManager');
        $refresh = $cacheManager->refreshExpiredCache($environment);

        return new JsonModel([
            'title' => $this->translate('Updating cache'),
            'message' => $this->translate('Cache data has been refreshed.'),
            'refresh' => $refresh
        ]);
    }

    public function clearAction()
    {
        $serviceManager = $this->getServiceLocator();

        /** @var EnvironmentInterface $environment */
        $environment = $serviceManager->get('EnvironmentRepository')->getById($this->params()->fromRoute('envId'));

        /** @var CacheManagerInterface $cacheManager */
        $cacheManager = $serviceManager->get('KmbCache\Service\CacheManager');
        $cacheManager->clearCache($environment);

        return new JsonModel(['message' => 'OK']);
    }
}
