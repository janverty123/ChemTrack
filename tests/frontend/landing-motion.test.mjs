import test from 'node:test';
import assert from 'node:assert/strict';
import { moduleScene, sceneProgress } from '../../resources/js/landing-motion.js';

test('module story starts at the beginning and only reveals the checkpoint after the illustrated completion action', () => {
    assert.deepEqual(moduleScene(-1), { reading: 0, handoff: 0, stage: 'reading' });
    assert.equal(moduleScene(.68).reading, 1);
    assert.equal(moduleScene(.68).stage, 'reading');
    assert.equal(moduleScene(.78).stage, 'complete');
    assert.equal(moduleScene(.78).handoff, 0);
    assert.equal(moduleScene(.95).stage, 'checkpoint');
    assert.deepEqual(moduleScene(2), { reading: 1, handoff: 1, stage: 'checkpoint' });
});

test('reverse scrolling restores the actual reading position instead of retaining a completed demonstration', () => {
    moduleScene(1);
    assert.equal(moduleScene(.34).reading, .5);
    assert.equal(moduleScene(.34).stage, 'reading');
    assert.equal(moduleScene(.34).handoff, 0);
});

test('scroll mapping handles entry, the end, resized and short viewports without escaping its bounds', () => {
    assert.equal(sceneProgress(100, 2000, 800, 88), 0);
    assert.equal(sceneProgress(-556, 2000, 800, 88), .5);
    assert.equal(sceneProgress(-1200, 2000, 800, 88), 1);
    assert.equal(sceneProgress(-5000, 400, 800, 88), 1);
});
