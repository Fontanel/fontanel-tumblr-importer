<?php
	@ini_set( 'display_errors', 'On' );
	
	require_once( '../../../wordpress/wp-load.php' );
	
	// Import settings:
	if( file_exists( dirname(__FILE__) . '/fontanel-tumblr-importer.class.php' ) ) {
		require_once( dirname(__FILE__) . '/fontanel-tumblr-importer.class.php' );
	}	
	
	if ( class_exists( 'FontanelTumblrImporter' ) ):
		$MyFontanelTumblrImporter = new FontanelTumblrImporter();
		foreach( $MyFontanelTumblrImporter->getPosts( $_GET['page'], 5,  true ) as $tumblr_post ):
			$MyFontanelTumblrImporter->defaultPostDisplay( $tumblr_post );
		endforeach;
	endif;
?>
