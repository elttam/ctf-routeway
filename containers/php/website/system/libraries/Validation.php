<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Validation library.
 *
 * ##### Loading the Library and POST values
 *
 *     // Create a new Validation object using the $_POST global array
 *     $post = new Validation($_POST);
 *
 *     // Combine multiple arrays for validation
 *     $post = new Validation(array_merge($_POST, $_FILES));
 *
 *     // Using the factory enables method chaining
 *     $post = Validation::factory($_POST)->add_rules('field_name', 'required');
 *
 *     // You can also use the $_POST array directly (not recommended)
 *     $_POST = new Validation($_POST);
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Validation_Core extends ArrayObject {

	/**
	 * Pre-validation filters
	 * @var array $pre_filters
	 */
	protected $pre_filters = array();

	/**
	 * Post-validation filters
	 * @var array $post_filters
	 */
	protected $post_filters = array();

	/**
	 * Validation Rules
	 * @var array $rules
	 */
	protected $rules = array();

	/**
	 * Validation Callbacks
	 * @var array $callbacks
	 */
	protected $callbacks = array();

	/**
	 * Rules that are allowed to run on empty fields
	 * @var array $empty_rules
	 */
	protected $empty_rules = array('required', 'matches');

	/**
	 * Validation Errors
	 * @var array $errors
	 */
	protected $errors = array();

	/**
	 * Validation Messages
	 * @var array $messages
	 */
	protected $messages = array();

	/**
	 * Validation Labels
	 * @var array $labels
	 */
	protected $labels = array();

	/**
	 * Fields that are expected to be arrays
	 * @var array $array_fields
	 */
	protected $array_fields = array();

	/**
	 * Creates a new Validation instance.
	 *
	 * ##### Example
	 *
	 *     // Using the factory enables method chaining
	 *     $post = Validation::factory($_POST);
	 *
	 * @param   array   $array  Array to use for validation
	 * @return  Validation
	 */
	public static function factory(array $array)
	{
		return new Validation($array);
	}

	/**
	 * Sets the unique "any field" key and creates an ArrayObject from the
	 * passed array.
	 *
	 * @param   array   $array  Array to validate
	 * @return  void
	 */
	public function __construct(array $array)
	{
		parent::__construct($array, ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST);
	}

	/**
	 * Magic clone method, clears errors and messages.
	 *
	 * @return  void
	 */
	public function __clone()
	{
		$this->errors = array();
		$this->messages = array();
	}

	/**
	 * Create a copy of the current validation rules and change the array.
	 *
	 * ##### Example
	 *
	 *     // Initialize the validation library on the $_POST array with the rule 'required' applied to 'field_name'
	 *     $post = Validation::factory($_POST)->add_rules('field_name', 'required');
	 *
	 *     // Here we copy the rule 'required' for 'field_name' and apply it to a new array (field names need to be the same)
	 *     $new_post = $post->copy($new_array);
	 *
	 * @param   array  $array  New array to validate
	 * @return  Validation
	 */
	public function copy(array $array)
	{
		$copy = clone $this;

		$copy->exchangeArray($array);

		return $copy;
	}

	/**
	 * Returns an array of all the field names that have filters, rules, or callbacks.
	 *
	 * ##### Example
	 *
	 *     $fields	= $post->field_names();
	 *     // Outputs an array with the names of all fields that have filters, rules, callbacks.
	 *
	 * @return  array
	 */
	public function field_names()
	{
		// All the fields that are being validated
		$fields = array_keys(array_merge
		(
			$this->pre_filters,
			$this->rules,
			$this->callbacks,
			$this->post_filters
		));

		// Remove wildcard fields
		$fields = array_diff($fields, array('*'));

		return $fields;
	}

	/**
	 * Returns the array values of the current object.
	 *
	 * ##### Example
	 *
	 *     // Assume $form mirrors the html form
	 *     $form    = array('form_field_name1' => '',
	 *                      'form_field_name2' => '');
	 *
	 *     // Overwrite the empty values of $form with the values returned from as_array() to get a qualified array
	 *     $form	= arr::overwrite($form, $this->validation->as_array());
	 *
	 * @return  array
	 */
	public function as_array()
	{
		return $this->getArrayCopy();
	}

	/**
	 * Returns the ArrayObject values, removing all inputs without rules.
	 * To choose specific inputs, list the field name as arguments.
	 *
	 * ##### Example
	 *
	 *     // Similar to as_array() but only returns array values that have had rules, filters, and/or callbacks assigned.
	 *     $input = array('form_field1' => '5',
	 *                    'form_field2' => '10',
	 *                    'form_field3' => '15');
	 *
	 *     $post  = Validation::factory($input)->add_rules('form_field1', 'required')->add_rules('form_field2', 'digit');
	 *     echo print_r($post->safe_array());
	 *
	 *     // Output: Array ( [form_field1] => 5 [form_field2] => 10 )
	 *
	 *     // Same as above but here we specify (using the field name) which ones we want to recieve back.
	 *     $post  = Validation::factory($input)->add_rules('form_field1', 'required')->add_rules('form_field2', 'digit');
	 *     echo print_r($post->safe_array('form_field2'));
	 *
	 *     // Output: Array ( [form_field2] => 10 )
	 *
	 * @param   boolean  Return only fields with filters, rules, and callbacks
	 * @return  array
	 */
	public function safe_array()
	{
		// Load choices
		$choices = func_get_args();
		$choices = empty($choices) ? NULL : array_combine($choices, $choices);

		// Get field names
		$fields = $this->field_names();

		$safe = array();
		foreach ($fields as $field)
		{
			if ($choices === NULL OR isset($choices[$field]))
			{
				if (isset($this[$field]))
				{
					$value = $this[$field];

					if (is_object($value))
					{
						// Convert the value back into an array
						$value = $value->getArrayCopy();
					}
				}
				else
				{
					// Even if the field is not in this array, it must be set
					$value = NULL;
				}

				// Add the field to the array
				$safe[$field] = $value;
			}
		}

		return $safe;
	}

	/**
	 * Add additional rules that will forced, even for empty fields. All arguments
	 * passed will be appended to the list. Note: required and matches are an example
	 * of rules that can run on empty fields.
	 *
	 * ##### Example
	 *
	 *     // Note: required and matches are already set by default, this is used only as an example.
	 *     $post->allow_empty_rules('required', 'matches');
	 *
	 * @param   string   $rules   Rule name
	 * @return  Validation
	 */
	public function allow_empty_rules($rules)
	{
		// Any number of args are supported
		$rules = func_get_args();

		// Merge the allowed rules
		$this->empty_rules = array_merge($this->empty_rules, $rules);

		return $this;
	}

	/**
	 * Sets or overwrites the label name for a field. Label names are used in the
	 * default validation error messages. You can use a label name in custom error
	 * messages with the `:field` place holder.
	 *
	 * ##### Example
	 *
	 *     // Set a default label
	 *     $post->label('form_field1');
	 *     // Label will be set to 'Form Field'
	 *
	 *     // Set an alternative label
	 *     $post->label('form_field1', 'My Field Name');
	 *
	 * @param   string   $field   Field name
	 * @param   string   $label   Label
	 * @return Validation
	 */
	public function label($field, $label = NULL)
	{
		if ($label === NULL AND ($field !== TRUE OR $field !== '*') AND ! isset($this->labels[$field]))
		{
			// Set the field label to the field name
			$this->labels[$field] = ucfirst(preg_replace('/[^\pL]+/u', ' ', $field));
		}
		elseif ($label !== NULL)
		{
			// Set the label for this field
			$this->labels[$field] = $label;
		}

		return $this;
	}

	/**
	 * Sets labels using an array. Works the same as [Validation::labels] except allows
	 * you to pass in array of labels.
	 *
	 * ##### Example
	 *
	 *     $post->labels(array('first_name' => 'First Name', 'field2' => 'Label 2'));
	 *
	 * @param  array   $labels   List of field => label names
	 * @return Validation
	 */
	public function labels(array $labels)
	{
		$this->labels = $labels + $this->labels;

		return $this;
	}

	/**
	 * Converts a filter, rule, or callback into a fully-qualified callback array.
	 *
	 * @param  mixed   $callback   Valid callback
	 * @return  mixed
	 */
	protected function callback($callback)
	{
		if (is_string($callback))
		{
			if (strpos($callback, '::') !== FALSE)
			{
				$callback = explode('::', $callback);
			}
			elseif (function_exists($callback))
			{
				// No need to check if the callback is a method
				$callback = $callback;
			}
			elseif (method_exists($this, $callback))
			{
				// The callback exists in Validation
				$callback = array($this, $callback);
			}
			elseif (method_exists('valid', $callback))
			{
				// The callback exists in valid::
				$callback = array('valid', $callback);
			}
		}

		if ( ! is_callable($callback, FALSE))
		{
			if (is_array($callback))
			{
				if (is_object($callback[0]))
				{
					// Object instance syntax
					$name = get_class($callback[0]).'->'.$callback[1];
				}
				else
				{
					// Static class syntax
					$name = $callback[0].'::'.$callback[1];
				}
			}
			else
			{
				// Function syntax
				$name = $callback;
			}

			throw new Kohana_Exception('Callback %name% used for Validation is not callable', array('%name%' => $name));
		}

		return $callback;
	}

	/**
	 * Add a pre-filter to one or more inputs. Pre-filters are applied before
	 * rules or callbacks are executed.
	 *
	 * ##### Example
	 *
	 *     // Pre-filters can be used to format/filter field values before they are validated
	 *     // in this example the trim function will be applied to the field to trim any
	 *     // extraneous whitespace.
	 *     $post->pre_filter('trim', 'form_field1');
	 *
	 *     // Multiple fields can be passed in...
	 *     $post->pre_filter('trim', 'form_field1', 'form_field2');
	 *
	 *     // All fields can be pre_filter'ed
	 *     $post->pre_filter('trim');
	 *
	 * @param   mixed     $filter   Filter
	 * @param   string    $field    Fields to apply filter to, use TRUE for all fields
	 * @return  Validation
	 */
	public function pre_filter($filter, $field = TRUE)
	{
		if ($field === TRUE OR $field === '*')
		{
			// Use wildcard
			$fields = array('*');
		}
		else
		{
			// Add the filter to specific inputs
			$fields = func_get_args();
			$fields = array_slice($fields, 1);
		}

		// Convert to a proper callback
		$filter = $this->callback($filter);

		foreach ($fields as $field)
		{
			// Add the filter to specified field
			$this->pre_filters[$field][] = $filter;
		}

		return $this;
	}

	/**
	 * Add a post-filter to one or more inputs. Post-filters are applied after
	 * rules and callbacks have been executed.
	 *
	 * ##### Example
	 *
	 *     // Post-filters do what pre-filters do but after validation, in this
	 *     // example we apply ucfirst.
	 *     $post->post_filter('ucfirst', 'form_field1');
	 *
	 *     // Multiple fields can be passed in...
	 *     $post->post_filter('ucfirst', 'form_field1', 'form_field2');
	 *
	 *     // All fields can be post_filter'ed
	 *     $post->post_filter('ucfirst');
	 *
	 * @chainable
	 * @param   mixed     $filter  Filter
	 * @param   string    $field   Fields to apply filter to, use TRUE for all fields
	 * @return  Validation
	 */
	public function post_filter($filter, $field = TRUE)
	{
		if ($field === TRUE)
		{
			// Use wildcard
			$fields = array('*');
		}
		else
		{
			// Add the filter to specific inputs
			$fields = func_get_args();
			$fields = array_slice($fields, 1);
		}

		// Convert to a proper callback
		$filter = $this->callback($filter);

		foreach ($fields as $field)
		{
			// Add the filter to specified field
			$this->post_filters[$field][] = $filter;
		}

		return $this;
	}

	/**
	 * Add rules to a field. Validation rules may only return TRUE or FALSE and
	 * can not manipulate the value of a field.
	 *
	 * ##### Example
	 *
	 *     $input = array('form_field1' => '5',
	 *                    'form_field2' => '10',
	 *                    'form_field3' => '15');
	 *
	 *     $post  = new Validation($input);
	 *
	 *     // Add rules to the fields of our form (these can be chained)
	 *     $post->add_rules('form_field1', 'required', 'alpha_dash', 'length[1,5]')
	 *          ->add_rules('form_field2', 'matches[form_field1]')
	 *          ->add_rules('form_field3', 'digit');
	 *
	 *     // In case you may have custom validation helpers...
	 *     $post->add_rules('form_field1', 'myhelper::func', 'digit');
	 *
	 *     // Commas in rule arguments can be escaped with a backslash: 'matches[some\,val]'
	 *
	 * @chainable
	 * @param   string   $field  Field name
	 * @param   mixed    $rules  Rules (one or more arguments)
	 * @return  Validation
	 */
	public function add_rules($field, $rules)
	{
		// Get the rules
		$rules = func_get_args();
		$rules = array_slice($rules, 1);

		// Set a default field label
		$this->label($field);

		if ($field === TRUE)
		{
			// Use wildcard
			$field = '*';
		}

		foreach ($rules as $rule)
		{
			// Arguments for rule
			$args = NULL;

			// False rule
			$false_rule = FALSE;

			$rule_tmp = trim(is_string($rule) ? $rule : $rule[1]);

			// Should the rule return false?
			if ($rule_tmp !== ($rule_name = ltrim($rule_tmp, '! ')))
			{
				$false_rule = TRUE;
			}

			if (is_string($rule))
			{
				// Use the updated rule name
				$rule = $rule_name;

				// Have arguments?
				if (preg_match('/^([^\[]++)\[(.+)\]$/', $rule, $matches))
				{
					// Split the rule into the function and args
					$rule = $matches[1];
					$args = preg_split('/(?<!\\\\),\s*/', $matches[2]);

					// Replace escaped comma with comma
					$args = str_replace('\,', ',', $args);
				}
			}
			else
			{
				$rule[1] = $rule_name;
			}

			if ($rule === 'is_array')
			{
				// This field is expected to be an array
				$this->array_fields[$field] = $field;
			}

			// Convert to a proper callback
			$rule = $this->callback($rule);

			// Add the rule, with args, to the field
			$this->rules[$field][] = array($rule, $args, $false_rule);
		}

		return $this;
	}

	/**
	 * Add callbacks to a field. Callbacks must accept the Validation object
	 * and the input name. Callback returns are not processed.
	 *
	 * ##### Example
	 *
	 *     // Because add_rules() arguments may take as its argument any single
	 *     // argument php function (such as trim), there is one particular idiomatic
	 *     // use of add_callbacks().
	 *     $post->add_callbacks('form_field1', array($object, 'email_exists'));
	 *
	 *     // The above example takes the form field name and a two member array
	 *     // of an object reference and a string containing the name of the method
	 *     // in the object.
	 *
	 *     // Here is an example of a callback function that checks if an email already exists
	 *     public function email_exists(Validation $aArray, $sField)
	 *     {
	 *			$query	= (bool) $this->db->count_records('user', array('user_email' => $aArray[$sField]));
	 *
	 *			// If true, set an error
	 *			if ($query)
	 *			{
	 *				$aArray->add_error($sField, 'email_exists');
	 *			}
	 *     }
	 *
	 * @param   string     $field       Field name
	 * @param   mixed      $callbacks   Callbacks (unlimited number)
	 * @return  Validation
	 */
	public function add_callbacks($field, $callbacks)
	{
		// Get all callbacks as an array
		$callbacks = func_get_args();
		$callbacks = array_slice($callbacks, 1);

		// Set a default field label
		$this->label($field);

		if ($field === TRUE)
		{
			// Use wildcard
			$field = '*';
		}

		foreach ($callbacks as $callback)
		{
			// Convert to a proper callback
			$callback = $this->callback($callback);

			// Add the callback to specified field
			$this->callbacks[$field][] = $callback;
		}

		return $this;
	}

	/**
	 * Validate by processing pre-filters, rules, callbacks, and post-filters.
	 * All fields that have filters, rules, or callbacks will be initialized if
	 * they are undefined.
	 *
	 * ##### Example
	 *
	 *     $input = array('form_field1' => '5',
	 *                    'form_field2' => '10',
	 *                    'form_field3' => '15');
	 *
	 *     $post  = Validation::factory($input)->add_rules('form_field1', 'required')->add_rules('form_field2', 'digit');
	 *
	 *     // Validate the input array!
	 *     if ($post->validate())
	 *     {
	 *			// Validation succeeded
	 *     }
	 *     else
	 *     {
	 *			// Validation failed
	 *     }
	 *
	 * @param   array   $object      Validation object, used only for recursion
	 * @param   array   $field_name  Name of field for errors
	 * @return  bool
	 */
	public function validate($object = NULL, $field_name = NULL)
	{
		if ($object === NULL)
		{
			// Use the current object
			$object = $this;
		}

		$array = $this->safe_array();

		// Get all defined field names
		$fields = array_keys($array);

		foreach ($this->pre_filters as $field => $callbacks)
		{
			foreach ($callbacks as $callback)
			{
				if ($field === '*')
				{
					foreach ($fields as $f)
					{
						$array[$f] = is_array($array[$f]) ? array_map($callback, $array[$f]) : call_user_func($callback, $array[$f]);
					}
				}
				else
				{
					$array[$field] = is_array($array[$field]) ? array_map($callback, $array[$field]) : call_user_func($callback, $array[$field]);
				}
			}
		}

		foreach ($this->rules as $field => $callbacks)
		{
			foreach ($callbacks as $callback)
			{
				// Separate the callback, arguments and is false bool
				list ($callback, $args, $is_false) = $callback;

				// Function or method name of the rule
				$rule = is_array($callback) ? $callback[1] : $callback;

				if ($field === '*')
				{
					foreach ($fields as $f)
					{
						// Note that continue, instead of break, is used when
						// applying rules using a wildcard, so that all fields
						// will be validated.

						if (isset($this->errors[$f]))
						{
							// Prevent other rules from being evaluated if an error has occurred
							continue;
						}

						if (empty($array[$f]) AND ! in_array($rule, $this->empty_rules))
						{
							// This rule does not need to be processed on empty fields
							continue;
						}

						$result = ($args === NULL) ? call_user_func($callback, $array[$f]) : call_user_func($callback, $array[$f], $args);

						if (($result == $is_false))
						{
							$this->add_error($f, $rule, $args);

							// Stop validating this field when an error is found
							continue;
						}
					}
				}
				else
				{
					if (isset($this->errors[$field]))
					{
						// Prevent other rules from being evaluated if an error has occurred
						break;
					}

					if ( ! in_array($rule, $this->empty_rules) AND ! $this->required($array[$field]))
					{
						// This rule does not need to be processed on empty fields
						continue;
					}

					// Results of our test
					$result = ($args === NULL) ? call_user_func($callback, $array[$field]) : call_user_func($callback, $array[$field], $args);

					if (($result == $is_false))
					{
						$rule = $is_false ? '!'.$rule : $rule;
						$this->add_error($field, $rule, $args);

						// Stop validating this field when an error is found
						break;
					}
				}
			}
		}

		foreach ($this->callbacks as $field => $callbacks)
		{
			foreach ($callbacks as $callback)
			{
				if ($field === '*')
				{
					foreach ($fields as $f)
					{
						// Note that continue, instead of break, is used when
						// applying rules using a wildcard, so that all fields
						// will be validated.
						if (isset($this->errors[$f]))
						{
							// Stop validating this field when an error is found
							continue;
						}

						call_user_func($callback, $this, $f);
					}
				}
				else
				{
					if (isset($this->errors[$field]))
					{
						// Stop validating this field when an error is found
						break;
					}

					call_user_func($callback, $this, $field);
				}
			}
		}

		foreach ($this->post_filters as $field => $callbacks)
		{
			foreach ($callbacks as $callback)
			{
				if ($field === '*')
				{
					foreach ($fields as $f)
					{
						$array[$f] = is_array($array[$f]) ? array_map($callback, $array[$f]) : call_user_func($callback, $array[$f]);
					}
				}
				else
				{
					$array[$field] = is_array($array[$field]) ? array_map($callback, $array[$field]) : call_user_func($callback, $array[$field]);
				}
			}
		}

		// Swap the array back into the object
		$this->exchangeArray($array);

		// Return TRUE if there are no errors
		return $this->errors === array();
	}

	/**
	 * Add an error to an input.
	 *
	 * ##### Example
	 *
	 *     $post->add_array('form_field1', 'email_exists');
	 *
	 *     print_r($post->errors());
	 *
	 *     // Output: Array ( [form_field1] => email_exists )
	 *
	 * @chainable
	 * @param   string  $field  Input name
	 * @param   string  $name   Unique error name
	 * @param   string  $args   Arguments to pass to lang file
	 * @return  Validation
	 */
	public function add_error($field, $name, $args = NULL)
	{
		$this->errors[$field] = array($name, $args);

		return $this;
	}

	/**
	 * Return the errors array.
	 *
	 * ##### Example
	 *
	 *     $post->add_array('form_field1', 'email_exists');
	 *
	 *     print_r($post->errors());
	 *
	 *     // Output: Array ( [form_field1] => email_exists )
	 *
	 * @param   string $file Message file to load errors from
	 * @return  array
	 */
	public function errors($file = NULL)
	{
		if ($file === NULL)
		{
			$errors = array();
			foreach($this->errors as $field => $error)
			{
				$errors[$field] = $error[0];
			}
			return $errors;
		}
		else
		{
			$errors = array();
			foreach ($this->errors as $input => $error)
			{
				// Locations to check for error messages
				$error_locations = array
				(
					"validation/{$file}.{$input}.{$error[0]}",
					"validation/{$file}.{$input}.default",
					"validation/default.{$error[0]}"
				);

				if (($message = Kohana::message($error_locations[0])) !== $error_locations[0])
				{
					// Found a message for this field and error
				}
				elseif (($message = Kohana::message($error_locations[1])) !== $error_locations[1])
				{
					// Found a default message for this field
				}
				elseif (($message = Kohana::message($error_locations[2])) !== $error_locations[2])
				{
					// Found a default message for this error
				}
				else
				{
					// No message exists, display the path expected
					$message = "validation/{$file}.{$input}.{$error[0]}";
				}

				// Start the translation values list
				$values = array(':field' => __($this->labels[$input]));

				if ( ! empty($error[1]))
				{
					foreach ($error[1] as $key => $value)
					{
						// Add each parameter as a numbered value, starting from 1
						$values[':param'.($key + 1)] = __($value);
					}
				}

				// Translate the message using the default language
				$errors[$input] = __($message, $values);
			}

			return $errors;
		}
	}

	/**
	 * Rule: required. Generates an error if the field has an empty value.
	 *
	 * ##### Example
	 *
	 *     $post->add_rules('form_field1', 'required');
     *
	 * @param   mixed   $str  Input value
	 * @return  bool
	 */
	public function required($str)
	{
		if (is_object($str) AND $str instanceof ArrayObject)
		{
			// Get the array from the ArrayObject
			$str = $str->getArrayCopy();
		}

		if (is_array($str))
		{
			return ! empty($str);
		}
		else
		{
			return ! ($str === '' OR $str === NULL OR $str === FALSE);
		}
	}

	/**
	 * Rule: matches. Generates an error if the field does not match one or more
	 * other fields.
	 *
	 * ##### Example
	 *
	 *     $post->add_rules('form_field1', 'matches');
     *
	 * @param   mixed   $str     Input value
	 * @param   array   $inputs  Input names to match against
	 * @return  bool
	 */
	public function matches($str, array $inputs)
	{
		foreach ($inputs as $key)
		{
			if ($str !== (isset($this[$key]) ? $this[$key] : NULL))
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Rule: length. Generates an error if the field is too long or too short.
	 *
	 * ##### Example
	 *
	 *     // For a minimum of 1 to a maximum of 5 characters
	 *     $post->add_rules('form_field1', 'length[1,5]');
	 *
	 *     // For an exact length of 5
	 *     $post->add_rules('form_field1', 'length[5]');
     *
	 * @param   mixed   $str     Input value
	 * @param   array   $length  Minimum, maximum, or exact length to match
	 * @return  bool
	 */
	public function length($str, array $length)
	{
		if ( ! is_string($str))
			return FALSE;

		$size = mb_strlen($str);
		$status = FALSE;

		if (count($length) > 1)
		{
			list ($min, $max) = $length;

			if ($size >= $min AND $size <= $max)
			{
				$status = TRUE;
			}
		}
		else
		{
			$status = ($size === (int) $length[0]);
		}

		return $status;
	}

	/**
	 * Rule: depends_on. Generates an error if the field does not depend on one
	 * or more other fields.
	 *
	 * ##### Example
	 *
	 *     // (separated by commas for more than one)
	 *     $post->add_rules('form_field2', 'depends_on[form_field1]');
     *
	 * @param   mixed   $field   Field name
	 * @param   array   $fields  Field names to check dependency
	 * @return  bool
	 */
	public function depends_on($field, array $fields)
	{
		foreach ($fields as $depends_on)
		{
			if ( ! isset($this[$depends_on]) OR $this[$depends_on] == NULL)
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Rule: chars. Generates an error if the field contains characters outside of the list.
	 *
	 * ##### Example
	 *
	 *     $post->add_rules('form_field1', 'chars[a,b,c,d]');
     *
	 * @param   string  $value  Field value
	 * @param   array   $chars  Allowed characters
	 * @return  bool
	 */
	public function chars($value, array $chars)
	{
		return ! preg_match('![^'.implode('', $chars).']!u', $value);
	}

} // End Validation