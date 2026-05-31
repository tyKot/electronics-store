<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём админа
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // Создаём обычного пользователя
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        // Категории
        $categories = [
            ['name' => 'Смартфоны', 'slug' => 'smartphones'],
            ['name' => 'Ноутбуки', 'slug' => 'laptops'],
            ['name' => 'Планшеты', 'slug' => 'tablets'],
            ['name' => 'Наушники', 'slug' => 'headphones'],
            ['name' => 'Умные часы', 'slug' => 'smartwatches'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Бренды
        $brands = [
            ['name' => 'Apple', 'slug' => 'apple', 'website' => 'https://apple.com'],
            ['name' => 'Samsung', 'slug' => 'samsung', 'website' => 'https://samsung.com'],
            ['name' => 'Xiaomi', 'slug' => 'xiaomi', 'website' => 'https://mi.com'],
            ['name' => 'Sony', 'slug' => 'sony', 'website' => 'https://sony.com'],
            ['name' => 'Huawei', 'slug' => 'huawei', 'website' => 'https://huawei.com'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['slug' => $brand['slug']], $brand);
        }

        // Товары
        $smartphones = Category::where('slug', 'smartphones')->first();
        $laptops = Category::where('slug', 'laptops')->first();

        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'price' => 129990,
                'old_price' => 139990,
                'brand' => 'Apple',
                'category_id' => $smartphones->id,
                'stock' => 15,
                'is_new' => true,
                'is_featured' => true,
                'short_description' => 'Титановый корпус, камера 48 МП, чип A17 Pro',
                'description' => 'iPhone 15 Pro — самый продвинутый iPhone с титановым дизайном, мощным чипом A17 Pro и профессиональной камерой.',
                'specs' => [
                    'Экран' => '6.1" Super Retina XDR',
                    'Процессор' => 'A17 Pro',
                    'Память' => '256 ГБ',
                    'Камера' => '48 МП + 12 МП + 12 МП',
                    'Аккумулятор' => '3274 мАч',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800',
                    'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=800',
                ],
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'slug' => 'samsung-galaxy-s24-ultra',
                'price' => 119990,
                'brand' => 'Samsung',
                'category_id' => $smartphones->id,
                'stock' => 20,
                'is_new' => true,
                'is_featured' => true,
                'short_description' => 'AI-функции, S Pen, камера 200 МП',
                'description' => 'Galaxy S24 Ultra с искусственным интеллектом Galaxy AI и встроенным S Pen.',
                'specs' => [
                    'Экран' => '6.8" Dynamic AMOLED 2X',
                    'Процессор' => 'Snapdragon 8 Gen 3',
                    'Память' => '512 ГБ',
                    'Камера' => '200 МП + 12 МП + 50 МП + 10 МП',
                    'Аккумулятор' => '5000 мАч',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=800',
                ],
            ],
            [
                'name' => 'MacBook Pro 14" M3',
                'slug' => 'macbook-pro-14-m3',
                'price' => 199990,
                'brand' => 'Apple',
                'category_id' => $laptops->id,
                'stock' => 8,
                'is_featured' => true,
                'short_description' => 'Чип M3 Pro, 18 ГБ RAM, дисплей Liquid Retina XDR',
                'description' => 'MacBook Pro с чипом M3 Pro для профессиональной работы.',
                'specs' => [
                    'Процессор' => 'Apple M3 Pro',
                    'Оперативная память' => '18 ГБ',
                    'Накопитель' => '512 ГБ SSD',
                    'Экран' => '14.2" Liquid Retina XDR',
                    'Автономность' => 'до 17 часов',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800',
                ],
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['slug' => $product['slug']], $product);
        }

        // Отзывы
        $user = User::where('email', 'user@example.com')->first();
        $iphone = Product::where('slug', 'iphone-15-pro')->first();

        if ($iphone && $user) {
            Review::create([
                'product_id' => $iphone->id,
                'user_id' => $user->id,
                'rating' => 5,
                'title' => 'Отличный смартфон',
                'comment' => 'Камера просто невероятная, титановый корпус очень приятный на ощупь.',
                'pros' => ['Камера', 'Дизайн', 'Производительность'],
                'cons' => ['Цена'],
                'is_verified' => true,
            ]);
        }
    }
}
