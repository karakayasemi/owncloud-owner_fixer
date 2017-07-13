<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 *
 * @copyright Copyright (c) 2017, ITU BIDB
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
namespace OCA\Owner_Fixer\Tests;
use OCA\Owner_Fixer\Fixer;
use OCA\Owner_Fixer\Hooks;
use OCP\Files\IRootFolder;
use Test\TestCase;
class HooksTest extends TestCase {
    /** @var \PHPUnit_Framework_MockObject_MockObject | Fixer */
    private $fixer;
    /** @var \PHPUnit_Framework_MockObject_MockObject | IRootFolder */
    private $rootFolder;
    /** @var  Hooks */
    private $hooks;
    public function setUp() {
        parent::setUp();
        $this->fixer = $this->getMockBuilder('OCA\Owner_Fixer\Fixer')
            ->disableOriginalConstructor()->getMock();
        $this->rootFolder = $this->getMockBuilder('\OC\Files\Node\Root')
            ->disableOriginalConstructor()->getMock();;
        $this->hooks = new Hooks($this->fixer, $this->rootFolder);
    }

    public function testRegister() {
        $this->rootFolder->expects($this->exactly(2))->method('listen');
        $this->hooks->register();
    }
}