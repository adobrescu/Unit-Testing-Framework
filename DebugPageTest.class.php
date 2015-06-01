<?php

include_once(__DIR__.'/DebugTest.class.php');
include_once(__DIR__.'/HtmlDocument.class.php');

class DebugPageTest extends DebugTest
{
        static $___superGlobalNames=array( 'GLOBALS', '_SERVER', '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_REQUEST' );
		static $documentRoot;
		
		public function __construct($fileName)
		{
			if(!static::$documentRoot)
			{
				/* $_SERVER['DOCUMENT_ROOT'] will be unset by cleanupSuperGlobals*/
				static::$documentRoot=realpath($_SERVER['DOCUMENT_ROOT']);
			}
			parent::__construct($fileName);
		}
        protected function loadParams()
        {
                parent::loadParams();

                if(isset($this->context['super_globals']))
                {
                        $this->loadSuperGlobals(0);
                }
        }
        public function loadSuperGlobals($index)
        {
                foreach($this->context['super_globals'][$index] as $superGlobalName=>$superGlobal)
                {
                        $GLOBALS[$superGlobalName]=$superGlobal;
                }
        }
        protected function cleanupSuperGlobals()
        {
			foreach(static::$___superGlobalNames as $superGlobalName)
			{
				global ${$superGlobalName};
				
				${$superGlobalName}=array();
				
				$GLOBALS[$superGlobalName]=array();
			}
        }
        public function getPage($pageFileNameOrUrl, $ob=true)
        {
			if($ob)
            {
				ob_start();
			}
            
			include($pageFileNameOrUrl);

			$html=ob_get_contents();

			if($ob)
			{
				ob_end_clean();
			}
            
			$domDoc=new HtmlDocument($html);

			return $domDoc;
        }

        public function run(&$context)
        {
				$this->cleanupSuperGlobals();
			
                parent::run($context);

                $this->cleanupSuperGlobals();
        }
		public function runPage($pageUri, $ob=true)
		{
			$pageUriParts=parse_url($pageUri);
			if(isset($pageUriParts['query']))
			{
				parse_str($pageUriParts['query'], $_GET);
			}
					
			$_SERVER['DOCUMENT_ROOT']=static::$documentRoot;
			$_SERVER['REQUEST_URI']=preg_replace('/[\/]+/', '/', substr(realpath(APP_TEST_WEBROOT_DIR), strlen(static::$documentRoot)).'/'.$pageUri);
			
			return $this->getPage(APP_TEST_WEBROOT_DIR.'/dispatch-http-request.php', $ob);
		}
		public function cleanupPageContext()
		{	
			RootController::___debugUnsetInstance();
		}
		public function getGIdBacktraceIndex($gId)
		{
			global $backtrace;
			
			foreach($backtrace as $backtraceKey=>$backtraceEntry)
			{
				if($backtraceEntry['gid']==$gId)
				{
					return $backtraceKey;
				}
			}
			return null;
		}
}