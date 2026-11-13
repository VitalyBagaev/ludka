export type Rarity =
    | 'consumer'
    | 'industrial'
    | 'mil-spec'
    | 'restricted'
    | 'classified'
    | 'covert'
    | 'gold';

export interface Skin {
    id: number;
    name: string;
    weapon: string;
    rarity: Rarity;
    price: number;
    image: string;
}

export interface Case {
    id: number;
    name: string;
    price: number;
    image: string;
    category: string;
    featured?: boolean;
    items: Skin[];
}

export interface LiveDrop {
    id: number;
    user: string;
    avatar: string;
    skin: Skin;
    caseName: string;
}

export interface InventoryItem {
    id: number;
    skin: Skin;
    source: string;
    obtainedAt: string;
}

export interface Battle {
    id: number;
    players: number;
    maxPlayers: number;
    cases: string[];
    totalPrice: number;
    status: 'waiting' | 'active' | 'finished';
}
