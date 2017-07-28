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

class LdapConnector
{
    private $ldapHost;
    private $ldapPort;
    private $ldapRdn;
    private $ldapPass;
    private $dn;
    private $ldapConn;
    private $search;
    
    public function __construct(){
        $ocConfig = \OC::$server->getConfig();
        $this->ldapHost = $ocConfig->getAppValue('user_ldap', 'ldap_host');
        $this->ldapPort = $ocConfig->getAppValue('user_ldap', 'ldap_port');
        $this->ldapRdn = $ocConfig->getAppValue('user_ldap', 'ldap_dn');
        $this->ldapPass = base64_decode($ocConfig->getAppValue('user_ldap', 'ldap_agent_password'));
        $this->dn = $ocConfig->getAppValue('user_ldap', 'ldap_base');
        
        //connect to server
        if(($this->ldapConn = ldap_connect($this->ldapHost, $this->ldapPort)) === false) {
            \OCP\Util::writeLog(
                'owner_fixer',
                "Could not connect to $this->ldapHost",
                \OCP\Util::ERROR);
            die();
        }
        
        //bind with ldapuser
        if ($this->ldapConn)
        {
            if((ldap_bind($this->ldapConn, $this->ldapRdn, $this->ldapPass)) === false) {
                \OCP\Util::writeLog(
                    'owner_fixer',
                    "LDAP bind failed to $this->ldapHost",
                    \OCP\Util::ERROR);
                die();
            }
        }
    }

    //samAccountName to uidNumber converter. This function can use for search other user attributes by changing result parameter.
    function searchUidNumber($samAccountName)
    {
        //set filter to search spesific samAccountName
        $filter='(&(objectClass=user)(samAccountName='.$samAccountName.'))';
        $result = array('uidNumber');
        //search with filter get desired result
        $this->search = ldap_search($this->ldapConn, $this->dn, $filter, $result);
        $info = ldap_get_entries($this->ldapConn, $this->search);
        
        if($info['count']>0 || empty($info[0]['uidnumber'][0])){
            return $info[0]['uidnumber'][0];
        }
        else{ 
            FALSE;
        }
    }
}

