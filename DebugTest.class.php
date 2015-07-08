<?php

/*
 * Un obiect test include un fisier de test.
 * Un fisier de test are extensia *.test.php
 * Obiectul determina daca exista alte fisiere legate de fisierul  de test:
 * 
 * NUMETEST.setup.php (pentru initializari)
 *		Diferite variable "globale" necesare testului se pun in $this->context ( de exmplu un obiect Database)
 *		$context e pasat prin referinta intre diversele obiecte Test si TEstDir, deci poate fi modificat intr-un test si modificarile se transimit
 *		mai departe (de ex. id-uri de obiecte nou create pot fi pasate mai departe pentru teste urmatoare)
 * NUMETEST.teardown.php (curatenie dupa test)
 *		Curata $this->context de ce a pus *.setup.php  acolo)
 * 
 * NUMETEST.params.php sau NUMETEST.*.params.php(contine un array de parametri pentru test)
 *		Parametrii se pun in $this->testDataset. 
 *		Testul va fi rulat pentru fisier cu parametri si pentru fiecare element al acestului array-urilor in parte, testul accesand elementul curent (parametrii) prin $this->testData	
 *		Daca parametrii de test trebuie modificati pentru testele (fisierele de test) care urmeaza se poate folosi $this->refTestData care este referinta la inregistrarea
 *			curenta din testDataset
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
	
	protected $collectXdebugTrace=true;
	protected $xdebugTraceDirName='xdebug-trace';
	protected $xdebugTrace;
	
	protected $fileName, $paramsFileNames, $setupFileName, $teardownFileName;
	protected $context=array(), $testDataset=array(), $testData;
	
	
	public $failedAssertions=array();
	public $numAssertions=0, $numFailedAssertions=0;
	
	public function __construct($fileName)
	{
		$this->collectXdebugTrace=!$this->collectXdebugTrace?false:extension_loaded('xdebug');
		
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
		$this->context=&$context;
		
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
			foreach($this->testDataset as &$data)
			{
				$this->testData=$data;
				$this->refTestData=&$data;
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
		$this->xdebugStartTrace();
			
		include($this->fileName);
		
		$this->xdebugStopTrace();
		
	}
	/*xdebug functions call info*/
	protected function xdebugStartTrace()
	{
		if(!$this->collectXdebugTrace)
		{
			return;
		}
		
		$traceFileName=$this->xdebugTraceDirName.'/'.$this->fileName;
		
		if(!is_dir(dirname($traceFileName)))
		{
			mkdir(dirname($traceFileName), 0777, true);
		}
		
		xdebug_start_trace($traceFileName);
	}
	protected function xdebugStopTrace()
	{
		if(!$this->collectXdebugTrace)
		{
			return;
		}
		xdebug_stop_trace();
	}
	public function xdebugGetTrace()
	{
		$this->xdebugLoadTrace();
		
		return $this->xdebugTrace;
	}
	protected function xdebugLoadTrace()
	{
		$traceFileName=$this->xdebugTraceDirName.'/'.$this->fileName.'.xt';
		$fp=fopen($traceFileName, 'r');
		$numLine=0;
		$break=false;
		
		while(!feof($fp))
		{
			//skip first 6 lines
			$traceLine=fgetcsv($fp, 4096, "\t");
			
			if($numLine++<6)
			{
				continue;
			}
			if(!isset($traceLine[2]))
			{
				continue;
			}
			$this->xdebugTrace[$numLine]=$traceLine;
			
			switch($traceLine[2])
			{
				case '0':
					//$traceLine[1] function #
					$functionCalls[$traceLine[1]]=&$this->xdebugTrace[$numLine];
					break;
				case '1': //exit, calculate call duration
					if(!isset($functionCalls[$traceLine[1]]))//exit from a function/include from first 6 entries; stop the loop
					{
						$break=true;
						break;
					}
					$functionCalls[$traceLine[1]]['duration']=$traceLine[3]-$functionCalls[$traceLine[1]][3];
					break;
				case 'R': //return
					$functionCalls[$traceLine[1]]['return']=$traceLine[5];
					break;
			}
			if($break)
			{
				break;
			}
		}
		
		fclose($fp);
	}
	/*misc.*/
	public function loadObjectFromArray(&$obj, $arr)
	{
		
		foreach($arr as $propertyName=>$propertyValue)
		{
			
			$obj->$propertyName=$propertyValue;
		}
	}
	
	public function trace($var, $exit=false)
	{
		echo '<pre>';
		print_r($var);
		echo '</pre>';
		if($exit)
		{
			exit();
		}
	}
	/*assertion methods*/
	public function ASSERT($evaluatedCondition, $expectedValue, $receivedValue, $msg='', $appendMsg=true)
	{
		$debugBacktrace=debug_backtrace(0,0);
		
		for($i=0; $i<count($debugBacktrace); $i++)
		{
			if($debugBacktrace[$i]['file']!=__FILE__)
			{
				$testFileName=$debugBacktrace[$i]['file'];
				$testLine=$debugBacktrace[$i]['line'];
				break;
			}
		}
		
		
		$this->numAssertions++;
		$this->numFailedAssertions+=(!$evaluatedCondition?1:0);
		if(!$evaluatedCondition)
		{
			$this->failedAssertions[]=array(
				'status' => $evaluatedCondition ? 'success':'failed',
				'msg' => ($msg && !$appendMsg?$msg:'Expected '.gettype($expectedValue).': '.htmlentities(print_r($expectedValue,1))."\n".'Received '.gettype($receivedValue).': '.htmlentities(print_r($receivedValue,1))).($appendMsg && $msg?"\n".$msg:''),
				'file' => $testFileName,
				'line' => $testLine
				);
			if(is_array($expectedValue) && is_array($receivedValue))
			{
				$this->failedAssertions[count($this->failedAssertions)-1]['msg'].="\n".'Arrays Diff:'."\n".htmlentities(print_r(@array_diff( $receivedValue, $expectedValue),1));
			}
		}
		return $evaluatedCondition;
	}
	public function ASSERT_TRUE($evaluatedCondition)
	{
		return $this->ASSERT($evaluatedCondition, 'TRUE', 'FALSE');
	}
	public function ASSERT_FALSE($evaluatedCondition)
	{
		return $this->ASSERT(!$evaluatedCondition, 'TRUE', 'FALSE');
	}
	public function ASSERT_EQUALS($val1, $val2, $strict=true, $msg='')
	{
		if($strict)
		{
			return $this->ASSERT($val1===$val2, $val1, $val2, $msg);
		}
		else
		{
			return $this->ASSERT($val1==$val2, $val1, $val2, $msg);
		}
	}
	public function ASSERT_ARRAYS_KEYS_EQUALS($arr1, $arr2)
	{
		$keys1=array_keys($arr1);
		$keys2=array_keys($arr2);
		
		$keys1=sort($keys1);
		$keys2=sort($keys2);
		
		return $this->ASSERT_EQUALS($keys1, $keys2);
	}
	public function ASSERT_ARRAY_HAS_KEYS($keys, $arr)
	{
		foreach($keys as $key)
		{
			{
				$this->ASSERT(array_key_exists($key, $arr), $key, '', 'Array key not set: '.$key);
			}
		}
	}
	public function ASSERT_MSG($msg)
	{
		return $this->ASSERT(false, null, null, $msg);
	}
	public function ASSERT_NULL($var)
	{
		return $this->ASSERT(is_null($var), 'NULL', 'NOT NULL');
	}
	public function ASSERT_ARRAY_IN_OBJECT($obj, $arr, $strict=true)
	{
		foreach($arr as $propertyName=>$propertyValue)
		{
			return $this->ASSERT_EQUALS($propertyValue, $obj->$propertyName, $strict, 'Property name: '.$propertyName);
		}
	}
	
	public function ASSERT_CLASS_HAS_STATIC_PROPERY($className, $propertyName)
	{
	}
}