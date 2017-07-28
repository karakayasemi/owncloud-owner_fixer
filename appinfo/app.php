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

use OCP\Util;
use OCP\App;
//register app
$app = new Application();

//Admin panel settings page
App::registerAdmin( 'owner_fixer', 'settings' );

//check dependencies
if (App::isEnabled('user_ldap') === false) {
    Util::writeLog('owner_fixer',
        'user_ldap is not enabled.',
        Util::ERROR);

    //check if quota service URI is set
} else if (\OC::$server->getConfig()->getAppValue('owner_fixer', 'quota_service_uri') == '') {
    Util::writeLog('owner_fixer',
        'Quota service URI is not entered.',
        Util::ERROR);

    //check if Permission Umask is set
} else if (\OC::$server->getConfig()->getAppValue('owner_fixer', 'permission_umask') == '') {
    Util::writeLog('owner_fixer',
        'Permission Umask is not entered.',
        Util::ERROR);
} else {
    //if everything is OK, register hooks
    $app->getContainer()->query('Hooks')->register();
}
