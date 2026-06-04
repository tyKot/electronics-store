<script setup>
import { useDebounceFn } from "@vueuse/core";
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted } from "vue";
import Button from "primevue/button";
import Tag from "primevue/tag";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Checkbox from "primevue/checkbox";
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
    categories: Array, // 👈 НОВОЕ: список категорий
    auth: Object,
});

const favorites = useFavoritesStore();

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

const filtersExpanded = ref(true);

if (typeof window !== "undefined" && window.innerWidth < 768) {
    filtersExpanded.value = false;
}

// --- Конфигурация фильтров ---
const sortOptions = [
    { label: "По популярности", value: "-created_at" },
    { label: "Сначала дешевые", value: "price" },
    { label: "Сначала дорогие", value: "-price" },
    { label: "По названию", value: "name" },
];

// 👇 Читаем категорию из URL: поддерживаем оба варианта
// /catalog?category=smartphones (простой параметр)
// /catalog?filter[category]=smartphones (через Spatie)
const initialCategory =
    props.filters.category || props.filters.filter?.category || null;

// Состояние фильтров
const selectedSort = ref(props.filters.sort || "-created_at");
const selectedBrand = ref(props.filters.brand || null);
const selectedCategory = ref(initialCategory); // 👈 НОВОЕ
const searchQuery = ref(props.filters.search || "");

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

// 👇 Счётчик активных фильтров (с категорией)
const activeFiltersCount = computed(() => {
    let count = 0;
    if (selectedBrand.value) count++;
    if (selectedCategory.value) count++; // 👈 НОВОЕ
    if (searchQuery.value) count++;
    if (priceRange.value.min || priceRange.value.max) count++;
    count += tagsFilter.value.length;
    return count;
});

// --- Применение фильтров ---
const applyFilters = () => {
    router.get(
        route("catalog.index"),
        {
            sort: selectedSort.value,
            "filter[brand]": selectedBrand.value,
            "filter[category_id]": selectedCategory.value, // 👈 НОВОЕ
            "filter[name]": searchQuery.value,
            "filter[price_min]": priceRange.value.min,
            "filter[price_max]": priceRange.value.max,
            "filter[tags]": tagsFilter.value.length ? tagsFilter.value : null,
            page: 1,
        },
        { preserveState: true, replace: true },
    );
};

watch([selectedSort, selectedBrand, selectedCategory, tagsFilter], applyFilters); // 👈 добавили selectedCategory

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

// 👇 Обновлённая проверка активных фильтров
const hasActiveFilters = computed(
    () =>
        selectedBrand.value ||
        selectedCategory.value || // 👈 НОВОЕ
        searchQuery.value ||
        priceRange.value.min ||
        priceRange.value.max ||
        tagsFilter.value.length > 0,
);

// 👇 Получаем название категории по slug
const getCategoryName = (id) => {
    if (!id) return null;
    const category = props.categories?.find((c) => c.id === id);
    return category?.name || id;
};

