/**
 * Dental clinic template behaviour + live personalisation binder.
 *
 * Personalisation arrives from the customiser (same-origin postMessage) or,
 * when the preview is opened in its own tab, from this browser's
 * sessionStorage. Values are only ever written with textContent / validated
 * attributes, never as HTML.
 */
( function () {
	'use strict';

	var config = window.DMT_PREVIEW || {};
	var schema = config.schema || {};
	var root = document.documentElement;
	var HEX = /^#[0-9a-f]{6}$/i;
	var LOGO = /^data:image\/(png|jpeg|webp);base64,[a-z0-9+/=]+$/i;

	/* ---------------- Colour helpers (mirror DMT_Color) ---------------- */

	function rgb( hex ) {
		return [ 1, 3, 5 ].map( function ( i ) {
			return parseInt( hex.substr( i, 2 ), 16 );
		} );
	}
	function lum( c ) {
		var v = c.map( function ( x ) {
			x /= 255;
			return x <= 0.03928 ? x / 12.92 : Math.pow( ( x + 0.055 ) / 1.055, 2.4 );
		} );
		return 0.2126 * v[ 0 ] + 0.7152 * v[ 1 ] + 0.0722 * v[ 2 ];
	}
	function toHex( c ) {
		return '#' + c.map( function ( x ) {
			return ( '0' + x.toString( 16 ) ).slice( -2 );
		} ).join( '' ).toUpperCase();
	}
	function on( hex ) {
		var l = lum( rgb( hex ) );
		return 1.05 / ( l + 0.05 ) >= ( l + 0.05 ) / 0.061 ? '#FFFFFF' : '#0B1B2B';
	}
	function ink( hex ) {
		var c = rgb( hex );
		for ( var i = 0; i < 12 && lum( c ) > 0.18; i++ ) {
			c = c.map( function ( x ) {
				return Math.round( x * 0.85 );
			} );
		}
		return toHex( c );
	}

	/* ---------------- Binding ---------------- */

	function resolve( values ) {
		var out = {};
		Object.keys( schema ).forEach( function ( key ) {
			var field = schema[ key ];
			var value = values && typeof values[ key ] === 'string' ? values[ key ] : field[ 'default' ];
			if ( field.max ) {
				value = value.slice( 0, field.max );
			}
			if ( field.type === 'color' && ! HEX.test( value ) ) {
				value = field[ 'default' ];
			}
			if ( field.required && value.trim() === '' ) {
				value = field[ 'default' ];
			}
			out[ key ] = value;
		} );
		return out;
	}

	function each( selector, fn ) {
		Array.prototype.forEach.call( document.querySelectorAll( selector ), fn );
	}

	function setLines( el, text ) {
		el.textContent = '';
		text.split( /\r\n|\r|\n/ ).forEach( function ( line, i ) {
			if ( i ) {
				el.appendChild( document.createElement( 'br' ) );
			}
			el.appendChild( document.createTextNode( line ) );
		} );
	}

	function setHours( el, text ) {
		el.textContent = '';
		text.split( /\r\n|\r|\n/ ).forEach( function ( line ) {
			line = line.trim();
			if ( ! line ) {
				return;
			}
			var li = document.createElement( 'li' );
			var idx = line.indexOf( ': ' );
			var parts = idx > -1 ? [ line.slice( 0, idx ), line.slice( idx + 2 ) ] : [ line ];
			parts.forEach( function ( part ) {
				var span = document.createElement( 'span' );
				span.textContent = part;
				li.appendChild( span );
			} );
			el.appendChild( li );
		} );
	}

	function tel( phone ) {
		return 'tel:' + phone.replace( /[^0-9+]/g, '' );
	}

	function apply( state ) {
		var v = resolve( state && state.values );
		var logo = state && typeof state.logo === 'string' && LOGO.test( state.logo ) ? state.logo : '';

		each( '[data-bind]', function ( el ) {
			var key = el.getAttribute( 'data-bind' );
			if ( key in v ) {
				el.textContent = v[ key ];
			}
		} );
		each( '[data-bind-ml]', function ( el ) {
			setLines( el, v[ el.getAttribute( 'data-bind-ml' ) ] || '' );
		} );
		each( '[data-bind-firstline]', function ( el ) {
			el.textContent = ( v[ el.getAttribute( 'data-bind-firstline' ) ] || '' ).split( /\r\n|\r|\n/ )[ 0 ];
		} );
		each( '[data-bind-hours]', function ( el ) {
			setHours( el, v[ el.getAttribute( 'data-bind-hours' ) ] || '' );
		} );
		each( '[data-bind-tel]', function ( el ) {
			el.setAttribute( 'href', tel( v[ el.getAttribute( 'data-bind-tel' ) ] || '' ) );
		} );
		each( '[data-bind-mailto]', function ( el ) {
			var email = v[ el.getAttribute( 'data-bind-mailto' ) ] || '';
			el.setAttribute( 'href', /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ? 'mailto:' + email : '#contact' );
		} );
		each( '[data-social]', function ( el ) {
			var url = v[ el.getAttribute( 'data-social' ) ] || '';
			var ok = /^https?:\/\/[^\s"'<>]+$/i.test( url );
			el.hidden = ! ok;
			el.querySelector( 'a' ).setAttribute( 'href', ok ? url : '#' );
		} );
		each( '[data-brand]', function ( el ) {
			var img = el.querySelector( '[data-logo-img]' );
			img.alt = v.clinic_name;
			if ( logo ) {
				img.src = logo;
				img.hidden = false;
			} else {
				img.removeAttribute( 'src' );
				img.hidden = true;
			}
			el.classList.toggle( 'has-logo', !! logo );
		} );
		document.title = v.clinic_name;

		var p = v.color_primary;
		var s = v.color_secondary;
		var a = v.color_accent;
		var vars = {
			'--c-primary': p,
			'--c-secondary': s,
			'--c-accent': a,
			'--c-on-primary': on( p ),
			'--c-on-secondary': on( s ),
			'--c-on-accent': on( a ),
			'--c-primary-ink': ink( p ),
			'--c-secondary-ink': ink( s )
		};
		var css = Object.keys( vars ).map( function ( k ) {
			return k + ':' + vars[ k ];
		} ).join( ';' );
		var style = document.getElementById( 'dt-brand-vars' );
		if ( style ) {
			style.textContent = ':root{' + css + '}';
		}
	}

	function readStored() {
		try {
			var raw = window.sessionStorage.getItem( config.storageKey );
			var values = raw ? JSON.parse( raw ) : null;
			var logo = window.sessionStorage.getItem( config.storageKey + ':logo' ) || '';
			return values || logo ? { values: values || {}, logo: logo } : null;
		} catch ( e ) {
			return null;
		}
	}

	/* ---------------- Interactions ---------------- */

	var header = document.querySelector( '[data-header]' );
	var toggle = document.querySelector( '[data-menu-toggle]' );

	function setMenu( open ) {
		if ( ! header || ! toggle ) {
			return;
		}
		header.classList.toggle( 'menu-open', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	}

	if ( toggle ) {
		toggle.addEventListener( 'click', function () {
			setMenu( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
				setMenu( false );
				toggle.focus();
			}
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( header && ! header.contains( e.target ) ) {
				setMenu( false );
			}
		} );
		each( '[data-nav] a', function ( a ) {
			a.addEventListener( 'click', function () {
				setMenu( false );
			} );
		} );
		window.addEventListener( 'resize', function () {
			if ( window.innerWidth >= 960 ) {
				setMenu( false );
			}
		} );
	}

	function onScroll() {
		if ( header ) {
			header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
		}
	}
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	onScroll();

	var toast = document.querySelector( '[data-toast]' );
	var toastTimer;
	each( '[data-demo-action]', function ( el ) {
		el.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( ! toast ) {
				return;
			}
			toast.textContent = ( config.i18n && config.i18n.previewNotice ) || '';
			toast.hidden = false;
			clearTimeout( toastTimer );
			toastTimer = setTimeout( function () {
				toast.hidden = true;
			}, 4200 );
		} );
	} );

	var reveal = document.querySelectorAll( '[data-reveal]' );
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					io.unobserve( entry.target );
				}
			} );
		}, { rootMargin: '0px 0px -6% 0px', threshold: 0.08 } );
		Array.prototype.forEach.call( reveal, function ( el ) {
			io.observe( el );
		} );
	} else {
		Array.prototype.forEach.call( reveal, function ( el ) {
			el.classList.add( 'is-visible' );
		} );
	}

	/* ---------------- Live updates ---------------- */

	var stored = readStored();
	if ( stored ) {
		apply( stored );
	}

	window.addEventListener( 'message', function ( e ) {
		if ( e.origin !== window.location.origin || ! e.data || typeof e.data !== 'object' ) {
			return;
		}
		if ( e.data.type === 'dmt:state' && e.data.template === config.template ) {
			apply( e.data );
		} else if ( e.data.type === 'dmt:reveal-all' ) {
			Array.prototype.forEach.call( reveal, function ( el ) {
				el.classList.add( 'is-visible' );
			} );
		}
	} );

	if ( window.parent && window.parent !== window ) {
		window.parent.postMessage( { type: 'dmt:ready', template: config.template }, window.location.origin );
	}
}() );
