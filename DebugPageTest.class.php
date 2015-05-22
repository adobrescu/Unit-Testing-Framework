<?php

include_once(__DIR__.'/DebugTest.class.php');
include_once(__DIR__.'/HtmlDocument.class.php');

class DebugPageTest extends DebugTest
{
        static $___superGlobalNames=array( 'GLOBALS', '_SERVER', '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_REQUEST' );

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
                        $GLOBALS[$superGlobalName]=array();
                }
        }
        public function getPage($pageFileNameOrUrl)
        {
                ob_start();

                include($pageFileNameOrUrl);

                $html=ob_get_contents();

                ob_end_clean();

                $domDoc=new HtmlDocument($html);

                return $domDoc;
        }

        public function run(&$context)
        {
                parent::run($context);

                $this->cleanupSuperGlobals();
        }
		public function runPage($pageUri)
		{
			$_SERVER['REQUEST_URI']=preg_replace('/[\/]+/', '/', substr(realpath(APP_TEST_WEBROOT_DIR), strlen(realpath($_SERVER['DOCUMENT_ROOT']))).'///'.$pageUri);
			
			return $this->getPage(APP_TEST_WEBROOT_DIR.'/dispatch-http-request.php');
		}
		public function cleanupPageContext()
		{	
			RootController::___debugUnsetInstance();
		}
}