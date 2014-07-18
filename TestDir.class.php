<?php

namespace debug;

include_once(__DIR__.'/Test.class.php');

class TestDir
{
	
	protected $context=array();
	protected $testFileNames, $setupFileNames, $teardownFileNames;
	protected $testDirs;
	
	public function __construct($dirName)
	{
		$this->getTestFiles($dirName);
	}
	protected function getTestFiles($dir)
	{
		$testFiles=array();
		
		$testExtensionPattern='|'.preg_quote('.'.Test::EXTENSION_TEST, '|').'$|';
		$paramsExtensionPattern='|'.preg_quote('.'.Test::EXTENSION_PARAMS, '|').'$|';
		$setupExtensionPattern='|'.preg_quote('.'.Test::EXTENSION_SETUP, '|').'$|';
		$teardownExtensionPattern='|'.preg_quote('.'.Test::EXTENSION_TEARDOWN, '|').'$|';
		
		$extensionsPattern='#'.preg_quote('.'.Test::EXTENSION_SETUP, '#').'$|'.preg_quote('.'.Test::EXTENSION_TEARDOWN, '#').'#';
		
		if($files=glob($dir.'/*'))
		{
			foreach($files as $file)
			{
				if(is_dir($file))
				{
					$this->testDirs[]=new TestDir($file);
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
					if (preg_match($setupExtensionPattern, $file) && !file_exists($baseFileName.'.'.Test::EXTENSION_TEST) )
					{
						$this->setupFileNames[]=$file;
					}
					elseif (preg_match($teardownExtensionPattern, $file) && !file_exists($baseFileName.'.'.Test::EXTENSION_TEST))
					{
						$this->teardownFileNames[]=$file;
					}
				}
					
			}
		}
		return $testFiles;
	}
	public function run(&$context)
	{
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
				$test=new Test($testFileName);
				$test->run($this->context);
			}
				
		}
		if($this->testDirs)
		{
			foreach($this->testDirs as $testDir)
			{
				$testDir->run($this->context);
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
}
