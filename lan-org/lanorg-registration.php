<?php
$lanorg_registration_form = array(
	array(
		'type' => 'text',
		'key' => 'tournament',
		'label' => 'Tournois :',
		'validator' => 'empty',
	),
);

// Get the HTML markup for the registration form
// Called from the template
function lanorg_get_registration_form_markup()
{
	global $lanorg_registration_form;
	return lanorg_form($lanorg_registration_form);
}

?>