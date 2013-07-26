<?php namespace League\PHPUnitCoverageListener\Tests;

use League\PHPUnitCoverageListener\Listener;
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
}