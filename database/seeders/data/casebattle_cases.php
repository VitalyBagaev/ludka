<?php

/**
 * Каталог кейсов: цены USD × 74 → ₽.
 *
 * @return list<array{name: string, price: int, category: string, featured: bool, jackpots?: list<string>, skins?: list<string>, pool_size?: int, max_skin_price?: int}>
 */
return [
    // ── Серийные ──
    ['name' => 'Пионер', 'price' => 29, 'category' => 'serial', 'featured' => true, 'jackpots' => ['mp7_bloodsport', 'ak47_night_wish', 'm4a4_tooth_fairy']],
    ['name' => 'Скаут', 'price' => 49, 'category' => 'serial', 'featured' => false, 'jackpots' => ['redline_ak', 'water_elemental', 'cyrex']],
    ['name' => 'Следопыт', 'price' => 55, 'category' => 'serial', 'featured' => false, 'jackpots' => ['elite_build', 'guardian', 'hyper_beast']],
    ['name' => 'Рейнджер', 'price' => 68, 'category' => 'serial', 'featured' => false, 'jackpots' => ['vulcan', 'neon_rider', 'kill_confirmed']],
    ['name' => 'Авангард', 'price' => 98, 'category' => 'serial', 'featured' => true, 'jackpots' => ['fuel_injector', 'empress', 'asiimov_m4']],
    ['name' => 'Оперативник', 'price' => 109, 'category' => 'serial', 'featured' => false, 'jackpots' => ['printstream', 'wild_lotus', 'containment_breach']],
    ['name' => 'Элита', 'price' => 167, 'category' => 'serial', 'featured' => false, 'jackpots' => ['ak47_bloodsport', 'awp_chrome', 'deagle_code_red']],
    ['name' => 'Командир', 'price' => 197, 'category' => 'serial', 'featured' => true, 'jackpots' => ['ak47_inheritance', 'm4a4_temukau', 'neo_noir_awp']],
    ['name' => 'Капитан', 'price' => 246, 'category' => 'serial', 'featured' => false, 'jackpots' => ['awp_fade', 'glock_queen', 'usp_orion']],
    ['name' => 'Майор', 'price' => 296, 'category' => 'serial', 'featured' => false, 'jackpots' => ['howl', 'knife_gut_doppler', 'marble_fade']],
    ['name' => 'Полковник', 'price' => 394, 'category' => 'serial', 'featured' => false, 'jackpots' => ['doppler_knife', 'tiger_tooth', 'gloves_king_snake']],
    ['name' => 'Генерал', 'price' => 440, 'category' => 'serial', 'featured' => true, 'jackpots' => ['fade_knife', 'knife_bayonet_doppler', 'gloves_cobalt']],
    ['name' => 'Маршал', 'price' => 691, 'category' => 'serial', 'featured' => false, 'jackpots' => ['knife_butterfly_doppler', 'gloves_fade', 'knife_karambit_lore']],
    ['name' => 'Повелитель', 'price' => 889, 'category' => 'serial', 'featured' => false, 'jackpots' => ['knife_talon_fade', 'gloves_emerald_web', 'dragon_lore']],
    ['name' => 'Титан', 'price' => 989, 'category' => 'serial', 'featured' => false, 'jackpots' => ['knife_skeleton_crimson', 'gloves_superconductor', 'howl']],
    ['name' => 'Легенда', 'price' => 1483, 'category' => 'serial', 'featured' => true, 'jackpots' => ['ak47_xray', 'knife_skeleton_crimson', 'gloves_drought']],
    ['name' => 'Мифический', 'price' => 1977, 'category' => 'serial', 'featured' => false, 'jackpots' => ['gloves_pandora', 'knife_butterfly_gamma', 'dragon_lore']],
    ['name' => 'Бессмертный', 'price' => 2472, 'category' => 'serial', 'featured' => false, 'jackpots' => ['gloves_spearmint', 'knife_karambit_crimson', 'fade_knife']],
    ['name' => 'Верховный', 'price' => 4396, 'category' => 'serial', 'featured' => true, 'jackpots' => ['gloves_crimson_kimono', 'knife_stiletto_fade', 'dragon_lore']],

    // ── Премиум (5 000 — 100 000 ₽) ──
    ['name' => 'Бронза', 'price' => 5000, 'category' => 'premium', 'featured' => false, 'jackpots' => ['deagle_print', 'glock_gamma', 'usp_print']],
    ['name' => 'Серебро', 'price' => 10000, 'category' => 'premium', 'featured' => false, 'jackpots' => ['ak47_bloodsport', 'printstream', 'containment_breach']],
    ['name' => 'Золото', 'price' => 15000, 'category' => 'premium', 'featured' => true, 'jackpots' => ['wild_lotus', 'ak47_inheritance', 'awp_chrome']],
    ['name' => 'Платина', 'price' => 20000, 'category' => 'premium', 'featured' => false, 'jackpots' => ['awp_fade', 'ak47_inheritance', 'neo_noir_awp']],
    ['name' => 'Изумруд', 'price' => 25000, 'category' => 'premium', 'featured' => false, 'jackpots' => ['awp_fade', 'knife_gut_doppler', 'knife_shadow_fade']],
    ['name' => 'Рубин', 'price' => 30000, 'category' => 'premium', 'featured' => false, 'jackpots' => ['knife_huntsman_slaughter', 'knife_paracord_slaughter', 'knife_flip_marble']],
    ['name' => 'Сапфир', 'price' => 40000, 'category' => 'premium', 'featured' => true, 'jackpots' => ['knife_ursus_doppler', 'gloves_bloodhound', 'knife_flip_marble']],
    ['name' => 'Алмаз', 'price' => 50000, 'category' => 'premium', 'featured' => false, 'jackpots' => ['knife_bayonet_doppler', 'gloves_overprint', 'knife_bayonet_autotronic']],
    ['name' => 'Чёрный алмаз', 'price' => 75000, 'category' => 'premium', 'featured' => true, 'jackpots' => ['marble_fade', 'knife_stiletto_damascus', 'gloves_king_snake']],

    ['name' => 'Королевский', 'price' => 100000, 'category' => 'premium', 'featured' => true, 'max_skin_price' => 350000, 'skins' => [
        // дешёвая ступень 50%–80% (50k–80k) — 10 шт.
        'knife_paracord_slaughter', 'gloves_bloodhound', 'knife_bayonet_autotronic', 'knife_ursus_doppler',
        'royal_overprint_70', 'royal_ursus_70', 'gloves_overprint', 'knife_bayonet_doppler',
        'knife_stiletto_damascus', 'royal_shadow_78',

        // неокупаемые 80%–100% (80k–99k)
        'gloves_king_snake', 'knife_m9_autotronic', 'knife_classic_fade',

        // +10% (105–110k)
        'royal_m9_105', 'royal_karambit_110',

        // +50% (150k) — 4 шт.
        'royal_talon_150', 'royal_gloves_150', 'royal_butterfly_150', 'royal_karambit_150',

        // 200–350k
        'royal_butterfly_220', 'royal_gloves_280', 'royal_awp_350',

        // окупающие выше
        'knife_m9_lore', 'knife_karambit_autotronic', 'knife_karambit_lore',
        'knife_butterfly_gamma', 'knife_butterfly_doppler', 'gloves_vice', 'gloves_superconductor',
    ]],

    // ── Брендовые ──
    ['name' => '007', 'price' => 55, 'category' => 'branded', 'featured' => true, 'jackpots' => ['asiimov', 'ak47_aquamarine', 'mac10_pipe']],
    ['name' => 'Янтарь', 'price' => 29, 'category' => 'bonus', 'featured' => true, 'jackpots' => ['redline_ak', 'water_elemental', 'deagle_blaze']],
    ['name' => 'Стилеты', 'price' => 4396, 'category' => 'branded', 'featured' => true, 'jackpots' => ['knife_stiletto_tiger', 'knife_stiletto_fade', 'knife_stiletto_damascus']],
    ['name' => 'Дракон', 'price' => 1483, 'category' => 'branded', 'featured' => true, 'jackpots' => ['dragon_lore', 'awp_fade', 'howl']],
    ['name' => 'Неон', 'price' => 197, 'category' => 'image', 'featured' => false, 'jackpots' => ['neon_rider', 'm4a4_inliving', 'p250_visions']],

    // ── Ножевые ──
    ['name' => 'Кровавый', 'price' => 989, 'category' => 'knife', 'featured' => false, 'jackpots' => ['knife_butterfly_crimson', 'knife_karambit_crimson', 'knife_huntsman_slaughter']],
    ['name' => 'Ножевой', 'price' => 2472, 'category' => 'knife', 'featured' => true, 'jackpots' => ['doppler_knife', 'fade_knife', 'tiger_tooth', 'marble_fade']],
    ['name' => 'Ножи и перчатки', 'price' => 4396, 'category' => 'knife', 'featured' => true, 'jackpots' => ['knife_butterfly_doppler', 'gloves_pandora', 'gloves_superconductor', 'gloves_vice']],

    // ── Valve CS2 ──
    ['name' => 'Революция', 'price' => 38, 'category' => 'valve', 'featured' => true, 'jackpots' => ['ak47_headshot', 'm4a4_eye', 'ump_scaffold']],
    ['name' => 'Киловатт', 'price' => 58, 'category' => 'valve', 'featured' => true, 'jackpots' => ['awp_duality', 'ak47_inheritance', 'five7_scumbria']],
    ['name' => 'Лихорадка', 'price' => 58, 'category' => 'valve', 'featured' => false, 'jackpots' => ['mp7_abyssal', 'awp_exoskeleton', 'm4a4_inliving']],
    ['name' => 'Змеиный укус', 'price' => 55, 'category' => 'valve', 'featured' => false, 'jackpots' => ['m4a4_temukau', 'usp_tickets', 'mac10_allure']],
    ['name' => 'Опасная зона', 'price' => 55, 'category' => 'valve', 'featured' => false, 'jackpots' => ['awp_capillary', 'ak47_safety', 'deagle_meteorite']],
    ['name' => 'CS20', 'price' => 83, 'category' => 'valve', 'featured' => false, 'jackpots' => ['awp_chrome', 'famas_meow', 'mp9_mod']],
    ['name' => 'Горизонт', 'price' => 84, 'category' => 'valve', 'featured' => false, 'jackpots' => ['neo_noir', 'r8_llama', 'p90_offworld']],
    ['name' => 'Феникс', 'price' => 109, 'category' => 'valve', 'featured' => true, 'jackpots' => ['redline_ak', 'asiimov_m4', 'vulcan']],
    ['name' => 'Хрома 2', 'price' => 127, 'category' => 'valve', 'featured' => false, 'jackpots' => ['fade_glock', 'hyper_beast', 'mac10_allure']],
    ['name' => 'Отдача', 'price' => 138, 'category' => 'valve', 'featured' => false, 'jackpots' => ['ak47_inheritance', 'usp_print', 'sg553_triarch']],
    ['name' => 'Спектр', 'price' => 148, 'category' => 'valve', 'featured' => false, 'jackpots' => ['hyper_beast_m4', 'cz75_tacticat', 'ssg08_dragon']],
    ['name' => 'Сцепление', 'price' => 158, 'category' => 'valve', 'featured' => false, 'jackpots' => ['neo_noir_awp', 'mp9_goo', 'dual_shallow']],
    ['name' => 'Спектр 2', 'price' => 167, 'category' => 'valve', 'featured' => false, 'jackpots' => ['printstream', 'ak47_bloodsport', 'cz75_tacticat']],
    ['name' => 'Сны и кошмары', 'price' => 197, 'category' => 'valve', 'featured' => true, 'jackpots' => ['famas_meow', 'mp7_abyssal', 'knife_gut_doppler']],
    ['name' => 'Хрома', 'price' => 197, 'category' => 'valve', 'featured' => true, 'jackpots' => ['doppler_knife', 'marble_fade', 'knife_gut_doppler']],
    ['name' => 'Перелом', 'price' => 149, 'category' => 'valve', 'featured' => false, 'jackpots' => ['glock_gamma', 'm4a1s_lead', 'tec9_cut']],
    ['name' => 'Призма 2', 'price' => 197, 'category' => 'valve', 'featured' => false, 'jackpots' => ['containment_breach', 'm4a4_temukau', 'mac10_allure']],
    ['name' => 'Хрома 3', 'price' => 394, 'category' => 'valve', 'featured' => false, 'jackpots' => ['knife_flip_marble', 'knife_bayonet_doppler', 'p250_visions']],
    ['name' => 'Гамма', 'price' => 440, 'category' => 'valve', 'featured' => true, 'jackpots' => ['knife_gut_doppler', 'tiger_tooth', 'fade_knife']],
    ['name' => 'Гамма 2', 'price' => 691, 'category' => 'valve', 'featured' => false, 'jackpots' => ['doppler_knife', 'knife_huntsman_slaughter', 'famas_meow']],
    ['name' => 'Перчатки', 'price' => 2472, 'category' => 'valve', 'featured' => true, 'jackpots' => ['gloves_pandora', 'gloves_spearmint', 'gloves_fade', 'gloves_superconductor']],

    // ── Лимитированный ──
    ['name' => 'Лимитированный', 'price' => 68, 'category' => 'limited', 'featured' => true, 'jackpots' => ['printstream', 'wild_lotus', 'knife_ursus_doppler'], 'pool_size' => 35],

    // ── Пасхалка ──
    ['name' => '??? Кейс', 'price' => 0, 'category' => 'secret', 'featured' => false, 'skins' => [
        'tiger_tooth', 'doppler_knife', 'knife_karambit_autotronic', 'knife_karambit_lore',
        'knife_m9_lore', 'gloves_drought', 'knife_karambit_crimson', 'gloves_emerald_web',
        'gloves_fade', 'knife_stiletto_tiger', 'gloves_superconductor', 'knife_skeleton_crimson',
        'knife_talon_fade', 'gloves_spearmint', 'fade_knife', 'knife_stiletto_fade',
        'knife_butterfly_crimson', 'gloves_crimson_kimono', 'knife_butterfly_gamma',
        'knife_butterfly_doppler', 'gloves_pandora', 'howl', 'dragon_lore',
    ]],
];
