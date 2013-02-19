if( $( '.waypoint' ).length > 0 ){
	var next_page = 1;
	var ajax_url = $( '.waypoint' ).attr( 'data-ajax-url' );
	var opts = {
	  lines: 9, // The number of lines to draw
	  length: 4, // The length of each line
	  width: 2, // The line thickness
	  radius: 4, // The radius of the inner circle
	  corners: 1, // Corner roundness (0..1)
	  rotate: 0, // The rotation offset
	  color: '#000', // #rgb or #rrggbb
	  speed: 1.3, // Rounds per second
	  trail: 25, // Afterglow percentage
	  shadow: false, // Whether to render a shadow
	  hwaccel: false, // Whether to use hardware acceleration
	  className: 'spinner', // The CSS class to assign to the spinner
	  zIndex: 2e9, // The z-index (defaults to 2000000000)
	  top: '10', // Top position relative to parent in px
	  left: 'auto' // Left position relative to parent in px
	};
	
	var args = {
		offset: '100%',
		triggerOnce: true,
		onlyOnScroll: true
	}
	
	function fetchAndProcessNewPosts() {
		var target = $('<div></div>').addClass('spinner-holder');
		$( '#tumblr-posts-wrapper' ).append( target );
		var spinner = new Spinner(opts).spin();
		target.append(spinner.el);
		$.get(
			ajax_url,
			{ page: next_page },
			function( data, txt, jqXHR ) {
				$( '#tumblr-posts-wrapper' ).append( data );
				registerNewWaypointListener();
				next_page++;
				// TODO: make this generic, probably attach an event
				$('#blog').fitVids(); // Update the video width
				spinner.stop();
				target.remove();
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
