<script setup>
import { useDebounceFn } from "@vueuse/core";
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import Button from "primevue/button";
import Tag from "primevue/tag";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber"; // Добавлено для цены
import Checkbox from "primevue/checkbox"; // Добавлено для меток
import Select from "primevue/select";
import { useCartStore } from "@/stores/cart";
import { useFavoritesStore } from "@/stores/favorites";
import { useToast } from "primevue/usetoast";
import placeholderImg from "@/images/placeholder.webp";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const page = usePage();
const props = defineProps({
    products: Object,
    filters: Object,
    brands: Array,
    auth: Object,
});

const favorites = useFavoritesStore();

// При смене страницы каталога проверяем статусы избранных
watch(
    () => props.products.data,
    (newProducts) => {
        if (newProducts?.length) {
            const ids = newProducts.map((p) => p.id);
            favorites.syncWithCatalog(ids);
        }
    },
    { immediate: true },
);

const cart = useCartStore();
const toast = useToast();

// --- Конфигурация фильтров ---
const sortOptions = [
    { label: "По популярности", value: "-created_at" },
    { label: "Сначала дешевые", value: "price" },
    { label: "Сначала дорогие", value: "-price" },
    { label: "По названию", value: "name" },
];

// Состояние фильтров
const selectedSort = ref(props.filters.sort || "-created_at");
const selectedBrand = ref(props.filters.brand || null);
const searchQuery = ref(props.filters.search || "");

// Новые фильтры
const priceRange = ref({
    min: props.filters.filter?.price_min ?? null,
    max: props.filters.filter?.price_max ?? null,
});

const tagsFilter = ref(props.filters.filter?.tags || []);
const availableTags = [
    { name: "Новинки", value: "new" },
    { name: "Хиты продаж", value: "featured" },
    { name: "Со скидкой", value: "discount" },
];

// --- Логика применения фильтров ---
// Используем debounce-подобную логику через watch для поиска и цены,
// но для простоты в Inertia часто используют ручное применение или отдельный хук.
// Здесь оставим автоматический watch, но для цены лучше добавить кнопку "Применить"
// или использовать useDebounceFn из VueUse (который у вас есть в стеке!)

const applyFilters = () => {
    router.get(
        route("catalog.index"),
        {
            sort: selectedSort.value,
            "filter[brand]": selectedBrand.value,
            "filter[name]": searchQuery.value,
            "filter[price_min]": priceRange.value.min,
            "filter[price_max]": priceRange.value.max,
            "filter[tags]": tagsFilter.value.length ? tagsFilter.value : null,
            page: 1, // Всегда сбрасываем на 1 страницу при смене фильтров
        },
        { preserveState: true, replace: true },
    );
};

// Мгновенная реакция для селектов и чекбоксов
watch([selectedSort, selectedBrand, tagsFilter], applyFilters);

// Debounce для текстового поиска и цены (чтобы не спамить запросами при вводе)
const debouncedApply = useDebounceFn(applyFilters, 400);
watch([searchQuery, priceRange], debouncedApply, { deep: true });

// --- Вспомогательные функции ---
const getDiscountPercent = (product) => {
    if (!product.old_price || product.old_price <= product.price) return 0;
    return Math.round(
        ((product.old_price - product.price) / product.old_price) * 100,
    );
};

const formatPrice = (price) => new Intl.NumberFormat("ru-RU").format(price);

const handleAddToCart = (product) => {
    cart.addToCart({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
    });
    toast.add({
        severity: "success",
        summary: "Добавлено в корзину",
        detail: product.name,
        life: 3000,
    });
};

const openProduct = (product) =>
    router.visit(route("products.show", product.slug ?? product.id));

const toggleFavorite = (product) => {
    const added = favorites.toggleFavorite(product);
    toast.add({
        severity: added ? "success" : "info",
        summary: added ? "Добавлено в избранное" : "Удалено из избранного",
        detail: product.name,
        life: 2500,
    });
};

const onPageChange = (event) => {
    router.get(
        route("catalog.index"),
        { page: event.page + 1 },
        { preserveState: true, preserveScroll: true },
    );
};

const hasActiveFilters = computed(
    () =>
        selectedBrand.value ||
        searchQuery.value ||
        priceRange.value.min ||
        priceRange.value.max ||
        tagsFilter.value.length > 0,
);

const clearFilters = () => {
    selectedBrand.value = null;
    searchQuery.value = "";
    priceRange.value = { min: null, max: null };
    tagsFilter.value = [];
    // applyFilters вызовется автоматически через watch
};

// watch(
//     () => page.props.flash.success,
//     (success) => {
//         if (!success) {
//             toast.add({
//                 severity: "success",
//                 summary: "Успех",
//                 detail: page.props.flash.success || "Операция выполнена успешно",
//                 life: 4000,
//             });
//         }
//     },
// );
</script>

