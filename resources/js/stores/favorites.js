import { defineStore } from "pinia";
import { ref, computed } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";

export const useFavoritesStore = defineStore("favorites", () => {
    const page = usePage();
    const favoriteIds = ref(new Set());
    const items = ref([]);
    const isLoaded = ref(false);
    const loading = ref(false);

    const count = computed(() => favoriteIds.value.size);

    // Инициализация: загружаем ID избранных из БД
    async function init() {
        if (isLoaded.value || loading.value || !!page.props.auth.user) return;

        loading.value = true;
        try {
            const { data } = await axios.get("/api/favorites");
            favoriteIds.value = new Set(data.data.map((p) => p.id));
            items.value = data.data;
            isLoaded.value = true;
        } catch (e) {
            console.error("Failed to load favorites:", e);
        } finally {
            loading.value = false;
        }
    }

    // Переключение с синхронизацией на сервер
    async function toggleFavorite(product) {
        // Оптимистичное обновление UI
        const wasFavorite = favoriteIds.value.has(product.id);

        if (wasFavorite) {
            favoriteIds.value.delete(product.id);
            items.value = items.value.filter((i) => i.id !== product.id);
        } else {
            favoriteIds.value.add(product.id);
            items.value.unshift(product);
        }

        // Синхронизация с БД в фоне
        try {
            const { data } = await axios.post(
                `/api/favorites/toggle/${product.id}`,
            );

            // Корректируем состояние по ответу сервера
            if (data.is_favorite !== !wasFavorite) {
                // Сервер вернул другой статус — откатываем оптимистичное обновление
                if (wasFavorite) {
                    favoriteIds.value.add(product.id);
                    items.value.unshift(product);
                } else {
                    favoriteIds.value.delete(product.id);
                    items.value = items.value.filter(
                        (i) => i.id !== product.id,
                    );
                }
            }
        } catch (e) {
            // Ошибка сети — откатываем
            if (wasFavorite) {
                favoriteIds.value.add(product.id);
                items.value.unshift(product);
            } else {
                favoriteIds.value.delete(product.id);
                items.value = items.value.filter((i) => i.id !== product.id);
            }
            console.error("Toggle favorite failed:", e);
        }

        return !wasFavorite;
    }

    function isFavorite(productId) {
        return favoriteIds.value.has(productId);
    }

    // Массовая проверка для каталога
    async function syncWithCatalog(productIds) {
        if (!isLoaded.value || !!page.props.auth.user) return;

        try {
            const { data } = await axios.post("/api/favorites/check", {
                product_ids: productIds,
            });
            // Объединяем серверные данные с локальными
            favoriteIds.value = new Set(data.favorite_ids);
        } catch (e) {
            console.error("Sync favorites failed:", e);
        }
    }

    return {
        favoriteIds,
        items,
        count,
        loading,
        init,
        toggleFavorite,
        isFavorite,
        syncWithCatalog,
    };
});
