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
require('lanorg-account.php');

// Main Object
class LanOrg {
	public $form_prefix = 'lanorg-';

	// Store the content for custom page
	public $page_content = '';
	public $page_title = '';

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
		add_action('init', array($this, 'setup_post_types'));
		add_action('wp_enqueue_scripts', array($this, 'load_static_files'));
		add_action('template_redirect', array($this, 'redirect_template'));

		add_shortcode('lanorg-register', 'lanorg_shortcode_registration_form');

		add_action('generate_rewrite_rules', array($this, 'add_rewrite_rules'));
		add_filter('query_vars', array($this, 'get_query_vars'));
	}

	function load_static_files() {
		wp_register_style('lanorg-form', plugins_url('css/form.css', __FILE__));
	}

	function setup_rewrite_tags() {
		add_rewrite_tag('%lanorg%','([^&]+)');
	}

	function setup_post_types() {
		register_post_type('lanparty',
			array(
				'labels' => array(
					'name' => 'Lan Parties',
					'singular_name' => 'Lan Party'
				),
				'public' => true,
				'has_archive' => false,
				'supports' => array('title'),
			)
		);
	}

	function get_query_vars($query_vars) {
		$query_vars[] = 'lanorg_page';
		return $query_vars;
	}

	public function add_rewrite_rules($wp_rewrite) {
		$new_rules = array(
			'login/?$' => 'index.php?lanorg_page=login',
			'live/?$' => 'index.php?lanorg_page=live',
		);
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	}

	// Called when the plugin is activated
	public function activate() {
	}

	// Called when the plugin is desactivated
	public function deactivate() {
	}

	public function redirect_template() {
		global $wp_query;

		if (isset($wp_query->query_vars['lanorg_page']))
		{
			switch ($wp_query->query_vars['lanorg_page']) {
			case 'live':
				$this->render_custom_page('lanorg-live.php');
				break ;
			case 'login':
				$this->render_custom_page('lanorg-login.php');
				break ;
			}
		}

		lanorg_process_registration_form();
	}

	// Render a given template, overwriting current template
	// Lookup first in the theme directory for $template_file, if not found then
	// fallback to the template directory in plugin directory.
	public function render_template($template_file) {
		$template_file_path = TEMPLATEPATH . '/' . $template_file;

		if (!file_exists($template_file_path)) {
			$template_file_path = $this->template_dir . '/' . $template_file;
		}

		include($template_file_path);
	}

	public function get_custom_page_title($title, $sep='', $seplocation='') {
		return 'Salut ' . $sep;
	}

	public function get_custom_page_content() {
		return $this->page_content;
	}

	// Render a given page
	public function render_custom_page($page_file) {
		global $wp_query;
		$wp_query->is_home = FALSE; // Not homepage

		//add_filter('the_title', array($this, 'get_custom_page_title'));
		add_filter('wp_title', array($this, get_custom_page_title));
		add_filter('the_content', array($this, 'get_custom_page_content'));

		$GLOBALS['page_title'] = 'abc';

		// Turn on output buffering to capture page content
		ob_start();

		$this->render_template($page_file);

		// Get buffered content
		$this->page_content = ob_get_clean();

		$this->page_title = $GLOBALS['page_title'];

		$this->render_template('lanorg-page.php');

		exit ;
	}
}

$GLOBALS['lanOrg'] = new LanOrg();

endif;

?>