<template>
    <AuthenticatedLayout>
        <!-- Hero-баннер (без изменений) -->
        <div
            class="relative bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 text-white overflow-hidden"
        >
            <div class="absolute inset-0 opacity-10">
                <div
                    class="absolute top-10 left-10 w-72 h-72 bg-white rounded-full blur-3xl"
                ></div>
                <div
                    class="absolute bottom-10 right-10 w-96 h-96 bg-blue-300 rounded-full blur-3xl"
                ></div>
            </div>
            <div class="container mx-auto px-6 py-16 relative z-10">
                <div class="max-w-3xl">
                    <span
                        class="inline-block px-4 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium mb-4"
                        >🔥 Весенняя распродажа</span
                    >
                    <h1
                        class="text-5xl md:text-6xl font-extrabold mb-4 leading-tight"
                    >
                        Каталог электроники
                    </h1>
                    <p class="text-xl text-blue-100 mb-6">
                        Более 10 000 товаров от ведущих мировых брендов с
                        гарантией и быстрой доставкой
                    </p>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-6 py-10">
            <!-- Панель фильтров и поиска -->
            <div
                class="sticky top-20 z-40 bg-slate-50/95 backdrop-blur-md -mx-6 px-6 py-4 mb-8 border-b border-slate-200 shadow-sm transition-all duration-300"
            >
                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <div class="flex-1 min-w-[250px] relative">
                        <i
                            class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                        ></i>
                        <InputText
                            v-model="searchQuery"
                            placeholder="Поиск по каталогу..."
                            class="w-full"
                            style="padding-left: 40px"
                        />
                    </div>
                    <Select
                        v-model="selectedBrand"
                        :options="brands"
                        optionLabel="name"
                        optionValue="slug"
                        placeholder="Все бренды"
                        class="w-full md:w-64"
                        showClear
                    />
                    <Select
                        v-model="selectedSort"
                        :options="sortOptions"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full md:w-64"
                    />
                </div>

                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-6 pt-4 border-t border-slate-200/60"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="text-sm font-medium text-slate-700 whitespace-nowrap"
                            >Цена, ₽:</span
                        >
                        <div class="flex items-center gap-2">
                            <InputNumber
                                v-model="priceRange.min"
                                placeholder="От"
                                :min="0"
                                :step="1000"
                                class="w-28"
                                inputClass="w-full"
                            />
                            <span class="text-slate-400">–</span>
                            <InputNumber
                                v-model="priceRange.max"
                                placeholder="До"
                                :min="0"
                                :step="1000"
                                class="w-28"
                                inputClass="w-full"
                            />
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div
                            v-for="tag in availableTags"
                            :key="tag.value"
                            class="flex items-center gap-2 cursor-pointer select-none"
                        >
                            <Checkbox
                                v-model="tagsFilter"
                                :inputId="`tag-${tag.value}`"
                                :value="tag.value"
                            />
                            <label
                                :for="`tag-${tag.value}`"
                                class="text-sm text-slate-700 cursor-pointer"
                                >{{ tag.name }}</label
                            >
                        </div>
                    </div>

                    <Button
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        label="Сбросить"
                        icon="pi pi-times"
                        severity="secondary"
                        outlined
                        class="shrink-0 ml-auto md:ml-0"
                    />
                </div>

                <!-- Активные фильтры (чипсы) -->
                <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mt-4">
                    <Tag
                        v-if="selectedBrand"
                        :value="
                            brands.find((b) => b.slug === selectedBrand)?.name
                        "
                        severity="info"
                        removable
                        @remove="selectedBrand = null"
                    />
                    <Tag
                        v-if="searchQuery"
                        :value="`«${searchQuery}»`"
                        severity="info"
                        removable
                        @remove="searchQuery = ''"
                    />
                    <Tag
                        v-if="priceRange.min || priceRange.max"
                        :value="`${priceRange.min || '0'} – ${priceRange.max || '∞'} ₽`"
                        severity="info"
                        removable
                        @remove="priceRange = { min: null, max: null }"
                    />
                    <Tag
                        v-for="tagVal in tagsFilter"
                        :key="tagVal"
                        :value="
                            availableTags.find((t) => t.value === tagVal)?.name
                        "
                        severity="info"
                        removable
                        @remove="
                            tagsFilter = tagsFilter.filter((t) => t !== tagVal)
                        "
                    />
                </div>
            </div>

            <!-- STICKY ПАГИНАЦИЯ И ЗАГОЛОВОК -->
            <!-- top-20 = 5rem (80px). Подстройте под высоту вашего Navbar в AuthenticatedLayout -->
            <div
                class="sticky top-[235px] z-30 bg-slate-50/95 backdrop-blur-sm -mx-6 px-6 py-3 mb-6 border-b border-slate-200/80 transition-shadow duration-300 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]"
            >
                <div
                    class="flex flex-col sm:flex-row items-center justify-between gap-4 max-w-[100%]"
                >
                    <h2 class="text-xl font-bold text-slate-800">
                        Найдено:
                        <span class="text-blue-600">{{
                            products.meta.total
                        }}</span>
                    </h2>

                    <Paginator
                        v-if="products.meta.last_page > 1"
                        :rows="products.meta.per_page"
                        :totalRecords="products.meta.total"
                        :first="
                            (products.meta.current_page - 1) *
                            products.meta.per_page
                        "
                        @page="onPageChange"
                        template="PrevPageLink PageLinks NextPageLink"
                        class="compact-paginator"
                    />
                </div>
            </div>

            <!-- Сетка товаров -->
            <div
                v-if="products.data.length > 0"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-20"
            >
                <!-- Карточка товара (без изменений в структуре, только сокращена для примера) -->
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl border border-slate-200 transition-all duration-300 hover:-translate-y-1 flex flex-col"
                >
                    <!-- ... (оставьте ваш существующий код карточки без изменений) ... -->
                    <div
                        class="relative aspect-square overflow-hidden bg-slate-100"
                    >
                        <div
                            class="absolute top-3 left-3 z-10 flex flex-col gap-2"
                        >
                            <span
                                v-if="getDiscountPercent(product) > 0"
                                class="inline-flex items-center px-2.5 py-1 bg-red-500 text-white text-xs font-bold rounded-lg shadow-lg"
                                >-{{ getDiscountPercent(product) }}%</span
                            >
                            <span
                                v-if="product.is_new"
                                class="inline-flex items-center px-2.5 py-1 bg-emerald-500 text-white text-xs font-bold rounded-lg shadow-lg"
                                >NEW</span
                            >
                        </div>
                        <Link
                            :href="
                                route(
                                    'products.show',
                                    product.slug ?? product.id,
                                )
                            "
                            class="block w-full h-full"
                        >
                            <img
                                :src="product.image"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                @error="$event.target.src = placeholderImg"
                            />
                        </Link>
                    </div>
                    <div class="p-5 flex flex-col flex-grow">
                        <span
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1"
                            >{{ product.brand }}</span
                        >
                        <Link
                            :href="
                                route(
                                    'products.show',
                                    product.slug ?? product.id,
                                )
                            "
                            class="block"
                        >
                            <h3
                                class="font-semibold text-slate-900 mb-2 line-clamp-2 min-h-[3rem] group-hover:text-blue-600 transition-colors"
                            >
                                {{ product.name }}
                            </h3>
                        </Link>
                        <div class="mt-auto pt-4 border-t border-slate-100">
                            <div class="flex items-baseline gap-2 mb-3">
                                <span class="text-2xl font-bold text-slate-900"
                                    >{{ formatPrice(product.price) }} ₽</span
                                >
                                <span
                                    v-if="
                                        product.old_price &&
                                        product.old_price > product.price
                                    "
                                    class="text-sm text-slate-400 line-through"
                                    >{{
                                        formatPrice(product.old_price)
                                    }}
                                    ₽</span
                                >
                            </div>
                            <Button
                                @click="handleAddToCart(product)"
                                label="В корзину"
                                icon="pi pi-shopping-cart"
                                class="w-full justify-center"
                                :severity="
                                    product.stock > 0 ? 'primary' : 'secondary'
                                "
                                :disabled="product.stock === 0"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Пустое состояние -->
            <div
                v-else
                class="bg-white rounded-2xl p-16 text-center shadow-sm border border-slate-200"
            >
                <div
                    class="w-24 h-24 mx-auto mb-6 bg-slate-100 rounded-full flex items-center justify-center"
                >
                    <i class="pi pi-search text-5xl text-slate-400"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">
                    Ничего не найдено
                </h3>
                <p class="text-slate-500 mb-6 max-w-md mx-auto">
                    Попробуйте изменить параметры поиска или сбросить фильтры
                </p>
                <Button
                    @click="clearFilters"
                    label="Сбросить фильтры"
                    icon="pi pi-refresh"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Анимации карточек */
.group {
    animation: fadeInUp 0.5s ease-out both;
}
.group:nth-child(n + 1):nth-child(-n + 8) {
    animation-delay: calc(var(--i, 0) * 0.05s);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Компактный стиль для sticky-пагинатора */
:deep(.compact-paginator .p-paginator-pages) {
    gap: 0.25rem;
}
:deep(.compact-paginator button) {
    min-width: 2.5rem;
    height: 2.5rem;
}
</style>
