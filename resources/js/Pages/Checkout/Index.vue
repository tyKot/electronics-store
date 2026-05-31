<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { z } from "zod";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Button from "primevue/button";
import Message from "primevue/message";
import { useCartStore } from "@/stores/cart";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({ errors: Object });
const cart = useCartStore();

// Zod Схема валидации
const checkoutSchema = z.object({
    first_name: z.string().min(2, "Минимум 2 символа"),
    last_name: z.string().min(2, "Минимум 2 символа"),
    address: z.string().min(5, "Укажите полный адрес"),
    phone: z.string().regex(/^\+7\d{10}$/, "Формат: +79991234567"),
});

const form = useForm({
    first_name: "",
    last_name: "",
    address: "",
    phone: "",
    cart_items: cart.items.map((i) => ({
        product_id: i.id,
        quantity: i.quantity,
        price: i.price,
    })),
});

const validationErrors = ref({});

const validateForm = () => {
    const result = checkoutSchema.safeParse(form.data());
    if (!result.success) {
        validationErrors.value = result.error.flatten().fieldErrors;
        return false;
    }
    validationErrors.value = {};
    return true;
};

const submit = () => {
    if (!validateForm()) return;

    form.post(route("checkout.store"), {
        onSuccess: () => {
            cart.clearCart(); // Очищаем корзину после успешного заказа
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="container mx-auto p-6 max-w-2xl">
            <h1 class="text-3xl font-bold mb-6">Оформление заказа</h1>

            <Message
                v-if="Object.keys($page.props.errors || {}).length > 0"
                severity="error"
                class="mb-4"
            >
                Проверьте правильность заполненных данных или наличие товаров в
                корзине.
            </Message>

            <form
                @submit.prevent="submit"
                class="bg-white p-6 rounded-lg shadow space-y-4"
            >
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block mb-1">Имя</label>
                        <InputText
                            id="first_name"
                            v-model="form.first_name"
                            class="w-full"
                            :class="{
                                'p-invalid':
                                    validationErrors.first_name ||
                                    form.errors.first_name,
                            }"
                        />
                        <small
                            v-if="validationErrors.first_name"
                            class="p-error"
                            >{{ validationErrors.first_name[0] }}</small
                        >
                    </div>
                    <div>
                        <label for="last_name" class="block mb-1"
                            >Фамилия</label
                        >
                        <InputText
                            id="last_name"
                            v-model="form.last_name"
                            class="w-full"
                            :class="{
                                'p-invalid':
                                    validationErrors.last_name ||
                                    form.errors.last_name,
                            }"
                        />
                        <small
                            v-if="validationErrors.last_name"
                            class="p-error"
                            >{{ validationErrors.last_name[0] }}</small
                        >
                    </div>
                </div>

                <div>
                    <label for="phone" class="block mb-1">Телефон</label>
                    <InputText
                        id="phone"
                        v-model="form.phone"
                        class="w-full"
                        placeholder="+79991234567"
                        :class="{
                            'p-invalid':
                                validationErrors.phone || form.errors.phone,
                        }"
                    />
                    <small v-if="validationErrors.phone" class="p-error">{{
                        validationErrors.phone[0]
                    }}</small>
                </div>

                <div>
                    <label for="address" class="block mb-1"
                        >Адрес доставки</label
                    >
                    <Textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="w-full"
                        :class="{
                            'p-invalid':
                                validationErrors.address || form.errors.address,
                        }"
                    />
                    <small v-if="validationErrors.address" class="p-error">{{
                        validationErrors.address[0]
                    }}</small>
                </div>

                <div class="pt-4 border-t">
                    <div class="flex justify-between text-xl font-bold mb-4">
                        <span>Итого к оплате:</span>
                        <span class="text-blue-600">{{
                            new Intl.NumberFormat("ru-RU", {
                                style: "currency",
                                currency: "RUB",
                                maximumFractionDigits: 0,
                            }).format(cart.totalPrice)
                        }}</span>
                    </div>
                    <Button
                        type="submit"
                        label="Подтвердить и оплатить (Mock)"
                        icon="pi pi-check"
                        class="w-full"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
