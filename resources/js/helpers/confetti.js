import confetti from 'canvas-confetti';

export function fireWinConfetti() {
    const colors = ['#f0b429', '#8b5cf6', '#ff6b35', '#22c55e'];
    confetti({
        particleCount: 80,
        spread: 70,
        origin: { y: 0.6 },
        colors,
    });
    setTimeout(() => {
        confetti({
            particleCount: 50,
            angle: 60,
            spread: 55,
            origin: { x: 0 },
            colors,
        });
        confetti({
            particleCount: 50,
            angle: 120,
            spread: 55,
            origin: { x: 1 },
            colors,
        });
    }, 200);
}

export function fireUpgradeConfetti(success) {
    if (!success) return;
    confetti({
        particleCount: 40,
        spread: 50,
        origin: { y: 0.65 },
        colors: ['#f0b429', '#a78bfa'],
    });
}
