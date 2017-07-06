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

namespace OCA\Owner_Fixer\BackgroundJob;

use OCA\Owner_Fixer\Fixer;
use OCA\Owner_Fixer\AppInfo\Application;

/**
 * @brief
 */
class FixOwner extends \OC\BackgroundJob\TimedJob {
    /**
     * @var \OCA\Owner_Fixer\db\DbService $connection
     */
    protected $connection;

    /**
     * @var Fixer $fixer
     */
    protected  $fixer;

    public function __construct() {
        $app = new Application();
        $container = $app->getContainer();
        $this->connection = $container->query('DbService');
        $this->fixer = $container->query('Fixer');
    }

    protected function run($argument) {
        $files = $this->connection->getNonFixedNodes();
        if(count($files) > 0) {
            foreach ($files as $fileid) {
                $this->fixer->fixOwnerInCron($fileid['fileid']);
            }
        }
    }
}