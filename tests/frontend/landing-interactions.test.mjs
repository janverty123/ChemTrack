import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

// Runs the real event handlers with a small DOM/timer stand-in; this does not render a browser.
function harness(reduced = false, accountReduced = false) {
    let now = 0, sequence = 0;
    const timers = new Map();
    const later = (fn, delay) => { const id = ++sequence; timers.set(id, { fn, due: now + delay }); return id; };
    const advance = (duration) => {
        const end = now + duration;
        for (;;) {
            const next = [...timers].filter(([, t]) => t.due <= end).sort((a, b) => a[1].due - b[1].due)[0];
            if (!next) break;
            timers.delete(next[0]); now = next[1].due; next[1].fn();
        }
        now = end;
    };
    class Element {
        dataset = {}; attributes = {}; handlers = {}; children = {}; lists = {};
        hidden = false; disabled = false; checked = false; textContent = ''; open = false;
        scrollHeight = 1400; clientHeight = 440;
        style = { removeProperty(name) { delete this[name.replace(/-([a-z])/g, (_, char) => char.toUpperCase())]; } };
        classList = { toggle() {}, add() {}, remove() {} };
        querySelector(selector) { return this.children[selector]; }
        querySelectorAll(selector) { return this.lists[selector] || []; }
        addEventListener(type, fn) { (this.handlers[type] ||= []).push(fn); }
        fire(type, properties = {}) { for (const fn of this.handlers[type] || []) fn({ detail: 1, target: this, preventDefault() {}, ...properties }); }
        setAttribute(key, value) { this.attributes[key] = value; }
        removeAttribute(key) { delete this.attributes[key]; }
        getBoundingClientRect() { return { top: 88, height: 2080, left: 0, right: 400, bottom: 500 }; }
        focus() { this.focused = true; }
        showModal() { this.open = true; }
        close() { this.open = false; this.fire('close'); }
        animate(_, settings) {
            let timer;
            return { finished: new Promise((resolve) => { timer = later(resolve, settings.duration + (settings.delay || 0)); }), cancel() { timers.delete(timer); } };
        }
    }
    const landing = new Element();
    const demo = new Element(), options = new Element(), submit = new Element(), play = new Element(), status = new Element(), receipt = new Element();
    receipt.hidden = true; submit.disabled = true;
    const radios = [new Element(), new Element()];
    options.lists.input = radios;
    Object.assign(demo.children, { '[data-demo-options]': options, '[data-demo-submit]': submit, '[data-demo-play]': play, '[data-demo-status]': status, '[data-demo-receipt]': receipt });
    Object.assign(landing.children, { '[data-checkpoint-demo]': demo });
    const preference = new Element(); preference.matches = reduced;
    const doc = new Element(); doc.querySelector = () => landing;
    doc.documentElement = { dataset: { reducedMotion: accountReduced ? 'true' : 'false' } };
    const win = new Element(); Object.assign(win, { innerHeight: 800, scrollY: 0, matchMedia: () => preference, requestAnimationFrame: (fn) => fn() });
    const source = readFileSync(new URL('../../resources/js/landing.js', import.meta.url), 'utf8');
    new Function('document', 'window', 'setTimeout', 'clearTimeout', source)(doc, win, later, (id) => timers.delete(id));
    const choose = (index) => { radios.forEach((radio, i) => { radio.checked = index === i; }); radios[index].fire('change'); };
    return { advance, choose, demo, options, submit, play, status, receipt, preference, landing };
}

test('manual answer must finish saving before submission; confirmation disables the sample answers', () => {
    const app = harness();
    app.choose(1);
    assert.equal(app.submit.disabled, true);
    assert.equal(app.status.textContent, 'Saving answer…');
    app.advance(449);
    assert.equal(app.submit.disabled, true);
    app.advance(1);
    assert.equal(app.submit.disabled, false);
    app.submit.fire('click');
    assert.equal(app.receipt.hidden, true);
    app.advance(350);
    assert.equal(app.receipt.hidden, false);
    assert.equal(app.options.disabled, true);
});

test('manual selection interrupts playback without a later automatic submission overriding it', () => {
    const app = harness();
    app.play.fire('click'); app.advance(700);
    app.choose(1); app.advance(3000);
    assert.equal(app.receipt.hidden, true);
    assert.equal(app.submit.disabled, false);
    assert.equal(app.status.textContent, 'Answer saved');
});

test('replay resets the submitted state and reduced motion uses manual actions', () => {
    const app = harness();
    app.play.fire('click'); app.advance(2200);
    assert.equal(app.receipt.hidden, false);
    app.play.fire('click');
    assert.equal(app.receipt.hidden, true);
    assert.equal(app.options.disabled, false);
    const quiet = harness(true);
    quiet.play.fire('click'); quiet.advance(5000);
    assert.equal(quiet.receipt.hidden, true);
    quiet.choose(0); quiet.advance(0); quiet.submit.fire('click'); quiet.advance(0);
    assert.equal(quiet.receipt.hidden, false);
});

test('saved account reduced motion disables automatic example playback', () => {
    const app = harness(false, true);
    app.play.fire('click'); app.advance(5000);
    assert.equal(app.receipt.hidden, true);
    app.choose(0); app.advance(0); app.submit.fire('click'); app.advance(0);
    assert.equal(app.receipt.hidden, false);
});

test('switching to reduced motion stops a running example and resets it for manual use', () => {
    const app = harness();
    app.play.fire('click'); app.advance(700);
    app.preference.matches = true;
    app.preference.fire('change');
    app.advance(5000);
    assert.equal(app.receipt.hidden, true);
    assert.equal(app.status.textContent, 'Choose an answer');
});
