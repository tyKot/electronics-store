<script setup>
import Menu from "primevue/menu";
import Avatar from "primevue/avatar";
import { Button } from "primevue";
import { Link, router } from "@inertiajs/vue3";
import { useCartStore } from "@/stores/cart";
import { computed, ref } from "vue";

const cart = useCartStore();
// Безопасное получение количества товаров в корзине
const cartItemsCount = computed(() => {
    return cart.totalItems || cart.items?.length || 0;
});

// Состояние для выпадающего меню пользователя
const userMenu = ref();

const toggleUserMenu = (event) => {
    userMenu.value.toggle(event);
};
// Пункты меню для авторизованного пользователя
const userMenuItems = computed(() => [
    {
        label: "Мой профиль",
        icon: "pi pi-user",
        command: () => router.visit(route("profile.index")), // Замените на ваш роут профиля
    },
    {
        separator: true,
    },
    {
        label: "Выйти",
        icon: "pi pi-sign-out",
        class: "text-red-500",
        command: () => router.post(route("logout")), // POST запрос для выхода
    },
]);
</script>
<template>
    <header
        class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200 shadow-sm"
    >
        <div
            class="container mx-auto px-6 py-3 flex items-center justify-between"
        >
            <!-- Логотип -->
            <Link
                :href="route('catalog.index')"
                class="flex items-center gap-2 group"
            >
                <div
                    class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center text-white shadow-md group-hover:scale-105 transition-transform"
                >
                    <i class="pi pi-bolt text-lg"></i>
                </div>
                <span class="text-xl font-bold text-slate-800 hidden sm:block"
                    >TechStore</span
                >
            </Link>

            <!-- Правая часть: Действия -->
            <div class="flex items-center gap-2 md:gap-4">
                <!-- Кнопка Админки (Видна только если пользователь админ) -->
                <Link
                    v-if="$page.props.auth?.user?.is_admin"
                    :href="route('admin.dashboard')"
                    class="hidden md:block"
                >
                    <Button
                        icon="pi pi-cog"
                        label="Админ-панель"
                        severity="secondary"
                        text
                        size="small"
                        class="text-slate-600 hover:text-blue-600"
                    />
                </Link>
                <Link
                    v-if="$page.props.auth?.user?.is_admin"
                    :href="route('admin.dashboard')"
                    class="md:hidden"
                >
                    <Button
                        icon="pi pi-cog"
                        severity="secondary"
                        text
                        rounded
                        size="small"
                    />
                </Link>

                <!-- Кнопка Корзины -->
                <Link :href="route('cart.index')">
                    <Button
                        icon="pi pi-shopping-cart"
                        severity="primary"
                        outlined
                        rounded
                        :badge="
                            cartItemsCount > 0
                                ? String(cartItemsCount)
                                : undefined
                        "
                        badgeClass="!bg-red-500 !text-white !text-xs"
                        class="relative hover:shadow-md transition-shadow"
                    />
                </Link>

                <!-- Разделитель -->
                <div class="w-px h-6 bg-slate-200 mx-1 hidden sm:block"></div>

                <!-- Блок авторизации -->
                <template v-if="$page.props.auth?.user">
                    <!-- Если залогинен: Аватар и Меню -->
                    <Button
                        @click="toggleUserMenu"
                        text
                        rounded
                        class="flex items-center gap-2 hover:bg-slate-100 transition-colors py-1 px-2"
                    >
                        <Avatar
                            :label="
                                $page.props.auth.user.name
                                    .charAt(0)
                                    .toUpperCase()
                            "
                            shape="circle"
                            class="bg-blue-100 text-blue-700 font-bold"
                        />
                        <span
                            class="hidden md:inline font-medium text-slate-700 max-w-[120px] truncate"
                        >
                            {{ $page.props.auth.user.name }}
                        </span>
                        <i class="pi pi-angle-down text-slate-400 text-sm"></i>
                    </Button>
                    <Menu
                        ref="userMenu"
                        :model="userMenuItems"
                        :popup="true"
                        class="!mt-2 shadow-xl border border-slate-100"
                    />
                </template>

                <template v-else>
                    <!-- Если не залогинен: Кнопки входа и регистрации -->
                    <Link :href="route('login')" class="hidden sm:block">
                        <Button
                            label="Войти"
                            icon="pi pi-sign-in"
                            severity="secondary"
                            text
                            size="small"
                        />
                    </Link>
                    <Link :href="route('register')">
                        <Button
                            label="Регистрация"
                            icon="pi pi-user-plus"
                            size="small"
                            class="shadow-sm"
                        />
                    </Link>
                </template>
            </div>
        </div>
    </header>
</template>
