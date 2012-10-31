<div class="wrap">
	<h2>Fontanel Tumblr Importer</h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'fontanel_tumblr_importer_section' ); ?>
		<?php do_settings_sections( 'fontanel-tumblr-importer-options' ); ?>
		<?php submit_button(); ?>
	</form>
</div>
