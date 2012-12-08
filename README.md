# Flute

### An utterly awesome and easy-to-use, lightweight and highly-extensible fluent validation framework for PHP.

Flute's fluent interface is heavily inspired by [FluentValidation for .NET](https://github.com/JeremySkinner/FluentValidation).

Here is a simple example to get you started :

```php
require 'path/to/flute.php';

$validator = new Validator();
$validator->rule_for('first_name')->max_length(100);

$p = new Person('John');

if ($validator->validate($p)) {
	echo 'Valid!';
}
```

These validation rules are supported out of the box :

 - NotNull
 - NotEmpty 
 - Required
 - MaxLength 
 - ... (Kind of boring, I know. More to come. Or you could just **write some yourself**!)

### Creating custom validation rules

It's very easy to create custom rules, and you don't even have to alter the `Validator` class. Everything is done with magic naming conventions! For example, here is how the NotEmpty rule is built :

```php
// The naming is very important here. More on that later.
class NotEmptyRule extends Rule // We must extend the Rule abstract class
{
	//We override the condition function to run our own validation logic
	public function condition($value)
	{
		// The $value variable contains the value we need to validate.
		// For this particular case, it is considered valid if it is
		// not equal to the empty string.
		return $value !== '';
	}
}
```

Once your rule is defined, it must be discoverable by any registered auto-loader in order the make it available to the `Validator` class. Here is how you invoke it :

```php
// Create an instance of the validator
$v = new Validator();

$v->rule_for('first_name')->not_empty();
```

See what I did there? No need to add the `not_empty()` function to the validator. Any unknown function invoked on a validator will create a rule named with the following conventions :

 - Every word delimited by underscores is capitalized.
 - The underscores are removed.
 - 'Rule' is appended to the resulting string.

So `not_empty` becomes `NotEmptyRule`. The validator will instantiate a `NotEmptyRule` class and associate it with the property name defined when we called `rule_for`. Easy, huh?

### What if I want to use parameters?

Very simple indeed. Here is a brief look at the `MaxLengthRule` implementation.

```php
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

$v = new Validator();
$v->rule_for('first_name')->max_length(100);
```

Any parameters passed to the function definition will be registered in the `args` array *in the same order* that they were passed to the validator. In this case, I defined a private method called `max_length`to make things clearer, but this is obviously not required.

### Extending existing rules

Your custom rule can extend the behaviour of existing rules. Let's take a look at the `RequiredRule` implementation :

```php
class RequiredRule extends Rule
{
	public function extend() {
		return [
			new NotNullRule(),
			new NotEmptyRule()
		];
	}
}
```

As you can see, the `RequiredRule` is only a combination of the `NotNullRule` and the `NotEmptyRule`. The `extend` function returns an array of instantiated rules which you want to extend. The `condition` function will be called on both rules and combined with a logical `and`. That is, if any of the condition fails, it is considered invalid.

That's it for the moment! Stay tuned for more awesome features!

### On its way :

 - More rules!
 - Composer support.
 - Register error messages in the validator upon failed validation.
 - Conditional conditions! (Run a rule only when some condition is met).
 - Allow to customize how extended rules are parsed, instead of evaluating them all together with a logical `and`.