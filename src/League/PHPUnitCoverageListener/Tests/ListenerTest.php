<?php namespace League\PHPUnitCoverageListener\Tests;

use League\PHPUnitCoverageListener\ListenerInterface;
use League\PHPUnitCoverageListener\PrinterInterface;
use League\PHPUnitCoverageListener\Listener;
use League\PHPUnitCoverageListener\Printer\ArrayOut;
use League\PHPUnitCoverageListener\Tests\Mocks\MockHook;
use \PHPUnit_Framework_TestCase;

/**
 * Listener class test
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class ListenerTest extends PHPUnit_Framework_TestCase
{
	public function testEmptyPrinter()
	{
		$this->setExpectedException('RuntimeException', 'Printer class not found');
		$listener = new Listener();
	}

	public function testInvalidPrinter()
	{
		$this->setExpectedException('RuntimeException', 'Invalid printer class');
		$listener = new Listener(array('printer' => new \stdClass()));
	}

	public function testIntegrity()
	{
		$listener = new Listener(array(
			'printer' => new ArrayOut
		), false);

		$this->assertInstanceOf('League\PHPUnitCoverageListener\ListenerInterface', $listener);
		$this->assertObjectHasAttribute('printer', $listener);
		$this->assertInstanceOf('League\PHPUnitCoverageListener\PrinterInterface', $listener->getPrinter());
	}

	public function testCollectAndSendCoverage()
	{
		$listener = new Listener(array(
			'printer' => new ArrayOut
		), false);

		// Use League\PHPUnitCoverageListener coveralls informations
		$listener->collectAndSendCoverage(array(
			'hook' => new MockHook(),
			'namespace' => 'League\PHPUnitCoverageListener',
			'repo_token' => 'XKUga6etuxSWYPXJ0lAiDyHM2jbKPQAKC',
			'target_url' => 'https://coveralls.io/api/v1/jobs',
			'coverage_dir' => realpath(__DIR__.'/Mocks/data'),
		));

		$output = $listener->getPrinter()->output;

		// Verify the output
		$this->assertContains('Collecting CodeCoverage information...', $output[0]);
		$this->assertContains(' * Checking:', $output[1]);
		$this->assertContains(' * Checking:', $output[2]);
		$this->assertContains(' * Checking:', $output[3]);
		$this->assertContains(' * Checking:', $output[4]);
		$this->assertContains('Writing coverage output...', $output[5]);
		$this->assertContains('Sending coverage output...', $output[6]);
		$this->assertContains(' * cURL Output:', $output[7]);
		$this->assertContains(' * cURL Result:', $output[8]);
		$this->assertContains('Done.', $output[9]);
	}
}