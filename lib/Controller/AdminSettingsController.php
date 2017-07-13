<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/1/16
 * Time: 4:36 PM
 */

namespace OCA\Owner_Fixer\Controller;

use OCA\Owner_Fixer\QuotaManager;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class AdminSettingsController extends ApiController
{
    /** @var  QuotaManager $quotaManager */
    protected $quotaManager;
    public function __construct($appName, IRequest $request, $quotaManager)
    {
        parent::__construct($appName, $request);
        $this->quotaManager = $quotaManager;
    }

    public function savePreferences($quotaServiceUri, $permissionUmask) {
        $changes = false;
        if(empty($quotaServiceUri) || empty($permissionUmask)) {
            return new JSONResponse(array(
                'message' => 'Preferences can not be empty, previous values are not changed.'));
        }
        if(\OC::$server->getConfig()->getAppValue('owner_fixer', 'quota_service_uri') !== $quotaServiceUri) {
            $this->quotaManager->setQuotaServiceURI($quotaServiceUri);
            $changes = true;
        }
        if(\OC::$server->getConfig()->getAppValue('owner_fixer', 'permission_umask') !== $permissionUmask) {
            \OC::$server->getConfig()->setAppValue('owner_fixer', 'permission_umask', $permissionUmask);
            $changes = true;
        }

        if($changes === true) {
            return new JSONResponse(array('message' => 'Preferences saved.'));
        } else {
            return new JSONResponse(array('message' => 'Nothing changed.'));
        }
    }
}