// 👇 Обновлённая очистка фильтров
const clearFilters = () => {
    selectedBrand.value = null;
    selectedCategory.value = null; // 👈 НОВОЕ
    searchQuery.value = "";
    priceRange.value = { min: null, max: null };
    tagsFilter.value = [];
};
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
            <!-- STICKY-ПАНЕЛЬ -->
            <div
                class="sticky top-[80px] z-40 bg-slate-50/95 backdrop-blur-md border-b border-slate-200 shadow-sm transition-all duration-300"
                style="
                    margin-left: calc(-1 * var(--container-padding, 1.5rem));
                    margin-right: calc(-1 * var(--container-padding, 1.5rem));
                    padding-left: var(--container-padding, 1.5rem);
                    padding-right: var(--container-padding, 1.5rem);
                "
            >
                <!-- Верхний ряд -->
                <div class="flex items-center justify-between gap-3 py-3">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <h2
                            class="text-base sm:text-xl font-bold text-slate-800 whitespace-nowrap"
                        >
                            Найдено:
                            <span class="text-blue-600">{{
                                products.meta.total
                            }}</span>
                        </h2>

                        <button
                            @click="filtersExpanded = !filtersExpanded"
                            class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                        >
                            <i class="pi pi-filter text-sm"></i>
                            <span>Фильтры</span>
                            <i
                                class="pi text-xs transition-transform duration-200"
                                :class="
                                    filtersExpanded
                                        ? 'pi-chevron-up'
                                        : 'pi-chevron-down'
                                "
                            ></i>
                            <span
                                v-if="hasActiveFilters"
                                class="ml-1 px-1.5 py-0.5 bg-blue-600 text-white text-xs rounded-full"
                            >
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>

                    <Paginator
                        v-if="products.meta.last_page > 1"
                        :rows="products.meta.per_page"
                        :totalRecords="products.meta.total"
                        :first="
                            (products.meta.current_page - 1) *
                            products.meta.per_page
                        "
                        @page="onPageChange"
                        :template="
                            products.meta.last_page > 5
                                ? 'PrevPageLink CurrentPageReport NextPageLink'
                                : 'PrevPageLink PageLinks NextPageLink'
                        "
                        currentPageReportTemplate="{currentPage} из {totalPages}"
                        class="compact-paginator shrink-0"
                    />
                </div>

                <!-- СВОРАЧИВАЕМАЯ ПАНЕЛЬ ФИЛЬТРОВ -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 max-h-0"
                    enter-to-class="opacity-100 max-h-[800px]"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 max-h-[800px]"
                    leave-to-class="opacity-0 max-h-0"
                >
                    <div
                        v-show="filtersExpanded"
                        class="overflow-hidden border-t border-slate-200/60"
                    >
                        <div class="py-4 space-y-4">
                            <!-- 👇 Ряд 1: Поиск + Категория + Бренд + Сортировка -->
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1fr_auto_auto_auto] gap-3"
                            >
                                <!-- Поиск -->
                                <div
                                    class="relative sm:col-span-2 lg:col-span-1"
                                >
                                    <i
                                        class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                                    ></i>
                                    <InputText
                                        v-model="searchQuery"
                                        placeholder="Поиск по каталогу..."
                                        class="w-full !pl-9 !py-2"
                                    />
                                </div>

                                <!-- 👇 НОВОЕ: Категория -->
                                <Select
                                    v-model="selectedCategory"
                                    :options="categories"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Все категории"
                                    class="w-full sm:w-auto sm:min-w-[180px]"
                                    showClear
                                />

                                <!-- Бренд -->
                                <Select
                                    v-model="selectedBrand"
                                    :options="brands"
                                    optionLabel="name"
                                    optionValue="slug"
                                    placeholder="Все бренды"
                                    class="w-full sm:w-auto sm:min-w-[180px]"
                                    showClear
                                />

                                <!-- Сортировка -->
                                <Select
                                    v-model="selectedSort"
                                    :options="sortOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Сортировка"
                                    class="w-full sm:w-auto sm:min-w-[180px]"
                                />
                            </div>

                            <!-- Ряд 2: Цена + Теги + Кнопка сброса (без изменений) -->
                            <div
                                class="flex flex-col lg:flex-row lg:items-center gap-4"
                            >
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span
                                        class="text-sm font-medium text-slate-700 whitespace-nowrap"
                                    >
                                        Цена, ₽:
                                    </span>
                                    <div
                                        class="flex items-center gap-2 flex-1 min-w-[200px]"
                                    >
                                        <InputNumber
                                            v-model="priceRange.min"
                                            placeholder="От"
                                            :min="0"
                                            :step="1000"
                                            class="flex-1 min-w-[80px]"
                                            inputClass="w-full !py-2"
                                        />
                                        <span class="text-slate-400">–</span>
                                        <InputNumber
                                            v-model="priceRange.max"
                                            placeholder="До"
                                            :min="0"
                                            :step="1000"
                                            class="flex-1 min-w-[80px]"
                                            inputClass="w-full !py-2"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="flex flex-wrap items-center gap-x-4 gap-y-2 flex-1"
                                >
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
                                            class="text-sm text-slate-700 cursor-pointer whitespace-nowrap"
                                        >
                                            {{ tag.name }}
                                        </label>
                                    </div>
                                </div>

                                <Button
                                    v-if="hasActiveFilters"
                                    @click="clearFilters"
                                    label="Сбросить"
                                    icon="pi pi-times"
                                    severity="secondary"
                                    outlined
                                    size="small"
                                    class="shrink-0 self-start lg:self-auto"
                                />
                            </div>

                            <!-- 👇 Ряд 3: Активные фильтры (с категорией) -->
                            <div
                                v-if="hasActiveFilters"
                                class="flex flex-wrap gap-2 pt-3 border-t border-slate-200/60"
                            >
                                <span
                                    class="text-xs text-slate-500 self-center mr-1"
                                >
                                    Активные:
                                </span>

                                <!-- 👇 НОВОЕ: Чипс категории -->
                                <Tag
                                    v-if="selectedCategory"
                                    :value="getCategoryName(selectedCategory)"
                                    severity="info"
                                    removable
                                    @remove="selectedCategory = null"
                                />

                                <Tag
                                    v-if="selectedBrand"
                                    :value="
                                        brands.find(
                                            (b) => b.slug === selectedBrand,
                                        )?.name
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
                                    @remove="
                                        priceRange = { min: null, max: null }
                                    "
                                />
                                <Tag
                                    v-for="tagVal in tagsFilter"
                                    :key="tagVal"
                                    :value="
                                        availableTags.find(
                                            (t) => t.value === tagVal,
                                        )?.name
                                    "
                                    severity="info"
                                    removable
                                    @remove="
                                        tagsFilter = tagsFilter.filter(
                                            (t) => t !== tagVal,
                                        )
                                    "
                                />
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Сетка товаров (без изменений) -->
            <div
                v-if="products.data.length > 0"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-3 pb-20"
            >
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl border border-slate-200 transition-all duration-300 hover:-translate-y-1 flex flex-col"
                >
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

            <!-- Пустое состояние (без изменений) -->
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

:deep(.compact-paginator) {
    padding: 0 !important;
    background: transparent !important;
    border: none !important;
}

:deep(.compact-paginator .p-paginator-pages) {
    gap: 2px;
}

:deep(.compact-paginator .p-paginator-pages button) {
    min-width: 32px;
    height: 32px;
    font-size: 0.875rem;
}

:deep(.compact-paginator .p-paginator-current) {
    font-size: 0.875rem;
    color: #64748b;
}

@media (max-width: 640px) {
    :deep(.compact-paginator .p-paginator-pages button) {
        min-width: 28px;
        height: 28px;
        font-size: 0.75rem;
    }
}

.tag-enter-active,
.tag-leave-active {
    transition: all 0.2s ease;
}

.tag-enter-from,
.tag-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
