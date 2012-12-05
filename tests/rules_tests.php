<?php

$tf->test('NotNullRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->not_null();

	$obj = new TestObject();
	$obj->name = null;

	$tf->assertFalse($v->validate($obj), 'NotNullRule should fail when null');

	$obj->name = 'Not null';
	$tf->assert($v->validate($obj), 'NotNullRule should succeed when not null');
});

$tf->test('NotEmptyRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->not_empty();

	$obj = new TestObject();
	$obj->name = '';

	$tf->assertFalse($v->validate($obj), 'NotEmptyRule should fail when empty');

	$obj->name = 'Not empty';
	$tf->assert($v->validate($obj), 'NotEmptyRule should succeed when not empty');
});

$tf->test('RequiredRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->required();

	$obj = new TestObject();
	$obj->name = null;

	$tf->assertFalse($v->validate($obj), 'RequiredRule should fail when null');

	$obj->name = '';
	$tf->assertFalse($v->validate($obj), 'RequiredRule should fail when empty');

	$obj->name = 'Valid';
	$tf->assert($v->validate($obj), 'RequiredRule should succeed when not null or empty');
});

$tf->test('MinLengthRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->min_length(6);

	$obj = new TestObject();
	$obj->name = 'Short';

	$tf->assertFalse($v->validate($obj), 'MinLengthRule should fail when not long enough');

	$obj->name = 'This is long enough';
	$tf->assert($v->validate($obj), 'MinLengthRule should succeed when long enough.');
});

$tf->test('MaxLengthRule', function($tf) {
	$v = new Validator();
	$v->rule_for('name')->max_length(6);

	$obj = new TestObject();
	$obj->name = 'This is too long';

	$tf->assertFalse($v->validate($obj), 'MaxLengthRule should fail when too long');

	$obj->name = 'Valid';
	$tf->assert($v->validate($obj), 'MaxLengthRule should succeed when not too long');
});