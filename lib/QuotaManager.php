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



use OCP\AppFramework\Http;

class QuotaManager
{
    public static function getQuotaByUid($ldapUidNumber)
    {
        $quotServiceHost = \OC::$server->getConfig()->getAppValue('owner_fixer', 'quota_service_uri');
        $options['headers']=array('uid'=>$ldapUidNumber);
        $client = \OC::$server->getHTTPClientService()->newClient();
        try {
            $response = $client->get($quotServiceHost, $options);
        } catch (\Exception $e) {
            \OCP\Util::writeLog('owner_fixer', $e->getMessage(), \OCP\Util::ERROR);
            return false;
        }
        if ($response->getStatusCode() === Http::STATUS_OK) {
            return json_decode($response->getBody(), true);
        } else {
            \OCP\Util::writeLog('owner_fixer', 'Quota service response not OK', \OCP\Util::ERROR);
            return false;
        }
    }

    public static function setQuotaServiceURI($quotaServiceUri)
    {
        \OC::$server->getConfig()->setAppValue('owner_fixer', 'quota_service_uri', $quotaServiceUri);
    }
}
