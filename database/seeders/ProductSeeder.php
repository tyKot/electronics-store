<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('ru_RU');
        // Убедимся, что категории и бренды существуют
        $categories = Category::pluck('id', 'slug')->toArray();
        $brands = Brand::pluck('name', 'slug')->toArray();

        if (empty($categories) || empty($brands)) {
            $this->command->error('Сначала запустите DatabaseSeeder для создания категорий и брендов!');
            return;
        }

        // Шаблоны товаров по категориям
        $templates = [
            'smartphones' => [
                'names' => ['Galaxy S24 Ultra', 'iPhone 15 Pro Max', 'Pixel 8 Pro', 'Xiaomi 14 Ultra', 'OnePlus 12', 'Nothing Phone (2)', 'Sony Xperia 1 V', 'Honor Magic6 Pro'],
                'specs_fn' => fn($faker) => [
                    'Экран' => $faker->randomElement(['6.1"', '6.7"', '6.8"']) . ' ' . $faker->randomElement(['AMOLED', 'LTPO OLED', 'Super Retina XDR']),
                    'Процессор' => $faker->randomElement(['Snapdragon 8 Gen 3', 'A17 Pro', 'Tensor G3', 'Dimensity 9300']),
                    'Память' => $faker->randomElement(['128 ГБ', '256 ГБ', '512 ГБ', '1 ТБ']),
                    'ОЗУ' => $faker->randomElement(['8 ГБ', '12 ГБ', '16 ГБ']),
                    'Камера' => $faker->randomElement(['48 МП', '50 МП', '200 МП']) . ' + ' . $faker->randomElement(['12 МП', '50 МП']) . ' + ' . $faker->randomElement(['10 МП', '12 МП']),
                    'Аккумулятор' => $faker->numberBetween(4000, 5500) . ' мАч',
                    'ОС' => $faker->randomElement(['Android 14', 'iOS 17', 'iOS 18']),
                ],
                'price_range' => [29990, 159990],
            ],
            'laptops' => [
                'names' => ['MacBook Pro 14 M3', 'ThinkPad X1 Carbon', 'ROG Zephyrus G14', 'Dell XPS 15', 'HP Spectre x360', 'ASUS ZenBook 14', 'Lenovo IdeaPad Slim 5', 'Acer Swift Go 14'],
                'specs_fn' => fn($faker) => [
                    'Процессор' => $faker->randomElement(['Apple M3', 'Apple M3 Pro', 'Intel Core i7-13700H', 'AMD Ryzen 9 7940HS', 'Intel Core Ultra 7']),
                    'ОЗУ' => $faker->randomElement(['16 ГБ', '32 ГБ', '64 ГБ']),
                    'Накопитель' => $faker->randomElement(['512 ГБ SSD', '1 ТБ SSD', '2 ТБ SSD']),
                    'Экран' => $faker->randomElement(['14"', '15.6"', '16"']) . ' ' . $faker->randomElement(['IPS', 'OLED', 'Liquid Retina XDR', 'Mini-LED']),
                    'Видеокарта' => $faker->randomElement(['Интегрированная', 'RTX 4060', 'RTX 4070', 'Apple M3 GPU']),
                    'Автономность' => 'до ' . $faker->numberBetween(8, 22) . ' ч',
                    'Вес' => $faker->randomFloat(2, 0.9, 2.5) . ' кг',
                ],
                'price_range' => [54990, 299990],
            ],
            'headphones' => [
                'names' => ['AirPods Pro 2', 'WH-1000XM5', 'Galaxy Buds2 Pro', 'Momentum TW 4', 'QuietComfort Ultra', 'Beats Fit Pro', 'JBL Tour Pro 3', 'Sennheiser IE 600'],
                'specs_fn' => fn($faker) => [
                    'Тип' => $faker->randomElement(['TWS', 'Накладные', 'Внутриканальные']),
                    'Шумоподавление' => $faker->randomElement(['Активное (ANC)', 'Пассивное', 'Адаптивное ANC']),
                    'Автономность' => $faker->numberBetween(4, 40) . ' ч',
                    'Bluetooth' => $faker->randomElement(['5.2', '5.3', '5.4']),
                    'Кодеки' => $faker->randomElement(['AAC, SBC', 'LDAC, AAC', 'aptX Adaptive, AAC']),
                    'Защита' => $faker->randomElement(['IPX4', 'IPX5', 'IP57']),
                ],
                'price_range' => [4990, 39990],
            ],
            'tablets' => [
                'names' => ['iPad Air M2', 'Galaxy Tab S9 Ultra', 'MatePad Pro 13.2', 'Xiaomi Pad 6S Pro', 'Lenovo Tab Extreme', 'iPad mini 6', 'Samsung Galaxy Tab A9+', 'Redmi Pad Pro'],
                'specs_fn' => fn($faker) => [
                    'Экран' => $faker->randomElement(['8.3"', '11"', '12.9"', '14.6"']) . ' ' . $faker->randomElement(['Liquid Retina', 'AMOLED', 'IPS']),
                    'Процессор' => $faker->randomElement(['Apple M2', 'Snapdragon 8 Gen 2', 'Kirin 9000S', 'Snapdragon 8 Gen 3']),
                    'Память' => $faker->randomElement(['64 ГБ', '128 ГБ', '256 ГБ', '512 ГБ']),
                    'ОЗУ' => $faker->randomElement(['6 ГБ', '8 ГБ', '12 ГБ']),
                    'Аккумулятор' => $faker->numberBetween(6000, 12000) . ' мАч',
                ],
                'price_range' => [19990, 129990],
            ],
            'smartwatches' => [
                'names' => ['Apple Watch Ultra 2', 'Galaxy Watch6 Classic', 'Pixel Watch 2', 'Garmin Fenix 7X', 'Huawei Watch GT 4', 'Amazfit T-Rex 2', 'Withings ScanWatch 2', 'Samsung Galaxy Ring'],
                'specs_fn' => fn($faker) => [
                    'Экран' => $faker->randomElement(['1.9"', '1.5"', '1.4"']) . ' ' . $faker->randomElement(['AMOLED', 'Retina', 'MIP']),
                    'Автономность' => $faker->randomElement(['1 день', '2 дня', '7 дней', '14 дней', 'до 30 дней']),
                    'Защита' => $faker->randomElement(['5ATM', 'IP68', 'MIL-STD-810H', '10ATM']),
                    'Датчики' => $faker->randomElement(['SpO2, ЭКГ, GPS', 'SpO2, GPS, барометр', 'ЭКГ, температура, SpO2']),
                    'Материал' => $faker->randomElement(['Титан', 'Нержавеющая сталь', 'Алюминий', 'Полимер']),
                ],
                'price_range' => [9990, 89990],
            ],
        ];

        $products = [];
        $now = now();

        foreach ($templates as $categorySlug => $template) {
            $categoryId = $categories[$categorySlug] ?? null;
            if (!$categoryId)
                continue;

            foreach ($template['names'] as $baseName) {
                // Генерируем несколько вариаций каждого товара
                $variants = rand(2, 4);
                for ($i = 0; $i < $variants; $i++) {
                    $brandSlug = array_rand($brands);
                    $brandName = $brands[$brandSlug];

                    $price = rand($template['price_range'][0], $template['price_range'][1]);
                    $hasDiscount = rand(1, 100) <= 30; // 30% товаров со скидкой
                    $oldPrice = $hasDiscount ? (int) ($price * rand(110, 140) / 100) : null;

                    $name = $brandName . ' ' . $baseName;
                    if ($variants > 1) {
                        $name .= ' ' . $faker->randomElement(['Black', 'White', 'Silver', 'Titanium', 'Midnight', 'Starlight']);
                    }

                    $products[] = [
                        'name' => $name,
                        'slug' => Str::slug($name) . '-' . Str::uuid()->toString(),
                        'description' => $faker->paragraphs(rand(2, 4), true),
                        'short_description' => $faker->sentence(rand(8, 15)),
                        'price' => $price,
                        'old_price' => $oldPrice,
                        'brand' => $brandName,
                        'specs' => json_encode(($template['specs_fn'])($faker), JSON_UNESCAPED_UNICODE),
                        'images' => json_encode([
                            "https://placehold.co/800x800/e2e8f0/475569?text=" . urlencode($name),
                            "https://placehold.co/800x800/f1f5f9/475569?text=" . urlencode($name . '+side'),
                        ], JSON_UNESCAPED_UNICODE),
                        'stock' => rand(0, 50),
                        'is_new' => rand(1, 100) <= 20,
                        'is_featured' => rand(1, 100) <= 15,
                        'is_active' => true,
                        'category_id' => $categoryId,
                        'created_at' => $now->copy()->subDays(rand(0, 90)),
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // Вставляем батчами для производительности
        foreach (array_chunk($products, 50) as $chunk) {
            Product::insert($chunk);
        }

        $this->command->info("✅ Создано " . count($products) . " товаров");
    }
}
