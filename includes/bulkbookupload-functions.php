<?php

/**
 * Verifies that the core WPBookList plugin is installed and activated - otherwise, the Extension doesn't load and a message is displayed to the user.
 */
function wpbooklist_bulkbookupload_core_plugin_required() {

  // Require core WPBookList Plugin.
  if ( ! is_plugin_active( 'wpbooklist/wpbooklist.php' ) && current_user_can( 'activate_plugins' ) ) {

    // Stop activation redirect and show error.
    wp_die( 'Whoops! This WPBookList Extension requires the Core WPBookList Plugin to be installed and activated! <br><a target="_blank" href="https://wordpress.org/plugins/wpbooklist/">Download WPBookList Here!</a><br><br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
  }
}

// Adding the front-end ui css file for this extension
function wpbooklist_jre_bulkbookupload_frontend_ui_style() {
    wp_register_style( 'wpbooklist-bulkbookupload-frontend-ui', BULKBOOKUPLOAD_ROOT_CSS_URL.'bulkbookupload-frontend-ui.css' );
    wp_enqueue_style('wpbooklist-bulkbookupload-frontend-ui');
}

// Code for adding the general admin CSS file
function wpbooklist_jre_bulkbookupload_admin_style() {
  if(current_user_can( 'administrator' )){
      wp_register_style( 'wpbooklist-bulkbookupload-admin-ui', BULKBOOKUPLOAD_ROOT_CSS_URL.'bulkbookupload-admin-ui.css');
      wp_enqueue_style('wpbooklist-bulkbookupload-admin-ui');
  }
}


// Code for adding file that prevents computer sleep during backup
function wpbooklist_jre_bulkbookupload_sleep_script() {
	if(current_user_can( 'administrator' )){
    	wp_register_script( 'wpbooklist-jre-bulkbookupload-sleepjs', BULKBOOKUPLOAD_JS_URL.'nosleep/sleep.js', array('jquery') );
    	wp_enqueue_script('wpbooklist-jre-bulkbookupload-sleepjs');
	}
}





?>