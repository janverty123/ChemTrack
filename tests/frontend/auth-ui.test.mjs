import test from 'node:test';
import assert from 'node:assert/strict';
import { initPasswordToggles } from '../../resources/js/auth-ui.js';

function field(label = 'Password') {
    const input = { type: 'password', value: 'synthetic passphrase' };
    const button = { hidden: true, dataset: { passwordLabel: label }, attributes: {}, addEventListener(_, fn) { this.click = fn; }, setAttribute(key, value) { this.attributes[key] = String(value); } };
    button.closest = () => ({ querySelector: () => input });
    return { input, button };
}
test('each in-field toggle reveals only its own password and exposes the correct accessible action', () => {
    const password = field(), confirmation = field('Confirm password');
    initPasswordToggles({ querySelectorAll: () => [password.button, confirmation.button] });
    assert.equal(password.button.hidden, false);
    password.button.click();
    assert.equal(password.input.type, 'text');
    assert.equal(confirmation.input.type, 'password');
    assert.equal(password.button.attributes['aria-label'], 'Hide password');
    assert.equal(password.button.attributes['aria-pressed'], 'true');
    assert.equal(password.input.value, 'synthetic passphrase');
    password.button.click();
    assert.equal(password.input.type, 'password');
    assert.equal(password.button.attributes['aria-label'], 'Show password');
    assert.equal(password.button.attributes['aria-pressed'], 'false');
    confirmation.button.click();
    assert.equal(confirmation.button.attributes['aria-label'], 'Hide confirm password');
});
test('initializing an unrelated page is harmless', () => {
    initPasswordToggles({ querySelectorAll: () => [] });
});
