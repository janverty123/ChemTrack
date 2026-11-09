import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const script = readFileSync(new URL('../../resources/views/components/appearance.blade.php', import.meta.url), 'utf8').match(/<script>([\s\S]*?)<\/script>/)[1];
function run(saved = null, stored = '{}', systemDark = false, storageThrows = false, publicPage = false) {
    const values = new Map([['chemtrack.appearance', stored]]);
    let dark = false;
    const root = { dataset: {}, style: {}, classList: { toggle(_, value) { dark = value; }, add() { dark = true; }, remove() { dark = false; } } };
    const mediaCallbacks = [];
    const media = { matches: systemDark, addEventListener(_, callback) { mediaCallbacks.push(callback); }, callback() { for (const callback of mediaCallbacks) callback({ matches: this.matches }); } };
    const storage = { getItem(key) { if (storageThrows) throw Error('blocked'); return values.get(key); }, setItem(key, value) { if (storageThrows) throw Error('blocked'); values.set(key, value); } };
    const windowHandlers = {}, documentHandlers = {}, stores = {};
    const document = { documentElement: root, addEventListener(name, callback) { documentHandlers[name] = callback; } };
    const window = { matchMedia() { return media; }, addEventListener(name, callback) { (windowHandlers[name] ||= []).push(callback); }, Alpine: { store(name, value) { if (arguments.length > 1) stores[name] = value; return stores[name]; }, effect(callback) { callback(); } } };
    const fire = (name, event) => { for (const callback of windowHandlers[name] || []) callback(event); };
    new Function('document', 'window', 'localStorage', script.replace('@json($appearance)', JSON.stringify(saved)).replace('@json($public)', JSON.stringify(publicPage)))(document, window, storage);
    const bootFilament = () => {
        const vendor = readFileSync(new URL('../../vendor/filament/filament/resources/js/dark-mode.js', import.meta.url), 'utf8');
        new Function('document', 'window', 'localStorage', 'getComputedStyle', vendor)(document, window, storage, () => ({ getPropertyValue() { return 'light'; } }));
        documentHandlers['alpine:init']();
    };
    return { root, media, values, fire, bootFilament, stores, isDark: () => dark };
}
test('account preferences override a previous device preference and initialize every appearance flag', () => {
    const app = run({ theme: 'dark', text_size: 'large', reduced_motion: true }, '{"theme":"light"}');
    assert.equal(app.root.dataset.theme, 'dark');
    assert.equal(app.root.dataset.textSize, 'large');
    assert.equal(app.root.dataset.reducedMotion, 'true');
    assert.equal(app.root.style.colorScheme, 'dark');
    assert.equal(app.isDark(), true);
});
test('system appearance responds to a live device change while explicit light stays light', () => {
    const system = run({ theme: 'system' });
    system.media.matches = true; system.media.callback();
    assert.equal(system.root.dataset.theme, 'dark');
    const light = run({ theme: 'light' }, '{}', true);
    light.media.matches = true; light.media.callback();
    assert.equal(light.root.dataset.theme, 'light');
});
test('guests always start light regardless of device appearance or a previous account', () => {
    assert.equal(run(null, '{"theme":"dark"}').root.dataset.theme, 'light');
    assert.equal(run(null, 'broken').root.dataset.textSize, 'standard');
    assert.equal(run(null, '{}', true, true).root.dataset.theme, 'light');
    assert.equal(run(null, '{"theme":"dark","text_size":"large"}').root.dataset.textSize, 'standard');
});
test('new accounts and unknown values start light even when the device is dark', () => {
    assert.equal(run({}, '{"theme":"dark"}', true).root.dataset.theme, 'light');
    assert.equal(run({ theme: 'unknown' }, '{}', true).root.dataset.theme, 'light');
    const app = run(null, '{"theme":"neon","text_size":"huge"}', false);
    assert.equal(app.root.dataset.theme, 'light');
    assert.equal(app.root.dataset.textSize, 'standard');
});
test('public pages stay light without overwriting the signed-in account theme', () => {
    const app = run({ theme: 'dark', text_size: 'large' }, '{"theme":"dark"}', true, false, true);
    app.fire('theme-changed', { detail: 'dark' });
    assert.equal(app.root.dataset.theme, 'light');
    assert.equal(app.root.dataset.textSize, 'standard');
    assert.equal(app.values.get('chemtrack.appearance'), '{"theme":"dark"}');
    assert.equal(app.values.get('filament:theme:mode'), 'light');
});
test('Filament boot preserves account dark appearance through its installed native initializer', () => {
    const app = run({ theme: 'dark' });
    app.bootFilament();
    assert.equal(app.stores.theme, 'dark');
    assert.equal(app.root.dataset.theme, 'dark');
    assert.equal(app.isDark(), true);
    assert.equal(app.values.get('filament:theme:mode'), 'dark');
});
test('native Filament theme changes keep ChemTrack appearance and device fallback synchronized', () => {
    const app = run({ theme: 'light', text_size: 'large', reduced_motion: true });
    app.bootFilament();
    app.fire('theme-changed', { detail: 'dark' });
    assert.equal(app.root.dataset.theme, 'dark');
    assert.equal(app.root.style.colorScheme, 'dark');
    assert.equal(app.isDark(), true);
    assert.deepEqual(JSON.parse(app.values.get('chemtrack.appearance')), { theme: 'dark', text_size: 'large', reduced_motion: true });
    app.fire('theme-changed', { detail: 'system' });
    assert.equal(app.root.dataset.theme, 'light');
    app.media.matches = true; app.media.callback();
    assert.equal(app.root.dataset.theme, 'dark');
});
