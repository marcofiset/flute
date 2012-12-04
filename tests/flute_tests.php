<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

require 'testify/testify.class.php';
require '../src/flute.php';

class TestObject
{
	public function __call($name, $args) {
		return $this->$name;
	}
}

class AlwaysValidRule extends Rule
{
	public function condition($value) {
		return true;
	}
}

$tf = new Testify('PHPluent Validation Tests');

$tf->test('Parameter-less Rule', function($tf) {
	$obj = new TestObject();
	$obj->name = 'Test Object';

	$validator = new Validator();
	$validator->rule_for('name')->always_valid();

	$result = $validator->validate($obj);	
	$tf->assert($result, 'Object should be valid');
});

$tf->test('Rule for multiple fields', function($tf) {
	$validator = new Validator();
	$validator->rule_for('first_name')->and_for('last_name')
			->not_empty();

	$obj = new TestObject();
	$obj->first_name = '';
	$obj->last_name = 'Valid name';

	$tf->assertFalse($validator->validate($obj), 'One property is invalid');

	$obj->first_name = 'Valid name';
	$tf->assert($validator->validate($obj), 'All properties are valid.');
});

$tf->test('Parameter rule', function($tf) {
	$validator = new Validator();
	$validator->rule_for('name')->max_length(10);

	$obj = new TestObject();
	$obj->name = '0123456789';
	$tf->assert($validator->validate($obj), 'Rule respected');

	$obj->name .= '0';
	$tf->assertFalse($validator->validate($obj), 'Rule infringed');
});

$tf->test('Extending other rules', function($tf) {
	$validator = new Validator();
	$validator->rule_for('name')->required();

	$obj = new TestObject();

	$obj->name = '';
	$tf->assertFalse($validator->validate($obj), 'Name is blank, invalid');

	$obj->name = null;
	$tf->assertFalse($validator->validate($obj), 'Name is null, invalid');

	$obj->name = 'Not empty or null';
	$tf->assert($validator->validate($obj), 'Name is not empty or null, valid');
});

$tf->run();