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

namespace OCA\Owner_Fixer\Lib;

class Hooks {

    /** @var Fixer */
    private static $fixer;

    public function __construct($fixer){
        self::$fixer=$fixer;
    }
    
    public function register()
    {
        //register preWrite hook to check quota of user, before write operation
        \OCP\Util::connectHook('OC_Filesystem', 'write', 'OCA\Owner_Fixer\Lib\Hooks', 'preWriteCallback');

        //register postWrite hook to fix ownerships and permissions
        \OCP\Util::connectHook('OC_Filesystem', 'post_write', 'OCA\Owner_Fixer\Lib\Hooks', 'postWriteCallback');
    }

    public static function postWriteCallback($params)
    {
        self::$fixer->fixOwnerInRuntime($params);
    }

    public static function preWriteCallback($params)
    {
        self::$fixer->checkQuota($params);
    }
}
