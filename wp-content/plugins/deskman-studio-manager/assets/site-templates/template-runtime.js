/**
 * Site template runtime: live personalisation binder + page behaviour.
 *
 * Shared by every template. Personalisation arrives from the customiser
 * (same-origin postMessage) or, when a preview is opened in its own tab, from
 * this browser's sessionStorage. Values are only ever written as text nodes
 * or validated attributes, never as HTML.
 *
 * Bindings (see a template's preview.php):
 *   data-bind, data-bind-rich ({accent}), data-bind-ml, data-bind-lines,
 *   data-bind-hours, data-bind-firstline, data-bind-tel, data-bind-mailto,
 *   data-bind-wa, data-bind-show, data-social, data-brand (logo);
 *   select fields become data-<key> attributes on <html>.
 */
( function () {
	'use strict';

	var config = window.DMT_PREVIEW || {};
	var schema = config.schema || {};
	var root = document.documentElement;
	var HEX = /^#[0-9a-f]{6}$/i;
	var LOGO = /^data:image\/(png|jpeg|webp);base64,[a-z0-9+/=]+$/i;

	function each( selector, fn ) {
		Array.prototype.forEach.call( document.querySelectorAll( selector ), fn );
	}
	function lines( text ) {
		return String( text || '' ).split( /\r\n|\r|\n/ );
	}

	/* ---------------- colour helpers (mirror deskman_tpl_*() in PHP) ---------------- */

	function rgb( hex ) {
		return [ 1, 3, 5 ].map( function ( i ) { return parseInt( hex.substr( i, 2 ), 16 ); } );
	}
	function lum( c ) {
		var v = c.map( function ( x ) { x /= 255; return x <= 0.03928 ? x / 12.92 : Math.pow( ( x + 0.055 ) / 1.055, 2.4 ); } );
		return 0.2126 * v[ 0 ] + 0.7152 * v[ 1 ] + 0.0722 * v[ 2 ];
	}
	function toHex( c ) {
		return '#' + c.map( function ( x ) { return ( '0' + x.toString( 16 ) ).slice( -2 ); } ).join( '' ).toUpperCase();
	}
	function on( hex ) {
		var l = lum( rgb( hex ) );
		return 1.05 / ( l + 0.05 ) >= ( l + 0.05 ) / 0.061 ? '#FFFFFF' : '#0B1B2B';
	}
	function ink( hex ) {
		var c = rgb( hex );
		for ( var i = 0; i < 12 && lum( c ) > 0.18; i++ ) {
			c = c.map( function ( x ) { return Math.round( x * 0.85 ); } );
		}
		return toHex( c );
	}

	/* ---------------- values ---------------- */

	function resolve( values ) {
		var out = {};
		Object.keys( schema ).forEach( function ( key ) {
			var f = schema[ key ];
			var v = values && typeof values[ key ] === 'string' ? values[ key ] : String( f[ 'default' ] );
			if ( f.max ) { v = v.slice( 0, f.max ); }
			if ( f.type === 'color' && ! HEX.test( v ) ) { v = f[ 'default' ]; }
			if ( f.type === 'select' && ( f.options || [] ).indexOf( v ) === -1 ) { v = f[ 'default' ]; }
			if ( f.required && v.trim() === '' ) { v = f[ 'default' ]; }
			out[ key ] = v;
		} );
		return out;
	}

	/* ---------------- binding ---------------- */

	function setLines( el, text ) {
		el.textContent = '';
		lines( text ).forEach( function ( line, i ) {
			if ( i ) { el.appendChild( document.createElement( 'br' ) ); }
			el.appendChild( document.createTextNode( line ) );
		} );
	}
	function setList( el, text ) {
		el.textContent = '';
		lines( text ).forEach( function ( line ) {
			if ( ! line.trim() ) { return; }
			var li = document.createElement( 'li' );
			li.textContent = line.trim();
			el.appendChild( li );
		} );
	}
	function setHours( el, text ) {
		el.textContent = '';
		lines( text ).forEach( function ( line ) {
			line = line.trim();
			if ( ! line ) { return; }
			var li = document.createElement( 'li' );
			var idx = line.indexOf( ': ' );
			( idx > -1 ? [ line.slice( 0, idx ), line.slice( idx + 2 ) ] : [ line ] ).forEach( function ( part ) {
				var span = document.createElement( 'span' );
				span.textContent = part;
				li.appendChild( span );
			} );
			el.appendChild( li );
		} );
	}
	function setRich( el, text ) {
		el.textContent = '';
		String( text ).split( /(\{[^{}]*\})/ ).forEach( function ( part ) {
			if ( ! part ) { return; }
			if ( part.charAt( 0 ) === '{' && part.charAt( part.length - 1 ) === '}' ) {
				var em = document.createElement( 'em' );
				em.className = 'dt-accent';
				em.textContent = part.slice( 1, -1 );
				el.appendChild( em );
			} else {
				el.appendChild( document.createTextNode( part ) );
			}
		} );
	}
	function digits( phone, keepPlus ) {
		return String( phone || '' ).replace( keepPlus ? /[^0-9+]/g : /\D/g, '' );
	}

	function apply( state ) {
		var v = resolve( state && state.values );
		var logo = state && typeof state.logo === 'string' && LOGO.test( state.logo ) ? state.logo : '';
		var attr = function ( sel, fn ) { each( '[' + sel + ']', function ( el ) { fn( el, v[ el.getAttribute( sel ) ] || '' ); } ); };

		attr( 'data-bind', function ( el, val ) { el.textContent = val; } );
		attr( 'data-bind-rich', setRich );
		attr( 'data-bind-ml', setLines );
		attr( 'data-bind-lines', setList );
		attr( 'data-bind-hours', setHours );
		attr( 'data-bind-firstline', function ( el, val ) { el.textContent = lines( val )[ 0 ]; } );
		attr( 'data-bind-tel', function ( el, val ) { el.setAttribute( 'href', 'tel:' + digits( val, true ) ); } );
		attr( 'data-bind-wa', function ( el, val ) { el.setAttribute( 'href', digits( val ) ? 'https://wa.me/' + digits( val ) : '#' ); } );
		attr( 'data-bind-mailto', function ( el, val ) {
			el.setAttribute( 'href', /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( val ) ? 'mailto:' + val : '#contact' );
		} );
		attr( 'data-bind-show', function ( el, val ) { el.hidden = val.trim() === ''; } );
		attr( 'data-social', function ( el, val ) {
			var ok = /^https?:\/\/[^\s"'<>]+$/i.test( val );
			el.hidden = ! ok;
			el.querySelector( 'a' ).setAttribute( 'href', ok ? val : '#' );
		} );
		each( '[data-brand]', function ( el ) {
			var img = el.querySelector( '[data-logo-img]' );
			img.alt = v.clinic_name || v.business_name || '';
			if ( logo ) { img.src = logo; img.hidden = false; } else { img.removeAttribute( 'src' ); img.hidden = true; }
			el.classList.toggle( 'has-logo', !! logo );
		} );

		Object.keys( schema ).forEach( function ( key ) {
			if ( schema[ key ].type === 'select' ) { root.setAttribute( 'data-' + key.replace( /_/g, '-' ), v[ key ] ); }
		} );
		document.title = v.clinic_name || v.business_name || document.title;

		if ( v.color_primary ) {
			var p = v.color_primary, s = v.color_secondary || p, a = v.color_accent || s;
			var vars = { '--c-primary': p, '--c-secondary': s, '--c-accent': a, '--c-on-primary': on( p ), '--c-on-secondary': on( s ), '--c-on-accent': on( a ), '--c-primary-ink': ink( p ), '--c-secondary-ink': ink( s ) };
			var style = document.getElementById( 'dt-brand-vars' );
			if ( style ) {
				style.textContent = ':root{' + Object.keys( vars ).map( function ( k ) { return k + ':' + vars[ k ]; } ).join( ';' ) + '}';
			}
		}
	}

	function readStored() {
		try {
			var raw = window.sessionStorage.getItem( config.storageKey );
			var logo = window.sessionStorage.getItem( config.storageKey + ':logo' ) || '';
			return raw || logo ? { values: raw ? JSON.parse( raw ) : {}, logo: logo } : null;
		} catch ( e ) {
			return null;
		}
	}

	/* ---------------- page behaviour ---------------- */

	var header = document.querySelector( '[data-header]' );
	var toggle = document.querySelector( '[data-menu-toggle]' );
	function setMenu( open ) {
		if ( ! header || ! toggle ) { return; }
		header.classList.toggle( 'menu-open', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	}
	if ( toggle ) {
		toggle.addEventListener( 'click', function () { setMenu( toggle.getAttribute( 'aria-expanded' ) !== 'true' ); } );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && toggle.getAttribute( 'aria-expanded' ) === 'true' ) { setMenu( false ); toggle.focus(); }
		} );
		document.addEventListener( 'click', function ( e ) { if ( header && ! header.contains( e.target ) ) { setMenu( false ); } } );
		each( '[data-nav] a', function ( a ) { a.addEventListener( 'click', function () { setMenu( false ); } ); } );
		window.addEventListener( 'resize', function () { if ( window.innerWidth >= 1024 ) { setMenu( false ); } } );
	}

	function onScroll() { if ( header ) { header.classList.toggle( 'is-scrolled', window.scrollY > 8 ); } }
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	onScroll();

	var toast = document.querySelector( '[data-toast]' );
	var toastTimer;
	function notice() {
		if ( ! toast ) { return; }
		toast.textContent = ( config.i18n && config.i18n.previewNotice ) || '';
		toast.hidden = false;
		clearTimeout( toastTimer );
		toastTimer = setTimeout( function () { toast.hidden = true; }, 4200 );
	}
	each( '[data-demo-action]', function ( el ) { el.addEventListener( 'click', function ( e ) { e.preventDefault(); notice(); } ); } );
	each( '[data-demo-form]', function ( form ) { form.addEventListener( 'submit', function ( e ) { e.preventDefault(); notice(); } ); } );

	var reveal = document.querySelectorAll( '[data-reveal]' );
	function showAll() { Array.prototype.forEach.call( reveal, function ( el ) { el.classList.add( 'is-visible' ); } ); }
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) { entry.target.classList.add( 'is-visible' ); io.unobserve( entry.target ); }
			} );
		}, { rootMargin: '0px 0px -6% 0px', threshold: 0.06 } );
		Array.prototype.forEach.call( reveal, function ( el ) { io.observe( el ); } );
	} else {
		showAll();
	}

	/* ---------------- live updates ---------------- */

	var stored = readStored();
	if ( stored ) { apply( stored ); }

	window.addEventListener( 'message', function ( e ) {
		if ( e.origin !== window.location.origin || ! e.data || typeof e.data !== 'object' ) { return; }
		if ( e.data.type === 'dmt:state' && e.data.template === config.template ) { apply( e.data ); }
		else if ( e.data.type === 'dmt:reveal-all' ) { showAll(); }
	} );

	if ( window.parent && window.parent !== window ) {
		window.parent.postMessage( { type: 'dmt:ready', template: config.template }, window.location.origin );
	}
}() );
