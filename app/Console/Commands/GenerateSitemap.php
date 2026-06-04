<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('sitemap:generate')]
#[Description('Генерация sitemap.xml для ботов и SEO')]
class GenerateSitemap extends Command
{
    public function handle(): void
    {
        $sitemap = Sitemap::create()
            // Главная страница
            ->add(Url::create('/')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0))

            // Каталог
            ->add(Url::create('/catalog')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
                ->setPriority(0.9))

            // Категории
            ->add(Url::create('/categories')
                ->setLastModificationDate(now())
                ->setPriority(0.8));


        // Все категории
        Category::where('is_active', true)->get()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(
                Url::create("/catalog?filter[category]={$category->slug}")
                    ->setLastModificationDate($category->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7)
            );
        });

        // Все товары (самое важное для бота!)
        Product::where('is_active', true)->get()->each(function (Product $product) use ($sitemap) {
            $url = Url::create("/products/{$product->slug}")
                ->setLastModificationDate($product->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.6);

            // Добавляем изображения для богатых сниппетов
            if (!empty($product->images)) {
                foreach ($product->images as $image) {
                    $url->addImage($image, $product->name);
                }
            }

            $sitemap->add($url);
        });

        // Сохраняем в public
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('✅ Sitemap.xml успешно сгенерирован!');
    }
}
