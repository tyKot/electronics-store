<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import Card from "primevue/card";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Password from "primevue/password";
import Tag from "primevue/tag";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Avatar from "primevue/avatar";
import Divider from "primevue/divider";
import Message from "primevue/message";
import { useToast } from "primevue/usetoast";
import { useFavoritesStore } from "@/stores/favorites";
import placeholderImg from "@/images/placeholder.webp";

const props = defineProps({
    user: Object,
    orders: Object,
    favorites: { type: Array, default: () => [] },
    stats: Object,
});

const toast = useToast();
const favoritesStore = useFavoritesStore();
const activeTab = ref(0);

onMounted(() => {
    favoritesStore.init();
});

// --- Форма профиля ---
const profileForm = useForm({ ...props.user });

const saveProfile = () => {
    profileForm.put(route("profile.update"), {
        onSuccess: () =>
            toast.add({
                severity: "success",
                summary: "Сохранено",
                detail: "Данные обновлены",
                life: 3000,
            }),
    });
};

// --- Форма пароля ---
const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const savePassword = () => {
    passwordForm.put(route("profile.password"), {
        onSuccess: () => {
            passwordForm.reset();
            toast.add({
                severity: "success",
                summary: "Готово",
                detail: "Пароль изменён",
                life: 3000,
            });
        },
    });
};

// --- Утилиты ---
const formatPrice = (price) => new Intl.NumberFormat("ru-RU").format(price);

