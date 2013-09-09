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
	private $next_props = array();
	private $rules = array();
	private $rules_props = array();
	private $rules_conditions = array();
	private $rules_messages = array();

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
		$this->next_props = array();
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
	 *   - This function creates a new rule from class MaxLengthRule
	 *   - The class must extend the Rule class (see the Rule class documentation to see how to do this)
	 * 
	 * @param string $name is the name of the function that was called
	 * @param array $arguments is an array containing all the arguments that were passed to the function
	 * 
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
	 * Defines a condition for the last defined rule.
	 * 
	 * Before evaluating a rule, we will pass the object being validated to
	 * the function received as a parameter which must return a boolean indicating
	 * wether we should run this rule or not.
	 * 
	 * @param callback $condition is a function taking an object as a parameter which must return a boolean
	 * @return $this in order to maintain the fluent interface
	 */
	public function when($condition) {
		//Get the last added rule and associate it with the condition
		$last_rule_id = end($this->rules)->get_id();
		$this->rules_conditions[$last_rule_id] = $condition;

		return $this;
	}

	/**
	 * Defines the error message for the last defined rule.
	 * 
	 * If a rule is not respected, the message will be registered as an error
	 * for the specific property.
	 * 
	 * @param string $message is the error message for the last rule
	 * @return $this in order to maintain the fluent interface 
	 */
	public function with_message($message) {
		$last_rule_id = end($this->rules)->get_id();
		$this->rules_messages[$last_rule_id] = $message;

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

		$results = new ValidationResult();

		foreach ($this->rules as $rule) {
			//Get the rule condition from the array if there is one
			$rule_id = $rule->get_id();

			//If a condition exists for the rule
			if (array_key_exists($rule_id, $this->rules_conditions)) {
				$rule_condition = $this->rules_conditions[$rule_id];

				//Skip the rule if the condition is not met
				if (!$rule_condition($obj)) continue;
			}

			//For each rule, we get the props associated with their id
			//in the other array containing the props.
			foreach ($this->rules_props[$rule_id] as $prop) {
				//Check if $prop is a method or property
				if (method_exists($obj, $prop)) {
					$value = $obj->$prop();
				} else if (property_exists($obj, $prop)) {
					$value = $obj->$prop;
				}

				if (!$rule->validate($value)) {
					if (array_key_exists($rule_id, $this->rules_messages)) {
						$error_message = $this->rules_messages[$rule_id];
					} else {
						$error_message = 'Field is invalid';
					}

					$results->addError($prop, $error_message);
				}
			}
		}

		return $results;
	}
}

/**
 * The base class for all rules
 *
 * You must extend this class in order to define validation rules. You have 
 * the choice to override any protected function. You want to override at
 * least one of the protected function.
 */
abstract class Rule
{
	private $id;
	private $next_arg_index;
	protected $args;
	protected $named_args;

	/**
	 * Constructor for Rule. 
	 * 
	 * You must not define a new constructor in the 
	 * derived classes as it will not work. This is intended to be the only 
	 * possible way to instantiate any rule.
	 * 
	 * @param array $arguments is the array containing the arguments that were
	 * passed to the validator when invoking the rule.
	 */
	public function __construct($arguments = array()) {
		$this->next_arg_index = 0;
		$this->named_args = array();
		$this->args = $arguments;

		//Assign a unique id to each rule so we can use it in a hash table
		$this->id = uniqid();
	}

	/**
	 * Gets the unique id of the rule.
	 * 
	 * @return int The unique id of the rule.
	 */
	public function get_id() { return $this->id; }

	/**
	 * Validates a given value against the defined condition and every 
	 * extended rules. If any of the condition is not respected, it returns false.
	 * If all conditions are met, true is returned instead
	 * 
	 * @param mixed $value is the value we want to validate.
	 * @return bool indicating wether the value was valid or not.
	 */
	public function validate($value) {
		//Reset the args index in case we validate multiple times.
		$this->next_arg_index = 0;

		$result = true;

		//Loop through the extended rules to invoke their validation.
		foreach ($this->extend() as $rule) {
			$result = $result & $rule->validate($value);
		}

		return $result & $this->condition($value);
	}

