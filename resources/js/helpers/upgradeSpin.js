/**
 * Угол стрелки апгрейда: зелёная зона 0…chance% сверху по часовой.
 *
 * @param {number} chance 1–75
 * @param {boolean} success
 * @returns {number}
 */
export function calcUpgradeArrowAngle(chance, success) {
    const clamped = Math.min(75, Math.max(1, Number(chance) || 1));
    const winSpan = (clamped / 100) * 360;
    const margin = Math.min(8, winSpan * 0.12);
    const spins = 5 + Math.floor(Math.random() * 3);

    let targetDeg;
    if (success) {
        const maxWin = Math.max(margin + 2, winSpan - margin);
        targetDeg = margin + Math.random() * (maxWin - margin);
    } else {
        const loseStart = Math.min(359 - margin, winSpan + margin);
        targetDeg = loseStart + Math.random() * (360 - margin - loseStart);
    }

    return spins * 360 + targetDeg;
}
