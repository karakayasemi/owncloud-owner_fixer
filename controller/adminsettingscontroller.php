<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/1/16
 * Time: 4:36 PM
 */

namespace OCA\Owner_Fixer\Controller;

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

    public function savePreferences($quotaServiceUri, $permissionUmask ) {
        $changes = false;
        \OCP\User::checkAdminUser();
        if(!empty($quotaServiceUri)) {
            \OC::$server->getConfig()->setAppValue('owner_fixer', 'quota_service_uri', $quotaServiceUri);
            $changes = true;
        }
        if(!empty($permissionUmask)) {
            \OC::$server->getConfig()->setAppValue('owner_fixer', 'permission_umask', $permissionUmask);
            $changes = true;
        }
        if($changes === true) {
            return new JSONResponse(array('message' => 'Preferences saved'));
        } else {
            return new JSONResponse(array('message' => 'Nothing changed'));
        }
    }
}