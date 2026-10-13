#!/usr/bin/env node
/**
 * Captures a real screenshot of a live template's preview page and saves it
 * as an optimised WebP thumbnail for the Templates page.
 *
 *   node tools/screenshot-thumbnail.mjs <preview-url> <output.webp>
 *
 * Example:
 *   node tools/screenshot-thumbnail.mjs http://localhost:8080/templates/dental-clinics/preview/ \
 *     wp-content/plugins/deskman-studio-manager/assets/site-templates/img/thumbs/dental-clinics.webp
 *
 * Requires Playwright (Chromium) and ImageMagick (`convert`) for WebP output.
 */
import { chromium } from 'playwright';
import { execFileSync } from 'node:child_process';
import { mkdtempSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const [ , , url, out ] = process.argv;
if ( ! url || ! out ) {
	console.error( 'Usage: screenshot-thumbnail.mjs <preview-url> <output.webp>' );
	process.exit( 1 );
}

const browser = await chromium.launch();
const page = await browser.newPage( { viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 } );
await page.emulateMedia( { reducedMotion: 'reduce' } );
await page.goto( url, { waitUntil: 'networkidle' } );
await page.evaluate( () => document.querySelectorAll( '[data-reveal]' ).forEach( ( el ) => el.classList.add( 'is-visible' ) ) );
await page.waitForTimeout( 400 );
const png = join( mkdtempSync( join( tmpdir(), 'dmt-thumb-' ) ), 'thumb.png' );
await page.screenshot( { path: png, clip: { x: 0, y: 0, width: 1440, height: 900 } } );
await browser.close();

execFileSync( 'convert', [ png, '-resize', '1200x750', '-quality', '82', '-define', 'webp:method=6', out ] );
console.log( 'wrote', out );
