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


namespace OCA\Owner_Fixer\Tests\Controller;


use OCA\Owner_Fixer\Controller\AdminSettingsController;
use OCA\Owner_Fixer\QuotaManager;
use Test\TestCase;
use OCP\AppFramework\Http\JSONResponse;

class AdminSettingsControllerTest extends TestCase {

    /** @var AdminSettingsController  */
    private $controller;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \OCP\IRequest */
    private $request;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \OCP\IL10N */
    private $l10n;

    /** @var QuotaManager */
    private $quotaManager;



    public function setUp() {
        parent::setUp();

        $this->request = $this->createMock('OCP\IRequest');
        $this->quotaManager = new QuotaManager(\OC::$server->getHTTPClientService()->newClient());

        $this->controller = new AdminSettingsController(
            'owner_fixer',
            $this->request,
            $this->quotaManager
        );
    }

    public function tearDown() {
        parent::tearDown();
        $this->quotaManager->setQuotaServiceURI('');
        \OC::$server->getConfig()->setAppValue('owner_fixer', 'permission_umask', '');
    }

    public function testSavePreferencesWithEmptyParameter() {
        $response = $this->controller->savePreferences('', '');
        $expectedResponse = new JSONResponse(array(
            'message' => 'Preferences can not be empty, previous values are not changed.'));
        $this->assertSame($expectedResponse->getData()['message'], $response->getData()['message']);
    }

    public function testSavePreferencesWithSameParameter() {
        $this->quotaManager->setQuotaServiceURI('uri');
        \OC::$server->getConfig()->setAppValue('owner_fixer', 'permission_umask', '007');
        $response = $this->controller->savePreferences('uri', '007');
        $expectedResponse = new JSONResponse(array(
            'message' => 'Nothing changed.'));
        $this->assertSame($expectedResponse->getData()['message'], $response->getData()['message']);
    }

    public function testSavePreferencesWithNewParameter() {
        $response = $this->controller->savePreferences('uri', '007');
        $expectedResponse = new JSONResponse(array(
            'message' => 'Preferences saved.'));
        $this->assertSame($expectedResponse->getData()['message'], $response->getData()['message']);
    }
}
