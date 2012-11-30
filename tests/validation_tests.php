<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

require 'testify/testify.class.php';
require '../src/phpluent_validation.php';

class TestObject
{
	private $values = [];
	
	public function get($name) {
		return $this->values[$name];
	}

	public function set($name, $value) {
		$this->values[$name] = $value;
	}
}

class AlwaysValidRule
{
	use Rule;

	public function validate($obj) {
		return true;
	}
}

$tf = new Testify('PHPluent Validation Tests');

$tf->beforeEach(function($tf) {
	$tf->data->validator = new Validator();
});

$tf->test('Parameterless Rule', function($tf) {
	$obj = new TestObject();
	$obj->name = 'Test Object';

	$validator = $tf->data->validator;
	$validator->rule_for('name')->always_valid();

	$result = $validator->validate($obj);	
	$tf->assert($result, 'Object should be valid');
});

$tf->run();