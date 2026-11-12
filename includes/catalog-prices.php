<?php
/**
 * Rwandan retail price list for the seeded Cypadi Shop catalogue.
 *
 * The demonstration catalogue was imported from an electronics shop that quoted
 * its prices in US dollars, and those dollar figures were being printed with an
 * "RWF" prefix — a Tecno Spark 20 advertised at "RWF 249" instead of the roughly
 * RWF 265,000 it actually sells for in Kigali. This file replaces those figures
 * with prices a Rwandan customer would recognise.
 *
 * Each entry is keyed by the product name as it was imported, because that is
 * the only stable identifier shared by the seed data in database.sql and the
 * live database. `name` is the corrected display name (the import truncated
 * every name to the column width and carried Uzbek labels such as "Planshet"
 * and "Noutbuk" across from the source shop), and `price_current` / `price_old`
 * are the selling price and the struck-through former price, in whole francs.
 *
 * The prices reflect mid-range Kigali retail for the given model and storage
 * variant: entry phones RWF 130,000-200,000, mid-range phones RWF 250,000-600,000,
 * flagships RWF 950,000-2,500,000, 32-inch smart televisions around RWF 165,000,
 * 43-inch around RWF 300,000-600,000, tablets RWF 135,000-620,000 and laptops
 * RWF 620,000-1,250,000.
 *
 * Display names must stay within products.name VARCHAR(30).
 */
