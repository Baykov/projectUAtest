<?php

use app\services\Importer;

class ImportTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
	public function testImport()
	{
		$dataType = 'tenders';
		$importer = new Importer($dataType);

		$this->assertFalse($importer->dataType == '');

		$this->assertTrue($importer->dataType == 'tenders');

		$this->assertFalse($importer->baseapiUrl == '');

		$this->assertTrue($importer->baseapiUrl !== '');

		$this->assertFalse($importer->apiUrl == '');

		$this->assertTrue($importer->apiUrl !== '');

		$this->assertFalse($importer->pageCounter == 0);

		$this->assertTrue($importer->pageCounter != 0);

		$importer->getTestListData();

		$this->assertFalse(empty($importer->data));

		$this->assertFalse(empty($importer->data->data));

		$this->assertTrue(is_array($importer->data->data));

		$importer->getTestItemData();

		$this->assertFalse(empty($importer->data));

		$this->assertFalse(empty($importer->data->data));

		$this->assertTrue(!is_array($importer->data->data));

	}
}