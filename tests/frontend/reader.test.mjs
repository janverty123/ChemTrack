import test from 'node:test';
import assert from 'node:assert/strict';
import { pageObservation, nativePosition, nativeObservation, createReadingSaver } from '../../resources/js/reader.js';

test('page observation requires half the smaller viewport or page and preserves position', () => {
    assert.deepEqual(pageObservation({ top: -500, bottom: 1500, height: 2000 }, 800), { viewed: true, visible: 800, offset: 0.25 });
    assert.equal(pageObservation({ top: 700, bottom: 1000, height: 300 }, 800).viewed, false);
    assert.equal(pageObservation({ top: 650, bottom: 950, height: 300 }, 800).viewed, true);
    assert.equal(pageObservation({ top: 900, bottom: 1200, height: 300 }, 800).viewed, false);
});

test('native position is bounded and represents visible document coverage', () => {
    assert.equal(nativePosition({ top: 900, height: 1000 }, 800), 0);
    assert.equal(nativePosition({ top: -200, height: 1000 }, 800), 100);
    assert.equal(nativePosition({ top: 200, height: 1000 }, 800), 60);
});

test('reading saves serialize updates and only confirm the sent revision', async () => {
    const pending = [], sent = [], statuses = [];
    const saver = createReadingSaver({ send: payload => { sent.push(payload); return new Promise(resolve => pending.push(resolve)); }, status: value => statuses.push(value) });
    saver.update({ position: 20 });
    const first = saver.save();
    saver.update({ position: 70 });
    const second = saver.save();
    assert.equal(sent.length, 1);
    pending.shift()({ progress: 20 });
    await first;
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(sent.length, 2);
    assert.equal(sent[1].position, 70);
    pending.shift()({ progress: 70 });
    await second;
    assert.equal(saver.dirty(), false);
    assert.equal(statuses.at(-1), 'saved');
});

test('failed saves keep observations available for retry', async () => {
    let fail = true;
    const saver = createReadingSaver({ send: async () => { if (fail) throw new Error('offline'); return {}; }, status: () => {} });
    saver.update({ position: 55, viewed_pages: [1, 5] });
    await saver.save();
    assert.equal(saver.dirty(), true);
    fail = false;
    await saver.save();
    assert.equal(saver.dirty(), false);
});

test('a changed document version blocks subsequent saves', async () => {
    let requests = 0;
    const statuses = [];
    const saver = createReadingSaver({ send: async () => { requests++; const error = new Error('version changed'); error.stale = true; throw error; }, status: value => statuses.push(value) });
    saver.update({ position: 55 });
    await saver.save();
    saver.update({ position: 90 });
    await saver.save();
    assert.equal(requests, 1);
    assert.equal(statuses.at(-1), 'stale');
    assert.equal(saver.dirty(), true);
});

test('native backtracking before the first save keeps furthest coverage and current location', async () => {
    const sent = [];
    const saver = createReadingSaver({ send: async payload => { sent.push(payload); return {}; }, status: () => {} });
    let observation = nativeObservation(100, 0);
    saver.update(observation);
    observation = nativeObservation(20, observation.furthest_position);
    saver.update(observation);
    await saver.save();
    assert.deepEqual(sent, [{ position: 20, furthest_position: 100 }]);
    assert.equal(saver.dirty(), false);
});

test('native backtracking while a save is pending keeps high coverage in the next confirmed payload', async () => {
    const pending = [], sent = [], confirmed = [];
    const saver = createReadingSaver({ send: payload => { sent.push(payload); return new Promise(resolve => pending.push(resolve)); }, confirmed: payload => confirmed.push(payload), status: () => {} });
    let observation = nativeObservation(10, 0);
    saver.update(observation);
    const first = saver.save();
    observation = nativeObservation(100, observation.furthest_position);
    saver.update(observation);
    observation = nativeObservation(20, observation.furthest_position);
    saver.update(observation);
    const second = saver.save();
    assert.equal(sent.length, 1);
    pending.shift()(sent[0]);
    await first;
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.deepEqual(sent[1], { position: 20, furthest_position: 100 });
    assert.equal(saver.dirty(), true);
    pending.shift()(sent[1]);
    await second;
    assert.deepEqual(confirmed.at(-1), { position: 20, furthest_position: 100 });
    assert.equal(saver.dirty(), false);
});
