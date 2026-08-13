// REPL driver for PMED2 (Laravel + AdminLTE web app running in Docker).
// Uses playwright-core against the system Chromium (/usr/bin/chromium) —
// no chromium-cli available in this environment, so this fills that role.
// Designed for agents: wrap in tmux, send-keys commands, capture-pane output.
import { chromium } from 'playwright-core';
import * as readline from 'node:readline';
import * as fs from 'node:fs';
import * as path from 'node:path';

const BASE_URL = process.env.PMED2_URL || 'http://localhost:8080';
const CHROMIUM_PATH = process.env.CHROMIUM_PATH || '/usr/bin/chromium';
const SHOT_DIR = process.env.SCREENSHOT_DIR || '/tmp/pmed2-shots';
fs.mkdirSync(SHOT_DIR, { recursive: true });

let browser = null;
let page = null;
let consoleErrors = [];

const COMMANDS = {
  async launch() {
    if (browser) return console.log('already launched');
    browser = await chromium.launch({
      executablePath: CHROMIUM_PATH,
      headless: true,
      args: ['--no-sandbox', '--disable-gpu'],
    });
    const context = await browser.newContext({ ignoreHTTPSErrors: true });
    page = await context.newPage();
    consoleErrors = [];
    page.on('console', msg => { if (msg.type() === 'error') consoleErrors.push(msg.text()); });
    page.on('pageerror', err => consoleErrors.push('pageerror: ' + err.message));
    console.log('launched. chromium at', CHROMIUM_PATH);
  },

  async nav(url) {
    if (!page) return console.log('ERROR: launch first');
    const target = url && url.startsWith('http') ? url : (BASE_URL + (url || '/'));
    await page.goto(target, { waitUntil: 'domcontentloaded', timeout: 20_000 });
    console.log('nav →', target, '(status via last response not tracked; use eval document.title)');
  },

  async ss(name) {
    if (!page) return console.log('ERROR: launch first');
    const f = path.join(SHOT_DIR, (name || `ss-${Date.now()}`) + '.png');
    await page.screenshot({ path: f, fullPage: true });
    console.log('screenshot:', f);
  },

  async click(sel) {
    if (!page) return console.log('ERROR: launch first');
    try { await page.click(sel, { timeout: 5000 }); console.log('click', sel, '→ OK'); }
    catch (e) { console.log('click', sel, '→ ERROR:', e.message.split('\n')[0]); }
  },

  async 'click-text'(text) {
    if (!page) return console.log('ERROR: launch first');
    try {
      await page.getByText(text, { exact: false }).first().click({ timeout: 5000 });
      console.log('click-text', JSON.stringify(text), '→ OK');
    } catch (e) { console.log('click-text', JSON.stringify(text), '→ ERROR:', e.message.split('\n')[0]); }
  },

  async fill(args) {
    if (!page) return console.log('ERROR: launch first');
    const sp = args.indexOf(' ');
    const sel = sp === -1 ? args : args.slice(0, sp);
    const value = sp === -1 ? '' : args.slice(sp + 1);
    try { await page.fill(sel, value, { timeout: 5000 }); console.log('fill', sel, '→ OK'); }
    catch (e) { console.log('fill', sel, '→ ERROR:', e.message.split('\n')[0]); }
  },

  async type(text) { if (page) await page.keyboard.type(text, { delay: 20 }); },
  async press(key) { if (page) await page.keyboard.press(key); },

  async wait(sel) {
    if (!page) return console.log('ERROR: launch first');
    try { await page.waitForSelector(sel, { timeout: 10_000 }); console.log('found:', sel); }
    catch { console.log('TIMEOUT:', sel); }
  },

  async eval(expr) {
    if (!page) return console.log('ERROR: launch first');
    try { console.log(JSON.stringify(await page.evaluate(expr))); }
    catch (e) { console.log('ERROR:', e.message.split('\n')[0]); }
  },

  async text(sel) {
    if (!page) return console.log('ERROR: launch first');
    console.log(await page.evaluate(
      s => (s ? document.querySelector(s) : document.body)?.innerText ?? '(null)',
      sel || null));
  },

  async url() {
    if (!page) return console.log('ERROR: launch first');
    console.log(page.url());
  },

  // App-specific: log in as the seeded admin user (admin@admin / admin).
  async login(args) {
    if (!page) return console.log('ERROR: launch first');
    const [email, password] = (args || '').split(' ').filter(Boolean);
    await page.goto(BASE_URL + '/login', { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', email || 'admin@admin');
    await page.fill('input[name="password"]', password || 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');
    console.log('login → landed on', page.url());
  },

  async 'console-errors'() {
    if (!consoleErrors.length) return console.log('no console errors captured since launch');
    console.log(`${consoleErrors.length} console error(s):`);
    for (const e of consoleErrors) console.log(' -', e.slice(0, 300));
  },

  async quit() { if (browser) await browser.close().catch(() => {}); browser = null; page = null; },
  help() { console.log('commands:', Object.keys(COMMANDS).join(', ')); },
};

const stdin = fs.createReadStream(null, { fd: fs.openSync('/dev/stdin', 'r') });
const rl = readline.createInterface({ input: stdin, output: process.stdout, prompt: 'driver> ' });

rl.on('line', async line => {
  const trimmed = line.trim();
  const sp = trimmed.indexOf(' ');
  const cmd = sp === -1 ? trimmed : trimmed.slice(0, sp);
  const rest = sp === -1 ? '' : trimmed.slice(sp + 1);
  if (!cmd) return rl.prompt();
  const fn = COMMANDS[cmd];
  if (!fn) { console.log('unknown:', cmd, '— try: help'); return rl.prompt(); }
  try { await fn(rest); } catch (e) { console.log('ERROR:', e.message); }
  if (cmd === 'quit') { rl.close(); process.exit(0); }
  rl.prompt();
});
rl.on('close', async () => { await COMMANDS.quit(); process.exit(0); });

console.log('pmed2 driver — "help" for commands, "launch" to start, then "login" then "nav /dashboard"');
rl.prompt();
