import type { Battle, Case, LiveDrop, Rarity } from '@/types';

export const rarityColors: Record<Rarity, string> = {
    consumer: '#b0c3d9',
    industrial: '#5e98d9',
    'mil-spec': '#4b69ff',
    restricted: '#8847ff',
    classified: '#d32ce6',
    covert: '#eb4b4b',
    gold: '#e4ae39',
};

export const rarityLabels: Record<Rarity, string> = {
    consumer: 'Consumer Grade',
    industrial: 'Industrial Grade',
    'mil-spec': 'Mil-Spec',
    restricted: 'Restricted',
    classified: 'Classified',
    covert: 'Covert',
    gold: '★ Rare Special',
};

const skins = {
    akRedline: {
        id: 1,
        name: 'Redline',
        weapon: 'AK-47',
        rarity: 'classified' as Rarity,
        price: 1250,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpot621FAR17P7NdTRH-tjJmYOdcPLxN7fYgGGQ8dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
    },
    awpDragon: {
        id: 2,
        name: 'Dragon Lore',
        weapon: 'AWP',
        rarity: 'gold' as Rarity,
        price: 45000,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpot7HxfDhjxszJemkV09-5gZKKkuXLPr7Vn35cpp12j-3H9Y3z0u3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
    },
    m4Asiimov: {
        id: 3,
        name: 'Asiimov',
        weapon: 'M4A4',
        rarity: 'covert' as Rarity,
        price: 3200,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpou-6kejhz2v_Nfz5H_uO1gb-Gw_alIITCmmpD7dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
    },
    glockFade: {
        id: 4,
        name: 'Fade',
        weapon: 'Glock-18',
        rarity: 'restricted' as Rarity,
        price: 890,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpot621FAR17P7NdTRH-tjJmYOdcPLxN7fYgGGQ8dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
    },
    karambitDoppler: {
        id: 5,
        name: 'Doppler',
        weapon: 'Karambit',
        rarity: 'gold' as Rarity,
        price: 28000,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpot7HxfDhjxszJemkV09-5gZKKkuXLPr7Vn35cpp12j-3H9Y3z0u3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
    },
    uspKill: {
        id: 6,
        name: 'Kill Confirmed',
        weapon: 'USP-S',
        rarity: 'covert' as Rarity,
        price: 2100,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpot621FAR17P7NdTRH-tjJmYOdcPLxN7fYgGGQ8dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
    },
    deagleBlaze: {
        id: 7,
        name: 'Blaze',
        weapon: 'Desert Eagle',
        rarity: 'restricted' as Rarity,
        price: 650,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpot621FAR17P7NdTRH-tjJmYOdcPLxN7fYgGGQ8dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
    },
    p250Sand: {
        id: 8,
        name: 'Sand Dune',
        weapon: 'P250',
        rarity: 'consumer' as Rarity,
        price: 5,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpot621FAR17P7NdTRH-tjJmYOdcPLxN7fYgGGQ8dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
    },
};

