PHPluentValidation
===================

### An utterly awesome and easy-to-use highly-extensible lightweight fluent validation framework for PHP.
*I really wish I could pack more adjectives into this description, but that's the best I was able to come up with*.

#### BEWARE! My TDD-fu failed me while developping this project! Proceed with care. Maybe some velociraptor is lurking around the corner.

These not-so-cool validation rules are supported out of the box :

 - NotNull (not null)
 - Required (not null or empty)
 - MaxLength 
 - ... (Kind of boring, I know. More to come.)

It's a so tiny piece of delicious carrot cake to create custom rules that even your annoying sister's three-legged incapable kitty cat can do it eyes shut while itching its back :

```php
class WorldShouldHaveEndedRule
{
	//Use the Rule trait. 
	//Or maybe it's an abstract class? 
	//Really, who gives a shit about this? 
	//Just use/extend it the right way. 
	use Rule; 
	
	/**
	 * Mandatory validate function with some useless description that don't mean anything.
	 * Who takes the time to read comments, really?
	 */
	public function validate($value)
	{
		return has_the_world_ended_yet(); // I really hope this returns true some day.

		//Why doesn't PHP support question markrs in identifiers?
		//This apocalyptic function name would be sooooo much cooler, like it's asking a question LOL!
	}
}

//WOW! That really looked like me using Ruby naming conventions in PHP :3
```

Your class must be discoverable by any registered autoloader (shame on you if you still use an old-(school/fashioned) out-of-date cheese-smelling `__autoload` function), and voilà! Now **YOU** figure out how to use it. Nah, just kidding, here it goes :

```php
$v = new Validator();
$v->rule_for("dont_you_know_how_to_use_this_already")->world_should_have_ended();

$obj = new AnyKindOfObjectNoReallyAnyObjectWillDoYouDontEvenNeedToUseOrSubclassAnything();

$v->validate($obj); // => false
```

This validator will check for function `dont_you_know_how_to_use_this_already()` on the target object and pass it through the rules defined for this particular function.

Phew!

# OK BRAIN FART IS OVER

No really, this is a real and serious project! I will put some real documentation with real examples in the real Wiki when I really feel like it. Really? Yeah, really.

### Features to come :

 - More unit tests!
 - Autoloader so you don't have to require every file.
 - Refactoring to remove unnecessary `$prop` property inside each rule.
 - Support for the same ruleset for different properties defined like this :

```php
//This way, both first_name and last_name will have the required and the max_length rules
$validator->rule_for('first_name')->and_for('last_name')
          ->required()
          ->max_length(100);
```

 - Allow a rule to extend other rules (not sure yet how it will work, but here is a brief preview) :

```php
class EmailRule
{
	public function validate($value) {
		return $this->extends([
			new RequiredRule(),
			new MaxLengthRule(255),
			new RegexRule('regex_for_email')
		], $value);
	}
}
```