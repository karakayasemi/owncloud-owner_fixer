<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/1/16
 * Time: 4:36 PM
 */

namespace OCA\Owner_Fixer\Controller;

use OCA\Owner_Fixer\Lib\QuotaManager;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class AdminSettingsController extends ApiController
{

    public function __construct($appName, IRequest $request)
    {
        parent::__construct($appName, $request);
    }

    public function savePreferences($quotaServiceUri, $permissionUmask) {
        $changes = false;
        \OCP\User::checkAdminUser();
        if(empty($quotaServiceUri) || empty($permissionUmask)) {
            return new JSONResponse(array('message' => 'Preferences can not be empty, 
                previous values are not changed.'));
        }
        if(\OC::$server->getConfig()->getAppValue('owner_fixer', 'quota_service_uri') !== $quotaServiceUri) {
            if(QuotaManager::setQuotaServiceURI($quotaServiceUri) === true) {
                $changes = true;
            } else {
                return new JSONResponse(array('message' => 'Something went wrong while saving quota service URI,
                    previous values are not changed.'));
            }
        }
        if(\OC::$server->getConfig()->getAppValue('owner_fixer', 'permission_umask') !== $permissionUmask) {
            if(\OC::$server->getConfig()->setAppValue('owner_fixer', 'permission_umask', $permissionUmask) === true) {
                $changes = true;
            } else {
                return new JSONResponse(array('message' => 'Something went wrong while saving Umask,
                    previous values are not changed.'));
            }

        }
        if($changes === true) {
            return new JSONResponse(array('message' => 'Preferences saved.'));
        } else {
            return new JSONResponse(array('message' => 'Nothing changed.'));
        }
    }
}