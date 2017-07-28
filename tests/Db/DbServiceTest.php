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


namespace OCA\Owner_Fixer\Tests\Db;

use OCA\Owner_Fixer\Db\DbService;
use OC\AppFramework\Utility\TimeFactory;
use OCP\IDBConnection;
use Test\TestCase;

/**
 * @group DB
 */
class DbServiceTest extends TestCase {

    /** @var  DbService */
    private $dbService;

    /** @var  IDBConnection */
    private $connection;

    /** @var  TimeFactory */
    private $factory;

    /** @var string  */
    private $dbTable = 'owner_fixer_fixed_list';

    public function setUp() {
        parent::setUp();

        $this->connection = \OC::$server->getDatabaseConnection();
        $this->factory = new TimeFactory();
        $this->dbService = new DbService($this->connection, $this->factory);

        $query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
        $result = $query->execute()->fetchAll();
        $this->assertEmpty($result, 'we need to start with a empty owner_fixer_fixed_list table');
    }

    public function tearDown() {
        parent::tearDown();
        $this->connection->getQueryBuilder()->delete($this->dbTable)->execute();
        $this->connection->getQueryBuilder()->delete('filecache')->execute();
    }

    private function createTestFileEntry($path, $storage = 1) {
        $qb = $this->connection->getQueryBuilder();
        $qb->insert('filecache')
            ->values([
                'storage' => $qb->expr()->literal($storage),
                'path' => $qb->expr()->literal($path),
                'path_hash' => $qb->expr()->literal(md5($path)),
                'name' => $qb->expr()->literal(basename($path)),
            ]);
        $this->assertEquals(1, $qb->execute());
        return $qb->getLastInsertId();
    }

    public function testAddNonFixedNodes() {
        $fileId1 = $this->createTestFileEntry('/files/test1.txt');
        $fileId2 = $this->createTestFileEntry('/files/test2.txt');

        $this->dbService->addNonFixedNodes();

        $query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
        $result = $query->execute()->fetchAll();
        $this->assertSame(2, count($result));
        $this->assertSame($fileId1, (int)$result[0]['fileid']);
        $this->assertSame($fileId2, (int)$result[1]['fileid']);
        $this->assertSame(0, (int)$result[0]['status']);
        $this->assertSame(0, (int)$result[1]['status']);
    }

    public function testAddNodeToFixedListInRuntime() {
        $fileId1 = 1;
        $this->dbService->addNodeToFixedListInRuntime(1);

        $query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
        $result = $query->execute()->fetchAll();
        $this->assertSame(1, count($result));
        $this->assertSame($fileId1, (int)$result[0]['fileid']);
        $this->assertSame(2, (int)$result[0]['status']);
    }

    public function testUpdateNodeStatusInFixedList() {
        $fileId1 = $this->createTestFileEntry('/files/test1.txt');

        $this->dbService->addNonFixedNodes();

        $query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
        $result = $query->execute()->fetchAll();
        $this->assertSame(1, count($result));
        $this->assertSame($fileId1, (int)$result[0]['fileid']);
        $this->assertSame(0, (int)$result[0]['status']);

        $this->dbService->updateNodeStatusInFixedList($fileId1);

        $query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
        $result = $query->execute()->fetchAll();
        $this->assertSame(1, count($result));
        $this->assertSame($fileId1, (int)$result[0]['fileid']);
        $this->assertSame(1, (int)$result[0]['status']);
    }

    public function testGetMaxCronFixedFileId() {
        $this->assertSame(0, (int)$this->dbService->getMaxCronFixedFileId());

        $fileId1 = $this->createTestFileEntry('/files/test1.txt');
        $this->dbService->addNonFixedNodes();
        $this->dbService->updateNodeStatusInFixedList($fileId1);

        $this->assertSame($fileId1, (int)$this->dbService->getMaxCronFixedFileId());
    }

    public function testDeleteFromFixedList() {
        $this->dbService->addNodeToFixedListInRuntime(1);
        $this->dbService->addNodeToFixedListInRuntime(2);

        $query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
        $result = $query->execute()->fetchAll();
        $this->assertSame(2, count($result));
        $this->assertSame(1, (int)$result[0]['fileid']);
        $this->assertSame(2, (int)$result[1]['fileid']);

        $this->dbService->deleteFromFixedList(2);
        $query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
        $result = $query->execute()->fetchAll();
        $this->assertSame(1, count($result));
        $this->assertSame(1, (int)$result[0]['fileid']);
    }
}
