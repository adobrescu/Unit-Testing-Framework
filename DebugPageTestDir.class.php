<?php

include_once(__DIR__.'/DebugTestDir.class.php');
include_once(__DIR__.'/DebugPageTest.class.php');

class DebugPageTestDir extends DebugTestDir
{
	protected function createTest($testFileName)
	{
		return new DebugPageTest($testFileName);
	}
}