/**
 * End-to-end tests for the Templates page, dental customiser and enquiry flow
 * (Deskman Studio Manager → site templates, inside the Deskman Studio theme).
 *
 *   BASE_URL=http://localhost:8080 node --test tests/e2e/
 *
 * Requires a WordPress site running the Deskman Studio theme and Manager
 * plugin (see tests/README.md) and Playwright with Chromium available.
 */
import { test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { writeFileSync, mkdtempSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { chromium } from 'playwright';

const BASE = ( process.env.BASE_URL || 'http://localhost:8080' ).replace( /\/$/, '' );
const GALLERY = `${ BASE }/templates/`;
const DENTAL = `${ BASE }/templates/dental-clinics/`;
const TMP = mkdtempSync( join( tmpdir(), 'dmt-e2e-' ) );

// 64×64 PNG logo, a fake "PNG" (text) and an oversized file.
const PNG_64 = Buffer.from( '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', 'base64' );
const LOGO = join( TMP, 'logo.png' );
const FAKE = join( TMP, 'fake.png' );
const HUGE = join( TMP, 'huge.png' );
writeFileSync( LOGO, PNG_64 );
writeFileSync( FAKE, 'this is not an image' );
writeFileSync( HUGE, Buffer.concat( [ PNG_64, Buffer.alloc( 2.2 * 1024 * 1024 ) ] ) );

let browser;
before( async () => {
	browser = await chromium.launch();
} );
after( async () => {
	await browser.close();
} );

async function open( url, viewport = { width: 1440, height: 900 } ) {
	const context = await browser.newContext( { viewport } );
	const page = await context.newPage();
	const problems = [];
	page.on( 'pageerror', ( e ) => problems.push( `pageerror: ${ e.message }` ) );
	page.on( 'console', ( m ) => {
		if ( m.type() === 'error' ) {
			problems.push( `console: ${ m.text() }` );
		}
	} );
	page.on( 'response', ( r ) => {
		if ( r.status() >= 400 && r.url().includes( '/site-templates/' ) ) {
			problems.push( `asset ${ r.status() }: ${ r.url() }` );
		}
	} );
	await page.goto( url, { waitUntil: 'networkidle' } );
	return { page, context, problems };
}

async function skipQuickStart( page ) {
	const skip = page.getByRole( 'button', { name: /Skip/ } );
	if ( await skip.isVisible() ) {
		await skip.click();
	}
}

async function noHorizontalOverflow( page ) {
	const [ scroll, inner ] = await page.evaluate( () => [ document.documentElement.scrollWidth, window.innerWidth ] );
	assert.ok( scroll <= inner, `horizontal overflow: ${ scroll } > ${ inner }` );
}

test( 'Templates page loads with all ten industry cards', async () => {
	const { page, context, problems } = await open( GALLERY );
	assert.match( await page.locator( 'h1' ).innerText(), /Website templates/i );
	assert.equal( await page.locator( '.nav a[aria-current="page"]' ).innerText(), 'Templates' );
	const cards = page.locator( '.dmt-cards .dmt-card' );
	assert.equal( await cards.count(), 10 );
	for ( const name of [ 'Dental Clinics', 'Hospitals', 'Schools', 'Colleges & Universities', 'Teachers & Tutors', 'Real Estate', 'Solar Energy', 'Salons & Beauty', 'Restaurants & Cafés', 'Professional Services' ] ) {
		assert.equal( await page.locator( '.dmt-card__title', { hasText: name } ).count(), 1, `${ name } card` );
	}
	assert.equal( await page.locator( '.dmt-card--live' ).count(), 1 );
	assert.equal( await page.locator( '.dmt-card--soon .dmt-badge--soon' ).count(), 9 );
	// Every thumbnail actually loaded.
	const broken = await page.$$eval( '.dmt-card img', ( imgs ) => imgs.filter( ( i ) => ! i.complete || i.naturalWidth === 0 ).map( ( i ) => i.src ) );
	assert.deepEqual( broken, [] );
	assert.deepEqual( problems, [] );
	await context.close();
} );

test( 'Coming-soon cards show a message and never open a template', async () => {
	const { page, context } = await open( GALLERY );
	await page.locator( '.dmt-card--soon', { hasText: 'Hospitals' } ).click();
	await page.waitForTimeout( 100 );
	assert.equal( page.url(), GALLERY );
	await assert.doesNotReject( page.locator( '[data-dmt-notice]' ).waitFor( { state: 'visible' } ) );
	assert.match( await page.locator( '[data-dmt-notice-text]' ).innerText(), /Hospitals templates are being prepared/ );
	await context.close();

	// Direct URL to a coming-soon industry redirects back to the gallery.
	const direct = await open( `${ BASE }/templates/hospitals/` );
	assert.ok( direct.page.url().startsWith( GALLERY ) );
	assert.match( await direct.page.locator( '[data-dmt-notice-text]' ).innerText(), /Hospitals/ );
	await direct.context.close();

	// Unknown industries are 404s.
	const ctx = await browser.newContext();
	const res = await ( await ctx.newPage() ).goto( `${ BASE }/templates/not-an-industry/` );
	assert.equal( res.status(), 404 );
	const preview = await ( await ctx.newPage() ).goto( `${ BASE }/templates/hospitals/preview/` );
	assert.equal( preview.status(), 404 );
	await ctx.close();
} );

test( 'Dental Clinics card opens the customiser with a live preview', async () => {
	const { page, context, problems } = await open( GALLERY );
	await page.locator( '.dmt-card--live' ).click();
	await page.waitForURL( DENTAL );
	await skipQuickStart( page );
	const frame = page.frameLocator( '[data-dmt-iframe]' );
	await frame.locator( '.dt-hero__title' ).waitFor();
	assert.match( await frame.locator( '.dt-hero__title' ).innerText(), /healthier, happier smiles/ );
	for ( const id of [ 'services', 'about', 'team', 'appointment', 'contact' ] ) {
		assert.equal( await frame.locator( `#${ id }` ).count(), 1, `#${ id } section` );
	}
	assert.match( await frame.locator( '.dt-testimonials' ).innerText(), /not real reviews/ );
	assert.deepEqual( problems, [] );
	await context.close();
} );

test( 'Clinic name, phone, colours and logo update the preview immediately', async () => {
	const { page, context } = await open( DENTAL );
	await skipQuickStart( page );
	const frame = page.frameLocator( '[data-dmt-iframe]' );

	await page.locator( '#dmt-panel-clinic_name' ).fill( 'Sunrise Family Dental' );
	await page.locator( '#dmt-panel-phone' ).fill( '+44 20 7946 0123' );
	await page.locator( '#dmt-panel-hours' ).fill( 'Weekdays: 9 am – 5 pm\nWeekends: Closed' );
	await page.locator( '#dmt-panel-color_primary' ).evaluate( ( el ) => {
		el.value = '#7c3aed';
		el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	} );
	await page.locator( '.dmt-color__hex[data-field="color_secondary"]' ).fill( '#E11D48' );

	await assert.doesNotReject( frame.locator( '.dt-header .dt-brand__name', { hasText: 'Sunrise Family Dental' } ).waitFor() );
	assert.equal( await frame.locator( '.dt-header__phone' ).getAttribute( 'href' ), 'tel:+442079460123' );
	assert.match( await frame.locator( '.dt-footer__bottom' ).innerText(), /Sunrise Family Dental/ );
	assert.equal( await frame.locator( '.dt-contact .dt-hours li' ).count(), 2 );
	const vars = await frame.locator( 'html' ).evaluate( () => {
		const cs = getComputedStyle( document.documentElement );
		return [ cs.getPropertyValue( '--c-primary' ).trim(), cs.getPropertyValue( '--c-secondary' ).trim() ];
	} );
	assert.deepEqual( vars, [ '#7C3AED', '#E11D48' ] );
	await page.waitForTimeout( 400 ); // Let the button's colour transition finish.
	const btnBg = await frame.locator( '.dt-header__cta' ).evaluate( ( el ) => getComputedStyle( el ).backgroundColor );
	assert.equal( btnBg, 'rgb(124, 58, 237)' );

	// Logo upload replaces the text brand in header and footer.
	await page.locator( '#dmt-panel-logo' ).setInputFiles( LOGO );
	await assert.doesNotReject( frame.locator( '.dt-header [data-brand].has-logo' ).waitFor() );
	assert.match( await frame.locator( '.dt-header [data-logo-img]' ).getAttribute( 'src' ), /^data:image\/png;base64,/ );
	assert.equal( await frame.locator( '.dt-footer [data-brand].has-logo' ).count(), 1 );

	// Reset restores the demo content.
	page.once( 'dialog', ( d ) => d.accept() );
	await page.locator( '.dmt-cz__actions [data-dmt-reset]' ).click();
	await assert.doesNotReject( frame.locator( '.dt-header .dt-brand__name', { hasText: 'Brightway Dental Care' } ).waitFor() );
	assert.equal( await frame.locator( '.dt-header [data-brand].has-logo' ).count(), 0 );
	await context.close();
} );

test( 'Invalid logo files are rejected with a helpful message', async () => {
	const { page, context } = await open( DENTAL );
	await skipQuickStart( page );
	const error = page.locator( '#dmt-panel-logo-err' );
	await page.locator( '#dmt-panel-logo' ).setInputFiles( FAKE );
	await assert.doesNotReject( error.filter( { hasText: /couldn’t read|PNG, JPEG or WebP/ } ).waitFor() );
	await page.locator( '#dmt-panel-logo' ).setInputFiles( HUGE );
	await assert.doesNotReject( error.filter( { hasText: /too large/ } ).waitFor() );
	await page.locator( '#dmt-panel-logo' ).setInputFiles( { name: 'logo.gif', mimeType: 'image/gif', buffer: Buffer.from( 'GIF89a' ) } );
	await assert.doesNotReject( error.filter( { hasText: /PNG, JPEG or WebP/ } ).waitFor() );
	assert.equal( await page.frameLocator( '[data-dmt-iframe]' ).locator( '.dt-header [data-brand].has-logo' ).count(), 0 );
	await context.close();
} );

test( 'Device previews render the real responsive layout at each width', async () => {
	const { page, context } = await open( DENTAL );
	await skipQuickStart( page );
	const frame = page.frameLocator( '[data-dmt-iframe]' );
	const expected = { mobile: 390, tablet: 768, laptop: 1366, desktop: 1440 };
	for ( const [ device, width ] of Object.entries( expected ) ) {
		await page.locator( `.dmt-devices [data-device="${ device }"]` ).click();
		await page.waitForTimeout( 250 );
		const inner = await frame.locator( 'html' ).evaluate( () => window.innerWidth );
		assert.equal( inner, width, `${ device } viewport width` );
		const menuVisible = await frame.locator( '[data-menu-toggle]' ).isVisible();
		assert.equal( menuVisible, width < 960, `${ device } hamburger menu` );
		const overflow = await frame.locator( 'html' ).evaluate( () => document.documentElement.scrollWidth - window.innerWidth );
		assert.ok( overflow <= 0, `${ device } has no horizontal overflow` );
		// The scaled device frame fits inside the stage.
		const fits = await page.evaluate( () => {
			const stage = document.querySelector( '[data-dmt-frame]' ).getBoundingClientRect();
			const dev = document.querySelector( '[data-dmt-device]' ).getBoundingClientRect();
			return dev.width <= stage.width + 1 && dev.height <= stage.height + 1;
		} );
		assert.ok( fits, `${ device } frame fits the stage` );
	}

	// Mobile navigation works inside the 390px preview.
	await page.locator( '.dmt-devices [data-device="mobile"]' ).click();
	await frame.locator( '[data-menu-toggle]' ).click();
	assert.equal( await frame.locator( '[data-menu-toggle]' ).getAttribute( 'aria-expanded' ), 'true' );
	await frame.locator( '.dt-nav__link', { hasText: 'Our Team' } ).click();
	assert.equal( await frame.locator( '[data-menu-toggle]' ).getAttribute( 'aria-expanded' ), 'false' );
	await context.close();
} );

test( 'Customiser works on a real phone-sized screen without overflow', async () => {
	for ( const viewport of [ { width: 390, height: 844 }, { width: 768, height: 1024 }, { width: 1366, height: 768 } ] ) {
		const { page, context, problems } = await open( DENTAL, viewport );
		await skipQuickStart( page );
		await noHorizontalOverflow( page );
		if ( viewport.width < 1024 ) {
			await page.getByRole( 'tab', { name: 'Preview' } ).click();
			assert.ok( await page.locator( '[data-dmt-stage]' ).isVisible() );
			assert.ok( ! ( await page.locator( '#dmt-panel' ).isVisible() ) );
			await page.getByRole( 'tab', { name: 'Customise' } ).click();
			assert.ok( await page.locator( '#dmt-panel' ).isVisible() );
		}
		await noHorizontalOverflow( page );
		assert.deepEqual( problems, [] );
		await context.close();

		const gallery = await open( GALLERY, viewport );
		await noHorizontalOverflow( gallery.page );
		await gallery.context.close();
	}
} );

test( 'Enquiry form validates input and submits a real enquiry', async () => {
	const { page, context } = await open( DENTAL );
	await skipQuickStart( page );
	await page.locator( '#dmt-panel-clinic_name' ).fill( 'E2E Test Dental' );
	await page.locator( '#dmt-panel-phone' ).fill( '+1 555 010 9999' );
	await page.locator( '#dmt-panel-logo' ).setInputFiles( LOGO );
	await page.frameLocator( '[data-dmt-iframe]' ).locator( '.dt-header [data-brand].has-logo' ).waitFor();

	await page.locator( '.dmt-cz__panel-foot [data-dmt-start]' ).click();
	const dialog = page.locator( '[data-dmt-enquiry]' );
	await dialog.waitFor( { state: 'visible' } );
	// Personalised values carry over; demo defaults do not.
	assert.equal( await page.locator( '#dmt-e-business' ).inputValue(), 'E2E Test Dental' );
	assert.equal( await page.locator( '#dmt-e-phone' ).inputValue(), '+1 555 010 9999' );
	assert.equal( await page.locator( '#dmt-e-email' ).inputValue(), '' );
	assert.match( await page.locator( '[data-dmt-summary-meta]' ).innerText(), /Logo included/ );

	// Empty / invalid submission is blocked client-side.
	await page.locator( '#dmt-e-email' ).fill( 'not-an-email' );
	await page.locator( '#dmt-e-phone' ).fill( 'abc' );
	await page.locator( '[data-dmt-submit]' ).click();
	assert.ok( await page.locator( '[data-dmt-errors]' ).isVisible() );
	assert.equal( await page.locator( '#dmt-e-email' ).getAttribute( 'aria-invalid' ), 'true' );
	assert.equal( await page.locator( '#dmt-e-phone' ).getAttribute( 'aria-invalid' ), 'true' );
	assert.equal( await page.locator( '#dmt-e-name' ).getAttribute( 'aria-invalid' ), 'true' );
	assert.match( await page.locator( '[data-error-for="consent"]' ).innerText(), /confirm/ );
	assert.ok( await page.locator( '[data-dmt-success]' ).isHidden() );

	// Valid submission is actually stored by WordPress.
	await page.locator( '#dmt-e-name' ).fill( 'Dr. E2E Tester' );
	await page.locator( '#dmt-e-email' ).fill( 'e2e@example.com' );
	await page.locator( '#dmt-e-phone' ).fill( '+1 555 010 9999' );
	await page.locator( '#dmt-e-req' ).fill( `Online booking please (e2e run ${ Date.now() }).` );
	await page.locator( '#dmt-e-consent' ).check();
	await page.waitForTimeout( 3200 ); // Minimum fill time enforced by the server.
	const [ response ] = await Promise.all( [
		page.waitForResponse( ( r ) => r.url().includes( '/deskman/v1/template-enquiry' ) ),
		page.locator( '[data-dmt-submit]' ).click(),
	] );
	assert.equal( response.status(), 201 );
	const body = await response.json();
	assert.match( body.reference, /^DMS-\d{6}-[A-Z0-9]{5}$/ );
	await page.locator( '[data-dmt-success]' ).waitFor( { state: 'visible' } );
	assert.equal( await page.locator( '[data-dmt-success-ref]' ).innerText(), body.reference );
	console.log( `    submitted enquiry ${ body.reference }` );
	await context.close();
} );

test( 'REST endpoint rejects bot submissions and exposes no enquiries', async () => {
	const ctx = await browser.newContext();
	const endpoint = `${ BASE }/wp-json/deskman/v1/template-enquiry`;
	// Honeypot filled.
	let res = await ctx.request.post( endpoint, { multipart: { website: 'http://spam', name: 'Bot', email: 'bot@example.com' } } );
	assert.equal( res.status(), 400 );
	// No nonce from a rendered page.
	res = await ctx.request.post( endpoint, { multipart: { name: 'Bot', email: 'bot@example.com', phone: '+44 20 7946 0000', business: 'Bot Inc', template: 'dental', consent: '1' } } );
	assert.equal( res.status(), 403 );
	// Enquiries are not exposed through the REST API.
	res = await ctx.request.get( `${ BASE }/wp-json/wp/v2/types` );
	assert.ok( ! ( await res.text() ).includes( 'deskman_enquiry' ) );
	res = await ctx.request.get( endpoint );
	assert.equal( res.status(), 404 );
	await ctx.close();
} );
