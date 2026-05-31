<script setup>
import { ref, onMounted } from "vue";
import Chart from "primevue/chart";
import Card from "primevue/card";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const chartData = ref();
const chartOptions = ref();

onMounted(() => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue("--text-color");
    const textColorSecondary = documentStyle.getPropertyValue(
        "--text-color-secondary",
    );
    const surfaceBorder = documentStyle.getPropertyValue("--surface-border");

    // Данные с бэкенда (моковые для примера)
    chartData.value = {
        labels: ["Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс"],
        datasets: [
            {
                label: "Продажи (руб.)",
                data: [45000, 59000, 82000, 81000, 56000, 55000, 40000],
                fill: false,
                borderColor: documentStyle.getPropertyValue("--blue-500"),
                tension: 0.4,
            },
        ],
    };

    chartOptions.value = {
        maintainAspectRatio: false,
        aspectRatio: 0.6,
        plugins: {
            legend: { labels: { color: textColor } },
        },
        scales: {
            x: {
                ticks: { color: textColorSecondary },
                grid: { color: surfaceBorder },
            },
            y: {
                ticks: { color: textColorSecondary },
                grid: { color: surfaceBorder },
            },
        },
    };
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="container mx-auto p-6">
            <h1 class="text-3xl font-bold mb-6">Админ-панель</h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <Card>
                    <template #title>Заказы сегодня</template>
                    <template #content
                        ><p class="text-4xl font-bold text-green-500">
                            24
                        </p></template
                    >
                </Card>
                <Card>
                    <template #title>Выручка за неделю</template>
                    <template #content
                        ><p class="text-4xl font-bold text-blue-500">
                            418 000 ₽
                        </p></template
                    >
                </Card>
                <Card>
                    <template #title>Новые пользователи</template>
                    <template #content
                        ><p class="text-4xl font-bold text-purple-500">
                            12
                        </p></template
                    >
                </Card>
            </div>

            <Card>
                <template #title>Динамика продаж</template>
                <template #content>
                    <div class="h-96">
                        <Chart
                            type="line"
                            :data="chartData"
                            :options="chartOptions"
                            class="h-full"
                        />
                    </div>
                </template>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
