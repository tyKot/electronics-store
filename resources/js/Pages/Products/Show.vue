<script setup>
import { ref, computed } from "vue";
import { router, Link } from "@inertiajs/vue3";
import Galleria from "primevue/galleria";
import Button from "primevue/button";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import Tag from "primevue/tag";
import Divider from "primevue/divider";
import InputNumber from "primevue/inputnumber";
import { useCartStore } from "@/stores/cart";
import { useToast } from "primevue/usetoast";
import placeholderImg from "@/images/placeholder.webp";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    product: Object,
    related: Array,
});

const cart = useCartStore();
const toast = useToast();
const quantity = ref(1);

const specsArray = computed(() =>
    Object.entries(props.product.specs || {}).map(([key, value]) => ({
        feature: key,
        value: value,
    })),
);

const specsColumns = computed(() => {
    const half = Math.ceil(specsArray.value.length / 2);
    return [specsArray.value.slice(0, half), specsArray.value.slice(half)];
});

const discountPercent = computed(() => {
    if (
        !props.product.old_price ||
        props.product.old_price <= props.product.price
    )
        return 0;
    return Math.round(
        ((props.product.old_price - props.product.price) /
            props.product.old_price) *
            100,
    );
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("ru-RU").format(price);
};

const galleriaImages = computed(() => {
    (props.product.images || []).map((img) => {
        console.log({
            img:img,
            itemImageSrc: placeholderImg,
            thumbnailImageSrc: placeholderImg,
            alt: props.product.name,
        });
        return {
            itemImageSrc: img,
            thumbnailImageSrc: img,
            alt: props.product.name,
        };
    });
});

const addToCart = () => {
    cart.addToCart({
        id: props.product.id,
        name: props.product.name,
        price: props.product.price,
        image: props.product.images?.[0] || placeholderImg,
        quantity: quantity.value,
    });
    toast.add({
        severity: "success",
        summary: "Добавлено в корзину",
        detail: `${props.product.name} × ${quantity.value}`,
        life: 3000,
    });
};

const buyNow = () => {
    addToCart();
    router.visit(route("checkout.index"));
};

const addToWishlist = () => {
    toast.add({
        severity: "info",
        summary: "Добавлено в избранное",
        detail: props.product.name,
        life: 3000,
    });
};

// ✅ Добавление похожего товара в корзину
const addRelatedToCart = (item) => {
    cart.addToCart({
        id: item.id,
        name: item.name,
        price: item.price,
        image: item.images?.[0] || item.image || "/images/placeholder.webp",
        quantity: 1,
    });

    toast.add({
        severity: "success",
        summary: "Добавлено в корзину",
        detail: `${item.name} × 1`,
        life: 3000,
    });
};

// ✅ Быстрая покупка похожего товара
const buyRelatedNow = (item) => {
    addRelatedToCart(item);
    router.visit(route("checkout.index"));
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="container mx-auto px-6 py-8">
            <!-- Хлебные крошки -->
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-8">
                <Link
                    :href="route('home')"
                    class="hover:text-blue-600 transition-colors"
                    >Главная</Link
                >
                <i class="pi pi-angle-right text-xs" />
                <Link
                    :href="route('catalog.index')"
                    class="hover:text-blue-600 transition-colors"
                    >Каталог</Link
                >
                <i class="pi pi-angle-right text-xs" />
                <Link
                    v-if="product.category"
                    :href="
                        route('catalog.index', {
                            'filter[category_id]': product.category.id,
                        })
                    "
                    class="hover:text-blue-600 transition-colors"
                >
                    {{ product.category.name }}
                </Link>
                <i v-if="product.category" class="pi pi-angle-right text-xs" />
                <span class="text-slate-900 font-medium truncate">{{
                    product.name
                }}</span>
            </nav>

            <!-- Основной блок: Галерея + Информация -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8 mb-8"
            >
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                    <!-- Галерея изображений -->
                    <div class="relative">
                        <div
                            class="absolute top-4 left-4 z-20 flex flex-col gap-2"
                        >
                            <span
                                v-if="discountPercent > 0"
                                class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-sm font-bold rounded-lg shadow-lg"
                            >
                                -{{ discountPercent }}%
                            </span>
                            <span
                                v-if="product.is_new"
                                class="inline-flex items-center px-3 py-1.5 bg-emerald-500 text-white text-sm font-bold rounded-lg shadow-lg"
                            >
                                NEW
                            </span>
                            <span
                                v-if="product.is_featured"
                                class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white text-sm font-bold rounded-lg shadow-lg"
                            >
                                ⭐ ХИТ
                            </span>
                        </div>

                        <Galleria
                            v-if="galleriaImages?.length || [].length > 0"
                            :value="galleriaImages"
                            :numVisible="5"
                            containerStyle="max-width: 100%"
                            :showThumbnails="galleriaImages.length > 1"
                            :showItemNavigators="true"
                            :showItemNavigatorsOnHover="true"
                            :circular="true"
                        >
                            <template #item="slotProps">
                                <div
                                    class="bg-slate-50 rounded-xl overflow-hidden flex items-center justify-center aspect-square"
                                >
                                    <img
                                        :src="slotProps.item.itemImageSrc"
                                        :alt="slotProps.item.alt"
                                        class="w-full h-full object-contain p-4"
                                    />
                                </div>
                            </template>
                            <template #thumbnail="slotProps">
                                <div
                                    class="bg-slate-50 rounded-lg overflow-hidden border-2 border-transparent hover:border-blue-500 transition-colors"
                                >
                                    <img
                                        :src="slotProps.item.thumbnailImageSrc"
                                        :alt="slotProps.item.alt"
                                        class="w-20 h-20 object-contain p-1"
                                    />
                                </div>
                            </template>
                        </Galleria>

                        <div
                            v-else
                            class="aspect-square bg-slate-100 rounded-xl flex items-center justify-center"
                        >
                            <i class="pi pi-image text-6xl text-slate-300" />
                        </div>
                    </div>

                    <!-- Информация о товаре -->
                    <div class="flex flex-col">
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="text-sm font-semibold text-blue-600 uppercase tracking-wider"
                            >
                                {{ product.brand }}
                            </span>
                            <div class="flex items-center gap-1 text-amber-400">
                                <i class="pi pi-star-fill text-sm" />
                                <i class="pi pi-star-fill text-sm" />
                                <i class="pi pi-star-fill text-sm" />
                                <i class="pi pi-star-fill text-sm" />
                                <i class="pi pi-star text-sm text-slate-300" />
                                <span class="text-sm text-slate-500 ml-2"
                                    >(24 отзыва)</span
                                >
                            </div>
                        </div>

                        <h1
                            class="text-3xl md:text-4xl font-bold text-slate-900 mb-4 leading-tight"
                        >
                            {{ product.name }}
                        </h1>

                        <p
                            v-if="product.short_description"
                            class="text-lg text-slate-600 mb-6 leading-relaxed"
                        >
                            {{ product.short_description }}
                        </p>

                        <div class="flex items-center gap-2 mb-6">
                            <span
                                v-if="product.stock > 0"
                                class="flex items-center gap-2 text-emerald-600 font-medium"
                            >
                                <span
                                    class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"
                                />
                                В наличии
                                <span
                                    v-if="product.stock < 10"
                                    class="text-sm text-orange-600 ml-2"
                                >
                                    (осталось {{ product.stock }} шт.)
                                </span>
                            </span>
                            <span
                                v-else
                                class="flex items-center gap-2 text-slate-500"
                            >
                                <i class="pi pi-times-circle" />
                                Нет в наличии
                            </span>
                        </div>

                        <Divider />

                        <div class="mb-6">
                            <div class="flex items-baseline gap-3 mb-2">
                                <span class="text-4xl font-bold text-slate-900">
                                    {{ formatPrice(product.price) }} ₽
                                </span>
                                <span
                                    v-if="
                                        product.old_price &&
                                        product.old_price > product.price
                                    "
                                    class="text-xl text-slate-400 line-through"
                                >
                                    {{ formatPrice(product.old_price) }} ₽
                                </span>
                            </div>
                            <p
                                v-if="discountPercent > 0"
                                class="text-sm text-emerald-600 font-medium"
                            >
                                <i class="pi pi-tag mr-1" />
                                Вы экономите
                                {{
                                    formatPrice(
                                        product.old_price - product.price,
                                    )
                                }}
                                ₽
                            </p>
                        </div>

                        <div class="flex items-center gap-4 mb-6">
                            <span class="text-slate-600 font-medium"
                                >Количество:</span
                            >
                            <InputNumber
                                v-model="quantity"
                                showButtons
                                :min="1"
                                :max="product.stock || 99"
                                class="w-32"
                                inputClass="w-32"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                            <Button
                                @click="addToCart"
                                label="В корзину"
                                icon="pi pi-shopping-cart"
                                size="large"
                                :disabled="product.stock === 0"
                                class="justify-center"
                            />
                            <Button
                                @click="buyNow"
                                label="Купить сейчас"
                                icon="pi pi-bolt"
                                size="large"
                                severity="success"
                                :disabled="product.stock === 0"
                                class="justify-center"
                            />
                        </div>

                        <Button
                            @click="addToWishlist"
                            label="В избранное"
                            icon="pi pi-heart"
                            severity="secondary"
                            outlined
                            class="w-full justify-center mb-6"
                        />

                        <div
                            class="grid grid-cols-3 gap-4 pt-6 border-t border-slate-100"
                        >
                            <div class="text-center">
                                <i
                                    class="pi pi-truck text-2xl text-blue-600 mb-2"
                                />
                                <p class="text-xs text-slate-600">
                                    Доставка<br />1-3 дня
                                </p>
                            </div>
                            <div class="text-center">
                                <i
                                    class="pi pi-shield text-2xl text-blue-600 mb-2"
                                />
                                <p class="text-xs text-slate-600">
                                    Гарантия<br />до 3 лет
                                </p>
                            </div>
                            <div class="text-center">
                                <i
                                    class="pi pi-credit-card text-2xl text-blue-600 mb-2"
                                />
                                <p class="text-xs text-slate-600">
                                    Рассрочка<br />0-0-12
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Табы -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8 mb-8"
            >
                <TabView>
                    <TabPanel header="Описание">
                        <div class="prose prose-slate max-w-none py-4">
                            <p
                                class="text-slate-700 leading-relaxed text-lg whitespace-pre-line"
                            >
                                {{
                                    product.description ||
                                    "Описание товара будет добавлено позже."
                                }}
                            </p>
                        </div>
                    </TabPanel>

                    <TabPanel :header="`Характеристики (${specsArray.length})`">
                        <div v-if="specsArray.length > 0" class="py-4">
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-3"
                            >
                                <div
                                    v-for="(spec, index) in specsArray"
                                    :key="index"
                                    class="flex justify-between items-baseline py-3 border-b border-slate-100 last:border-0"
                                >
                                    <span class="text-slate-500">{{
                                        spec.feature
                                    }}</span>
                                    <span
                                        class="text-slate-900 font-medium text-right ml-4"
                                        >{{ spec.value }}</span
                                    >
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center text-slate-500">
                            <i class="pi pi-info-circle text-4xl mb-3" />
                            <p>Характеристики пока не указаны</p>
                        </div>
                    </TabPanel>

                    <TabPanel header="Отзывы">
                        <div class="py-8 text-center text-slate-500">
                            <i class="pi pi-comment text-4xl mb-3" />
                            <p>Отзывы будут добавлены в следующей версии</p>
                        </div>
                    </TabPanel>
                </TabView>
            </div>

            <!-- ✅ Похожие товары — полностью переработанные карточки -->
            <div v-if="related && related.length > 0" class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900">
                        Похожие товары
                    </h2>
                    <Link
                        :href="route('catalog.index')"
                        class="text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1"
                    >
                        Все товары
                        <i class="pi pi-arrow-right text-sm" />
                    </Link>
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"
                >
                    <div
                        v-for="item in related"
                        :key="item.id"
                        class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl border border-slate-200 transition-all duration-300 hover:-translate-y-1 flex flex-col"
                    >
                        <!-- Кликабельное изображение → страница товара -->
                        <Link
                            :href="route('products.show', item.slug)"
                            class="block"
                        >
                            <div
                                class="relative aspect-square overflow-hidden bg-slate-100"
                            >
                                <span
                                    v-if="item.is_new"
                                    class="absolute top-3 left-3 z-10 inline-flex items-center px-2.5 py-1 bg-emerald-500 text-white text-xs font-bold rounded-lg shadow-lg"
                                >
                                    NEW
                                </span>
                                <span
                                    v-if="
                                        item.old_price &&
                                        item.old_price > item.price
                                    "
                                    class="absolute top-3 right-3 z-10 inline-flex items-center px-2.5 py-1 bg-red-500 text-white text-xs font-bold rounded-lg shadow-lg"
                                >
                                    -{{
                                        Math.round(
                                            ((item.old_price - item.price) /
                                                item.old_price) *
                                                100,
                                        )
                                    }}%
                                </span>
                                <img
                                    :src="item.images?.[0] || item.image"
                                    :alt="item.name"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    @error="
                                        $event.target.src =
                                            '/images/placeholder.webp'
                                    "
                                />
                                <!-- Оверлей при наведении -->
                                <div
                                    class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300 flex items-center justify-center"
                                >
                                    <span
                                        class="opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 bg-white/90 backdrop-blur-sm text-slate-800 px-4 py-2 rounded-full text-sm font-semibold shadow-lg flex items-center gap-2"
                                    >
                                        <i class="pi pi-eye text-sm" />
                                        Подробнее
                                    </span>
                                </div>
                            </div>
                        </Link>

                        <div class="p-5 flex flex-col flex-grow">
                            <span
                                class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2"
                            >
                                {{ item.brand }}
                            </span>

                            <!-- Кликабельное название → страница товара -->
                            <Link :href="route('products.show', item.slug)">
                                <h3
                                    class="font-semibold text-slate-900 mb-2 line-clamp-2 min-h-[3rem] group-hover:text-blue-600 transition-colors"
                                >
                                    {{ item.name }}
                                </h3>
                            </Link>

                            <div class="mt-auto pt-4 border-t border-slate-100">
                                <div class="flex items-baseline gap-2 mb-3">
                                    <span
                                        class="text-xl font-bold text-slate-900"
                                    >
                                        {{ formatPrice(item.price) }} ₽
                                    </span>
                                    <span
                                        v-if="
                                            item.old_price &&
                                            item.old_price > item.price
                                        "
                                        class="text-sm text-slate-400 line-through"
                                    >
                                        {{ formatPrice(item.old_price) }} ₽
                                    </span>
                                </div>

                                <!-- ✅ Две кнопки: «В корзину» и «Купить» -->
                                <div class="flex gap-2">
                                    <Link
                                        :href="
                                            route('products.show', item.slug)
                                        "
                                        class="flex-1"
                                    >
                                        <Button
                                            label="Подробнее"
                                            icon="pi pi-arrow-right"
                                            class="w-full justify-center"
                                            size="small"
                                            severity="secondary"
                                            outlined
                                        />
                                    </Link>
                                    <Button
                                        @click="addRelatedToCart(item)"
                                        label="В корзину"
                                        icon="pi pi-shopping-cart"
                                        class="flex-1 justify-center"
                                        size="small"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

:deep(.p-tabview-nav) {
    border-bottom: 2px solid #e2e8f0;
}

:deep(.p-tabview-nav li .p-tabview-nav-link) {
    font-weight: 600;
    color: #64748b;
    padding: 1rem 1.5rem;
    transition: all 0.2s;
}

:deep(.p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    color: #2563eb;
    border-bottom: 3px solid #2563eb;
    margin-bottom: -2px;
}

:deep(.p-tabview-nav li .p-tabview-nav-link:hover) {
    color: #2563eb;
    background-color: #f1f5f9;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
