<?php

namespace debug;

class Test
{
	const EXTENSION_TEST='test.php';
	const EXTENSION_PARAMS='params.php';
	const EXTENSION_SETUP='setup.php';
	const EXTENSION_TEARDOWN='teardown.php';
	
	protected $fileName, $paramsFileName, $setupFileName, $teardownFileName;
	protected $context=array(), $testDataset, $testData;
	
	public function __construct($fileName)
	{
		$this->fileName=$fileName;
		$baseFileName=preg_replace('|'.preg_quote('.'.static::EXTENSION_TEST, '|').'|', '', $fileName);
		
		if(is_file($baseFileName.'.'.static::EXTENSION_PARAMS))
		{
			$this->paramsFileName=$baseFileName.'.'.static::EXTENSION_PARAMS;
		}
		if(is_file($baseFileName.'.'.static::EXTENSION_SETUP))
		{
			$this->setupFileName=$baseFileName.'.'.static::EXTENSION_SETUP;
		}
		if(is_file($baseFileName.'.'.static::EXTENSION_TEARDOWN))
		{
			$this->teardownFileName=$baseFileName.'.'.static::EXTENSION_TEARDOWN;
		}
		
	}
	public function run(&$context)
	{
		$this->context=$context;
		if($this->setupFileName)
		{
			include($this->setupFileName);
			if(!is_array($this->context))
			{
				die('Test context must be an array in file: '.$this->setupFileName);
			}
		}
		if($this->paramsFileName)
		{
			include($this->paramsFileName);
		}
		if(is_array($this->testDataset))
		{
			foreach($this->testDataset as $data)
			{
				$this->testData=$data;
				$this->runTest();
			}
		}
		else
		{
			$this->runTest();
		}
		if($this->teardownFileName)
		{
			include($this->teardownFileName);
		}
	}
	public function runTest()
	{
		include($this->fileName);
	}
}