export const cases: Case[] = [
    {
        id: 1,
        name: 'Phoenix Case',
        price: 49,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpou-6kejhz2v_Nfz5H_uO1gb-Gw_alIITCmmpD7dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
        category: 'popular',
        featured: true,
        items: [skins.akRedline, skins.awpDragon, skins.m4Asiimov, skins.glockFade, skins.p250Sand],
    },
    {
        id: 2,
        name: 'Chroma Case',
        price: 79,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpou-6kejhz2v_Nfz5H_uO1gb-Gw_alIITCmmpD7dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
        category: 'popular',
        featured: true,
        items: [skins.karambitDoppler, skins.m4Asiimov, skins.uspKill, skins.deagleBlaze],
    },
    {
        id: 3,
        name: 'Gamma Case',
        price: 99,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpou-6kejhz2v_Nfz5H_uO1gb-Gw_alIITCmmpD7dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
        category: 'new',
        items: [skins.karambitDoppler, skins.awpDragon, skins.glockFade],
    },
    {
        id: 4,
        name: 'Spectrum Case',
        price: 59,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpou-6kejhz2v_Nfz5H_uO1gb-Gw_alIITCmmpD7dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
        category: 'popular',
        items: [skins.akRedline, skins.uspKill, skins.p250Sand],
    },
    {
        id: 5,
        name: 'Danger Zone',
        price: 39,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpou-6kejhz2v_Nfz5H_uO1gb-Gw_alIITCmmpD7dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
        category: 'budget',
        items: [skins.deagleBlaze, skins.glockFade, skins.p250Sand],
    },
    {
        id: 6,
        name: 'Knife Case',
        price: 199,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpou-6kejhz2v_Nfz5H_uO1gb-Gw_alIITCmmpD7dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
        category: 'premium',
        featured: true,
        items: [skins.karambitDoppler, skins.awpDragon],
    },
    {
        id: 7,
        name: 'Dreams & Nightmares',
        price: 69,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpou-6kejhz2v_Nfz5H_uO1gb-Gw_alIITCmmpD7dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
        category: 'new',
        items: [skins.m4Asiimov, skins.akRedline, skins.uspKill],
    },
    {
        id: 8,
        name: 'Revolution Case',
        price: 89,
        image: 'https://community.cloudflare.steamstatic.com/economy/image/-9a81dlWLwJ2UUGcVs_nsVtzdOEdtWwKGZZLQHTxDZ7I56KU0Zwwo4NUX4oFJZEHLbXH5ApeO4YmlhxYQknCRvCo04DEVlxkKgpou-6kejhz2v_Nfz5H_uO1gb-Gw_alIITCmmpD7dN-t2j3-3r0e3q-0E4M2n3d4S6d1iY7A-lC3kO-5h5y7e1Jz3I3f3YgJSXQ/360fx360f',
        category: 'new',
        items: [skins.awpDragon, skins.karambitDoppler, skins.glockFade],
    },
];

export const liveDrops: LiveDrop[] = [
    { id: 1, user: 'ProGamer', avatar: 'PG', skin: skins.karambitDoppler, caseName: 'Knife Case' },
    { id: 2, user: 'xXSlayerXx', avatar: 'XS', skin: skins.awpDragon, caseName: 'Phoenix Case' },
    { id: 3, user: 'SkinHunter', avatar: 'SH', skin: skins.m4Asiimov, caseName: 'Chroma Case' },
    { id: 4, user: 'LuckyOne', avatar: 'LO', skin: skins.akRedline, caseName: 'Spectrum Case' },
    { id: 5, user: 'NoScope', avatar: 'NS', skin: skins.uspKill, caseName: 'Gamma Case' },
    { id: 6, user: 'DropKing', avatar: 'DK', skin: skins.deagleBlaze, caseName: 'Danger Zone' },
    { id: 7, user: 'CaseMaster', avatar: 'CM', skin: skins.glockFade, caseName: 'Revolution Case' },
    { id: 8, user: 'WinStreak', avatar: 'WS', skin: skins.karambitDoppler, caseName: 'Knife Case' },
];

export const battles: Battle[] = [
    { id: 1, players: 2, maxPlayers: 2, cases: ['Phoenix Case', 'Chroma Case'], totalPrice: 256, status: 'waiting' },
    { id: 2, players: 3, maxPlayers: 4, cases: ['Knife Case'], totalPrice: 796, status: 'waiting' },
    { id: 3, players: 4, maxPlayers: 4, cases: ['Gamma Case', 'Spectrum Case'], totalPrice: 632, status: 'active' },
    { id: 4, players: 1, maxPlayers: 2, cases: ['Danger Zone', 'Dreams & Nightmares'], totalPrice: 216, status: 'waiting' },
    { id: 5, players: 2, maxPlayers: 2, cases: ['Revolution Case', 'Knife Case'], totalPrice: 576, status: 'active' },
];

export function formatPrice(price: number): string {
    return price.toLocaleString('ru-RU') + ' ₽';
}

export function getCaseById(id: number): Case | undefined {
    return cases.find((c) => c.id === id);
}
