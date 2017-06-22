<?php
/**
 *
 * @author Semih Serhat Karakaya
 * @copyright Copyright (c) 2016, ITU IT HEAD OFFICE.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\Owner_Fixer\AppInfo;

use \OCP\AppFramework\App;

use OCA\Owner_Fixer\Hooks;
use OCA\Owner_Fixer\LdapConnector;
use OCA\Owner_Fixer\Fixer;
use OCA\Owner_Fixer\Db\DBService;
use OCA\Owner_Fixer\Controller\AdminSettingsController;
use OC\AppFramework\Utility\TimeFactory;

class Application extends App {

    public function __construct(array $urlParams=array()){
        parent::__construct('owner_fixer', $urlParams);

        $container = $this->getContainer();
        
        /**
        * Controllers
        */

        $container->registerService('AdminSettingsController', function($c) {
            return new AdminSettingsController(
                $c->query('AppName'),
                $c->query('Request')
            );
        });

        $container->registerService('LdapConnector', function($c) {
            return new LdapConnector();
        });

        $container->registerService('DBService', function($c) {
            return new DBService(
                $c->query('ServerContainer')->getDb(),
                new TimeFactory()
            );
        });

        $container->registerService('Fixer', function($c) {
            return new Fixer(
                $c->query('LdapConnector'),
                $c->query('DBService')
            );
        });

        $container->registerService('Hooks', function($c) {
            return new Hooks(
                $c->query('Fixer'),
                $c->query('ServerContainer')->getRootFolder()
            );
        });
        
    }
}