	/**
	 * Returns an array containing the rules we want to extend.
	 * 
	 * Override this function if you want your custom rule to inherit
	 * the conditions of another rule. You must return an array containing
	 * correctly instantiated rules. Each of the rules contained in the returned
	 * array will be invoked when validating a value.
	 * 
	 * You do not have to override this function. It returns an empty array by default.
	 * 
	 * @return array of Rule containing the rules we want to extend.
	 */
	protected function extend() { return array(); }

	/**
	 * Defines the condition for which a given value is valid.
	 * 
	 * You do not have to override this function if you want to only extend existing
	 * rules. Otherwise, override it and define your own behaviour. If you
	 * need any parameters to help you with the validation, they must be passed
	 * in the constructor as an array of arguments.
	 * 
	 * @param mixed $value The value to validate
	 * @return bool indicating wether the value was valid according to the defined rule.
	 */
	protected function condition($value) { return true; }

	/**
	 * Gets the next argument from the $args array.
	 * 
	 * @param $name the name of the property we want to get
	 * @return mixed The next item from the $args array, unless calling with the same name
	 */
	public function __get($name) {
		//If we are reusing a name, we return the same value
		if (array_key_exists($name, $this->named_args)) {
			return $this->named_args[$name];
		}

		//Retrieves the next args and then increments the index for the next call.
		$arg = $this->args[$this->next_arg_index++];

		//Register the arg with the name used to call this function so we can reuse the same name
		$this->named_args[$name] = $arg;

		return $arg;
	}
}

/**
 * The class representing the validation results
 * 
 * This class is used as a return value from the validate function of the Validator
 */ 
class ValidationResult
{
	/**
	 * This is public to be able to json_encode it.
	 */ 
	public $errors = array();

	/**
	 * @return bool indicating wether the validation was successful
	 */ 
	public function valid() {
		return count($this->errors) === 0;
	}

	/**
	 * Adds an error message for a specific property name
	 * 
	 * @param string $prop is the name of the property to add the error to
	 * @param string $message is the error message
	 */ 
	public function addError($prop, $message) {
		$this->errors[$prop] = $message;
	}
}

class NotNullRule extends Rule
{
	public function condition($value) {
		return $value !== null;
	}
}

class NotEmptyRule extends Rule
{
	public function condition($value) { 
		return $value !== ''; 
	}
}

class RequiredRule extends Rule
{
	public function extend() {
		return array(
			new NotNullRule(),
			new NotEmptyRule()
		);
	}
}

class MinLengthRule extends Rule
{
	public function condition($value) {
		return strlen($value) >= $this->min_length;
	}
}

class MaxLengthRule extends Rule
{
	public function condition($value) {
		return strlen($value) <= $this->max_length;
	}
}

class LengthRule extends Rule
{
	public function extend() {
		return array(
			new MinLengthRule(array($this->min_length)),
			new MaxLengthRule(array($this->max_length))
		);
	}
}

class NotEqualToRule extends Rule
{
	public function condition($value) {
		return !in_array($value, $this->args);
	}
}

class GreaterThanRule extends Rule
{
	public function condition($value) {
		return $value > $this->value;
	}
}

class GreaterOrEqualRule extends Rule
{
	public function condition($value) {
		return $value >= $this->min;
	}
}

class LessThanRule extends Rule
{
	public function condition($value) {
		return $value < $this->value;
	}
}

class LessOrEqualRule extends Rule
{
	public function condition($value) {
		return $value <= $this->max;
	}
}

class ExclusiveBetweenRule extends Rule
{
	public function extend() {
		return array(
			new GreaterThanRule(array($this->min)),
			new LessThanRule(array($this->max))
		);
	}
}

class BetweenRule extends Rule
{
	public function extend() {
		return array(
			new GreaterOrEqualRule(array($this->min)),
			new LessOrEqualRule(array($this->max))
		);
	}
}