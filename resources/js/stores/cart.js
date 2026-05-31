import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';
import { computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
    // useStorage автоматически синхронизирует массив с localStorage
    const items = useStorage('cart-items', []);

    const totalItems = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0));
    const totalPrice = computed(() => items.value.reduce((sum, item) => sum + (item.price * item.quantity), 0));

    function addToCart(product) {
        const existingItem = items.value.find(i => i.id === product.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            items.value.push({ ...product, quantity: 1 });
        }
    }

    function removeFromCart(productId) {
        items.value = items.value.filter(i => i.id !== productId);
    }

    function updateQuantity(productId, quantity) {
        const item = items.value.find(i => i.id === productId);
        if (item) {
            if (quantity <= 0) removeFromCart(productId);
            else item.quantity = quantity;
        }
    }

    function clearCart() {
        items.value = [];
    }

    return { items, totalItems, totalPrice, addToCart, removeFromCart, updateQuantity, clearCart };
});
