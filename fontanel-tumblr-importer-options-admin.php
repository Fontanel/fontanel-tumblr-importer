<div class="wrap">
	<h2>Fontanel Tumblr Importer</h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'fontanel_tumblr_importer_section' ); ?>
		<?php do_settings_sections( 'fontanel-tumblr-importer-options' ); ?>
		<?php submit_button(); ?>
	</form>
	
	<style>
  	table { border-collapse: collapse; }
    table, tr, td {
       border: 1px solid black;
    }
	</style>
	
	<script>
  	function confirmDelete(delUrl) {
      if (confirm("Weet je het zeker? Als je per ongeluk de verkeerde post verwijderd word Jasper boos!")) {
        document.location = delUrl;
      }
    }
	</script>
	
	<table>
  	<tbody>
      <?php $MyFontanelTumblrImporter = new FontanelTumblrImporter(); ?>
      <?php foreach( $MyFontanelTumblrImporter->getPosts( $_GET['page'], 5,  true ) as $tumblr_post ): ?>
        <tr>
          <td>
            <?php echo $MyFontanelTumblrImporter->defaultPostDisplay( $tumblr_post ); ?>
          </td>
          <td style="vertical-align: top;">
            <a href="javascript:confirmDelete('delete.page?id=1')">Verwijder dit artikel van de Magazine Homepage</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
	</table>
</div>
