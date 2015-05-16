<?php

global $setupVar;

//Display vars set by setup file
echo '<pre>';
echo "<h1>Run Test 1</h1>";
echo "Global var:\n";
echo $setupVar;
echo "\n\n---------------------------------------------------------------------------\n\n";
echo 'Context vars array coming from setup file:<br>';
print_r($this->context);
echo "\n\n---------------------------------------------------------------------------\n\n";

//Show test data setup by params file
echo "Test Data:\n";
print_r($this->testData);

//Do some assertions
//for full list of asset methods view DebugTest class methods (starting with "ASSERT");

$this->ASSERT_TRUE(true);
$this->ASSERT_TRUE(false);
$this->ASSERT_EQUALS('string', 'string');

echo '<hr></pre>';

