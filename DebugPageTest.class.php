<?php

include_once(__DIR__.'/DebugTest.class.php');


class DebugPageTest extends DebugTest
{
	protected function loadParams()
	{
		parent::loadParams();
		
		if(isset($this->context['super_globals']))
		{
			$this->restoreSuperGlobals(0);
		}		
	}
	public function restoreSuperGlobals($index)
	{
		foreach($this->context['super_globals'][$index] as $superGlobalName=>$superGlobal)
		{
			$GLOBALS[$superGlobalName]=$superGlobal;
		}
	}
	public function run(&$context)
	{
		ob_start();
		
		parent::run($context);
		
		//ob_end_clean();
	}
}