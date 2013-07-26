<?php namespace League\PHPUnitCoverageListener\Tests;

use League\PHPUnitCoverageListener\ListenerInterface;
use League\PHPUnitCoverageListener\PrinterInterface;
use League\PHPUnitCoverageListener\Listener;
use League\PHPUnitCoverageListener\Printer\ArrayOut;
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
}