<?php

namespace debug;

include_once(__DIR__.'/TestDir.class.php');

/**
 * Creeaza o lista de TestDir si le ruleaza testele
 * 
 */

class BatchTest
{
	protected $dirs, $testFiles;
	protected $testDirs;
	
	public $numTests, $numFailedTests;
	public $failedAssertions=array(), $numAssertions=0, $numFailedAssertions=0;
	
	public function __construct($testDirs)
	{
		$this->dirs=is_array($testDirs)?$testDirs:array($testDirs);
		$this->createTestDirs();
		
		$this->run();
		$this->printInfo();
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
			
			$this->numTests+=$testDir->numTests;
			$this->numFailedTests+=$testDir->numFailedTests;
			
			$this->numAssertions+=$testDir->numAssertions;
			$this->numFailedAssertions+=$testDir->numFailedAssertions;
			
			$this->failedAssertions=array_merge($this->failedAssertions, $testDir->failedAssertions);
		}
	}
	public function printInfo()
	{
		$html='Number of tests: '.$this->numTests."\n".
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
		echo $html;
	}
}