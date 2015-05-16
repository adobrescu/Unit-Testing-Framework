<?php

//Set a global var
$GLOBALS['setupVar']='This is a global variable';

//Set context vars
$this->context=array(
	'contextVar1' => 'This is a test dir context var',
	'contextVar2' => 'Context vars array is available to all tests in a test dir',
	'contextVar3' => 'Eg: One var could be a database connection',
);
