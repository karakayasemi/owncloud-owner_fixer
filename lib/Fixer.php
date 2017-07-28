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

use OCA\Owner_Fixer\Db\DbService;
use OCP\Files\NotFoundException;
use OCP\Util;
class Fixer
{
    /**
     * @var DbService $connection
     */
    protected $dbService;

    /** @var LdapConnector */
    private static $ldapConnector;

    /** @var string */
    private static $nodePermission;

    /** @var string */
    private static $fixerScriptPath;

    /** @var QuotaManager */
    protected $quotaManager;

    public function __construct($ldapConnector, $dbService, $quotaManager) {
        self::$ldapConnector = $ldapConnector;
        self::$fixerScriptPath = \OC_App::getAppPath('owner_fixer') . '/lib/owner_fixer';
        self::$nodePermission = \OC::$server->getConfig()->getAppValue('owner_fixer', 'permission_umask');
        $this->dbService = $dbService;
        $this->quotaManager = $quotaManager;
    }

    /**
     * @param \OCP\Files\Node $node pointing to the file
     * write hook call_back. Check user have enough space to upload.
     */
    public function checkQuota($node) {
        $l = \OC::$server->getL10N('owner_fixer');

        $ldapUserName = $node->getStorage()->getOwner($node->getPath());
        if(\OC_User::isAdminUser($ldapUserName)) {
            return;
        }

        $ldapUidNumber = $this->learnLdapUidNumber($ldapUserName);
        if($ldapUidNumber === false) {
            \OCP\JSON::error(array('data' => array_merge(array('message' => $l->t('Ldap kullanıcısı değilsiniz. Yükleme yapılamaz.')))));
            die();
        }

        if (!isset($_FILES['files'])) {
        	try {
				$totalSize = $node->getSize();
			} catch (NotFoundException $e) {
        		$totalSize = 0;
			}
        } else {
            $files = $_FILES['files'];
            $totalSize = 0;
            //calculate total uploaded file size
            foreach ($files['size'] as $size) {
                $totalSize += $size;
            }
            $totalSize /= 1024;
        }

        //ask user quota
        $quotaResponse = $this->quotaManager->getQuotaByUid($ldapUidNumber);

        //if quota manager not responding, return json error and kill all process
        if ($quotaResponse === false) {
            \OCP\JSON::error(array('data' => array_merge(array('message' => $l->t('Kota servisi yanıt vermiyor.')))));
            die();
        }

        //parse result determine quotaLimit and currentUsage
        $quotaLimit = $quotaResponse['quota_limit'];
        $currentUsage = $quotaResponse['current_usage'];

        // TODO: l10n files will be arrange.
        //check have user enough space. if have not set an error message
        if ($currentUsage + $totalSize > $quotaLimit) {
            \OCP\JSON::error(array('data' => array_merge(array('message' => $l->t('Kota limitini aştınız. Yüklediğiniz dosya %s MB boyutunda, fakat %s MB kullanılabilir disk alanınız var', array(round(($totalSize / 1024), 3), round((($quotaLimit - $currentUsage) / 1024), 3)))))));
            die();
        }

    }

    /**
     * @param \OC\Files\Node\File $node
     * @return bool
     */
    public function fixOwnerInRuntime($node) {
        $fileId = $node->getId();
        $ldapUserName = $this->getOwnerFromFileId($fileId);
        if ($ldapUserName === false) {
            Util::writeLog(
                'owner_fixer',
                'owner could not fix. Node File Id:'. $fileId ,
                Util::ERROR);
            return false;
        }

        if(\OC_User::isAdminUser($ldapUserName)) {
            $this->dbService->addNodeToFixedListInRuntime($fileId);
            return true;
        }

        $internalNodePath = \OC::$server->getUserFolder($ldapUserName)
            ->getStorage()->getCache()->getPathById($fileId);
        $localPath = \OC::$server->getUserFolder($ldapUserName)->getStorage()->getLocalFile($internalNodePath);

        $ldapUidNumber = $this->learnLdapUidNumber($ldapUserName);
        if($ldapUidNumber === false) {
            Util::writeLog(
                'owner_fixer',
                'learnLdapUidnumber failed to: '. $ldapUserName ,
                Util::ERROR);
            return false;
        }

        //ldap user found. Fix ownership and permissions by using owner_fixer script
        $result = $this->fixOwner($localPath, $ldapUidNumber);
        if($result == 0) {
            $this->dbService->addNodeToFixedListInRuntime($node->getId());
        } else {
            Util::writeLog(
                'owner_fixer',
                'owner could not fix. Node Path:'. $localPath ,
                Util::ERROR);
            return false;
        }
        return true;
    }

    /**
     * @param string $fileId
     * @return bool
     */
    public function fixOwnerInCron($fileId) {
        $ldapUserName = $this->getOwnerFromFileId($fileId);
        if ($ldapUserName === false) {
            Util::writeLog(
                'owner_fixer',
                'owner could not fix. Node File Id:'. $fileId ,
                Util::ERROR);
            $this->dbService->deleteFromFixedList($fileId);
            return false;
        }

        if(\OC_User::isAdminUser($ldapUserName)) {
            $this->dbService->updateNodeStatusInFixedList($fileId);
            return true;
        }

        $internalNodePath = \OC::$server->getUserFolder($ldapUserName)
            ->getStorage()->getCache()->getPathById($fileId);
        $localPath = \OC::$server->getUserFolder($ldapUserName)->getStorage()->getLocalFile($internalNodePath);

        $ldapUidNumber = $this->learnLdapUidNumber($ldapUserName);
        if($ldapUidNumber === false) {
            Util::writeLog(
                'owner_fixer',
                'learnLdapUidnumber failed to: '. $ldapUserName ,
                Util::ERROR);
            return false;
        }

        $result = $this->fixOwner($localPath, $ldapUidNumber);
        if($result == 0) {
            $this->dbService->updateNodeStatusInFixedList($fileId);
        } else {
            $this->dbService->deleteFromFixedList($fileId);
        }
    }

    /**
     * @param string $ldapUserName
     * @return bool
     */
    private function learnLdapUidNumber($ldapUserName)
    {
        //search and get uidnumber by using ldapUserName
        $ldapUidNumber = self::$ldapConnector->searchUidNumber($ldapUserName);

        //if it is not an ldap user, don't do anything
        if ($ldapUidNumber == FALSE) {
            return false;
        } else {
            return $ldapUidNumber;
        }
    }

    /**
     * @param string $path
     * @param string $uidNumber
     * @return bool
     */
    private function fixOwner($path, $uidNumber)
    {
        $script = self::$fixerScriptPath . ' "' . $path . '" ' . $uidNumber . " " . self::$nodePermission;
        $output = array();
        exec($script, $output, $returnValue);
        return $returnValue;
    }

    /**
     * @param int $fileId
     * @return string
     */
    private function getOwnerFromFileId($fileId)
    {
        $mountCache = \OC::$server->getMountProviderCollection()->getMountCache();
        $cachedMounts = $mountCache->getMountsForFileId($fileId);
        if (!empty($cachedMounts)) {
            $mount = reset($cachedMounts);
            $ldapUserName = $mount->getMountPointNode()->getOwner()->getUID();
            return $ldapUserName;
        } else {
            return false;
        }
    }

}
