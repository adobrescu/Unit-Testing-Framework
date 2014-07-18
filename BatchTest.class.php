<?php

namespace debug;

include_once(__DIR__.'/TestDir.class.php');

class BatchTest
{
	protected $dirs, $testFiles;
	protected $testDirs;
	
	public function __construct($testDirs)
	{
		$this->dirs=is_array($testDirs)?$testDirs:array($testDirs);
		$this->createTestDirs();
		
		$this->run();
	}
	protected function createTestDirs()
	{
		foreach($this->dirs as $dir)
		{
			
			$this->testDirs[]=new TestDir($dir);
		}
	}
	protected function run()
	{
		$context=null;
		foreach($this->testDirs as $testDir)
		{
			$testDir->run($context);
		}
	}
}