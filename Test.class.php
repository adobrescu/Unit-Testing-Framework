<?php

namespace debug;
/*
 * Un obiect test include un fisier de test.
 * Un fisier de test are extensia *.test.php
 * Obiectul determina daca exista alte fisiere legate de fisierul  de test:
 * 
 * *.setup.php (pentru initializari)
 *		Diferite variable "globale" necesare testului se pun in $this->context ( de exmplu un obiect Database)
 * 
 * *.teardown.php (curatenie dupa test)
 *		Curata $this->context de ce a pus *.setup.php  acolo)
 * 
 * *.params.php (contine un array de parametri pentru test)
 *		Parametrii se pun in $this->testDataset. 
 *		Testul va fi rulat pentru fiecare element al acestului array in parte, testul accesand elementul curent prin $this->testData	
 *	
 * Exista si alte fisiere de initializare si de curatenie per director (vezi TestDir)
 * 
 */
class Test
{
	const EXTENSION_TEST='test.php';
	const EXTENSION_PARAMS='params.php';
	const EXTENSION_SETUP='setup.php';
	const EXTENSION_TEARDOWN='teardown.php';
	
	protected $fileName, $paramsFileName, $setupFileName, $teardownFileName;
	protected $context=array(), $testDataset=array(), $testData;
	
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
			if(!is_array($this->testDataset))
			{
				die('Test data must be an array in file: '.$this->paramsFileName);
			}
		}
		
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
	public function runTest()
	{
		include($this->fileName);
	}
}