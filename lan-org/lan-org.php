<?php

// Plugin Name: LAN Party Organization
// Plugin URI:  http://lanravageur.github.com/wp-lan-plugin
// Description: Wordpress LAN plugin is used to organize LAN Party.
// Version:     0.0
// Text Domain: lanorg
// Domain Path: /lan-org/

define('LANORG_TEMPLATE_DIR_NAME', 'templates');

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}


if (!class_exists('lanOrg')) :

require('lanorg-form.php');

// Main Object
class LanOrg {

		public $registration_form = array(
			array(
				'type' => 'text',
				'key' => 'nickname',
				'label' => 'Choissez un pseudonyme :',
				'validator' => array('empty', 'username_exists', 'username_valid'),
			),
			array(
				'type' => 'text',
				'key' => 'firstname',
				'label' => 'Prénom :',
				'validator' => 'empty',
			),
			array(
				'type' => 'text',
				'key' => 'lastname',
				'label' => 'Nom :',
				'validator' => 'empty',
			),
			array(
				'type' => 'text',
				'key' => 'email',
				'label' => 'Courriel :',
				'validator' => array('empty', 'email_exists', 'email_valid'),
			),
			array(
				'type' => 'text',
				'key' => 'password',
				'label' => 'Mot de passe :',
				'password' => true,
				'validator' => 'empty',
			),
		);

	public $form_prefix = 'lanorg-';

	// Setup the LAN Party Organization plugin
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	private function setup_globals() {

		// Main plugin file
		$this->file	= __FILE__;

		// Gets the plugin name based from the file path (e.g., lan-org/lan-org.php)
		$this->basename = plugin_basename($this->file);

		// Plugin directory (e.g., /wwwroot/wp/wp-content/plugins/lan-org/)
		$this->plugin_dir = plugin_dir_path($this->file);

		// Absolute plugin URL (e.g., http://127.0.0.1/wp/wp-content/plugins/lan-org/)
		$this->plugin_url = plugin_dir_url($this->file);

		// path to templates directory
		$this->template_dir = $this->plugin_dir . LANORG_TEMPLATE_DIR_NAME;
	}

	// Includes plugin files
	private function includes() {
	}

	// Set-up plugin actions
	private function setup_actions() {
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));

		add_action('init', array($this, 'setup_rewrite_tags'));
		add_action('wp_enqueue_scripts', array($this, 'load_static_files'));
		add_action('template_redirect', array($this, 'redirect_template'));

		add_shortcode('lanorg-register', array($this, 'register_form'));
	}

	function load_static_files() {
		wp_register_style('lanorg-form', plugins_url('css/form.css', __FILE__));
	}

	function setup_rewrite_tags() {
		add_rewrite_tag('%lanorg%','([^&]+)');
	}

	// Called when the plugin is activated
	public function activate() {
	}

	// Called when the plugin is desactivated
	public function deactivate() {
	}

	public function redirect_template() {
		$values = array();
		if (lanorg_form_post($this->registration_form, $values, $this->form_prefix)) {
			$errors = array();
			if (lanorg_form_validation($this->registration_form, $values, $errors)) {
				wp_insert_user(array(
					'user_login' => $values['nickname'],
					'first_name' => $values['firstname'],
					'last_name' => $values['lastname'],
					'user_email' => $values['email'],
					'user_pass' => $values['password'],
				));

				wp_redirect(home_url());
				exit;
			}
		}

	}

	public function register_form($attr) {
		wp_enqueue_style('lanorg-form');

		// Turn on output buffering to capture form markup
		ob_start();

		$this->render_template('lanorg-register.php');

		// Get buffered content
		$content = ob_get_clean();
		return $content;
	}

	// Get the HTML markup for the registration form
	// Called from the template
	// It MUST not process any data, as it can be rendered multiple times
	public function registration_form_markup() {
		$values = array();
		$errors = array();

		lanorg_form_post($this->registration_form, $values, $this->form_prefix);
		lanorg_form_validation($this->registration_form, $values, $errors);

		return lanorg_form_html_as_p($this->registration_form, $values, $this->form_prefix, $errors);
	}

	// Render a given template, overwriting current template
	public function render_template($template_file) {
		$template_file_path = TEMPLATEPATH . '/' . $template_file;

		if (!file_exists($template_file_path)) {
			$template_file_path = $this->template_dir . '/' . $template_file;
		}

		include($template_file_path);
	}
}

$GLOBALS['lanOrg'] = new LanOrg();

endif;

?>