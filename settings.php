<?php
	global $wpdb;
	
	$options = array(
		'TABLE_NAME' => $wpdb->prefix . 'fontanel_tumblr_importer_raw_json_dumps'
	);
	
	foreach( $options as $key => $value ) {
		define( 'FONTANEL_TUMBLR_IMPORTER_' . $key, $value );
	}
?>
