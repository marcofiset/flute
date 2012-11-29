<?php

class Validator
{
	private $rules = [];
	private $next_prop = '';

	public function validate($obj)
	{
		foreach ($this->rules as $rule) 
		{
			if (!$rule->validate($obj)) return false;
		}

		return true;
	}

	public function rule_for($prop)
	{
		$this->next_prop = $prop;
		return $this;
	}

	public function required()
	{
		array_push($this->rules, new RequiredRule($this->next_prop));
		return $this; 
	}

	public function length($max_length)
	{
		array_push($this->rules, new LengthRule($this->next_prop, $max_length));
		return $this;
	}
}

abstract class Rule
{
	protected $prop;
	public abstract function validate($obj);

	public function __construct($prop)
	{
		$this->prop = $prop;
	}
}

class RequiredRule extends Rule
{
	public function validate($obj)
	{
		$prop = $this->prop;
		$value = $obj->$prop();
		return isset($value) && $value !== '';
	}
}

class LengthRule extends Rule
{
	private $max_length;

	public function __construct($prop, $max_length)
	{
		parent::__construct($prop);
		$this->max_length = $max_length;
	}

	public function validate($obj)
	{
		$prop = $this->prop;
		$value = $obj->$prop();
		return strlen($value) <= $this->max_length;
	}
}

class TestObject
{
	private $name = 'Valid name!';
	public function get_name() 
	{
		return $this->name;
	}
}

$validator = new Validator();
$validator->rule_for('get_name')->required()->length(50);


echo $validator->validate(new TestObject()) ? 'true' : 'false';
