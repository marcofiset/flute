<?php

class Validator
{
	private $rules = [];
	private $next_prop = '';

	/**
	 * Indicates wether the object passed as a parameter is valid as defined by the rules.
	 * 
	 * Loops through the rules defined for this validator, calling the rule's validate method, 
	 * passing $obj as a parameter. Returns false at the first rule that fails. If all rules
	 * succeed, true is returned.
	 * 
	 * @param mixed $obj is the object on which we want to test the validations
	 * @return bool indicating wether $obj was valid according to the rules
	 */
	public function validate($obj)
	{
		foreach ($this->rules as $rule) 
		{
			if (!$rule->validate($obj)) return false;
		}

		return true;
	}

	/**
	 * Start defining rules for a given property name.
	 * 
	 * All rules defined after calling this function will be registered for $prop until another
	 * call to rule_for is made with a different value for $prop.
	 * 
	 * @param string $prop is the name of the property on which the next rules will be applied.
	 * @return $this in order to maintain the fluent interface.
	 */
	public function rule_for($prop)
	{
		$this->next_prop = $prop;
		return $this;
	}

	/**
	 * Magic method that triggers every time an undefined method is called on this class
	 * 
	 * Creates a new rule following these conventions :
	 *   - For example, you call an undefined function on this object called max_length
	 * 
	 *   - This function creates a new rule from class MaxLengthRule
	 * 
	 *   - The rule class should use the Rule trait, or have a constructor which 
	 *     takes two parameters : the property name and an array of arguments
	 * 
	 *   - The class must have a validate method that takes an object as a parameter 
	 *     and returns a boolean, indicating wether the object is valid
	 * 
	 * @param string $name is the name of the function that was called
	 * @param array $arguments is an array containing all the arguments that were passed to the function
	 * @return $this in order to maintain the fluent interface.
	 */
	public function __call($name, $arguments)
	{
		// Transforms the function name to the corresponding Rule class
		// like so : max_length => MaxLengthRule
		$name = str_replace('_', ' ', $name);
		$rule_name = str_replace(' ', '', ucwords($name)) . 'Rule';

		array_push($this->rules, new $rule_name($this->next_prop, $arguments));

		return $this;
	}
}

trait Rule
{
	private $prop = '';
	private $args = [];

	public function __construct($prop, $arguments)
	{
		$this->prop = $prop;
		$this->args = $arguments;
	}
}

class NotNullRule
{
	use Rule;

	public function validate($obj)
	{
		$prop = $this->prop;

		return $obj->$prop() !== null;
	}
}

class RequiredRule
{
	use Rule;

	public function validate($obj)
	{
		$prop = $this->prop;
		$value = $obj->$prop();
		return isset($value) && $value !== '';
	}
}

class MaxLengthRule
{
	use Rule;

	private function max_length()
	{
		return $this->args[0];
	}

	public function validate($obj)
	{
		$prop = $this->prop;
		$value = $obj->$prop();
		return strlen($value) <= $this->max_length();
	}
}

class TestObject
{
	private $name = '012345678901234567';
	public function get_name()
	{
		return $this->name;
	}
}

$v = new Validator();
$v->rule_for('get_name')->required()->max_length(18);

echo $v->validate(new TestObject()) ? 'true' : 'false';