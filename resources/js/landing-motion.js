const clamp = (value) => Math.max(0, Math.min(1, value));

export function sceneProgress(top, height, viewport, inset = 88) {
    return clamp((inset - top) / Math.max(1, height - viewport + inset));
}

export function moduleScene(progress) {
    const position = clamp(progress);
    return {
        reading: clamp(position / .68),
        handoff: clamp((position - .84) / .16),
        stage: position < .72 ? 'reading' : position < .84 ? 'complete' : 'checkpoint',
    };
}
