<?php

// Functions used to generate HTML code
$lanorg_field_html = array(
	'text' => 'lanorg_text_field_html',
	'select' => 'lanorg_select_field_html',
	'checkbox' => 'lanorg_checkbox_field_html',
);

$lanorg_custom_validators = array(
	'empty' => 'lanorg_validate_empty',
	'username_exists' => 'lanorg_validate_username_exists',
	'username_valid' => 'lanorg_validate_username_valid',
	'email_exists' => 'lanorg_validate_email_exists',
	'email_valid' => 'lanorg_validate_email_valid',
);

$lanorg_field_validators = array(
	'checkbox' => 'lanorg_validate_checkbox',
);

// Generate HTML markup for a form
// The form is validated if it has been correctly submitted
function lanorg_form($fields, &$values = array(), &$errors = array(), $renderer='lanorg_form_html_as_p') {
	global $lanOrg;

	lanorg_form_post($fields, $values, $lanOrg->form_prefix);
	lanorg_form_validation($fields, $values, $errors);

	return call_user_func($renderer, $fields, $values, $lanOrg->form_prefix, $errors);
}

// Get POST values for each field
// @return Boolean 
function lanorg_form_post($fields, &$values, $prefix) {
	global $lanorg_field_validators;

	$token = isset($_POST[$prefix . 'token']) ? $_POST[$prefix . 'token'] : '';

	if (wp_verify_nonce($token, $prefix . 'token'))
	{
		$complete = TRUE;
		foreach ($fields as $field) {
			$key = $field['key'];
			$type = $field['type'];

			$name_attr = $prefix . $key;
			$value = NULL;
			if (isset($values[$key])) {
				$value = $values[$key];
			}

			if (isset($_POST[$name_attr])) {
				$value = $_POST[$name_attr];
			}
			if (isset($lanorg_field_validators[$type])) {
				$validator = $lanorg_field_validators[$type];
				// If the validator returns false, then the POST field is incomplete
				if (!call_user_func($validator, $options, &$value)) {
					$complete = FALSE;
				}
			}
			elseif (!isset($_POST[$name_attr])) {
				// By default, missing fields doesn't validate
				$complete = FALSE;
			}
			$values[$key] = $value;
		}
	}
	else {
		$complete = FALSE;
	}
	return $complete;
}

// Generate HTML markup for a form
// Each field is enclosed in a <P> element
// @param fields Array which contains field
function lanorg_form_html_as_p($fields, $values, $prefix='', $errors=array()) {
	$markup = '';

	foreach ($fields as $field) {

		$key = $field['key'];
		$value = isset($values[$key]) ? $values[$key] : NULL;

		$error = NULL;

		if (isset($errors[$key])) {
			$error = $errors[$key];
		}

		$field_markup = lanorg_form_field_html($field, $value, $prefix, $error);

		if ($field_markup !== NULL) {
			$markup .= '<p>';

			if ($error !== NULL) {
				$markup .= $error . '<br/>';
				$css_classes .= ' lanorg-error';
			}

			// Label tag, if supplied
			if (isset($field['label'])) {
				$markup .= lanorg_label_html($prefix . $key, $field['label']);
			}

			$markup .= $field_markup;
			$markup .= '</p>';
		}
	}

	$markup .= wp_nonce_field($prefix . 'token', $prefix . 'token', FALSE, FALSE);

	return $markup;
}

// Generate HTML markup for a form
// Each field is enclosed in a table
function lanorg_form_html_as_table($fields, $values, $prefix='', $errors=array()) {
	$markup = '';

	$markup .= '<table class="form-table">';
	foreach ($fields as $field) {

		$key = $field['key'];
		$value = isset($values[$key]) ? $values[$key] : NULL;

		$error = NULL;

		if (isset($errors[$key])) {
			$error = $errors[$key];
		}

		$field_markup = lanorg_form_field_html($field, $value, $prefix, $error);

		if ($field_markup !== NULL) {

			if ($error !== NULL) {
				$markup .= '<tr><td>';
				$markup .= '<br/>' . $error;
				$css_classes .= ' lanorg-error';
				$markup .= '</td></tr>';
			}

			$markup .= '<tr valign="top">';
			// Label tag, if supplied
			if (isset($field['label'])) {
				$markup .= '<th valign="top" scope="row">';
				$markup .= lanorg_label_html($prefix . $key, $field['label']);
				$markup .= '</th>';
			}

			$markup .= '<td>';
			$markup .= $field_markup;
			$markup .= '</td>';

			$markup .= '</tr>';
		}
	}

	$markup .= '</table>';

	$markup .= wp_nonce_field($prefix . 'token', $prefix . 'token', FALSE, FALSE);

	return $markup;
}

// Return HTML markup for a field
// @param field Array which contains field options
// @param value Field value
// @return string HTML markup or NULL if the field type is unknown
function lanorg_form_field_html($field, $value, $prefix='', &$error) {
	global $lanorg_field_html;
	$markup = NULL;

	if (isset($field['type']) && isset($field['key'])) {

		$type = $field['type'];
		$key = $field['key'];

		if (isset($lanorg_field_html[$type])) {
			$func = $lanorg_field_html[$type];

			$markup = call_user_func($func, $field, $value, $prefix, $error);
		}
	}

	return $markup;
}

