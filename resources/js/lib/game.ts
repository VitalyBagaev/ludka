export function formatPrice(price: number): string {
    return price.toLocaleString('ru-RU') + ' ₽';
}

export const rarityColors: Record<string, string> = {
    consumer: '#b0c3d9',
    industrial: '#5e98d9',
    'mil-spec': '#4b69ff',
    restricted: '#8847ff',
    classified: '#d32ce6',
    covert: '#eb4b4b',
    gold: '#e4ae39',
};

export const rarityLabels: Record<string, string> = {
    consumer: 'Consumer Grade',
    industrial: 'Industrial Grade',
    'mil-spec': 'Mil-Spec',
    restricted: 'Restricted',
    classified: 'Classified',
    covert: 'Covert',
    gold: '★ Rare Special',
};
