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

namespace OCA\Owner_Fixer;

use OCP\Files\IRootFolder;
/**
 * Class Hooks
 * @package OCA\Owner_Fixer\Lib
 */
class Hooks {

    /** @var Fixer */
    private static $fixer;

    /** @var IRootFolder*/
    protected $rootFolder;

    /**
     * @param Fixer $fixer
     * @param IRootFolder $rootFolder
     */
    public function __construct($fixer, $rootFolder){
        self::$fixer=$fixer;
        $this->rootFolder = $rootFolder;
    }
    
    public function register()
    {
        /**
         * @param \OC\Files\Node\File $node
         */
        $preWriteListener = function ($targetNode) {
            self::$fixer->checkQuota($targetNode);
        };


        /**
         * @param \OC\Files\Node\File $node
         */
        $postWriteListener = function ($targetNode) {
            self::$fixer->fixOwnerInRuntime($targetNode);
        };

        $this->rootFolder->listen('\OC\Files', 'preWrite', $preWriteListener);
        $this->rootFolder->listen('\OC\Files', 'postWrite', $postWriteListener);
    }
}
