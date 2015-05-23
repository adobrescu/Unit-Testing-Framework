<?php

include_once(__DIR__.'/DebugTestDir.class.php');

/**
 * Creeaza o lista de TestDir si le ruleaza testele
 * 
 */

class DebugBatchTest
{
	protected $dirs, $testFiles;
	protected $testDirs;
	protected $testClassName;
	
	public $numTests, $numFailedTests;
	public $failedAssertions=array(), $numAssertions=0, $numFailedAssertions=0;
	public $executionTime;
	public function __construct($testDirs, $testClassName)
	{
		$this->dirs=is_array($testDirs)?$testDirs:array($testDirs);
		$this->testClassName=$testClassName;
		$this->createTestDirs();
		
		$this->run();
		
		$this->printInfo();
	}
	protected function createTestDirs()
	{
		foreach($this->dirs as $dir)
		{
			
			$this->testDirs[]=$this->createTestDir($dir);
		}
	}
	protected function run()
	{
		$context=null;
		$timeStart=microtime(true);
		if(!defined('DEBUG_TESTS_RUNNING'))
		{
			define('DEBUG_TESTS_RUNNING', true);
		}
		foreach($this->testDirs as $testDir)
		{
			$testDir->run($context);
			
			$this->numTests+=$testDir->numTests;
			$this->numFailedTests+=$testDir->numFailedTests;
			
			$this->numAssertions+=$testDir->numAssertions;
			$this->numFailedAssertions+=$testDir->numFailedAssertions;
			
			$this->failedAssertions=array_merge($this->failedAssertions, $testDir->failedAssertions);
		}
		$this->executionTime=microtime(true)-$timeStart;
	}
	public function printInfo()
	{
		$html='Execution time: '.$this->executionTime."s\n".
				'Number of tests: '.$this->numTests."\n".
				'Number of failed tests: '.$this->numFailedTests."\n".
				'Number of assertions: '.$this->numAssertions."\n".
				'Number of failed assertions: '.$this->numFailedAssertions."\n".
				'Failed assertions: '."\n";
		
		$line=1;
		foreach($this->failedAssertions as $assertion)
		{
			$html.=$line.'.'."\n".'File: '.$assertion['file']."\n".
					'Line: '.$assertion['line']."\n".
					$assertion['msg']."\n\n";
			$line++;
		}
		echo '<pre>'.$html.'</pre>';
	}
	public function createTestDir($dir)
	{
		return new DebugTestDir($dir, $this->testClassName);
	}
}