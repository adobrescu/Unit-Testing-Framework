<?php

include_once(__DIR__.'/DebugTest.class.php');

class DebugContext
{
	protected $depth;
	
	protected $contextDirName, $contextFileName;
	protected $testDirName, $testFileName;
	
	protected $superGlobalNames=array('_GET', '_POST', '_COOKIE', '_FILES', '_ENV', '_REQUEST', '_SERVER');
	protected $superGlobals=array();
	
	public function __construct($webrootUrl, $contextsDirName, $pageTestsDirName)
	{
		if(defined('DEBUG_TESTS_RUNNING'))
		{
			return;
		}
		
		$url=preg_replace('|'.preg_quote($webrootUrl,'|').'|', '', $_SERVER['REDIRECT_URL']);
		
		$this->depth=substr_count($url, '/');
		$pathInfo=pathInfo($url);
		
		$this->contextDirName=$contextsDirName.$pathInfo['dirname'];
		
		date_default_timezone_set('Europe/Bucharest');
		$this->contextFileName=$this->contextDirName.'/'.$pathInfo['filename'].'.'.date('YmdHs', time()).'.'.DebugTest::EXTENSION_PARAMS;
		
		
		$this->testDirName=$pageTestsDirName.$pathInfo['dirname'];
		$this->testFileName=$this->testDirName.'/'.$pathInfo['filename'].'.'.DebugTest::EXTENSION_TEST;
		
		foreach($this->superGlobalNames as $superGlobalName)
		{
			$this->superGlobals[0][$superGlobalName]=$GLOBALS[$superGlobalName];
		}
		register_shutdown_function(array($this, 'saveContext'));
	}
	public function saveContext()
	{
		foreach($this->superGlobalNames as $superGlobalName)
		{
			$this->superGlobals[1][$superGlobalName]=$GLOBALS[$superGlobalName];
		}
		
		
		
		if(!is_dir($this->contextDirName))
		{
			mkdir($this->contextDirName, 0777, true);
			chmod($this->contextDirName, 0777);
		}
		
		
		if(!($fp=fopen($this->contextFileName, 'w')))
		{
			die('Failed writing debug context in '.__FILE__.', line '.__LINE__);
		}
		
		fputs($fp, '<?php'."\n\t".'$this->context[\'super_globals\']='.var_export($this->superGlobals, true).';');
		fclose($fp);
		
		chmod($this->contextFileName, 0777);
		
		$this->generateTestFile();
	}
	public function generateTestFile()
	{
		
		if(file_exists($this->testFileName))
		{
			return;
		}
		if(!is_dir($this->testDirName))
		{
			mkdir($this->testDirName, 0777, true);
			chmod($this->testDirName, 0777);
		}
		if(!($fp=fopen($this->testFileName, 'w')))
		{
			die('Failed writing test file in '.__FILE__.', line '.__LINE__);
		}
		
		fputs($fp, '<?php'."\n\t".'include(__DIR__.\'/'.str_repeat('../', $this->depth+1).'webroot/dispatch.php\');');
		fclose($fp);
	}
}