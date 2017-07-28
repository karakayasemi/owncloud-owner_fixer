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

use OCA\Owner_Fixer\QuotaManager;
use OCP\Http\Client\IClient;
use Test\TestCase;

class QuotaManagerTest extends TestCase {

    /** @var QuotaManager */
    private $quotaManager;
    /** @var \PHPUnit_Framework_MockObject_MockObject | IClient */
    private $client;

    public function setUp() {
        parent::setUp();

        $this->client = $this->getMockBuilder('Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->quotaManager = new QuotaManager(\OC::$server->getHTTPClientService()->newClient());
    }

    public function testSetQuotaServiceURI()
    {
        $this->quotaManager->setQuotaServiceURI('uri');
        $this->assertEquals('uri',
            \OC::$server->getConfig()->getAppValue('owner_fixer', 'quota_service_uri'));
    }
}