return [
    // ---------------------------------------------------------------- phones
    'Tecno Spark 20' => [
        'name' => 'Tecno Spark 20 8/256',
        'price_current' => 265000,
        'price_old' => 320000,
    ],
    'Smartfon Infinix Note 40Pro' => [
        'name' => 'Infinix Note 40 Pro 12/256',
        'price_current' => 425000,
        'price_old' => 520000,
    ],
    'Samsung Galaxy A55' => [
        'name' => 'Samsung Galaxy A55 5G',
        'price_current' => 575000,
        'price_old' => 690000,
    ],
    'Samsung Galaxy A15' => [
        'name' => 'Samsung Galaxy A15 6/128',
        'price_current' => 255000,
        'price_old' => 320000,
    ],
    'Samsung Galaxy S24 Ultra' => [
        'name' => 'Samsung Galaxy S24 Ultra',
        'price_current' => 1850000,
        'price_old' => 2300000,
    ],
    'Xiaomi Redmi A3' => [
        'name' => 'Xiaomi Redmi A3 4/128',
        'price_current' => 145000,
        'price_old' => 190000,
    ],
    'Infinix Hot 40' => [
        'name' => 'Infinix Hot 40 8/128',
        'price_current' => 185000,
        'price_old' => 240000,
    ],
    'Apple iPhone 13' => [
        'name' => 'Apple iPhone 13 128GB',
        'price_current' => 950000,
        'price_old' => 1150000,
    ],
    'IPhone 15 Pro/Promax' => [
        'name' => 'iPhone 15 Pro Max 256GB',
        'price_current' => 1750000,
        'price_old' => 2100000,
    ],
    'Honor X7b 8/128 GB' => [
        'name' => 'Honor X7b 8/128 GB',
        'price_current' => 285000,
        'price_old' => 350000,
    ],
    'Xiaomi Redmi 13C' => [
        'name' => 'Xiaomi Redmi 13C 8/256',
        'price_current' => 195000,
        'price_old' => 250000,
    ],
    'Samsung Galaxy A24,' => [
        'name' => 'Samsung Galaxy A24 6/128',
        'price_current' => 295000,
        'price_old' => 360000,
    ],

    // ------------------------------------------------------------ televisions
    'Televizor AVAX Android 12 Smar' => [
        'name' => 'AVAX 32 Android 12 Smart TV',
        'price_current' => 175000,
        'price_old' => 230000,
    ],
    'Televizor LiTV Android 12 Smar' => [
        'name' => 'LiTV 32 Android 12 Smart TV',
        'price_current' => 165000,
        'price_old' => 220000,
    ],
    'Televizor MoonX 43' => [
        'name' => 'MoonX 43 Full HD Smart TV',
        'price_current' => 295000,
        'price_old' => 380000,
    ],
    'Televizor QLT 32' => [
        'name' => 'QLT 32 Smart TV Android 12',
        'price_current' => 165000,
        'price_old' => 210000,
    ],
    'Televizor Samsung Crystal UHD' => [
        'name' => 'Samsung Crystal UHD 43 Smart',
        'price_current' => 520000,
        'price_old' => 620000,
    ],
    'smart televizori Xiaomi Mi TV' => [
        'name' => 'Xiaomi Mi TV A2 43 Smart',
        'price_current' => 480000,
        'price_old' => 580000,
    ],
    'Smart televizor TCL 43' => [
        'name' => 'TCL 43 Google TV 4K',
        'price_current' => 430000,
        'price_old' => 520000,
    ],
    'Smart televizor WellStars 32' => [
        'name' => 'WellStars 32 Smart TV',
        'price_current' => 165000,
        'price_old' => 210000,
    ],
    'Televizor JVC 32N3105 HD Smart' => [
        'name' => 'JVC 32 HD Smart TV',
        'price_current' => 175000,
        'price_old' => 225000,
    ],
    'Televizor Sonor 32SNR100S Smar' => [
        'name' => 'Sonor 32 Smart TV',
        'price_current' => 165000,
        'price_old' => 220000,
    ],
    'Televizor Premier 32PRM700S Sm' => [
        'name' => 'Premier 32 Smart TV',
        'price_current' => 175000,
        'price_old' => 230000,
    ],
    'Televizor Artel MF3300' => [
        'name' => 'Artel MF3300 43 Full HD',
        'price_current' => 585000,
        'price_old' => 690000,
    ],

    // --------------------------------------------------------------- tablets
    'Planshet Xiaomi Redmi Pad SE' => [
        'name' => 'Xiaomi Redmi Pad SE 8/256',
        'price_current' => 285000,
        'price_old' => 350000,
    ],
    'Planshet Oukitel OT6, 2024 mod' => [
        'name' => 'Oukitel OT6 4/64 GB',
        'price_current' => 155000,
        'price_old' => 195000,
    ],
    'Smart planshet CCIT A 103 PRO' => [
        'name' => 'CCIT A103 Pro 8/512',
        'price_current' => 210000,
        'price_old' => 280000,
    ],
    // Imported with a marketplace listing title as its name: "5.0 ( 101 baho )
    // 400 ta buyurt" is a star rating and an order count. The description
    // identifies the device - Snapdragon 870, 11-inch 2880x1800, 8840 mAh.
    '5.0 ( 101 baho ) 400 ta buyurt' => [
        'name' => 'Xiaomi Pad 6 11 8/256 GB',
        'price_current' => 620000,
        'price_old' => 780000,
    ],
    'Planshet Modio M28' => [
        'name' => 'Modio M28 6/128',
        'price_current' => 195000,
        'price_old' => 250000,
    ],
    'Planshet Nextbook BRT81' => [
        'name' => 'Nextbook BRT81 3/32',
        'price_current' => 135000,
        'price_old' => 175000,
    ],
    'Smart Planshet, A1000 Pro,' => [
        'name' => 'A1000 Pro 4/128 4G',
        'price_current' => 165000,
        'price_old' => 220000,
    ],
    'Planshet Blackview Tab 80' => [
        'name' => 'Blackview Tab 80 4/128',
        'price_current' => 245000,
        'price_old' => 310000,
    ],
    'Planshet Blackview Tab 13 Pro' => [
        'name' => 'Blackview Tab 13 Pro 8/128',
        'price_current' => 320000,
        'price_old' => 400000,
    ],
    'Planshet Blackview TabActive 8' => [
        'name' => 'Blackview TabActive 8 8/256',
        'price_current' => 520000,
        'price_old' => 620000,
    ],
    'Planshet Blackview Tab 18' => [
        'name' => 'Blackview Tab 18 8/256',
        'price_current' => 510000,
        'price_old' => 610000,
    ],
    'Planshet Teclast T40 Air 10' => [
        'name' => 'Teclast T40 Air 8/256',
        'price_current' => 320000,
        'price_old' => 400000,
    ],

    // ------------------------------------------------------------- computers
    'Oyin monitori 2E Gaming 27' => [
        'name' => '2E Gaming Monitor 27 165Hz',
        'price_current' => 295000,
        'price_old' => 360000,
    ],
    'Noutbuk Asus TUF GAMING Intel' => [
        'name' => 'Asus TUF Gaming F15 i7',
        'price_current' => 1250000,
        'price_old' => 1500000,
    ],
    'HP Victus 15 - 15,6' => [
        'name' => 'HP Victus 15 Ryzen 5',
        'price_current' => 1050000,
        'price_old' => 1200000,
    ],
    'MSI GF63 12UCX 8/512 Nvidia RT' => [
        'name' => 'MSI GF63 i5 RTX 2050',
        'price_current' => 1000000,
        'price_old' => 1150000,
    ],
    'Noutbuk Lenovo LOQ, i5' => [
        'name' => 'Lenovo LOQ 15 i5 RTX 2050',
        'price_current' => 1050000,
        'price_old' => 1300000,
    ],
    'Noutbuk Acer Aspire 3 Core i3' => [
        'name' => 'Acer Aspire 3 Core i3',
        'price_current' => 620000,
        'price_old' => 780000,
    ],
    // The placeholder that was used to prove the payment flow: it was priced at
    // 5, read as RWF 5, so a test deposit cost almost nothing. It is a real HP
    // business laptop, so it now carries that laptop's price.
    'HP' => [
        'name' => 'HP 250 G9 Core i5',
        'price_current' => 745000,
        'price_old' => 880000,
    ],
];
