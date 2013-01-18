if( $( '.waypoint' ).length > 0 ){
	var next_page = 1;
	var ajax_url = $( '.waypoint' ).attr( 'data-ajax-url' );
	
	var args = {
		offset: '100%',
		triggerOnce: true,
		onlyOnScroll: true
	}
	
	function fetchAndProcessNewPosts() {
		$.get(
			ajax_url,
			{ page: next_page },
			function( data, txt, jqXHR ) {
				$( '#tumblr-posts-wrapper' ).append( data );
				registerNewWaypointListener();
				next_page++;
			}
		);
	}
	
	function registerNewWaypointListener() {
		$('.waypoint').waypoint( function( el, dir ) {
			fetchAndProcessNewPosts();
		}, args);	
	}
	
	function createNewWaypoint() {
		var	new_waypoint = $('<div></div>').addClass('waypoint');
		$( '#tumblr-posts-wrapper' ).after( new_waypoint );
		registerNewWaypointListener();
	}
	
	registerNewWaypointListener();
}
