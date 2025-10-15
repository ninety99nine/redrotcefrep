<template>
    <div class="pt-24 px-8 relative select-none">
        <!-- Filters -->
        <div class="bg-white rounded-lg p-4 mb-4">
            <h1 class="text-lg font-semibold mb-4">Store Analytics</h1>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <Datepicker
                        v-model="start_date"
                        format="dd MMM yyyy"
                        modelType="yyyy-MM-dd"
                        @change="fetchAllAnalytics"
                        :enableTimePicker="false"
                        :errorText="formState.getFormError('start_date')">
                    </Datepicker>
                    <span>-</span>
                    <Datepicker
                        v-model="end_date"
                        format="dd MMM yyyy"
                        modelType="yyyy-MM-dd"
                        @change="fetchAllAnalytics"
                        :enableTimePicker="false"
                        :errorText="formState.getFormError('end_date')">
                    </Datepicker>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="!store || isLoadingAnalytics" class="text-center">Loading...</div>

        <!-- Dynamic Charts -->
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-for="chart in chartConfigs" :key="chart.type" class="bg-white rounded-lg space-y-4 p-4">
                <h2 class="text-sm font-bold mb-2">{{ chart.title }}</h2>
                <component
                    :is="chart.chartComponent"
                    :chartData="chartData[chart.type]"
                    :options="getChartOptions(chart.xLabel, chart.yLabel)"
                    ref="chartComponents"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { LineChart, BarChart } from 'vue-chart-3';
import Datepicker from '@Partials/Datepicker.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default {
    name: 'Analytics',
    inject: ['formState', 'storeState', 'notificationState'],
    components: { Datepicker, LineChart, BarChart },
    data() {
        return {
            chartData: {}, // Store data for all charts
            isLoadingAnalytics: false,
            end_date: this.getDefaultEndDate(),
            start_date: this.getDefaultStartDate(),
            // Configuration for all charts
            chartConfigs: [
                { type: 'store_visitors', title: 'Store Visitors', xLabel: 'Date', yLabel: 'Visitors', dataKey: 'visits', chartComponent: 'LineChart' },
                { type: 'page_views', title: 'Page Views', xLabel: 'Date', yLabel: 'Views', dataKey: 'views', chartComponent: 'LineChart' },
                { type: 'top_pages', title: 'Top 10 Pages Viewed', xLabel: 'Page Name', yLabel: 'View Count', dataKey: 'views', chartComponent: 'BarChart' },
            ],
        };
    },
    watch: {
        store(newValue, oldValue) {
            if (!oldValue && newValue) {
                this.fetchAllAnalytics();
            }
        },
    },
    computed: {
        store() {
            return this.storeState.store;
        },
        filters() {
            return {
                start_date: this.start_date,
                end_date: this.end_date,
            };
        },
    },
    methods: {
        getDefaultStartDate() {
            const date = new Date();
            date.setDate(date.getDate() - 7);
            return date.toISOString().split('T')[0];
        },
        getDefaultEndDate() {
            return new Date().toISOString().split('T')[0];
        },
        getChartOptions(xLabel, yLabel) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#333',
                        titleFont: { size: 14 },
                        bodyFont: { size: 14 },
                        callbacks: {
                            label: (context) => ` ${context.dataset.label}: ${context.parsed.y}`,
                        },
                    },
                },
                scales: {
                    x: {
                        title: { display: true, text: xLabel, font: { size: 14 } },
                        grid: {
                            display: true,
                            drawBorder: false,
                            borderDash: [5, 5],
                            drawOnChartArea: true,
                            color: 'rgba(0, 0, 0, 0.1)',
                        },
                        border: { display: true, color: '#000', width: 1 },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            autoSkip: true,
                            callback: function(value, index, values) {
                                // For top_pages, truncate long page names to avoid clutter
                                if (this.chart.data.datasets[0].label === 'views' && this.chart.config.type === 'bar') {
                                    const label = this.chart.data.labels[value];
                                    return label.length > 20 ? label.substring(0, 17) + '...' : label;
                                }
                                return this.chart.data.labels[value];
                            }
                        },
                    },
                    y: {
                        title: { display: true, text: yLabel, font: { size: 14 } },
                        beginAtZero: true,
                        grid: {
                            display: true,
                            drawBorder: false,
                            borderDash: [5, 5],
                            drawOnChartArea: true,
                            color: 'rgba(0, 0, 0, 0.1)',
                        },
                        border: { display: true, color: '#000', width: 1 },
                        ticks: { stepSize: 10 },
                    },
                },
                interaction: { mode: 'nearest', intersect: false },
            };
        },
        createGradient(ctx, chartArea) {
            if (!chartArea) return 'rgba(22, 93, 252, 0.5)'; // Fallback color if chartArea is undefined
            const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
            gradient.addColorStop(0, 'rgba(22, 93, 252, 0)'); // Transparent at the bottom
            gradient.addColorStop(1, 'rgba(22, 93, 252, 0.5)'); // Semi-opaque blue at the top
            return gradient;
        },
        transform(data, label, chartType) {
            return {
                labels: data.labels,
                datasets: [
                    {
                        label: label,
                        data: data.values,
                        backgroundColor: chartType === 'LineChart'
                            ? (ctx) => this.createGradient(ctx.chart?.ctx, ctx.chart?.chartArea)
                            : ['#165dfc'],
                        borderColor: '#165dfc',
                        borderWidth: chartType === 'LineChart' ? 2 : 0,
                        fill: chartType === 'LineChart' ? true : false,
                        tension: chartType === 'LineChart' ? 0.3 : 0,
                        pointHitRadius: chartType === 'LineChart' ? 10 : 0, // Fix hitRadius issue
                    },
                ],
            };
        },
        updateChartGradients() {
            if (!this.$refs.chartComponents || !this.chartData) return;

            this.chartConfigs.forEach((chart, index) => {
                if (chart.chartComponent === 'LineChart' && this.$refs.chartComponents[index]) {
                    const ctx = this.$refs.chartComponents[index].chart?.ctx;
                    const chartArea = this.$refs.chartComponents[index].chart?.chartArea;
                    if (ctx && chartArea) {
                        const gradient = this.createGradient(ctx, chartArea);
                        this.$set(this.chartData[chart.type].datasets[0], 'backgroundColor', () => gradient);
                    }
                }
            });
        },
        async fetchAllAnalytics() {
            if (!this.store) return;

            this.isLoadingAnalytics = true;
            try {
                const requests = this.chartConfigs.map((chart) =>
                    axios.get('/api/analytics', {
                        params: {
                            ...this.filters,
                            type: chart.type,
                            store_id: this.store.id,
                        },
                    })
                );

                const responses = await Promise.all(requests);

                // Update chart data
                this.chartData = this.chartConfigs.reduce((acc, chart, index) => {
                    acc[chart.type] = this.transform(responses[index].data, chart.dataKey, chart.chartComponent);
                    return acc;
                }, {});
            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching analytics';
                this.notificationState.showWarningNotification(message);
                this.formState.setServerFormErrors(error);
                console.error('Failed to fetch analytics:', error);
            } finally {
                this.isLoadingAnalytics = false;
            }
        },
    },
    updated() {
        this.$nextTick(() => {
            this.updateChartGradients();
        });
    },
    created() {
        if (this.store) {
            this.fetchAllAnalytics();
        }
    },
};
</script>
