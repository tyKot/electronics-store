<script setup lang="ts">
import Navbar from "@/Components/Navbar.vue";
import { Toast } from "primevue";
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useFavoritesStore } from "@/stores/favorites";

const favorites = useFavoritesStore();
const { auth } = usePage().props;

onMounted(() => {
    if (auth.user) {
        favorites.init();
    }
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
            <Navbar />
            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <Toast position="top-right" />
        </div>
    </div>
</template>
