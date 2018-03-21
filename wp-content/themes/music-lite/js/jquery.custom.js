( function( $ ) {

	'use strict';

	function removeNoJsClass() {
		$( 'html:first' ).removeClass( 'no-js' );
	}

	/* Sidr Menu ---------------------*/
	function sidrMenu() {
		if ($("body.music-lite-logo-right")[0]) {
			$('#menu-toggle').sidr({
				name: 'side-menu',
				side: 'left', // By default
				source: '#navigation'
			});
		} else {
			$('#menu-toggle').sidr({
				name: 'side-menu',
				side: 'right', // By default
				source: '#navigation'
			});
		}
	}

	/* Submenu Offset Fix ---------------------*/
	function menuOffset() {
		// Fix menu if off screen.
		var mainWindowWidth = $(window).width() + 180;

		$('#navigation ul.menu li.menu-item-has-children').mouseover(function() {

			// Checks if second level menu exist.
			var subMenuExist = $(this).find('.sub-menu').length;

			if ( subMenuExist > 0 ) {
				var subMenuWidth = $(this).find('.sub-menu').width();
				var subMenuOffset = $(this).find('.sub-menu').parent().offset().left + subMenuWidth;

				// If sub menu is off screen, give new position.
				if ( (subMenuOffset + subMenuWidth) > mainWindowWidth ) {
					var newSubMenuPosition = subMenuWidth;
					$(this).find('ul.sub-menu').css({
						right: 0,
						left: 'auto',
					});
					$(this).find('ul.sub-menu ul.sub-menu').css({
						left: -newSubMenuPosition - 24,
						right: 'auto',
					});
				}
			}
		});

	}

	function modifyPosts() {

		/* Toggle Mobile Menu Icon ---------------------*/
		$('.menu-toggle').on('click touchstart', function() {
			$('.icon-menu-open').toggle();
			$('.icon-menu-close').toggle();
		});

		/* Insert Line Break Before More Links ---------------------*/
		$('<br />').insertBefore('p a.more-link');

		/* Animate Page Scroll ---------------------*/
		$(".scroll").click(function(event){
			event.preventDefault();
			$('html,body').animate({scrollTop:$(this.hash).offset().top}, 500);
		});

		/* Fit Vids ---------------------*/
		$('.content').fitVids();

		/* Check Element BG Brightness ---------------------*/
		$('#nav-bar').bgBrightness();
		if ( $('.banner-img').length ) {
			$('.banner-img').bgBrightness();
		}
		if ( $('.wp-custom-header').length ) {
			$('.wp-custom-header').bgBrightness();
		}
		if ( $('.post-area').length ) {
			$('.post-area').bgBrightness();
		}
		if ( $('.sidebar').length ) {
			$('.sidebar').bgBrightness();
		}
		if ( $('.footer').length ) {
			$('.footer').bgBrightness();
		}

	}

	$( document )
	.ready( removeNoJsClass )
	.ready( sidrMenu )
	.ready( menuOffset )
	.ready( modifyPosts )
	.on( 'post-load', modifyPosts );

})( jQuery );
