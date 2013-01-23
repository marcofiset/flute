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

$tf = new Testify('Flute Tests');

$tf->test('Parameter-less Rule', function($tf) {
	$obj = new TestObject();
	$obj->name = 'Test Object';

	$validator = new Validator();
	$validator->rule_for('name')->always_valid();

	$result = $validator->validate($obj);	
	$tf->assert($result->valid(), 'Object should be valid');
});

$tf->test('Rule for multiple fields', function($tf) {
	$validator = new Validator();
	$validator->rule_for('first_name')->and_for('last_name')
			->not_empty();

	$obj = new TestObject();
	$obj->first_name = '';
	$obj->last_name = 'Valid name';

	$tf->assertFalse($validator->validate($obj)->valid(), 'One property is invalid');

	$obj->first_name = 'Valid name';
	$tf->assert($validator->validate($obj)->valid(), 'All properties are valid.');
});

$tf->test('Parameter rule', function($tf) {
	$validator = new Validator();
	$validator->rule_for('name')->max_length(10);

	$obj = new TestObject();
	$obj->name = '0123456789';
	$tf->assert($validator->validate($obj)->valid(), 'Rule respected');

	$obj->name .= '0';
	$tf->assertFalse($validator->validate($obj)->valid(), 'Rule infringed');
});

$tf->test('Extending other rules', function($tf) {
	$validator = new Validator();
	$validator->rule_for('name')->required();

	$obj = new TestObject();

	$obj->name = '';
	$tf->assertFalse($validator->validate($obj)->valid(), 'Name is blank, invalid');

	$obj->name = null;
	$tf->assertFalse($validator->validate($obj)->valid(), 'Name is null, invalid');

	$obj->name = 'Not empty or null';
	$tf->assert($validator->validate($obj)->valid(), 'Name is not empty or null, valid');
});

$tf->test('Rule conditions', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->required()->when(function($o) { return false; });

	$obj = new TestObject();

	$obj->name = null;
	$tf->assert($v->validate($obj)->valid(), 'Rule should not evaluate when condition is not met');
});

class TempRule extends Rule
{
	public function extend() {
		return [new RequiredRule()];
	}
}

$tf->test('Multi-level rule hirearchy', function($tf) {
	$validator = new Validator();

	//temp extends required, which extends not_null.
	$validator->rule_for('age')->temp();

	$obj = new TestObject();
	$obj->age = null; 

	$tf->assertFalse($validator->validate($obj)->valid(), 'Null should not be valid');
});

$tf->test('Rule with multiple args', function($tf) {
	$rule = new TempRule([1, 3]);

	$tf->assert($rule->arg1 === 1, 'First call to __get should return first arg');
	
	$tf->assert($rule->arg2 === 3, 'Second call to __get should return second arg');

	$tf->assert($rule->arg1 === 1, 'Calling same arg name should yield the same value');
});

include 'rules_tests.php';

$tf->run();