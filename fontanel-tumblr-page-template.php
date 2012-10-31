<?php
/**
	*	Template Name: Tumblr Posts
	*/
	
	if ( class_exists( 'FontanelTumblrImporter' ) ):
		$MyFontanelTumblrImporter = new FontanelTumblrImporter();
	endif;
	
	get_header();
?>

	<h2>Fontanel Tumblr Demo Template</h2>
	<div id="tumblr-posts-wrapper">
		<?php foreach( $MyFontanelTumblrImporter->getPosts( 0, 5,  true ) as $tumblr_post ): ?>
			<?php $MyFontanelTumblrImporter->defaultPostDisplay( $tumblr_post ); ?>
		<?php endforeach; ?>
	</div>
	
	<div class="waypoint" data-ajax-url="<?php echo $MyFontanelTumblrImporter->ajaxUrl(); ?>/fontanel-tubmlr-importer/fontanel-tumblr-importer.ajax.php"></div>

<?php
	get_sidebar();
	get_footer();
?>
