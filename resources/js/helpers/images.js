import casePlaceholder from '../../images/case-placeholder.svg?url';
import skinPlaceholder from '../../images/skin-placeholder.svg?url';

export { rarityClass, rarityLabel } from './images-meta.js';

export const SKIN_PLACEHOLDER = skinPlaceholder;
export const CASE_PLACEHOLDER = casePlaceholder;

export function imgError(event, type = 'skin') {
    const fallback = type === 'case' ? CASE_PLACEHOLDER : SKIN_PLACEHOLDER;
    if (event.target.src !== fallback) {
        event.target.src = fallback;
    }
}

export function skinImg(src) {
    if (!src || src.includes('placeholder')) {
        return SKIN_PLACEHOLDER;
    }
    return src;
}

export function caseImg(src) {
    if (!src || src.includes('placeholder')) {
        return CASE_PLACEHOLDER;
    }
    return src;
}
