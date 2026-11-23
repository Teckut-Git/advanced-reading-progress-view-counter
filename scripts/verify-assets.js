#!/usr/bin/env node
/**
 * Verify the committed, WP.org-facing build assets.
 *
 * The plugin's admin UI and progress-bar behaviour live in the compiled files
 * under build/. The legacy files in src/ do not reproduce those assets and are
 * kept only for reference, so `npm run build` verifies the shipped files
 * instead of overwriting them with stale sources.
 */

const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const required = [
  'build/css/admin.css',
  'build/css/main.css',
  'build/js/arpvc-admin.min.js',
  'build/js/scroll-tracker.min.js',
];

let failed = false;

for (const relativePath of required) {
  const absolutePath = path.join(root, relativePath);
  if (!fs.existsSync(absolutePath)) {
    console.error(`MISSING  ${relativePath}`);
    failed = true;
    continue;
  }

  const size = fs.statSync(absolutePath).size;
  if (size === 0) {
    console.error(`EMPTY    ${relativePath}`);
    failed = true;
    continue;
  }

  console.log(`OK       ${relativePath} (${size} bytes)`);
}

if (failed) {
  console.error('\nBuild verification failed: shipped assets are missing or empty.');
  process.exit(1);
}

console.log('\nAll shipped assets are present.');
