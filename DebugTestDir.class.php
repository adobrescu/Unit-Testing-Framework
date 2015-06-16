<?php

/* Creaza o lista de teste dintr-un director si le ruleaza
 * Pe langa fisierele de test din director (vezi Test), TestDir cauta si fisiere 
 * 
 * *.setup.php si *teardown.php care nu sint asociate unor teste (de exemplu un fisier init.setup.php care nu are in acelasi director un init.test.php)
 * Si le include inaintea, respectiv dupa rularea testelor.
 * 
 * Pentru subdirectoare creeaza o lista de TestDir pe care , de asemenea, le va rula la run.
 * 
 */

include_once(__DIR__.'/DebugTest.class.php');


class DebugTestDir
{
	protected $testClassName;
	protected $context=array();
	protected $testFileNames, $setupFileNames, $teardownFileNames;
	protected $testDirs;
	
	public $numTests, $numFailedTests;
	public $failedAssertions=array(), $numAssertions=0, $numFailedAssertions=0;
	
	
	public function __construct($dirName, $testClassName)
	{
		include_once(__DIR__.'/'.$testClassName.'.class.php');
		$this->testClassName=$testClassName;
		$this->getTestFiles($dirName);
	}
	protected function getTestFiles($dir)
	{
		$testFiles=array();
		
		$testExtensionPattern='|'.preg_quote('.'.DebugTest::EXTENSION_TEST, '|').'$|';
		$paramsExtensionPattern='|'.preg_quote('.'.DebugTest::EXTENSION_PARAMS, '|').'$|';
		$setupExtensionPattern='|'.preg_quote('.'.DebugTest::EXTENSION_SETUP, '|').'$|';
		$teardownExtensionPattern='|'.preg_quote('.'.DebugTest::EXTENSION_TEARDOWN, '|').'$|';
		
		$extensionsPattern='#'.preg_quote('.'.DebugTest::EXTENSION_SETUP, '#').'$|'.preg_quote('.'.DebugTest::EXTENSION_TEARDOWN, '#').'#';
		
		if($files=glob($dir.'/*'))
		{
			foreach($files as $file)
			{
				if(is_dir($file))
				{
					$this->testDirs[]=$this->createTestDir($file);
					continue;
				}
				if (preg_match($paramsExtensionPattern, $file))
				{
					continue;
				}
				if (preg_match($testExtensionPattern, $file))
				{
					$this->testFileNames[]=$file;
				}
				else
				{
					$baseFileName=preg_replace($extensionsPattern, '', $file);
					if (preg_match($setupExtensionPattern, $file) && !file_exists($baseFileName.'.'.DebugTest::EXTENSION_TEST) )
					{
						$this->setupFileNames[]=$file;
					}
					elseif (preg_match($teardownExtensionPattern, $file) && !file_exists($baseFileName.'.'.DebugTest::EXTENSION_TEST))
					{
						$this->teardownFileNames[]=$file;
					}
				}
					
			}
		}
		
		if($this->testFileNames)
		{
			sort($this->testFileNames);
		}
		return $testFiles;
	}
	public function run(&$context)
	{
		$this->context=&$context;
		if(!defined('DEBUG_TESTS_RUNNING'))
		{
			define('DEBUG_TESTS_RUNNING', true);
		}
		if($this->setupFileNames)
		{
			foreach($this->setupFileNames as $setupFileName)
			{
				include($setupFileName);
				if(!is_array($this->context))
				{
					die('Test context must be an array in file: '.$setupFileName);
				}
			}
		}
		if($this->testFileNames)
		{
			foreach($this->testFileNames as $testFileName)
			{
				$test=$this->createTest($testFileName);
				$test->run($this->context);
				$this->addTestAssertions($test);
			}
				
		}
		if($this->testDirs)
		{
			foreach($this->testDirs as $testDir)
			{
				$testDir->run($this->context);
				$this->addTestAssertions($testDir);
			}
				
		}	
		if($this->teardownFileNames)
		{
			foreach($this->teardownFileNames as $teardownFileName)
			{
				include($teardownFileName);
			}
		}
	}
	protected function addTestAssertions($test)
	{
		
		$this->numTests+=is_a($test, 'DebugTestDir') ? $test->numTests : 1;
		$this->numFailedTests+=is_a($test, 'DebugTestDir') ? $test->numFailedTests : ($test->numFailedAssertions?1:0);
				
		$this->failedAssertions=array_merge($this->failedAssertions, $test->failedAssertions);
				
		$this->numAssertions+=$test->numAssertions;
		$this->numFailedAssertions+=$test->numFailedAssertions;
	}
	protected function createTest($testFileName)
	{
		$testClassName=$this->testClassName;
		return new $testClassName($testFileName);
	}
	protected function createTestDir($dir)
	{
		return new DebugTestDir($dir, $this->testClassName);
	}
	public function getTestFileNames()
	{
		$testFileNames=$this->testFileNames?$this->testFileNames:array();;
		
		if($this->testDirs)
		{
			foreach($this->testDirs as $testDir)
			{
				$testFileNames=array_merge($testFileNames, $testDir->getTestFileNames());
			}
		}
		return $testFileNames;
	}
}
