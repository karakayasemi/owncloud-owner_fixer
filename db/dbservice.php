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

namespace OCA\Owner_Fixer\Db;

use Doctrine\DBAL\Exception\DatabaseObjectExistsException;
use OCP\IDBConnection;

/**
 * Class DBService
 * @package OCA\Owner_Fixer\Db
 */
class DBService {

    /**
     * @var IDBConnection
     */
    private $connection;

    /**
     * DBService constructor.
     *
     * @param IDBConnection $connection
     */
    public function __construct(IDBConnection $connection) {
        $this->connection = $connection;
    }

    /**
     *
     */
    public function addNonFixedNodes(){
        $builder = $this->connection->getQueryBuilder();
        $maxFileId = self::getMaxCronFixedFileId();
        $files = $builder->select('fileid')
            ->from('filecache')
            ->where($builder->expr()->gt('fileid',$builder->createNamedParameter($maxFileId)))
            ->execute()
            ->fetchAll();
        foreach ($files as $file) {
                $this->connection->insertIfNotExist('*PREFIX*owner_fixer_fixed_list', [
                    'fileid' => $file['fileid'],
                    'status'=>0
                ], ['fileid']);
        }
    }

    /**
     * @param $fileId
     */
    public function addNodeToFixedListInRuntime($fileId) {
        $builder = $this->connection->getQueryBuilder();
        $builder->insert('owner_fixer_fixed_list')
            ->values(array('fileid'=>$fileId,
                'status'=>2
                ))
            ->execute();
    }

    /**
     * @param $fileId
     */
    public function updateNodeStatusInFixedList($fileId) {
        $builder = $this->connection->getQueryBuilder();
        $builder->update('owner_fixer_fixed_list')
            ->where($builder->expr()->eq('fileid',$builder->createNamedParameter($fileId)))
            ->set('status',$builder->createNamedParameter(1))
            ->execute();
    }

    /**
     * @return array
     */
    public function getNonFixedNodes() {
        self::addNonFixedNodes();

        $builder = $this->connection->getQueryBuilder();
        $files = $builder->select(['fileid'])
            ->from('owner_fixer_fixed_list')
            ->where("status=0")
            ->execute()
            ->fetchAll();
        return $files;
    }

    /**
     * @return array
     */
    public function getMaxCronFixedFileId() {
        $builder = $this->connection->getQueryBuilder();
        //max fileid which is fixed from cron
        $maxFileId = $builder->select('fileid')
            ->from('owner_fixer_fixed_list')
            ->where('status=1')
            ->orderBy('fileid','DESC')
            ->setMaxResults(1)
            ->execute()
            ->fetch()['fileid'];
        if(empty($maxFileId)) {
            $maxFileId=0;
        }
        return $maxFileId;
    }
}