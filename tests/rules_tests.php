<?php

$tf->test('NotNullRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->not_null();

	$obj = new TestObject();
	$obj->name = null;
	$tf->assertFalse($v->validate($obj)->valid(), 'NotNullRule should fail when null');

	$obj->name = 'Not null';
	$tf->assert($v->validate($obj)->valid(), 'NotNullRule should succeed when not null');
});

$tf->test('NotEmptyRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->not_empty();

	$obj = new TestObject();
	$obj->name = '';
	$tf->assertFalse($v->validate($obj)->valid(), 'NotEmptyRule should fail when empty');

	$obj->name = 'Not empty';
	$tf->assert($v->validate($obj)->valid(), 'NotEmptyRule should succeed when not empty');
});

$tf->test('RequiredRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->required();

	$obj = new TestObject();
	$obj->name = null;
	$tf->assertFalse($v->validate($obj)->valid(), 'RequiredRule should fail when null');

	$obj->name = '';
	$tf->assertFalse($v->validate($obj)->valid(), 'RequiredRule should fail when empty');

	$obj->name = 'Valid';
	$tf->assert($v->validate($obj)->valid(), 'RequiredRule should succeed when not null or empty');
});

$tf->test('MinLengthRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->min_length(6);

	$obj = new TestObject();

	$obj->name = 'Short';
	$tf->assertFalse($v->validate($obj)->valid(), 'MinLengthRule should fail when not long enough');

	$obj->name = 'This is long enough';
	$tf->assert($v->validate($obj)->valid(), 'MinLengthRule should succeed when long enough.');
});

$tf->test('MaxLengthRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->max_length(6);

	$obj = new TestObject();
	$obj->name = 'This is too long';
	$tf->assertFalse($v->validate($obj)->valid(), 'MaxLengthRule should fail when too long');

	$obj->name = 'Valid';
	$tf->assert($v->validate($obj)->valid(), 'MaxLengthRule should succeed when not too long');
});

$tf->test('LengthRule', function ($tf) {
	$v = new Validator();
	$v->rule_for('name')->length(10, 20);

	$obj = new TestObject();
	$obj->name = 'Too short';
	$tf->assertFalse($v->validate($obj)->valid(), 'LengthRule should fail when too short');

	$obj->name = 'This is too long and it should fail the test';
	$tf->assertFalse($v->validate($obj)->valid(), 'LengthRuleShould fail when too long');

	$obj->name = 'Just long enough';
	$tf->assert($v->validate($obj)->valid(), 'LengthRule should succeed when valid length');
});

$tf->test('NotEqualToRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->not_equal_to('Smith');

	$obj = new TestObject();
	$obj->name = 'Smith';
	$tf->assertFalse($v->validate($obj)->valid(), 'NotEqualToRule should fail when equal');

	$obj->name = 'Not Smith';
	$tf->assert($v->validate($obj)->valid(), 'NotEqualToRule should succeed when not equal');
});

$tf->test('NotEqualToRule Multiple values', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->not_equal_to('Foo', 'Bar', 'Bazz');

	$obj = new TestObject();
	$obj->name = 'Foo';
	$tf->assertFalse($v->validate($obj)->valid(), 'NotEqualToRule should fail when equal to one of the values');

	$obj->name = 'Bar';
	$tf->assertFalse($v->validate($obj)->valid(), 'NotEqualToRule should fail when equal to one of the values');

	$obj->name = 'Bazz';
	$tf->assertFalse($v->validate($obj)->valid(), 'NotEqualToRule should fail when equal to one of the values');

	$obj->name = 'Fizz';
	$tf->assert($v->validate($obj)->valid(), 'NotEqualToRule should succeed when not equal to any of the values');
});

