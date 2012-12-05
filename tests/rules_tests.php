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