const removeFromFavorites = (productId) => {
    const product = props.favorites.find((p) => p.id === productId);
    if (product) favoritesStore.toggleFavorite(product);
    toast.add({
        severity: "info",
        summary: "Удалено",
        detail: "Товар убран из избранного",
        life: 2000,
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="container mx-auto px-6 py-10 max-w-6xl">
            <!-- Шапка профиля -->
            <div class="flex flex-col md:flex-row items-center gap-6 mb-10">
                <Avatar
                    :label="user.name?.charAt(0)"
                    size="xlarge"
                    shape="circle"
                    class="!bg-blue-600 !text-white !text-3xl !w-24 !h-24"
                />
                <div class="text-center md:text-left">
                    <h1 class="text-3xl font-bold text-slate-900">
                        {{ user.name }}
                    </h1>
                    <p class="text-slate-500 mt-1">{{ user.email }}</p>
                    <p class="text-sm text-slate-400 mt-1">
                        С нами с {{ stats.member_since }}
                    </p>
                </div>

                <!-- Мини-статистика -->
                <div class="flex gap-6 ml-auto">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">
                            {{ stats.total_orders }}
                        </p>
                        <p
                            class="text-xs text-slate-500 uppercase tracking-wide"
                        >
                            Заказов
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ formatPrice(stats.total_spent) }} ₽
                        </p>
                        <p
                            class="text-xs text-slate-500 uppercase tracking-wide"
                        >
                            Потрачено
                        </p>
                    </div>
                </div>
            </div>

            <!-- Табы -->
            <Card class="!rounded-2xl !shadow-sm !border !border-slate-200">
                <template #content>
                    <TabView v-model:activeIndex="activeTab">
                        <!-- 📦 Вкладка: Мои заказы -->
                        <TabPanel header="Мои заказы">
                            <div class="py-4">
                                <DataTable
                                    :value="orders.data"
                                    stripedRows
                                    responsiveLayout="scroll"
                                    emptyMessage="У вас пока нет заказов"
                                    class="[&_.p-datatable-thead>tr>th]:!bg-transparent [&_.p-datatable-thead>tr>th]:!text-slate-500"
                                >
                                    <Column
                                        field="order_number"
                                        header="№ Заказа"
                                    >
                                        <template #body="{ data }">
                                            <span
                                                class="font-mono font-semibold text-slate-700"
                                                >#{{ data.order_number }}</span
                                            >
                                        </template>
                                    </Column>

                                    <Column field="created_at" header="Дата" />

                                    <Column
                                        field="items_count"
                                        header="Товаров"
                                    >
                                        <template #body="{ data }">
                                            {{ data.items_count }} шт.
                                        </template>
                                    </Column>

                                    <Column field="total_amount" header="Сумма">
                                        <template #body="{ data }">
                                            <span class="font-bold"
                                                >{{
                                                    formatPrice(
                                                        data.total_amount,
                                                    )
                                                }}
                                                ₽</span
                                            >
                                        </template>
                                    </Column>

                                    <Column field="status" header="Статус">
                                        <template #body="{ data }">
                                            <Tag
                                                :value="data.status.label"
                                                :severity="data.status.severity"
                                            />
                                        </template>
                                    </Column>

                                    <Column>
                                        <template #body="{ data }">
                                            <Button
                                                icon="pi pi-eye"
                                                severity="secondary"
                                                outlined
                                                size="small"
                                                @click="
                                                    router.visit(
                                                        route(
                                                            'orders.show',
                                                            data.id,
                                                        ),
                                                    )
                                                "
                                            />
                                        </template>
                                    </Column>
                                </DataTable>

                                <!-- Пагинация заказов -->
                                <div
                                    v-if="orders.meta.last_page > 1"
                                    class="mt-6 flex justify-center"
                                >
                                    <Button
                                        v-for="page in orders.meta.last_page"
                                        :key="page"
                                        :label="String(page)"
                                        :severity="
                                            page === orders.meta.current_page
                                                ? 'primary'
                                                : 'secondary'
                                        "
                                        :outlined="
                                            page !== orders.meta.current_page
                                        "
                                        size="small"
                                        class="mx-1"
                                        @click="
                                            router.get(
                                                route('profile.index'),
                                                { page },
                                                { preserveState: true },
                                            )
                                        "
                                    />
                                </div>
                            </div>
                        </TabPanel>

                        <!-- ❤️ Вкладка: Избранное -->
                        <TabPanel :header="`Избранное (${favorites.length})`">
                            <div class="py-4">
                                <div
                                    v-if="favorites.length > 0"
                                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                                >
                                    <div
                                        v-for="product in favorites"
                                        :key="product.id"
                                        class="flex gap-4 p-4 rounded-xl border border-slate-200 hover:border-blue-300 transition-colors group"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'products.show',
                                                    product.slug,
                                                )
                                            "
                                        >
                                            <img
                                                :src="product.image"
                                                :alt="product.name"
                                                class="w-20 h-20 object-cover rounded-lg bg-slate-100"
                                                @error="
                                                    $event.target.src =
                                                        placeholderImg
                                                "
                                            />
                                        </Link>
                                        <div class="flex-1 min-w-0">
                                            <Link
                                                :href="
                                                    route(
                                                        'products.show',
                                                        product.slug,
                                                    )
                                                "
                                                class="font-semibold text-slate-900 line-clamp-2 hover:text-blue-600 transition-colors"
                                            >
                                                {{ product.name }}
                                            </Link>
                                            <p
                                                class="text-lg font-bold text-blue-600 mt-1"
                                            >
                                                {{ formatPrice(product.price) }}
                                                ₽
                                            </p>
                                            <div class="flex gap-2 mt-2">
                                                <Button
                                                    label="В корзину"
                                                    size="small"
                                                    icon="pi pi-shopping-cart"
                                                    @click="
                                                        $emit(
                                                            'add-to-cart',
                                                            product,
                                                        )
                                                    "
                                                />
                                                <Button
                                                    icon="pi pi-trash"
                                                    severity="danger"
                                                    outlined
                                                    size="small"
                                                    @click="
                                                        removeFromFavorites(
                                                            product.id,
                                                        )
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="text-center py-16">
                                    <i
                                        class="pi pi-heart text-5xl text-slate-300 mb-4"
                                    ></i>
                                    <h3
                                        class="text-xl font-bold text-slate-700 mb-2"
                                    >
                                        Список избранного пуст
                                    </h3>
                                    <p class="text-slate-500 mb-6">
                                        Добавляйте товары, нажимая на сердечко в
                                        каталоге
                                    </p>
                                    <Link
                                        :href="route('catalog.index')"
                                        as="button"
                                        class="p-button p-button-primary"
                                    >
                                        Перейти в каталог
                                    </Link>
                                </div>
                            </div>
                        </TabPanel>

                        <!-- ⚙️ Вкладка: Настройки -->
                        <TabPanel header="Настройки">
                            <div class="py-4 max-w-2xl space-y-8">
                                <!-- Личные данные -->
                                <section>
                                    <h3
                                        class="text-lg font-bold text-slate-900 mb-4"
                                    >
                                        Личные данные
                                    </h3>
                                    <form
                                        @submit.prevent="saveProfile"
                                        class="space-y-4"
                                    >
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 mb-1"
                                                >Имя</label
                                            >
                                            <InputText
                                                v-model="profileForm.name"
                                                class="w-full"
                                                :class="{
                                                    'p-invalid':
                                                        profileForm.errors.name,
                                                }"
                                            />
                                            <small
                                                v-if="profileForm.errors.name"
                                                class="p-error"
                                                >{{
                                                    profileForm.errors.name
                                                }}</small
                                            >
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 mb-1"
                                                >Email</label
                                            >
                                            <InputText
                                                v-model="profileForm.email"
                                                class="w-full"
                                                :class="{
                                                    'p-invalid':
                                                        profileForm.errors
                                                            .email,
                                                }"
                                            />
                                            <small
                                                v-if="profileForm.errors.email"
                                                class="p-error"
                                                >{{
                                                    profileForm.errors.email
                                                }}</small
                                            >
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 mb-1"
                                                >Телефон</label
                                            >
                                            <InputText
                                                v-model="profileForm.phone"
                                                placeholder="+79991234567"
                                                class="w-full"
                                                :class="{
                                                    'p-invalid':
                                                        profileForm.errors
                                                            .phone,
                                                }"
                                            />
                                            <small
                                                v-if="profileForm.errors.phone"
                                                class="p-error"
                                                >{{
                                                    profileForm.errors.phone
                                                }}</small
                                            >
                                        </div>

                                        <Button
                                            type="submit"
                                            label="Сохранить изменения"
                                            :loading="profileForm.processing"
                                        />
                                    </form>
                                </section>

                                <Divider />

                                <!-- Смена пароля -->
                                <section>
                                    <h3
                                        class="text-lg font-bold text-slate-900 mb-4"
                                    >
                                        Безопасность
                                    </h3>
                                    <form
                                        @submit.prevent="savePassword"
                                        class="space-y-4"
                                    >
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 mb-1"
                                                >Текущий пароль</label
                                            >
                                            <Password
                                                v-model="
                                                    passwordForm.current_password
                                                "
                                                toggleMask
                                                feedback="{false}"
                                                class="w-full"
                                                inputClass="w-full"
                                            />
                                            <small
                                                v-if="
                                                    passwordForm.errors
                                                        .current_password
                                                "
                                                class="p-error"
                                                >{{
                                                    passwordForm.errors
                                                        .current_password
                                                }}</small
                                            >
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 mb-1"
                                                >Новый пароль</label
                                            >
                                            <Password
                                                v-model="passwordForm.password"
                                                toggleMask
                                                class="w-full"
                                                inputClass="w-full"
                                            />
                                            <small
                                                v-if="
                                                    passwordForm.errors.password
                                                "
                                                class="p-error"
                                                >{{
                                                    passwordForm.errors.password
                                                }}</small
                                            >
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 mb-1"
                                                >Подтвердите пароль</label
                                            >
                                            <Password
                                                v-model="
                                                    passwordForm.password_confirmation
                                                "
                                                toggleMask
                                                feedback="{false}"
                                                class="w-full"
                                                inputClass="w-full"
                                            />
                                        </div>

                                        <Button
                                            type="submit"
                                            label="Изменить пароль"
                                            severity="warn"
                                            :loading="passwordForm.processing"
                                        />
                                    </form>
                                </section>
                            </div>
                        </TabPanel>
                    </TabView>
                </template>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
