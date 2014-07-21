<?php

/*
 * Un obiect test include un fisier de test.
 * Un fisier de test are extensia *.test.php
 * Obiectul determina daca exista alte fisiere legate de fisierul  de test:
 * 
 * NUMETEST.setup.php (pentru initializari)
 *		Diferite variable "globale" necesare testului se pun in $this->context ( de exmplu un obiect Database)
 * 
 * NUMETEST.teardown.php (curatenie dupa test)
 *		Curata $this->context de ce a pus *.setup.php  acolo)
 * 
 * NUMETEST.params.php sau NUMETEST.*.params.php(contine un array de parametri pentru test)
 *		Parametrii se pun in $this->testDataset. 
 *		Testul va fi rulat pentru fisier cu parametri si pentru fiecare element al acestului array-urilor in parte, testul accesand elementul curent (parametrii) prin $this->testData	
 *	
 * Exista si alte fisiere de initializare si de curatenie per director (vezi TestDir)
 * 
 */
class DebugTest
{
	const EXTENSION_TEST='test.php';
	const EXTENSION_PARAMS='params.php';
	const EXTENSION_SETUP='setup.php';
	const EXTENSION_TEARDOWN='teardown.php';
	
	protected $fileName, $paramsFileNames, $setupFileName, $teardownFileName;
	protected $context=array(), $testDataset=array(), $testData;
	
	
	public $failedAssertions=array();
	public $numAssertions=0, $numFailedAssertions=0;
	
	public function __construct($fileName)
	{
		$this->fileName=$fileName;
		$baseFileName=preg_replace('|'.preg_quote('.'.static::EXTENSION_TEST, '|').'|', '', $fileName);
		
		$this->paramsFileNames=glob($baseFileName.'.*.'.static::EXTENSION_PARAMS);
		
		if(is_file($baseFileName.'.'.static::EXTENSION_PARAMS))
		{
			$this->paramsFileNames[]=$baseFileName.'.'.static::EXTENSION_PARAMS;
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
		//print_r($this); exit();
		if(!defined('DEBUG_TESTS_RUNNING'))
		{
			define('DEBUG_TESTS_RUNNING', true);
		}
		$this->context=$context;
		if($this->setupFileName)
		{
			include($this->setupFileName);
			if(!is_array($this->context))
			{
				die('Test context must be an array in file: '.$this->setupFileName);
			}
		}
		
		$this->loadParams();
		
		if(!$this->testDataset)
		{
			$this->runTest();
		}
		else
		{
			foreach($this->testDataset as $data)
			{
				$this->testData=$data;
				$this->runTest();
			}
		}
		if($this->teardownFileName)
		{
			include($this->teardownFileName);
		}
		
	}
	protected function loadParams()
	{
		if($this->paramsFileNames)
		{
			foreach($this->paramsFileNames as $paramsFileName)
			{
				include($paramsFileName);
				
				if(!is_array($this->testDataset))
				{
					die('Test data must be an array in file: '.$paramsFileName);
				}
			}
		}
	}
	public function runTest()
	{
		include($this->fileName);
	}
	
	/*assertion methods*/
	public function ASSERT($evaluatedCondition, $expectedValue, $receivedValue, $msg='')
	{
		$debugBacktrace=debug_backtrace(0,0);
		
		for($i=0; $i<count($debugBacktrace); $i++)
		{
			if(preg_match('|'.preg_quote('.'.static::EXTENSION_TEST, '|').'$|', $debugBacktrace[$i]['file']))
			{
				$testFileName=$debugBacktrace[$i]['file'];
				$testLine=$debugBacktrace[$i]['line'];
			}
		}
		
		$this->numAssertions++;
		$this->numFailedAssertions+=(!$evaluatedCondition?1:0);
		if(!$evaluatedCondition)
		{
			$this->failedAssertions[]=array(
				'status' => $evaluatedCondition ? 'success':'failed',
				'msg' => ($msg?$msg:'Expected: '.$expectedValue."\n".'Received: '.$receivedValue),
				'file' => $testFileName,
				'line' => $testLine
				);
		}
	}
	public function ASSERT_TRUE($evaluatedCondition)
	{
		$this->ASSERT($evaluatedCondition, 'TRUE', 'FALSE');
	}
	public function ASSERT_EQUALS($val1, $val2, $strict=true)
	{
		if($strict)
		{
			$this->ASSERT($val1===$val2, $val1, $val2);
		}
		else
		{
			$this->ASSERT($val1==$val2, $val1, $val2);
		}
	}
	public function ASSERT_MSG($msg)
	{
		$this->ASSERT(false, null, null, $msg);
	}
}