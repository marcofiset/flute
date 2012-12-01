<?php

/**
 * The Validator class.
 * 
 * This class is used as a fluent interface to declare validation rules on specific 
 * properties of objects. May be used directly for defining inline validation rules
 * or it can be subclassed for better reusability.
 */
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
	public function rule_for($prop) {
		$this->next_props = [];
		$this->next_props[] = $prop;

		return $this;
	}

	/**
	 * Adds another property to the chain.
	 * 
	 * When invoked, all further rule declaration will be applied to $prop and every
	 * other $prop defined since the last invoke to rule_for.
	 * 
	 * @param string $prop to be added to the list of properties on which to apply further rules
	 * @return $this in order to maintain de fluent interface
	 */
	public function and_for($prop) {
		$this->next_props[] = $prop;

		return $this;
	}

	/**
	 * Magic method that triggers every time an undefined method is called on this class
	 * 
	 * Creates a new rule based on these conventions :
	 *   - For example, you call an undefined function on this object called max_length
	 * 
	 *   - This function creates a new rule from class MaxLengthRule
	 * 
	 *   - The class must extend the Rule class and therefore implement any abstract method
	 * 
	 * @param string $name is the name of the function that was called
	 * @param array $arguments is an array containing all the arguments that were passed to the function
	 * @return $this in order to maintain the fluent interface.
	 */
	public function __call($name, $arguments) {
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
 * The base class for all rules
 *
 * 
 */
abstract class Rule
{
	private $id;
	private $extended_rules;
	protected $args;

	public function __construct($arguments = []) {
		$this->args = $arguments;
		$this->id = uniqid();
		$this->extended_rules = $this->extend();
	}

	public function get_id() { return $this->id; }

	public function validate($value) {
		$result = true;

		foreach ($this->extended_rules as $rule) {
			$result &= $rule->condition($value);
		}

		return $result && $this->condition($value);
	}

	protected function extend() { return []; }

	abstract protected function condition($value);
}

class NotNullRule extends Rule
{
	public function condition($value)
	{
		return $value !== null;
	}
}

class RequiredRule extends Rule
{
	public function condition($value)
	{
		return isset($value) && $value !== '';
	}
}

class MaxLengthRule extends Rule
{
	private function max_length()
	{
		return $this->args[0];
	}

	public function condition($value)
	{
		return strlen($value) <= $this->max_length();
	}
}