$tf->test('GreaterThanRule', function($tf) {
	$v = new Validator();
	$v->rule_for('age')->greater_than(21);

	$obj = new TestObject();
	$obj->age = 18;
	$tf->assertFalse($v->validate($obj)->valid(), 'GreaterThanRule should fail when value is smaller');

	$obj->age = 21;
	$tf->assertFalse($v->validate($obj)->valid(), 'GreaterThanRule should fail when value is equal');

	$obj->age = 22;
	$tf->assert($v->validate($obj)->valid(), 'GreaterThanRule should succeed when value is greater');
});

$tf->test('LessThanRule', function($tf) {
	$v = new Validator();
	$v->rule_for('age')->less_than(65);

	$obj = new TestObject();
	$obj->age = 70;
	$tf->assertFalse($v->validate($obj)->valid(), 'LessThanRule should fail when value is greater');

	$obj->age = 65;
	$tf->assertFalse($v->validate($obj)->valid(), 'LessThanRule should fail when value is equal');

	$obj->age = 64;
	$tf->assert($v->validate($obj)->valid(), 'LessThanRule should succeed when value is lower');
});

$tf->test('ExclusiveBetweenRule', function($tf) {
	$v = new Validator();
	$v->rule_for('age')->exclusive_between(21, 65);

	$obj = new TestObject();
	$obj->age = 20;
	$tf->assertFalse($v->validate($obj)->valid(), 'ExclusiveBetweenRule should fail when value is lower');

	$obj->age = 21;
	$tf->assertFalse($v->validate($obj)->valid(), 'ExclusiveBetweenRule should fail when value is equal to lower limit');

	$obj->age = 35;
	$tf->assert($v->validate($obj)->valid(), 'ExclusiveBetweenRule should succeed when value is between limits');

	$obj->age = 65;
	$tf->assertFalse($v->validate($obj)->valid(), 'ExclusiveBetweenRule should fail when value is equal to upper limit');

	$obj->age = 66;
	$tf->assertFalse($v->validate($obj)->valid(), 'ExclusiveBetweenRule should fail when value is greater');
});

$tf->test('GreaterOrEqualRule', function($tf) {
	$v = new Validator();
	$v->rule_for('age')->greater_or_equal(21);

	$obj = new TestObject();
	$obj->age = 20;
	$tf->assertFalse($v->validate($obj)->valid(), 'GreaterOrEqualRule should fail when value is lower');

	$obj->age = 21;
	$tf->assert($v->validate($obj)->valid(), 'GreaterOrEqualRule should succeed when value is equal');

	$obj->age = 22;
	$tf->assert($v->validate($obj)->valid(), 'GreaterOrEqualRule should succeed when value is greater');
});

$tf->test('LessOrEqualRule', function($tf) {
	$v = new Validator();
	$v->rule_for('age')->less_or_equal(65);

	$obj = new TestObject();
	$obj->age = 66;
	$tf->assertFalse($v->validate($obj)->valid(), 'LessOrEqualRule should fail when value is greater');

	$obj->age = 65;
	$tf->assert($v->validate($obj)->valid(), 'LessOrEqualRule should succeed when value is equal');

	$obj->age = 64;
	$tf->assert($v->validate($obj)->valid(), 'LessOrEqualRule should succeed when value is lower');
});

$tf->test('BetweenRule', function($tf) {
	$v = new Validator();
	$v->rule_for('age')->between(21, 65);

		$obj = new TestObject();
	$obj->age = 20;
	$tf->assertFalse($v->validate($obj)->valid(), 'BetweenRule should fail when value is lower');

	$obj->age = 21;
	$tf->assert($v->validate($obj)->valid(), 'BetweenRule should succeed when value is equal to lower limit');

	$obj->age = 35;
	$tf->assert($v->validate($obj)->valid(), 'BetweenRule should succeed when value is between limits');

	$obj->age = 65;
	$tf->assert($v->validate($obj)->valid(), 'BetweenRule should succeed when value is equal to upper limit');

	$obj->age = 66;
	$tf->assertFalse($v->validate($obj)->valid(), 'BetweenRule should fail when value is greater');
});