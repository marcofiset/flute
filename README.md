# Flute

### An utterly awesome and easy-to-use, lightweight and highly-extensible fluent validation framework for PHP.

Flute's fluent interface is heavily inspired by [FluentValidation for .NET](https://github.com/JeremySkinner/FluentValidation).

Here is a simple example to get you started :

```php
require 'path/to/flute.php';

$validator = new Validator();
$validator->rule_for('first_name')->max_length(100);

$p = new Person('John');
$result = $validator->validate($p);

if ($result->valid()) {
	echo 'Valid!';
}
```

These validation rules are supported out of the box :

 - NotNull
 - NotEmpty 
 - Required
 - MaxLength 
 - ... (and many more. You can also **add rules yourself**)

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

Very simple. Here is a brief look at the `MaxLengthRule` implementation.

```php
class MaxLengthRule extends Rule
{
	public function condition($value)
	{
		return strlen($value) <= $this->max_length;
		//W-w-w-wait, what!? Where does max_length come from?
	}
}

$v = new Validator();
$v->rule_for('first_name')->max_length(100);
```

Any parameters passed to the function definition will be registered in the `args` array *in the same order* that they were passed to the validator rule. You can also access args with the magic `__get` method, like I did here with `max_length`. I you were to call `max_length` again, it will send back the same value as the first time it was called. When using multiple arguments, they are handed out in the order they were passed, and each name gets its own argument.

### Extending existing rules

Your custom rule can extend the behaviour of existing rules. Let's take a look at the `RequiredRule` implementation :

```php
class RequiredRule extends Rule
{
	public function extend() {
		return array(
			new NotNullRule(),
			new NotEmptyRule()
		);
	}
}
```

As you can see, the `RequiredRule` is only a combination of the `NotNullRule` and the `NotEmptyRule`. The `extend` function returns an array of instantiated rules which you want to extend. The `condition` function will be called on both rules and combined with a logical `and`. That is, if any of the condition fails, it is considered invalid.

That's it for the moment! Stay tuned for more awesome features!