/**
 * Template customiser.
 *
 * - Keeps the visitor's personalisation in memory + sessionStorage (this
 *   browser tab only); nothing is sent to the server until they submit the
 *   "Start My Project" form.
 * - Pushes every change to the preview iframe (same-origin postMessage).
 * - Sizes the iframe to real device widths (390/768/1366/1440) and scales the
 *   whole frame to fit, so the preview is a genuine responsive layout.
 * - Validates and submits the project enquiry to the WordPress REST API.
 */
( function () {
	'use strict';

	var C = window.DMT_CUSTOMISER;
	var root = document.querySelector( '[data-dmt-customiser]' );
	if ( ! C || ! root ) {
		return;
	}

	var i18n = C.i18n || {};
	var schema = C.schema || {};
	var ORIGIN = window.location.origin;
	var HEX6 = /^#?([0-9a-f]{6})$/i;
	var HEX3 = /^#?([0-9a-f])([0-9a-f])([0-9a-f])$/i;
	var EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
	var PHONE = /^[0-9+()\-.\s]{6,25}$/;
	var LOGO_SIGNATURES = {
		'image/png': function ( b ) { return b[ 0 ] === 0x89 && b[ 1 ] === 0x50 && b[ 2 ] === 0x4E && b[ 3 ] === 0x47; },
		'image/jpeg': function ( b ) { return b[ 0 ] === 0xFF && b[ 1 ] === 0xD8 && b[ 2 ] === 0xFF; },
		'image/webp': function ( b ) { return b[ 0 ] === 0x52 && b[ 1 ] === 0x49 && b[ 2 ] === 0x46 && b[ 3 ] === 0x46 && b[ 8 ] === 0x57 && b[ 9 ] === 0x45 && b[ 10 ] === 0x42 && b[ 11 ] === 0x50; }
	};

	function $( sel, ctx ) { return ( ctx || root ).querySelector( sel ); }
	function $$( sel, ctx ) { return Array.prototype.slice.call( ( ctx || root ).querySelectorAll( sel ) ); }
	function fmt( str ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		var i = 0;
		return String( str ).replace( /%(\d+\$)?[sd]/g, function ( m, pos ) {
			var idx = pos ? parseInt( pos, 10 ) - 1 : i++;
			return args[ idx ] !== undefined ? args[ idx ] : '';
		} ).replace( /%%/g, '%' );
	}

	var live = $( '[data-dmt-live]' );
	function announce( msg ) {
		if ( ! live ) { return; }
		live.textContent = '';
		window.setTimeout( function () { live.textContent = msg; }, 40 );
	}

	/* ------------------------------------------------------------------ *
	 * State
	 * ------------------------------------------------------------------ */

	function defaults() {
		var out = {};
		Object.keys( schema ).forEach( function ( k ) {
			if ( schema[ k ].type !== 'logo' ) {
				out[ k ] = String( schema[ k ][ 'default' ] );
			}
		} );
		return out;
	}

	var storage = ( function () {
		try {
			var s = window.sessionStorage;
			s.setItem( '__dmt', '1' );
			s.removeItem( '__dmt' );
			return s;
		} catch ( e ) {
			return null;
		}
	}() );

	var state = { values: defaults(), logo: '' };

	( function load() {
		if ( ! storage ) { return; }
		try {
			var raw = JSON.parse( storage.getItem( C.storageKey ) || 'null' );
			if ( raw && typeof raw === 'object' ) {
				Object.keys( state.values ).forEach( function ( k ) {
					if ( typeof raw[ k ] === 'string' ) {
						state.values[ k ] = raw[ k ].slice( 0, schema[ k ].max || 500 );
					}
				} );
			}
			var logo = storage.getItem( C.storageKey + ':logo' );
			if ( logo && /^data:image\/(png|jpeg|webp);base64,/.test( logo ) ) {
				state.logo = logo;
			}
		} catch ( e ) { /* ignore corrupt storage */ }
	}() );

	var saveTimer;
	function save() {
		if ( ! storage ) { return; }
		window.clearTimeout( saveTimer );
		saveTimer = window.setTimeout( function () {
			try {
				storage.setItem( C.storageKey, JSON.stringify( state.values ) );
			} catch ( e ) { /* storage full or disabled */ }
		}, 200 );
	}
	function saveLogo() {
		if ( ! storage ) { return; }
		try {
			if ( state.logo ) {
				storage.setItem( C.storageKey + ':logo', state.logo );
			} else {
				storage.removeItem( C.storageKey + ':logo' );
			}
		} catch ( e ) { /* too large to remember across reloads; still works now */ }
	}

	function changedCount() {
		var d = defaults();
		return Object.keys( d ).filter( function ( k ) { return state.values[ k ] !== d[ k ]; } ).length + ( state.logo ? 1 : 0 );
	}

	/* ------------------------------------------------------------------ *
	 * Preview iframe
	 * ------------------------------------------------------------------ */

	var iframe = $( '[data-dmt-iframe]' );
	var urlLabel = $( '[data-dmt-url]' );

	function previewValues() {
		var out = {};
		Object.keys( state.values ).forEach( function ( k ) {
			var v = state.values[ k ];
			if ( schema[ k ].type === 'color' ) {
				v = normaliseHex( v ) || schema[ k ][ 'default' ];
			}
			out[ k ] = v;
		} );
		return out;
	}

	function push() {
		if ( iframe && iframe.contentWindow ) {
			iframe.contentWindow.postMessage( { type: 'dmt:state', template: C.template, values: previewValues(), logo: state.logo }, ORIGIN );
		}
		if ( urlLabel ) {
			var slug = ( state.values.clinic_name || '' ).toLowerCase().normalize( 'NFKD' ).replace( /[^a-z0-9]+/g, '' ).slice( 0, 40 );
			urlLabel.textContent = 'www.' + ( slug || 'yourclinic' ) + '.com';
		}
	}

	window.addEventListener( 'message', function ( e ) {
		if ( e.origin === ORIGIN && e.data && e.data.type === 'dmt:ready' && iframe && e.source === iframe.contentWindow ) {
			push();
		}
	} );
	if ( iframe ) {
		iframe.addEventListener( 'load', push );
	}

	/* ------------------------------------------------------------------ *
	 * Fields
	 * ------------------------------------------------------------------ */

	function normaliseHex( v ) {
		v = String( v || '' ).trim();
		var m = v.match( HEX6 );
		if ( m ) { return '#' + m[ 1 ].toUpperCase(); }
		m = v.match( HEX3 );
		if ( m ) { return ( '#' + m[ 1 ] + m[ 1 ] + m[ 2 ] + m[ 2 ] + m[ 3 ] + m[ 3 ] ).toUpperCase(); }
		return '';
	}

	function fieldError( input, msg ) {
		var wrap = input.closest( '.dmt-field' );
		var el = wrap && wrap.querySelector( '[data-field-error]' );
		if ( el ) { el.textContent = msg || ''; }
		if ( msg ) {
			input.setAttribute( 'aria-invalid', 'true' );
		} else {
			input.removeAttribute( 'aria-invalid' );
		}
	}

	function validateField( key, value ) {
		var f = schema[ key ];
		if ( ! f ) { return ''; }
		if ( value.length > f.max ) { return fmt( i18n.tooLong, f.max ); }
		if ( value.trim() === '' ) { return ''; } // Empty fields fall back to the demo text.
		if ( f.type === 'email' && ! EMAIL.test( value.trim() ) ) { return i18n.email; }
		if ( f.type === 'tel' && ( ! PHONE.test( value.trim() ) || value.replace( /\D/g, '' ).length < 6 ) ) { return i18n.phone; }
		if ( f.type === 'url' && ! /^https?:\/\/\S+\.\S+$/i.test( value.trim() ) ) { return 'https://…'; }
		if ( f.type === 'color' && ! normaliseHex( value ) ) { return '#RRGGBB'; }
		return '';
	}

	function syncInputs( except ) {
		$$( '[data-field]' ).forEach( function ( input ) {
			if ( input === except ) { return; }
			var key = input.getAttribute( 'data-field' );
			var value = state.values[ key ];
			if ( value === undefined ) { return; }
			if ( input.type === 'color' ) {
				input.value = ( normaliseHex( value ) || schema[ key ][ 'default' ] ).toLowerCase();
			} else {
				input.value = value;
			}
			if ( ! except ) { fieldError( input, '' ); }
		} );
	}

	function onInput( e ) {
		var input = e.target;
		var key = input.getAttribute( 'data-field' );
		if ( ! key || ! schema[ key ] ) { return; }
		var value = input.value;
		if ( input.type === 'color' ) {
			value = normaliseHex( value );
		} else if ( input.hasAttribute( 'data-hex' ) ) {
			var hex = normaliseHex( value );
			if ( ! hex ) { return; } // Wait for a complete code before applying.
			value = hex;
		}
		state.values[ key ] = value;
		syncInputs( input );
		if ( input.getAttribute( 'aria-invalid' ) === 'true' ) {
			fieldError( input, validateField( key, input.value ) );
		}
		push();
		save();
	}

	function onBlur( e ) {
		var input = e.target;
		var key = input.getAttribute && input.getAttribute( 'data-field' );
		if ( ! key || ! schema[ key ] ) { return; }
		if ( input.hasAttribute( 'data-hex' ) ) {
			var hex = normaliseHex( input.value );
			if ( hex ) {
				input.value = hex;
				fieldError( input, '' );
			} else {
				fieldError( input, validateField( key, input.value ) );
			}
			return;
		}
		fieldError( input, validateField( key, input.value ) );
	}

	root.addEventListener( 'input', onInput );
	root.addEventListener( 'change', onInput );
	root.addEventListener( 'focusout', onBlur );

	/* ------------------------------------------------------------------ *
	 * Logo upload
	 * ------------------------------------------------------------------ */

	function renderLogo() {
		$$( '[data-dmt-logo-field]' ).forEach( function ( field ) {
			var preview = field.querySelector( '[data-dmt-logo-preview]' );
			var remove = field.querySelector( '[data-dmt-logo-remove]' );
			var label = field.querySelector( '[data-dmt-logo-pick-label]' );
			if ( state.logo ) {
				if ( ! preview.querySelector( 'img' ) ) {
					preview.dataset.empty = preview.innerHTML;
					preview.innerHTML = '';
					preview.appendChild( document.createElement( 'img' ) );
				}
				preview.querySelector( 'img' ).src = state.logo;
				preview.querySelector( 'img' ).alt = '';
				preview.classList.add( 'has-logo' );
			} else if ( preview.dataset.empty !== undefined ) {
				preview.innerHTML = preview.dataset.empty;
				delete preview.dataset.empty;
				preview.classList.remove( 'has-logo' );
			}
			remove.hidden = ! state.logo;
			if ( label ) {
				label.textContent = state.logo ? i18n.replaceLogo : label.dataset.upload;
			}
		} );
	}

	function readHead( file ) {
		return new Promise( function ( resolve, reject ) {
			var r = new FileReader();
			r.onload = function () { resolve( new Uint8Array( r.result ) ); };
			r.onerror = reject;
			r.readAsArrayBuffer( file.slice( 0, 16 ) );
		} );
	}

	function readDataUrl( file ) {
		return new Promise( function ( resolve, reject ) {
			var r = new FileReader();
			r.onload = function () { resolve( r.result ); };
			r.onerror = reject;
			r.readAsDataURL( file );
		} );
	}

	function loadImage( src ) {
		return new Promise( function ( resolve, reject ) {
			var img = new Image();
			img.onload = function () { resolve( img ); };
			img.onerror = reject;
			img.src = src;
		} );
	}

	function processLogo( file, input ) {
		var types = ( C.logo && C.logo.types ) || [];
		fieldError( input, '' );
		if ( ! file ) { return; }
		if ( types.indexOf( file.type ) === -1 ) {
			fieldError( input, i18n.logoType );
			input.value = '';
			return;
		}
		if ( file.size > C.logo.maxBytes ) {
			fieldError( input, i18n.logoSize );
			input.value = '';
			return;
		}
		readHead( file ).then( function ( head ) {
			if ( ! LOGO_SIGNATURES[ file.type ]( head ) ) {
				throw new Error( 'signature' );
			}
			return readDataUrl( file );
		} ).then( function ( dataUrl ) {
			return loadImage( dataUrl ).then( function ( img ) {
				if ( img.naturalWidth < 16 || img.naturalHeight < 16 ) {
					throw new Error( 'small' );
				}
				return dataUrl;
			} );
		} ).then( function ( dataUrl ) {
			state.logo = dataUrl;
			saveLogo();
			renderLogo();
			push();
			announce( i18n.logoAdded );
		} ).catch( function ( err ) {
			fieldError( input, err && err.message === 'small' ? i18n.logoSmall : i18n.logoInvalid );
		} ).then( function () {
			input.value = '';
		} );
	}

	$$( '[data-dmt-logo-pick-label]' ).forEach( function ( label ) {
		label.dataset.upload = label.textContent;
	} );

	root.addEventListener( 'change', function ( e ) {
		if ( e.target.matches && e.target.matches( '[data-dmt-logo-input]' ) ) {
			processLogo( e.target.files && e.target.files[ 0 ], e.target );
		}
	} );
	root.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest && e.target.closest( '[data-dmt-logo-remove]' );
		if ( ! btn ) { return; }
		state.logo = '';
		saveLogo();
		renderLogo();
		push();
		announce( i18n.logoRemoved );
		var input = btn.closest( '[data-dmt-logo-field]' ).querySelector( '[data-dmt-logo-input]' );
		if ( input ) { input.focus(); }
	} );

	/* ------------------------------------------------------------------ *
	 * Reset
	 * ------------------------------------------------------------------ */

	$$( '[data-dmt-reset]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			if ( ! window.confirm( i18n.resetConfirm ) ) { return; }
			state = { values: defaults(), logo: '' };
			if ( storage ) {
				try {
					storage.removeItem( C.storageKey );
					storage.removeItem( C.storageKey + ':logo' );
				} catch ( e ) { /* ignore */ }
			}
			syncInputs();
			$$( '[data-field-error]' ).forEach( function ( el ) { el.textContent = ''; } );
			renderLogo();
			push();
			announce( i18n.resetDone );
		} );
	} );

	/* ------------------------------------------------------------------ *
	 * Device previews
	 * ------------------------------------------------------------------ */

	var frame = $( '[data-dmt-frame]' );
	var device = $( '[data-dmt-device]' );
	var sizeLabel = $( '[data-dmt-size]' );
	var deviceBtns = $$( '[data-device]', $( '.dmt-devices' ) );
	var current = deviceBtns.filter( function ( b ) { return b.getAttribute( 'aria-checked' ) === 'true'; } )[ 0 ] || deviceBtns[ 0 ];

	function layout() {
		if ( ! frame || ! device || ! iframe || ! current || ! frame.clientWidth ) { return; }
		var w = parseInt( current.getAttribute( 'data-width' ), 10 );
		var h = parseInt( current.getAttribute( 'data-height' ), 10 );
		var id = current.getAttribute( 'data-device' );
		var availW = frame.clientWidth;
		var availH = frame.clientHeight;

		iframe.style.width = w + 'px';
		iframe.setAttribute( 'width', w );

		// Chrome + bezel sizes, measured unscaled.
		iframe.style.height = '100px';
		var extraW = device.offsetWidth - w;
		var extraH = device.offsetHeight - 100;
		var outerW = w + extraW;
		var scale;

		if ( id === 'mobile' || id === 'tablet' ) {
			var outerH = h + extraH;
			scale = Math.min( 1, availW / outerW, availH / outerH );
			iframe.style.height = h + 'px';
		} else {
			scale = Math.min( 1, availW / outerW );
			var ih = Math.max( 360, Math.floor( availH / scale - extraH ) );
			iframe.style.height = ih + 'px';
		}
		iframe.setAttribute( 'height', parseInt( iframe.style.height, 10 ) );
		scale = Math.max( 0.15, Math.floor( scale * 1000 ) / 1000 );
		device.style.setProperty( '--dmt-scale', scale );

		if ( sizeLabel ) {
			sizeLabel.textContent = scale >= 0.999 ? fmt( i18n.actualSize, w ) : fmt( i18n.scaleLabel, w, Math.round( scale * 100 ) );
		}
	}

	function setDevice( btn, focus ) {
		if ( ! btn ) { return; }
		current = btn;
		deviceBtns.forEach( function ( b ) {
			var on = b === btn;
			b.setAttribute( 'aria-checked', on ? 'true' : 'false' );
			b.tabIndex = on ? 0 : -1;
		} );
		if ( focus ) { btn.focus(); }
		frame.setAttribute( 'data-device', btn.getAttribute( 'data-device' ) );
		layout();
		if ( storage ) {
			try { storage.setItem( 'dmt:device', btn.getAttribute( 'data-device' ) ); } catch ( e ) { /* ignore */ }
		}
	}

	deviceBtns.forEach( function ( btn, i ) {
		btn.addEventListener( 'click', function () {
			setDevice( btn );
			announce( fmt( i18n.deviceChanged, btn.getAttribute( 'data-label' ), btn.getAttribute( 'data-width' ) ) );
			if ( root.getAttribute( 'data-tab' ) === 'edit' && window.matchMedia( '(max-width: 1023px)' ).matches ) {
				setTab( 'preview' );
			}
		} );
		btn.addEventListener( 'keydown', function ( e ) {
			var dir = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[ e.key ];
			if ( e.key === 'Home' ) { dir = -i; }
			if ( e.key === 'End' ) { dir = deviceBtns.length - 1 - i; }
			if ( dir === undefined ) { return; }
			e.preventDefault();
			setDevice( deviceBtns[ ( i + dir + deviceBtns.length ) % deviceBtns.length ], true );
		} );
	} );

	( function initialDevice() {
		var saved = null;
		try { saved = storage && storage.getItem( 'dmt:device' ); } catch ( e ) { /* ignore */ }
		var vw = window.innerWidth;
		var pick = saved || ( vw < 640 ? 'mobile' : ( vw < 1024 ? 'tablet' : ( vw >= 1700 ? 'desktop' : 'laptop' ) ) );
		var btn = deviceBtns.filter( function ( b ) { return b.getAttribute( 'data-device' ) === pick; } )[ 0 ];
		setDevice( btn || current );
	}() );

	if ( 'ResizeObserver' in window && frame ) {
		new ResizeObserver( function () { layout(); } ).observe( frame );
	} else {
		window.addEventListener( 'resize', layout );
	}

	/* ------------------------------------------------------------------ *
	 * Mobile tabs (Customise / Preview)
	 * ------------------------------------------------------------------ */

	var tabs = $$( '[data-dmt-tab]' );
	function setTab( name, focus ) {
		root.setAttribute( 'data-tab', name );
		tabs.forEach( function ( t ) {
			var on = t.getAttribute( 'data-dmt-tab' ) === name;
			t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			t.tabIndex = on ? 0 : -1;
			if ( on && focus ) { t.focus(); }
		} );
		if ( name === 'preview' ) {
			window.requestAnimationFrame( layout );
		}
	}
	tabs.forEach( function ( t, i ) {
		t.addEventListener( 'click', function () { setTab( t.getAttribute( 'data-dmt-tab' ) ); } );
		t.addEventListener( 'keydown', function ( e ) {
			if ( e.key !== 'ArrowLeft' && e.key !== 'ArrowRight' ) { return; }
			e.preventDefault();
			setTab( tabs[ ( i + 1 ) % tabs.length ].getAttribute( 'data-dmt-tab' ), true );
		} );
	} );

	/* ------------------------------------------------------------------ *
	 * Dialog helpers
	 * ------------------------------------------------------------------ */

	var opener = null;
	function openDialog( dialog, trigger ) {
		if ( ! dialog || dialog.open ) { return; }
		opener = trigger || document.activeElement;
		if ( typeof dialog.showModal === 'function' ) {
			dialog.showModal();
		} else {
			dialog.setAttribute( 'open', '' );
		}
		document.body.classList.add( 'dmt-dialog-open' );
	}
	function closeDialog( dialog ) {
		if ( ! dialog ) { return; }
		if ( typeof dialog.close === 'function' && dialog.open ) {
			dialog.close();
		} else {
			dialog.removeAttribute( 'open' );
		}
	}
	$$( 'dialog' ).forEach( function ( d ) {
		d.addEventListener( 'close', function () {
			document.body.classList.remove( 'dmt-dialog-open' );
			if ( opener && document.contains( opener ) ) { opener.focus(); }
		} );
		// Click on the backdrop closes the dialog.
		d.addEventListener( 'click', function ( e ) {
			if ( e.target === d ) { closeDialog( d ); }
		} );
	} );

	/* ------------------------------------------------------------------ *
	 * Quick start (first visit)
	 * ------------------------------------------------------------------ */

	var quick = $( '[data-dmt-quick]' );
	var quickSeenKey = 'dmt:quick:' + C.template;
	var seen = false;
	try { seen = !! ( storage && storage.getItem( quickSeenKey ) ); } catch ( e ) { /* ignore */ }

	if ( quick ) {
		quick.addEventListener( 'close', function () {
			try { storage && storage.setItem( quickSeenKey, '1' ); } catch ( e ) { /* ignore */ }
			if ( quick.returnValue === 'apply' && window.matchMedia( '(max-width: 1023px)' ).matches ) {
				setTab( 'preview' );
			}
		} );
		if ( ! seen && changedCount() === 0 ) {
			window.setTimeout( function () { openDialog( quick, null ); }, 350 );
		}
	}

	/* ------------------------------------------------------------------ *
	 * Start My Project enquiry
	 * ------------------------------------------------------------------ */

	var enquiry = $( '[data-dmt-enquiry]' );
	var eForm = $( '[data-dmt-enquiry-form]' );
	var formWrap = $( '[data-dmt-enquiry-form-wrap]' );
	var success = $( '[data-dmt-success]' );
	var errorsBox = $( '[data-dmt-errors]' );
	var statusEl = $( '[data-dmt-status]' );
	var submitBtn = $( '[data-dmt-submit]' );
	var submitLabel = $( '[data-dmt-submit-label]' );
	var submitting = false;

	function prefill() {
		var d = defaults();
		var map = { name: 'dentist_name', business: 'clinic_name', email: 'email', phone: 'phone' };
		Object.keys( map ).forEach( function ( name ) {
			var input = eForm.elements[ name ];
			var key = map[ name ];
			if ( input && ! input.value && state.values[ key ] && state.values[ key ] !== d[ key ] ) {
				input.value = state.values[ key ];
			}
		} );

		var logoBox = $( '[data-dmt-summary-logo]' );
		logoBox.textContent = '';
		logoBox.classList.toggle( 'has-logo', !! state.logo );
		if ( state.logo ) {
			var img = document.createElement( 'img' );
			img.src = state.logo;
			img.alt = '';
			logoBox.appendChild( img );
		} else {
			logoBox.textContent = ( state.values.clinic_name || '?' ).trim().charAt( 0 ).toUpperCase();
			logoBox.style.background = normaliseHex( state.values.color_primary ) || '';
		}

		var n = changedCount();
		$( '[data-dmt-summary-meta]' ).textContent = ( n ? fmt( i18n.customised, n ) : i18n.noneCustomised ) + ' · ' + ( state.logo ? i18n.logoIncluded : i18n.noLogo );

		var sw = $( '[data-dmt-summary-swatches]' );
		sw.textContent = '';
		[ 'color_primary', 'color_secondary', 'color_accent' ].forEach( function ( k ) {
			var i = document.createElement( 'i' );
			i.style.background = normaliseHex( state.values[ k ] ) || schema[ k ][ 'default' ];
			sw.appendChild( i );
		} );
	}

	$$( '[data-dmt-start]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			if ( ! enquiry ) { return; }
			if ( success.hidden ) {
				prefill();
			}
			openDialog( enquiry, btn );
			window.setTimeout( function () {
				var first = success.hidden ? eForm.querySelector( 'input:not([type=hidden]):not([readonly])' ) : success;
				if ( first ) { first.focus(); }
			}, 50 );
		} );
	} );
	$$( '[data-dmt-close]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () { closeDialog( enquiry ); } );
	} );

	function setErr( name, msg ) {
		var input = eForm.elements[ name ];
		var el = eForm.querySelector( '[data-error-for="' + name + '"]' );
		if ( el ) { el.textContent = msg || ''; }
		if ( input && input.setAttribute ) {
			if ( msg ) { input.setAttribute( 'aria-invalid', 'true' ); } else { input.removeAttribute( 'aria-invalid' ); }
		}
	}

	function showSummary( errors ) {
		var names = Object.keys( errors );
		errorsBox.textContent = '';
		if ( ! names.length ) {
			errorsBox.hidden = true;
			return;
		}
		var p = document.createElement( 'p' );
		p.textContent = i18n.errorSummary;
		var ul = document.createElement( 'ul' );
		names.forEach( function ( name ) {
			var li = document.createElement( 'li' );
			var input = eForm.elements[ name ];
			if ( input && input.id ) {
				var a = document.createElement( 'a' );
				a.href = '#' + input.id;
				a.textContent = errors[ name ];
				a.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					input.focus();
				} );
				li.appendChild( a );
			} else {
				li.textContent = errors[ name ];
			}
			ul.appendChild( li );
		} );
		errorsBox.appendChild( p );
		errorsBox.appendChild( ul );
		errorsBox.hidden = false;
		errorsBox.focus();
	}

	function validateEnquiry() {
		var f = eForm.elements;
		var errors = {};
		var name = f.name.value.trim();
		var business = f.business.value.trim();
		var email = f.email.value.trim();
		var phone = f.phone.value.trim();
		var digits = phone.replace( /\D/g, '' );

		if ( name.length < 2 ) { errors.name = name ? i18n.name : i18n.required; }
		if ( business.length < 2 ) { errors.business = i18n.required; }
		if ( ! email ) { errors.email = i18n.required; } else if ( ! EMAIL.test( email ) ) { errors.email = i18n.email; }
		if ( ! phone ) { errors.phone = i18n.required; } else if ( ! PHONE.test( phone ) || digits.length < 6 || digits.length > 18 ) { errors.phone = i18n.phone; }
		if ( f.requirements.value.length > 3000 ) { errors.requirements = fmt( i18n.tooLong, 3000 ); }
		if ( ! f.consent.checked ) { errors.consent = i18n.consent; }

		[ 'name', 'business', 'email', 'phone', 'requirements', 'consent' ].forEach( function ( n ) { setErr( n, errors[ n ] ); } );
		return errors;
	}

	function dataUrlToBlob( dataUrl ) {
		var parts = dataUrl.split( ',' );
		var mime = parts[ 0 ].match( /data:([^;]+)/ )[ 1 ];
		var bin = window.atob( parts[ 1 ] );
		var arr = new Uint8Array( bin.length );
		for ( var i = 0; i < bin.length; i++ ) { arr[ i ] = bin.charCodeAt( i ); }
		return new Blob( [ arr ], { type: mime } );
	}

	function setBusy( busy ) {
		submitting = busy;
		submitBtn.disabled = busy;
		submitBtn.setAttribute( 'aria-busy', busy ? 'true' : 'false' );
		if ( busy ) {
			submitLabel.dataset.label = submitLabel.textContent;
			submitLabel.textContent = i18n.sending;
		} else if ( submitLabel.dataset.label ) {
			submitLabel.textContent = submitLabel.dataset.label;
		}
	}

	if ( eForm ) {
		eForm.addEventListener( 'focusout', function ( e ) {
			var t = e.target;
			if ( t.getAttribute && t.getAttribute( 'aria-invalid' ) === 'true' ) {
				var errs = validateEnquiry();
				setErr( t.name, errs[ t.name ] );
			}
		} );

		eForm.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			if ( submitting ) { return; }
			statusEl.textContent = '';
			var errors = validateEnquiry();
			if ( Object.keys( errors ).length ) {
				showSummary( errors );
				return;
			}
			showSummary( {} );

			var fd = new FormData();
			[ 'name', 'business', 'email', 'phone', 'requirements', 'website', 'token' ].forEach( function ( n ) {
				fd.append( n, eForm.elements[ n ].value );
			} );
			fd.append( 'consent', eForm.elements.consent.checked ? '1' : '0' );
			fd.append( 'template', C.template );
			fd.append( 'customisation', JSON.stringify( previewValues() ) );
			if ( state.logo ) {
				var blob = dataUrlToBlob( state.logo );
				fd.append( 'logo', blob, 'logo.' + ( { 'image/png': 'png', 'image/jpeg': 'jpg', 'image/webp': 'webp' }[ blob.type ] || 'png' ) );
			}

			setBusy( true );
			window.fetch( C.endpoint, { method: 'POST', body: fd, credentials: 'omit', headers: { Accept: 'application/json' } } )
				.then( function ( res ) {
					return res.json().catch( function () { return {}; } ).then( function ( body ) {
						return { status: res.status, ok: res.ok, body: body };
					} );
				} )
				.then( function ( r ) {
					if ( r.ok && r.body && r.body.success && r.body.reference ) {
						$( '[data-dmt-success-ref]' ).textContent = r.body.reference;
						$( '[data-dmt-success-text]' ).textContent = r.body.message || '';
						formWrap.hidden = true;
						success.hidden = false;
						success.focus();
						announce( r.body.reference );
						return;
					}
					if ( r.status === 422 && r.body && r.body.data && r.body.data.fields ) {
						var fields = r.body.data.fields;
						Object.keys( fields ).forEach( function ( n ) { setErr( n, fields[ n ] ); } );
						showSummary( fields );
						return;
					}
					statusEl.textContent = r.status === 429 ? i18n.rateLimited : ( ( r.body && r.body.message ) || i18n.serverError );
				} )
				.catch( function () {
					statusEl.textContent = i18n.network;
				} )
				.then( function () {
					setBusy( false );
				} );
		} );
	}

	/* ------------------------------------------------------------------ *
	 * Init
	 * ------------------------------------------------------------------ */

	syncInputs();
	if ( quick ) {
		// Quick-start fields start empty (demo text as placeholder) so typing
		// replaces the demo content instead of appending to it.
		$$( 'input[data-field], textarea[data-field]', quick ).forEach( function ( input ) {
			var key = input.getAttribute( 'data-field' );
			if ( input.type !== 'color' && state.values[ key ] === schema[ key ][ 'default' ] ) {
				input.value = '';
			}
		} );
	}
	renderLogo();
	push();
}() );
