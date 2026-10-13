/**
 * Templates gallery: explain coming-soon industries instead of opening a
 * non-existent template.
 */
( function () {
	'use strict';

	var notice = document.querySelector( '[data-dmt-notice]' );
	if ( ! notice ) {
		return;
	}
	var text = notice.querySelector( '[data-dmt-notice-text]' );
	var close = notice.querySelector( '[data-dmt-notice-close]' );
	var lastTrigger = null;

	function message( name ) {
		// Translatable sentence rendered by PHP, e.g. "Our %s templates are…".
		return ( notice.getAttribute( 'data-template' ) || '%s' ).replace( '%s', name );
	}

	document.addEventListener( 'click', function ( e ) {
		var card = e.target.closest( '[data-dmt-soon]' );
		if ( ! card ) {
			return;
		}
		lastTrigger = card;
		notice.hidden = false;
		// Re-announce even if the same card is clicked twice.
		text.textContent = '';
		window.setTimeout( function () {
			text.textContent = message( card.getAttribute( 'data-dmt-soon' ) );
		}, 30 );
		var top = notice.getBoundingClientRect().top;
		if ( top < 0 || top > window.innerHeight - 80 ) {
			notice.scrollIntoView( { behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth', block: 'center' } );
		}
	} );

	if ( close ) {
		close.addEventListener( 'click', function () {
			notice.hidden = true;
			if ( lastTrigger ) {
				lastTrigger.focus();
			}
		} );
	}

	// Remove ?coming-soon= from the address bar once shown.
	if ( window.history && window.history.replaceState && /[?&]coming-soon=/.test( window.location.search ) ) {
		var url = new URL( window.location.href );
		url.searchParams.delete( 'coming-soon' );
		window.history.replaceState( null, '', url.pathname + url.search + url.hash );
	}
}() );
