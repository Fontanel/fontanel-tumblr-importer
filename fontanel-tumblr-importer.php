<?php
/*
	Plugin Name: Fontanel Tumblr Importer
	Description: Periodically imports posts from a Tumblr blog
	Version: 2.0
	Author: Fontanel, Jasper Kennis
	Author URI: http://fontanel.nl
	License: None
*/

	@ini_set( 'display_errors', 'On' );
	
	// Import settings:
	if( file_exists( dirname(__FILE__) . '/fontanel-tumblr-importer.class.php' ) ) {
		require_once( dirname(__FILE__) . '/fontanel-tumblr-importer.class.php' );
	}	

	if ( class_exists( 'FontanelTumblrImporter' ) ):
		$MyFontanelTumblrImporter = new FontanelTumblrImporter();
	endif;
?>
