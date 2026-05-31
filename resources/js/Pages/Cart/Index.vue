<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import Button from "primevue/button";
import InputNumber from "primevue/inputnumber";
import { useCartStore } from "@/stores/cart";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const cart = useCartStore();

const totalItems = computed(() =>
    cart.items.reduce((sum, item) => sum + item.quantity, 0),
);

const formatCurrency = (value) => {
    return new Intl.NumberFormat("ru-RU", {
        style: "currency",
        currency: "RUB",
        maximumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <AuthenticatedLayout>
        <!-- <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50"> -->
        <div class="container mx-auto px-4 py-8 lg:px-6 lg:py-12">
            <!-- Header -->
            <div
                class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
            >
                <div>
                    <p
                        class="mb-2 text-sm font-semibold uppercase tracking-[0.25em] text-blue-600"
                    >
                        Ваш заказ
                    </p>
                    <h1 class="text-3xl font-black text-slate-900 md:text-5xl">
                        Ваша корзина
                    </h1>
                    <p class="mt-3 max-w-2xl text-slate-500">
                        Проверьте товары, измените количество и переходите к
                        оформлению заказа.
                    </p>
                </div>

                <Link
                    :href="route('catalog.index')"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-600 hover:shadow-md"
                >
                    ← Продолжить покупки
                </Link>
            </div>

            <!-- Empty state -->
            <div
                v-if="cart.items.length === 0"
                class="mx-auto max-w-2xl overflow-hidden rounded-3xl border border-dashed border-slate-200 bg-white p-10 text-center shadow-lg"
            >
                <div
                    class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-blue-50 text-4xl text-blue-600"
                >
                    <i class="pi pi-shopping-cart"></i>
                </div>

                <h2 class="text-2xl font-bold text-slate-900">Корзина пуста</h2>
                <p class="mt-3 text-slate-500">
                    Похоже, вы ещё не добавили товары. Посмотрите каталог и
                    выберите понравившиеся позиции.
                </p>

                <div class="mt-8">
                    <Link
                        :href="route('catalog.index')"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 font-semibold text-white shadow-lg shadow-blue-200 transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-xl"
                    >
                        Перейти в каталог
                    </Link>
                </div>
            </div>

            <!-- Cart -->
            <div v-else class="grid grid-cols-1 gap-8 xl:grid-cols-3">
                <!-- Items -->
                <div class="space-y-4 xl:col-span-2">
                    <div
                        v-for="item in cart.items"
                        :key="item.id"
                        class="group overflow-hidden rounded-3xl border border-white bg-white/80 shadow-lg backdrop-blur transition hover:-translate-y-0.5 hover:shadow-2xl"
                    >
                        <div class="p-4 sm:p-6">
                            <div
                                class="flex flex-col gap-5 md:flex-row md:items-center"
                            >
                                <!-- Image -->
                                <div class="shrink-0">
                                    <img
                                        :src="item.image"
                                        :alt="item.name"
                                        class="h-28 w-full rounded-2xl object-cover shadow-md md:w-28"
                                    />
                                </div>

                                <!-- Info -->
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between"
                                    >
                                        <div class="min-w-0">
                                            <h3
                                                class="truncate text-xl font-bold text-slate-900"
                                            >
                                                {{ item.name }}
                                            </h3>
                                            <p
                                                class="mt-1 text-sm text-slate-500"
                                            >
                                                Товар в вашей корзине
                                            </p>
                                        </div>

                                        <div class="text-left lg:text-right">
                                            <p
                                                class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400"
                                            >
                                                Цена за штуку
                                            </p>
                                            <p
                                                class="mt-1 text-lg font-bold text-slate-900"
                                            >
                                                {{ formatCurrency(item.price) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <!-- Quantity -->
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="text-sm font-medium text-slate-500"
                                                >Количество</span
                                            >
                                            <InputNumber
                                                :modelValue="item.quantity"
                                                @update:modelValue="
                                                    (val) =>
                                                        cart.updateQuantity(
                                                            item.id,
                                                            val,
                                                        )
                                                "
                                                :min="1"
                                                :showButtons="true"
                                                buttonLayout="horizontal"
                                                class="w-32"
                                                inputClass="w-full text-center font-semibold"
                                            />
                                        </div>

                                        <!-- Total + remove -->
                                        <div class="flex items-center gap-3">
                                            <div class="text-right">
                                                <p
                                                    class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400"
                                                >
                                                    Сумма
                                                </p>
                                                <p
                                                    class="mt-1 text-2xl font-black text-blue-600"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            item.price *
                                                                item.quantity,
                                                        )
                                                    }}
                                                </p>
                                            </div>

                                            <Button
                                                icon="pi pi-trash"
                                                severity="danger"
                                                rounded
                                                outlined
                                                @click="
                                                    cart.removeFromCart(item.id)
                                                "
                                                class="!h-12 !w-12"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <aside class="xl:sticky xl:top-6">
                    <div
                        class="relative overflow-hidden rounded-3xl bg-slate-900 p-6 text-white shadow-2xl"
                    >
                        <div
                            class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-500/20 blur-3xl"
                        ></div>
                        <div
                            class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-cyan-400/10 blur-3xl"
                        ></div>

                        <div class="relative">
                            <p
                                class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-300"
                            >
                                Итог заказа
                            </p>
                            <h2 class="mt-2 text-2xl font-bold">Оформление</h2>

                            <div class="mt-6 space-y-4">
                                <div
                                    class="flex items-center justify-between text-sm text-slate-300"
                                >
                                    <span>Товаров в корзине</span>
                                    <span class="font-semibold text-white">{{
                                        totalItems
                                    }}</span>
                                </div>

                                <div
                                    class="flex items-center justify-between text-sm text-slate-300"
                                >
                                    <span>Доставка</span>
                                    <span class="font-semibold text-emerald-300"
                                        >Будет рассчитана позже</span
                                    >
                                </div>

                                <div class="border-t border-white/10 pt-4">
                                    <div class="flex items-end justify-between">
                                        <span class="text-slate-300"
                                            >К оплате</span
                                        >
                                        <span class="text-3xl font-black">
                                            {{
                                                formatCurrency(cart.totalPrice)
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <Link
                                :href="route('checkout.index')"
                                class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-5 py-4 text-lg font-bold text-white shadow-lg shadow-blue-900/30 transition hover:-translate-y-0.5 hover:bg-blue-500"
                            >
                                Оформить заказ
                            </Link>

                            <p class="mt-4 text-center text-xs text-slate-400">
                                Нажимая «Оформить заказ», вы переходите к
                                заполнению данных доставки.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
        <!-- </div> -->
    </AuthenticatedLayout>
</template>
