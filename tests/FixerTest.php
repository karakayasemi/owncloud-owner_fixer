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

use OCA\Owner_Fixer\Db\DbService;
use OCA\Owner_Fixer\Fixer;
use OCA\Owner_Fixer\LdapConnector;
use OCA\Owner_Fixer\QuotaManager;
use OCP\Files\Node;
use OCP\Files\Storage;
use OCP\IDBConnection;
use OCP\User;
use Test\TestCase;

/**
 * @group DB
 */
class FixerTest extends TestCase {

    /** @var  Fixer */
    private $fixer;

    /** @var  IDBConnection */
    private $connection;

    /**@var \PHPUnit_Framework_MockObject_MockObject | DbService */
    private $dbService;

    /**@var \PHPUnit_Framework_MockObject_MockObject | LdapConnector */
    private $ldapConnector;

    /**@var \PHPUnit_Framework_MockObject_MockObject | QuotaManager */
    private $quotaManager;

    /**@var \PHPUnit_Framework_MockObject_MockObject | Node */
    private $mockNode;

    /**@var \PHPUnit_Framework_MockObject_MockObject | Storage */
    private $mockStorage;

    /**@var \PHPUnit_Framework_MockObject_MockObject | User */
    private $mockUser;

    public function setUp() {
        parent::setUp();

        $this->mockStorage = $this->getMockBuilder('OCP\Files\Storage')
            ->disableOriginalConstructor()->getMock();
        $this->mockNode = $this->getMockBuilder('OCP\Files\Node')
            ->disableOriginalConstructor()->getMock();
        $this->mockUser = $this->getMockBuilder('OCP\Files\User')
            ->disableOriginalConstructor()->getMock();
        $this->dbService = $this->getMockBuilder('DbService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->ldapConnector = $this->getMockBuilder('LdapConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $this->quotaManager = $this->getMockBuilder('QuotaManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fixer = new Fixer($this->ldapConnector, $this->connection, $this->quotaManager);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testFailCheckQuota() {
        /*$this->mockNode->expects($this->once())->method('getStorage')->willReturn($this->mockStorage);

        $this->mockNode->expects($this->once())->method('getPath')->willReturn('/files/temp');

        $this->mockStorage->expects($this->once())->method('getOwner')->willReturn($this->mockUser);
        */
    }

    public function testSuccessCheckQuota() {

    }

    public function testNoLdapUserFixOwnerInRuntime() {

    }

    public function testAdminUserFixOwnerInRuntime() {

    }

    public function testLdapUserFixOwnerInRuntime() {

    }
}
