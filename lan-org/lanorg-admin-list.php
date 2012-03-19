<?php

if(!class_exists('WP_List_Table')){
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class LanOrgListTable extends WP_List_Table {
	var $title_column_key = NULL;
	var $table_name;
	var $columns;
	var $form;
	var $form_values = array();
	var $form_errors = array();
	var $show_form = FALSE;
	var $id = -1;

	function __construct($options){
		$options['ajax'] = FALSE; // AJAX is NOT supported
		$this->columns = $options['columns'];
		$this->table_name = $options['table_name'];
		$this->form = $options['form'];
		parent::__construct($options);
	}

	function column_default($item, $column_name){
		$content = isset($item[$column_name]) ? $item[$column_name] : '';
		if ($column_name == $this->title_column_key) {
			// Row actions
			$hash = wp_create_nonce('tournament' . $item['id']);
			$actions = array(
				'edit'      =>	'<a href="?page=' . $_REQUEST['page'] .
												'&action=edit&id=' . $item['id'] .
												'">Edit</a>',
				'delete'    =>	'<a href="?page=' . $_REQUEST['page'] .
												'&action=delete&id=' . $item['id'] .
												'&hash=' . $hash . '">Delete</a>',
			);

			$content .= $this->row_actions($actions);
		}
		return $content;
	}

	function set_title_column($column_key) {
		$this->title_column_key = $column_key;
	}

	function column_cb($item){
			return '<input type="checkbox" name="' . $this->_args['singular'] . '[]" value="' . $item['id'] . '" />';
	}

	function get_columns(){
		$columns = array(
			'cb'				=> '<input type="checkbox" />', //Render a checkbox instead of text
		);
		$columns = array_merge($columns, $this->columns);
		return $columns;
	}

	function get_sortable_columns() {
		// TODO
		return array();
	}

	function get_bulk_actions() {
		// TODO Bulk action
		$actions = array(
		);
		return $actions;
	}

	// Displays the list if $this->show_form is FALSE
	// otherwise, displays the form.
	function display() {
		global $wpdb, $lanOrg;

		if ($this->show_form) {
			echo '<form method="POST" action="?page=' . $_REQUEST['page'] . '">';
			echo '<input type="hidden" name="action" value="edit" />';
			echo '<input type="hidden" name="id" value="' . (int) $this->id . '" />';
			echo '<input type="hidden" name="page" value="' . $_REQUEST['page'] . '" />';
			echo lanorg_form_html_as_table($this->form, $this->form_values, $lanOrg->form_prefix, $this->form_errors);
			echo '<p class="submit"><input type="submit" class="button-primary" value="Save"/></p>';
			echo '</form>';
		}
		else {
			echo '<form method="GET">';
			echo '<input type="hidden" name="page" value="' . $_REQUEST['page'] . '" />';
			parent::display();
			echo '</form>';
		}
	}

	// Run action, if any
	function run_action() {
		global $wpdb, $lanOrg;
		// MySQL table name
		$table_name = $wpdb->prefix . $this->table_name;

		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;

		$this->show_form = FALSE;

		// Item ID currently being edited or deleted
		$this->id = isset($_POST['id']) ? (int) $_POST['id']
															: (isset($_GET['id']) ? (int) $_GET['id']
															: -1);

		switch ($action) {
		case 'edit':
		case 'insert':
			// Handles edit form
			$this->show_form = TRUE;


			// Get form data from HTTP POST
			if (lanorg_form_post($this->form, $this->form_values, $lanOrg->form_prefix)) {

				// Validates data
				if (lanorg_form_validation($this->form, $this->form_values, $this->form_errors)) {

					if ($this->id == -1) { // Insertion
						$wpdb->insert($table_name, $this->form_values);
						
					}
					else { // Update
						// Updates data in database
						$wpdb->update($table_name, $this->form_values, array('id' => $this->id), NULL, array('%d'));
					}
					$this->show_form = FALSE; // Do not display edit/insert form
				}
			}
			else {
				// Otherwise, load data from database
				$this->form_values = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $this->id", ARRAY_A);
			}
			break ;
		case 'delete':
			if ($this->id >= 0) {
				$wpdb->query("DELETE FROM $table_name WHERE id = $this->id LIMIT 1");
			}
			break ;
		}
	}

	// Prepare the item list
	function prepare_items() {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table_name;

		// How many records per page to show
		$per_page = 10;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$current_page = $this->get_pagenum();
		$row_offset = ($current_page - 1) * $per_page;

		$data = $wpdb->get_results("SELECT * FROM $table_name LIMIT $row_offset, $per_page", ARRAY_A);

		$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name;"));

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,									// Total number of items
				'per_page'    => $per_page,											// How many items to show on a page
				'total_pages' => ceil($total_items / $per_page)	// Total number of pages
		) );

	}
}

?>