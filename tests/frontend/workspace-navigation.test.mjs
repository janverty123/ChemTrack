import test from 'node:test';
import assert from 'node:assert/strict';
import { initWorkspaceNavigation } from '../../resources/js/workspace-navigation.js';

function harness({ narrow = false, saved = null, blockedStorage = false } = {}) {
    const handlers = new Map(), values = new Map(saved === null ? [] : [['chemtrack.navigation.collapsed', saved]]);
    const doc = { activeElement: null, addEventListener(type, fn) { handlers.set(type, fn); } };
    const element = () => ({
        hidden: false, inert: false, attributes: {}, events: {},
        classList: { values: new Set(), add(key) { this.values.add(key); }, toggle(key, value) { value ? this.values.add(key) : this.values.delete(key); } },
        setAttribute(key, value) { this.attributes[key] = value; }, removeAttribute(key) { delete this.attributes[key]; },
        addEventListener(type, fn) { this.events[type] = fn; }, fire(type, props = {}) { this.events[type]?.({ preventDefault() {}, ...props }); },
        focus() { doc.activeElement = this; },
    });
    const root = element(), sidebar = element(), main = element(), hide = element(), show = element(), backdrop = element(), toolbar = element(), link = element(), logout = element();
    sidebar.contains = (target) => [hide,link,logout].includes(target);
    sidebar.querySelectorAll = () => [hide,link,logout];
    sidebar.querySelector = () => ({ addEventListener: (type, fn) => { link.events[type] = fn; } });
    const nodes = { '.workspace-page': root, '[data-workspace-sidebar]': sidebar, '#main': main, '[data-sidebar-hide]': hide, '[data-sidebar-show]': show, '[data-sidebar-backdrop]': backdrop, '[data-sidebar-toolbar]': toolbar };
    doc.querySelector = (key) => nodes[key];
    const media = { matches: narrow, addEventListener(_, fn) { this.change = fn; } };
    const win = { matchMedia: () => media, sessionStorage: { getItem(key) { if (blockedStorage) throw Error('unavailable'); return values.get(key) ?? null; }, setItem(key, value) { if (blockedStorage) throw Error('unavailable'); values.set(key, value); } } };
    initWorkspaceNavigation(doc, win);
    return { root, sidebar, main, hide, show, toolbar, backdrop, link, logout, doc, media, values, key(key, props = {}) { const event = { key, preventDefault() { this.prevented = true; }, ...props }; handlers.get('keydown')?.(event); return event; } };
}

test('desktop hide/show makes the panel inert, returns focus and preserves the preference', () => {
    const app = harness();
    assert.equal(app.sidebar.inert, false);
    app.hide.fire('click');
    assert.equal(app.sidebar.inert, true);
    assert.equal(app.toolbar.hidden, false);
    assert.equal(app.doc.activeElement, app.show);
    assert.equal(app.values.get('chemtrack.navigation.collapsed'), 'true');
    app.show.fire('click');
    assert.equal(app.sidebar.inert, false);
    assert.equal(app.toolbar.hidden, true);
    assert.equal(app.doc.activeElement, app.hide);
});

test('saved collapsed desktop preference is restored and storage refusal does not break toggling', () => {
    assert.equal(harness({ saved: 'true' }).sidebar.inert, true);
    const blocked = harness({ blockedStorage: true });
    blocked.hide.fire('click'); blocked.show.fire('click');
    assert.equal(blocked.sidebar.inert, false);
});

test('narrow view starts closed, opens as a dialog and Escape restores the page and focus', () => {
    const app = harness({ narrow: true });
    assert.equal(app.sidebar.inert, true);
    app.show.fire('click');
    assert.equal(app.sidebar.attributes.role, 'dialog');
    assert.equal(app.main.inert, true);
    assert.equal(app.backdrop.hidden, false);
    app.key('Escape');
    assert.equal(app.main.inert, false);
    assert.equal(app.sidebar.inert, true);
    assert.equal(app.doc.activeElement, app.show);
});

test('narrow dialog wraps keyboard focus and closes on a navigation link', () => {
    const app = harness({ narrow: true }); app.show.fire('click');
    app.logout.focus(); assert.equal(app.key('Tab').prevented, true);
    assert.equal(app.doc.activeElement, app.hide);
    app.key('Tab', { shiftKey: true }); assert.equal(app.doc.activeElement, app.logout);
    app.link.fire('click', { target: { closest: () => app.link } });
    assert.equal(app.sidebar.inert, true);
    assert.equal(app.main.inert, false);
});

test('resizing to a narrow view closes the overlay without discarding the desktop preference', () => {
    const app = harness();
    app.media.matches = true; app.media.change();
    assert.equal(app.sidebar.inert, true);
    app.media.matches = false; app.media.change();
    assert.equal(app.sidebar.inert, false);
});

test('public pages without a workspace do not receive navigation behavior', () => {
    assert.doesNotThrow(() => initWorkspaceNavigation({ querySelector: () => null }, {}));
});
