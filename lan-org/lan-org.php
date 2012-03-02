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

		add_action('template_redirect', array($this, 'template_redirect'));
		add_action('init', array($this, 'setup_rewrite_tags'));
		add_action('wp_enqueue_scripts', array($this, 'load_static_files'));

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
	public function registration_form_markup() {
		$fields = array(
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
				'validator' => 'empty',
			),
			array(
				'type' => 'text',
				'key' => 'password',
				'label' => 'Mot de passe :',
				'password' => true,
				'validator' => 'empty',
			),
		);
		$values = array();
		$errors = array();

		lanorg_form_post($fields, $values, 'lanorg-');
		lanorg_form_validation($fields, $values, $errors);

		return lanorg_form_html_as_p($fields, $values, 'lanorg-', $errors);
	}

	// Custom page handler
	public function template_redirect() {
		global $wp;

		echo $wp->query_vars['lanorg'];
		switch ($wp->query_vars['lanorg']) {
		case 'register':
			$this->render_template('lanorg-register.php');
			break ;
		}
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