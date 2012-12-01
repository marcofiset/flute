PHPluentValidation
===================

### An utterly awesome and easy-to-use highly-extensible lightweight fluent validation framework for PHP.
*I really wish I could pack more adjectives into this description, but that's the best I was able to come up with*.

#### BEWARE! Maybe some velociraptor is lurking around the corner! My TDD-fu failed me while developping this project. Proceed with care through these untested realms.

These not-so-cool validation rules are supported out of the box :

 - NotNull
 - NotEmpty 
 - Required (extends NotNull and NotEmpty)
 - MaxLength 
 - ... (Kind of boring, I know. More to come. OR WRITE SOME YOURSELF! PFFT! :japanese_goblin: (failed attempt at finding a furious guy in the emoji collection))

It's a so tiny piece of delicious carrot cake to create custom rules that even your annoying sister's three-legged incapable kitty cat can do it eyes shut while itching its back :

```php
class WorldShouldHaveEndedRule extends Rule //You must extend the Rule abstract class.
{
	//You must use the Rule trait. 
	use Rule; 
	
	//Wasn't Rule supposed to be an abstract class?
	//Hmmm... Thought it was a trait.
	//Really, who gives a shit about this? 
	//Just check the source to figure it out.
	
	/**
	 * Mandatory condition function with some useless description that don't mean anything.
	 * Who takes the time to read comments, really?
	 */
	public function condition($value)
	{
		return MayanShaman::has_the_world_ended_yet(); // I really hope this returns true some day.

		//Why doesn't PHP support question marks in identifiers?
		//This apocalyptic function name would be sooooo much cooler, like it's asking a question LOL!
	}

	/**
	 * Implement this virtual function if you want to extend other rules.
	 * But... why would you do that? Code reuse is so overrated.
	 */
	public function extend()
	{
		//I don't feel like explaining it.
	}
}

//WOW! That really looked like trying to use Ruby naming conventions in PHP :3
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

# OKAY BRAIN FART IS OVER

No really, this is a real and serious project! I will put some real documentation with real examples in the real Wiki when I really feel like it. Really? Yeah, really.

### On its way :

 - More unit tests!
 - Autoloader so you don't have to require every file.
 - Register errors in the validator upon failed validation