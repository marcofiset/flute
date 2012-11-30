PHPluentValidation
===================

### A ~~magic!~~ highly-extensible lightweight fluent validation framework for PHP.

#### BEWARE! This has not been tested rigorously yet! Not sure if it all works fine.

These validation rules are supported out of the box :

 - Required (not null or empty)
 - MaxLength 
 - ... (I know that's not a lot, but come on! This project has been alive for a few hours only)

It's so easy to create new rules that even your sister's kitty cat can do it :

	require_once 'phpluent_validation.php' // require the file, only if you want to

	class WorldShouldHaveEndedRule
	{
		use Rule; //Use the Rule trait for more awesomeness.
		
		/**
		 * The given object will be considered valid only if the world has ended. 
		 */
		public function validate($obj)
		{
			return has_the_world_ended_yet();
		}
	}
	
Your class must be discoverable by any registered autoloader, and voilà! You can now create a validator and invoke your new awesome rule :

	$v = new Validator();
	$v->rule_for('this_is_getting_long')->world_should_have_ended();
	
	$obj = new AnyObject();
	
	$v->validate($obj); // => false
	
This validator will check for function `this_is_getting_long()` on the target object and pass it through the rules defined for this particular function.

### TODO:

 - Some refactoring (having the prop inside each rule is kinda bad)