// Validates each field
// Values are validated and error are added to the $error array.
// @return TRUE when no error is found.
function lanorg_form_validation($fields, $values, &$errors) {
	global $lanorg_custom_validators;

	foreach ($fields as $field) {

		$key = $field['key'];

		if (isset($field['validator']) && isset($values[$key])) {

			$value = $values[$key];

			$validators = $field['validator'];

			// string is accepted, convert it to an array of one element
			if (is_string($validators)) {
				$validators = array($validators);
			}

			// The value passes a series of validations
			foreach ($validators as $validator) {

				if (isset($lanorg_custom_validators[$validator])) {

					$validator_func = $lanorg_custom_validators[$validator];

					$field_errors = array();

					call_user_func($validator_func, $field, $value, &$field_errors);

					// currently, only one error is retained
					if (count($field_errors)) {
						$errors[$key] = $field_errors[0];
						break ;
					}
				}
			}
		}
	}

	return (bool) (count($errors) == 0);
}

// ** Fields type ***********

// Gets HTML code for a LABEL element
function lanorg_label_html($id, $label) {
	$markup = '';
	$label = htmlentities($label, NULL, 'UTF-8');
	$markup = '<label for="' . $id . '">' . $label . '</label>';
	return $markup;
}

// Displays errors
function lanorg_display_error() {
	
}

// Text field
// Parameters accepted in options array :
// key, label, default, password
function lanorg_text_field_html($options, $value, $prefix, $error) {
	$markup = '';
	$key = $prefix . $options['key'];
	$is_password = isset($options['password']) && $options['password'];
	$css_classes = 'lanorg-field lanorg-text';

	// Default option
	if ($value === NULL && isset($options['default'])) {
		$value = $options['default'];
	}

	$markup .= '<input ';
	$markup .= 'type="' . ($is_password ? 'password' : 'text') . '" ';
	$markup .= 'id="' . $key . '" ';
	$markup .= 'name="' . $key . '" ';

	if ($value !== NULL) {
		$markup .= 'value="' . htmlentities($value, NULL, 'UTF-8') . '" ';
	}
	$markup .= 'class="' . $css_classes . '"/>';
	return $markup;
}

// Select field
// Parameters accepted in options array :
// key, label, default, choices
function lanorg_select_field_html($options, $value, $prefix) {
	$markup = '';
	$key = $prefix . $options['key'];
	$css_classes = 'lanorg-field lanorg-select';
	$choices = $options['choices'];
	if (is_string($choices)) {
		$choices = call_user_func($choices, $options, $value);
	}

	if ($value !== NULL) {
		if (!isset($choices[$value])) {
			$value = NULL;
		}
	}
	// Default option
	if ($value === NULL && isset($options['default'])) {
		$value = $options['default'];
	}

	$markup .= '<select ';
	$markup .= 'id="' . $key . '" ';
	$markup .= 'name="' . $key . '" ';
	$markup .= 'class="' . $css_classes . '"/>';
	
	foreach ($choices as $choice => $choiceText)
	{
		$markup .= '<option value="'.$choice.'" ';
		if($choice == $value)
		{
			$markup .= 'selected="selected"';
		}
		$markup .= '>' . htmlentities($choiceText, NULL, 'UTF-8') . '</option>';
	}
	if ($value !== NULL) {
		$markup .= 'value="' . htmlentities($value, NULL, 'UTF-8') . '" ';
	}
	$markup .= '</select>';
	return $markup;
}

// Checkbox
function lanorg_checkbox_field_html($options, $value, $prefix) {
	$markup = '';
	$key = $prefix . $options['key'];
	$css_classes = 'lanorg-field lanorg-checkbox';

	$checked = FALSE;

	if ($value !== NULL) {
		$checked = !empty($value);
	}
	// Default state
	if ($value === NULL && isset($options['default'])) {
		$checked = !!$options['default'];
	}

	$markup .= '<input type="checkbox" ';
	$markup .= 'id="' . $key . '" name="' . $key . '" value="1" ';
	if ($checked) {
		$markup .= 'checked="checked" ';
	}
	$markup .= 'class="' . $css_classes . '"/>';

	if (isset($options['text'])) {
		$markup .= ' ' . lanorg_label_html($key, $options['text']);
	}

	return $markup;
}

// ** Data validation ***********

// Field validators

// Checkboxes can only have '1' or '0'
function lanorg_validate_checkbox($options, &$value) {
	if ($value === NULL) {
		$value = '0';
	}
	elseif (!empty($value)) {
		$value = '1';
	}
	return TRUE;
}

// Custom validators

// Checks if the field is empty
function lanorg_validate_empty($options, $value, &$errors) {
	if (empty($value) && $value !== '0') {
		array_push($errors, __('This field is required.', 'lanorg'));
	}
}

// Raise an error when the username is already taken
function lanorg_validate_username_exists($options, $value, &$errors) {
	if (username_exists($value)) {
		array_push($errors, __('This nickname already exists.', 'lanorg'));
	}
}

// Raise an error when the username is already taken
function lanorg_validate_username_valid($options, $value, &$errors) {
	if (!validate_username($value)) {
		array_push($errors, __('This nickname is not valid.', 'lanorg'));
	}
}

// Raise an error when the email address has already been registered
function lanorg_validate_email_exists($options, $value, &$errors) {
	if (email_exists($value)) {
		array_push($errors, __('This email is already registered.', 'lanorg'));
	}
}

// Raise an error when the given email address is not valid
function lanorg_validate_email_valid($options, $value, &$errors) {
	if (!is_email($value)) {
		array_push($errors, __('This email is not a valid format.', 'lanorg'));
	}
}

?>