<?php

include_once(__DIR__.'/DebugBatchTest.class.php');
include_once(__DIR__.'/DebugPageTestDir.class.php');


class DebugBatchPageTest extends DebugBatchTest
{
	public function createTestDir($dir)
	{
		return new DebugPageTestDir($dir);
	}
}
