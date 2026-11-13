let audioCtx = null;

function getCtx() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    return audioCtx;
}

function tone(freq, duration, type = 'sine', volume = 0.08) {
    try {
        const ctx = getCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = type;
        osc.frequency.value = freq;
        gain.gain.value = volume;
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
        osc.stop(ctx.currentTime + duration);
    } catch {
        // audio optional
    }
}

export const sounds = {
    click: () => tone(600, 0.05, 'square', 0.04),
    spin: () => tone(220, 0.08, 'sawtooth', 0.03),
    tick: () => tone(440, 0.03, 'square', 0.025),
    win: () => {
        tone(523, 0.12, 'sine', 0.07);
        setTimeout(() => tone(659, 0.12, 'sine', 0.07), 100);
        setTimeout(() => tone(784, 0.2, 'sine', 0.08), 200);
    },
    lose: () => {
        tone(300, 0.15, 'sawtooth', 0.05);
        setTimeout(() => tone(200, 0.25, 'sawtooth', 0.04), 120);
    },
    upgrade: () => {
        tone(400, 0.1, 'triangle', 0.06);
        setTimeout(() => tone(500, 0.15, 'triangle', 0.06), 80);
    },
    contract: () => {
        tone(180, 0.2, 'square', 0.05);
        setTimeout(() => tone(350, 0.15, 'sine', 0.06), 150);
    },
};
