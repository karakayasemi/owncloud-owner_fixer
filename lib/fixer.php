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

use OCP\Files\IRootFolder;

class Fixer
{

    /** @var IRootFolder*/
    protected $rootFolder;

    /**
     * @var \OCA\Owner_Fixer\Db\DBService $connection
     */
    protected $dbConnection;

    /** @var LdapConnector */
    private static $ldapConnector;

    /** @var nodePermission */
    private static $nodePermission;

    /** @var appPath */
    private static $fixerScriptPath;

    public function __construct($ldapConnector, $rootFolder, $dbConnection) {
        self::$ldapConnector = $ldapConnector;
        self::$fixerScriptPath = \OC_App::getAppPath('owner_fixer') . '/lib/owner_fixer';
        self::$nodePermission = \OC::$server->getConfig()->getAppValue('owner_fixer', 'permission_umask');
        $this->rootFolder = $rootFolder;
        $this->dbConnection = $dbConnection;
    }

    /**
     * @param $params
     * write hook call_back. Check user have enough space to upload.
     */
    public function checkQuota($params) {
        if(\OC_User::isAdminUser(\OC::$server->getUserSession()->getUser()->getUID())) {
            return;
        }
        $errorCode = null;
        $files = $_FILES['files'];
        $totalSize = 0;
        $l = \OC::$server->getL10N('owner_fixer');

        //calculate total uploaded file size
        foreach ($files['size'] as $size) {
            $totalSize += $size;
        }

        //learn ldap uidnumber
        $ldapUserName = \OC::$server->getUserSession()->getUser()->getUID();
        $ldapUidNumber = $this->learnLdapUidNumber($ldapUserName);
        if($ldapUidNumber === false) {
            \OCP\JSON::error(array('data' => array_merge(array('message' => $l->t('Ldap kullanıcısı değilsiniz. Yükleme yapılamaz.')))));
            die();
        }

        //ask user quota
        $quotaResponse = QuotaManager::getQuotaByUid($ldapUidNumber);

        //if quota manager not responding, return json error and kill all process
        if ($quotaResponse === false) {
            $params['run'] = FALSE;
            \OCP\JSON::error(array('data' => array_merge(array('message' => $l->t('Kota servisi yanıt vermiyor.')))));
            die();
        }

        //parse result determine quotaLimit and currentUsage
        $quotaLimit = $quotaResponse['quota_limit'];
        $currentUsage = $quotaResponse['current_usage'];
        $totalSize /= 1024;

        // TODO: l10n files will be arrange.
        //check have user enough space. if have not set an error message
        if ($currentUsage + $totalSize > $quotaLimit) {
            $params['run'] = FALSE;
            \OCP\JSON::error(array('data' => array_merge(array('message' => $l->t('Kota limitini aştınız. Yüklediğiniz dosya %s MB boyutunda, fakat %s MB kullanılabilir disk alanınız var', array(round(($totalSize / 1024), 3), round((($quotaLimit - $currentUsage) / 1024), 3)))))));
            die();
        }

    }

    /**
     * @param $params
     * @return bool
     */
    public function fixOwnerInRuntime($params) {
        $nodePath = \OC\Files\Filesystem::getView()->getLocalFile($params['path']);
        $params['fileid'] = \OC\Files\Filesystem::getView()->getFileInfo($params['path'])->getId();
        $ldapUserName = \OC::$server->getUserSession()->getUser()->getUID();
        if(\OC_User::isAdminUser($ldapUserName)) {
            $this->dbConnection->addNodeToFixedListInRuntime($params['fileid']);
            return true;
        }
        $ldapUidNumber = $this->learnLdapUidNumber($ldapUserName);
        if($ldapUidNumber === false) {
            \OCP\Util::writeLog('owner_fixer', 'learnLdapUidnumber failed to: '. $ldapUserName , \OCP\Util::ERROR);
            return false;
        }

        //ldap user found. Fix ownership and permissions by using owner_fixer script
        $result = $this->fixOwner($nodePath, $ldapUidNumber);
        if($result == 0) {
            $this->dbConnection->addNodeToFixedListInRuntime($params['fileid']);
        } else {
            \OCP\Util::writeLog('owner_fixer', 'owner could not fix. Node Path:'. $nodePath , \OCP\Util::ERROR);
            return false;
        }
        return true;
    }

    /**
     * @param $params
     * @return bool
     */
    public function fixOwnerInCron($params) {
        $mountCache = \OC::$server->getMountProviderCollection()->getMountCache();
        $mounts = $mountCache->getMountsForFileId($params['fileid']);
        if (count($mounts) > 0) {
            $ldapUserName = $mounts[0]->getUser()->getUID();
            if(\OC_User::isAdminUser($ldapUserName)) {
                $this->dbConnection->updateNodeStatusInFixedList($params['fileid']);
                return true;
            }
            //get internal file path
            $internalNodePath = \OC::$server->getUserFolder($ldapUserName)->getStorage()->getCache()->getPathById($params['fileid']);
            if (empty($internalNodePath)) {
                \OCP\Util::writeLog('owner_fixer', 'Could not find file with fileid:' . $params['fileid'] , \OCP\Util::ERROR);
                return false;
            }
            //get local file path
            $nodePath = \OC::$server->getUserFolder($ldapUserName)->getStorage()->getLocalFile($internalNodePath);
        }

        $ldapUidNumber = $this->learnLdapUidNumber($ldapUserName);
        if($ldapUidNumber === false) {
            \OCP\Util::writeLog('owner_fixer', 'learnLdapUidnumber failed to: '. $ldapUserName .' Node Path:' . $nodePath , \OCP\Util::ERROR);
            return false;
        }

        //ldap user found. Fix ownership and permissions by using owner_fixer script
        $result = $this->fixOwner($nodePath, $ldapUidNumber);
        if($result == 0) {
            $this->dbConnection->updateNodeStatusInFixedList($params['fileid']);
        } else {
            \OCP\Util::writeLog('owner_fixer', 'owner could not fix. Node Path:'. $nodePath , \OCP\Util::ERROR);
            return false;
        }
        return true;
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
        if ($ldapUidNumber == FALSE ) {
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

}
