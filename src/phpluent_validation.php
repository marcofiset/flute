<?php

class Validator
{
	private $rules = [];
	private $rules_props = [];
	private $next_props = [];

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
		$this->next_props = [];
		$this->next_props[] = $prop;
		return $this;
	}

	public function and_for($prop)
	{
		$this->next_props[] = $prop;
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

		$rule = new $rule_name($arguments);

		//Since we cannot use an object as an array key, we store the rules
		//in an array and their ids associated with the props in another array.
		$this->rules[] = $rule;
		$this->rules_props[$rule->get_id()] = $this->next_props;

		return $this;
	}

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
	public function validate($obj) {	

		foreach ($this->rules as $rule) {
			//For each rule, we get the props associated with their id
			//in the other array containing the props.
			foreach ($this->rules_props[$rule->get_id()] as $prop) {
				if (!$rule->validate($obj->$prop())) return false;
			}
		}

		return true;
	}
}

/**
 * This is a trait that represents a Rule. Provides a default constructor
 * with $prop and $args property. User defined rules should use this trait.
 */
trait Rule
{
	private $id;
	private $args;

	public function __construct($arguments = [])
	{
		$this->args = $arguments;
		$this->id = uniqid();
	}

	public function get_id() { return $this->id; }
}

class NotNullRule
{
	use Rule;

	public function validate($value)
	{
		return $value !== null;
	}
}

class RequiredRule
{
	use Rule;

	public function validate($value)
	{
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

	public function validate($value)
	{
		return strlen($value) <= $this->max_length();
	}
}