<?php
	if( ! class_exists( 'FontanelTumblrImporter' ) ):
	
		class FontanelTumblrImporter {
			function __construct() {				
				// Import settings:
				if( file_exists( dirname(__FILE__) . '/settings.php' ) ) {
					require_once( dirname(__FILE__) . '/settings.php' );
				}
				
				add_filter( 'cron_schedules', array( &$this, 'add_fiveminutely' ) );
				
				add_action( 'my_fiveminutely_event', array( &$this, 'fetch_post' ) );
				add_action( 'admin_init', array( &$this, 'register_editable_settings' ) );
				add_action( 'admin_menu', array( &$this, 'add_plugin_menu' ) );
				add_action( 'wp_enqueue_scripts', array( &$this, 'register_fontanel_tumblr_import_scripts' ) );
				
				add_option( 'fontanel_tumblr_importer_api_key' );
				add_option( 'fontanel_tumblr_importer_blog_url' );
				
				register_activation_hook( $this->plugin_symlink_path( __FILE__ ), array( &$this, 'activate_periodical_call' ) );
				register_deactivation_hook( $this->plugin_symlink_path( __FILE__ ), array( &$this, 'deactivate_periodical_call' ) );
				register_activation_hook( $this->plugin_symlink_path( __FILE__ ), array( &$this, 'create_tables' ) );
			}
			
			function register_fontanel_tumblr_import_scripts() {
				wp_register_script( 'waypoints', plugins_url( '/js/waypoints.min.js', __FILE__ ), array('jquery'), 1, true );
				wp_register_script( 'infinite-scroll', plugins_url( '/js/infinite-scroll.js', __FILE__ ), array('jquery'), 1, true );
				
				wp_enqueue_script( 'waypoints' );
				wp_enqueue_script( 'infinite-scroll' ); 
			}


			
			// TODO: figure out why this used to work but doesn't anymore
			private function ugly_initialize() {
				$this->activate_periodical_call();
				$this->create_tables();
			}
			
			private function plugin_symlink_path( $file ) {
		    // If the file is already in the plugin directory we can save processing time.
		    if ( preg_match( '/'.preg_quote( WP_PLUGIN_DIR, '/' ).'/i', $file ) ) return $file;
		
		    // Examine each segment of the path in reverse
		    foreach ( array_reverse( explode( '/', $file ) ) as $segment ) {
		      // Rebuild the path starting from the WordPress plugin directory
		      // until both resolved paths match.
		
		      $path = rtrim($segment .'/'. $path, '/');       
		
		      if ( __FILE__ == realpath( WP_PLUGIN_DIR . '/' . $path ) ) {
		        return WP_PLUGIN_DIR . '/' . $path;
		      }
		    }
		
		    // If all else fails, return the original path.
		    return $file;
			}


			
			public function add_fiveminutely( $schedules ) {
				$schedules['fiveminutely'] = array(
					'interval' => ( 60 * 5 ),
					'display' => __( 'Once every five minutes' )
				);
				return $schedules;
			}
	
	
	
			public function activate_periodical_call() {
				$timestamp = time();
				$recurrence = 'fiveminutely';
				$hook = 'my_fiveminutely_event';
				
				wp_schedule_event( $timestamp, $recurrence, $hook );
			}
			
			
			
			public function deactivate_periodical_call() {
				$hook = 'fetch_post';
				
				wp_clear_scheduled_hook( $hook );
			}
			
			
			
			public function create_tables() {
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
				$sql = "CREATE TABLE IF NOT EXISTS " . FONTANEL_TUMBLR_IMPORTER_TABLE_NAME . " ( part mediumint(9) NOT NULL AUTO_INCREMENT, time int NOT NULL, post text NOT NULL, PRIMARY KEY(part) );";
				
				dbDelta( $sql );
			}	
			
			
	
			private function fetch_post() {
				$chandle = curl_init();
				$url = 'http://api.tumblr.com/v2/blog/fontanel.tumblr.com/posts?api_key=' . get_option( 'fontanel_tumblr_importer_api_key' ) . '&limit=1';
				curl_setopt( $chandle, CURLOPT_URL, $url );
				curl_setopt( $chandle, CURLOPT_RETURNTRANSFER, 1 );
				$result = curl_exec( $chandle );
				
				curl_close( $chandle );
				
				save_raw_input( $result );
			}

			private function save_raw_input( $json_string ) {
				global $wpdb;
				$json_object = json_decode( $json_string );
				$last_post = $json_object->response->posts[0];
				$last_db_entry = $wpdb->get_results( "SELECT `part` FROM `" . FONTANEL_TUMBLR_IMPORTER_TABLE_NAME . "` WHERE	`time` = " . $last_post->timestamp . " ORDER BY `part` DESC LIMIT 1" );
				
				if( !$last_db_entry ) {
					$new_post = serialize( $last_post );
					$wpdb->insert( FONTANEL_TUMBLR_IMPORTER_TABLE_NAME, array( 'post' => $new_post, 'time' => $last_post->timestamp ) );
				}
			}
			
			
			
			private function fetch_old_posts() {
				$chandle = curl_init();
				$url = 'http://api.tumblr.com/v2/blog/fontanel.tumblr.com/posts?api_key=' . get_option( 'fontanel_tumblr_importer_api_key' );
				curl_setopt( $chandle, CURLOPT_URL, $url );
				curl_setopt( $chandle, CURLOPT_RETURNTRANSFER, 1 );
				$result = curl_exec( $chandle );
				
				curl_close( $chandle );
				
				global $wpdb;
				$json_object = json_decode( $result );
				$old_posts = $json_object->response->posts;
				foreach( $old_posts as $old_post ):
					$last_db_entry = $wpdb->get_results( "SELECT `part` FROM `" . FONTANEL_TUMBLR_IMPORTER_TABLE_NAME . "` WHERE	`time` = " . $old_post->timestamp );
					
					if( !$last_db_entry ) {
						$new_post = serialize( $old_post );
						$wpdb->insert( FONTANEL_TUMBLR_IMPORTER_TABLE_NAME, array( 'post' => $new_post, 'time' => $old_post->timestamp ) );
					}
				endforeach;
			}



			function register_editable_settings() {
				register_setting( 'fontanel_tumblr_importer_section', 'fontanel_tumblr_importer_api_key', array( &$this, 'sanitize_fontanel_tumblr_importer_api_key' ) );
				register_setting( 'fontanel_tumblr_importer_section', 'fontanel_tumblr_importer_blog_url', array( &$this, 'sanitize_fontanel_tumblr_importer_blog_url' ) );
			}
			
			function add_plugin_menu() {
				add_settings_section( 'fontanel_tumblr_importer_section', 'General', array( &$this, 'render_fontanel_tumblr_importer_api_key_section' ), 'fontanel-tumblr-importer-options' );
				add_settings_field( 'fontanel_tumblr_importer_api_key_field', 'Api Key', array( &$this, 'render_fontanel_tumblr_importer_api_key_field' ), 'fontanel-tumblr-importer-options', 'fontanel_tumblr_importer_section' );
				add_settings_field( 'fontanel_tumblr_importer_blog_url_field', 'Url (without \'http\' or \'www\')', array( &$this, 'render_fontanel_tumblr_importer_blog_url_field' ), 'fontanel-tumblr-importer-options', 'fontanel_tumblr_importer_section' );
				add_options_page( 'Fontanel Tumblr Importer Options', 'Tumblr Importer', 'manage_options', 'fontanel-tumblr-importer-options', array( &$this, 'render_options_admin' ) );
			}
			
			function render_options_admin() {
				if ( !current_user_can( 'manage_options' ) )	{
					wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
				}
				
				if( file_exists( dirname(__FILE__) . '/fontanel-tumblr-importer-options-admin.php' ) ) {
					require_once( dirname(__FILE__) . '/fontanel-tumblr-importer-options-admin.php' );
				}
			}
			
			function sanitize_fontanel_tumblr_importer_api_key( $input ) {
				$this->ugly_initialize();
				$this->fetch_old_posts();
				return $input['fontanel_tumblr_importer_api_key_field'];
			}
			
			function sanitize_fontanel_tumblr_importer_blog_url( $input ) {
				$this->ugly_initialize();
  			$this->fetch_old_posts();
				return $input['fontanel_tumblr_importer_blog_url_field'];
			}
			
			function render_fontanel_tumblr_importer_api_key_field() {
				echo '<input id="fontanel_tumblr_importer_api_key_field" name="fontanel_tumblr_importer_api_key[fontanel_tumblr_importer_api_key_field]" value="' . get_option( 'fontanel_tumblr_importer_api_key' ) . '">';
			}
			
			function render_fontanel_tumblr_importer_blog_url_field() {
				echo '<input id="fontanel_tumblr_importer_blog_url_field" name="fontanel_tumblr_importer_blog_url[fontanel_tumblr_importer_blog_url_field]" value="' . get_option( 'fontanel_tumblr_importer_blog_url' ) . '">';
			}
			
			function render_fontanel_tumblr_importer_api_key_section() {
				do_settings_fields( 'fontanel-tumblr-importer-options', 'fontanel_tumblr_importer_api_key_field' );
				do_settings_fields( 'fontanel-tumblr-importer-options', 'fontanel_tumblr_importer_blog_url_field' );
			}
			
			function set_content_regexes( $regexes = array() ) {
  			$this->content_regexes = $regexes;
			}
			
			function execute_content_regexes( $input = '' ) {
  		  if( count( $this->content_regexes ) > 0 ):
    			foreach( $this->content_regexes as $key => $regex_set ):
      			$input = preg_replace( $regex_set[0], $regex_set[1], $input );
    			endforeach;
    		endif;
  			return $input;
			}


			public function getPosts( $page = 0, $rate = 5, $simple = true ) {
				global $wpdb; // To be able to reach all db data
		
				$raw_tumblr_posts = $wpdb->get_results( "SELECT `post` FROM `" . FONTANEL_TUMBLR_IMPORTER_TABLE_NAME . "` ORDER BY `time` DESC LIMIT " . ( $page * $rate ) . ", " . $rate ); // Query for the serialized posts
				
				$tumblr_posts = array(); // A storage for deserialized posts
				
				foreach( $raw_tumblr_posts as $raw_tumblr_post ):
					$tumblr_posts[] = unserialize( $raw_tumblr_post->post );
				endforeach;
				
				if( $simple ):
					return $tumblr_posts;
				endif;
			}
			
			public function defaultPostDisplay( $tumblr_post ) {
				if( $tumblr_post->state == 'published' ):
					setlocale('LC_ALL', WPLANG);
					$original_date = $tumblr_post->date;
					$posted_date = strftime( "%e %B %Y", strtotime( $original_date ) );
					?>
					<article class="tumblr-<?php echo $tumblr_post->type; ?><?php
						if( $tumblr_post->type == 'photo' and count( $tumblr_post->photos ) == 1 ):
							echo ' single-image';
						elseif( $tumblr_post->type == 'photo' and count( $tumblr_post->photos ) == 2 ):
							echo ' two-images';
						endif;
					?>">
						<?php switch( $tumblr_post->type ):
							case 'text': ?>
								<?php if( $tumblr_post->title ): ?>
									<h3><?php echo $tumblr_post->title; ?></h3>
									<?php if( $tumblr_post->body ): ?>
										<p><?php echo $this->execute_content_regexes( $tumblr_post->body ); ?></p>
									<?php endif; ?>
								<?php endif; ?>
								<footer>
									<p><a href="<?php echo $tumblr_post->post_url; ?>" target="_blank">lees meer</a> &raquo;</p>
								</footer>
								<?php break; ?>
								
							<?php case 'photo': ?>
								<?php foreach( array_slice( $tumblr_post->photos, 0, 3 ) as $key => $photo ): ?>
									<?php $arindex = 1; ?>
									<a href="<?php echo $tumblr_post->post_url; ?>" class="tubmlr-image" target="_blank">
										<div style="background-image: url('<?php echo $photo->alt_sizes[$arindex]->url; ?>');"></div>
									</a>
								<?php endforeach; ?>
								<?php if ( $tumblr_post->caption ): ?>
									<?php echo strip_tags( $tumblr_post->caption, '<p><a><h2>' ); ?>
								<?php endif; ?>
								<?php break; ?>
								
							<?php case 'quote': ?>
								<blockquote><p><?php echo $tumblr_post->text; ?></p></blockquote>
								<?php if( $tumblr_post->source ): ?>
									<div class="caption">
										<blockquote><p>{Source}</p></blockquote>
									</div>
								<?php endif; ?>
								<?php break; ?>
								
							<?php case 'link': ?>
								<h3><a href="<?php $tumblr_post->url ?>"><?php echo $tumblr_post->title ? $tumblr_post->title : $tumblr_post->post_url; ?></a></h3>
								<?php if( $tumblr_post->description ): ?>
									<p><?php echo strip_tags( $tumblr_post->description ); ?></p>
								<?php endif; ?>
								<?php break; ?>
							
							<?php case 'video': ?>
								<?php echo $tumblr_post->player[0]->embed_code; ?>
								<?php if ( $tumblr_post->caption ): ?>
									<?php echo strip_tags( $tumblr_post->caption, '<p><a><h2>' ); ?>
								<?php endif; ?>
								<?php break; ?>
	
							<?php default: ?>
								<p>Unknown Tumblr format</p>
						<?php endswitch; ?>
						<footer><p>Geplaatst op <time><?php echo $posted_date; ?></time></p></footer>
					</article>
				<?php endif;
			}
			
			public function ajaxUrl() {
				return plugins_url() . '/fontanel-tumblr-importer/fontanel-tumblr-importer.ajax.php';
			}
		}
	endif;
?>