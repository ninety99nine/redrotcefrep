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
                        @change="showAnalytics"
                        :enableTimePicker="false"
                        :errorText="formState.getFormError('start_date')">
                    </Datepicker>

                    <span>-</span>

                    <Datepicker
                        v-model="end_date"
                        format="dd MMM yyyy"
                        modelType="yyyy-MM-dd"
                        @change="showAnalytics"
                        :enableTimePicker="false"
                        :errorText="formState.getFormError('end_date')">
                    </Datepicker>

                </div>

                <Select
                    class="w-40"
                    :search="false"
                    v-model="interval"
                    @change="showAnalytics"
                    :options="intervalTypes"
                    :errorText="formState.getFormError('end_date')">
                </Select>

            </div>

        </div>

        <!-- Loading State -->
        <div v-if="isLoadingAnalytics" class="text-center">Loading...</div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Page Views -->
            <div class="bg-white rounded-lg space-y-4 p-4">

                <h2 class="text-sm font-bold mb-2">Store Visitors</h2>

                <LineChart :chartData="storeVisitorsData" :options="storeVisitorsOptions" />

            </div>

            <!-- Page Views -->
            <div class="bg-white rounded-lg space-y-4 p-4">

                <h2 class="text-sm font-bold mb-2">Page Views</h2>

                <LineChart :chartData="pageViewsData" :options="pageViewsOptions" />

            </div>

        </div>

    </div>

</template>

<script>

import { LineChart } from 'vue-chart-3';
import Select from '@Partials/Select.vue';
import { Chart, registerables } from 'chart.js';
import Datepicker from '@Partials/Datepicker.vue';

Chart.register(...registerables);

export default {
    name: 'Analytics',
    inject: ['formState', 'storeState', 'notificationState'],
    components: { Select, Datepicker, LineChart },
    data() {
        return {
            interval: 'daily',
            pageViewsData: [],
            intervalTypes: [
                { label: 'Daily', value: 'daily'},
                { label: 'Weekly', value: 'weekly'},
                { label: 'Monthly', value: 'monthly'}
            ],
            storeVisitorsData: [],
            isLoadingAnalytics: false,
            end_date: this.getDefaultEndDate(),
            start_date: this.getDefaultStartDate(),
            pageViewsOptions: this.getOptions('Date', 'views'),
            storeVisitorsOptions: this.getOptions('Date', 'visitors'),
        };
    },
    watch: {
        store(newValue, oldValue) {
            if (!oldValue && newValue) {
                this.showAnalytics();
            }
        },
    },
    computed: {
        store() {
            return this.storeState.store
        }
    },
    methods: {
        getDefaultStartDate() {
            const date = new Date();
            date.setDate(date.getDate() - 30);
            return date.toISOString().split('T')[0];
        },
        getDefaultEndDate() {
            return new Date().toISOString().split('T')[0];
        },
        async showAnalytics() {

            try {

                this.isLoadingAnalytics = true;

                const pageViewsConfig = {
                    params: {
                        ...this.filters,
                        type: 'page_views',
                        store_id: this.store.id
                    }
                };

                const storeVisitorsConfig = {
                    params: {
                        ...this.filters,
                        type: 'store_visitors',
                        store_id: this.store.id
                    }
                };

                // Show Page Views
                const pageViewsResponse = await axios.get('/api/analytics', pageViewsConfig);
                this.pageViewsData = this.transform(pageViewsResponse.data, 'views');

                // Show Store Visitors
                const storeVisitorsResponse = await axios.get('/api/analytics', storeVisitorsConfig);
                this.storeVisitorsData = this.transform(storeVisitorsResponse.data, 'visits');

                console.log('storeVisitorsResponse');
                console.log(this.storeVisitorsData);

            } catch (error) {
                console.error('Failed to fetch analytics data:', error);
            } finally {
                this.isLoadingAnalytics = false;
            }
        },
        getOptions(xLabel, yLabel) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#333',
                        titleFont: { size: 14 },
                        bodyFont: { size: 14 },
                        callbacks: {
                            label: (context) => ` ${context.dataset.label}: ${context.parsed.y}`,
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            //  text: xLabel,
                            display: true,
                            font: { size: 14 },
                        },
                        grid: {
                            display: true, // Show grid lines
                            drawBorder: false, // Prevent grid line from drawing on axis
                            borderDash: [5, 5], // Dashed pattern: 5px dash, 5px gap
                            drawOnChartArea: true, // Draw grid lines in the chart area
                            color: 'rgba(0, 0, 0, 0.1)', // Light grid lines
                        },
                        border: {
                            display: true, // Show x-axis line
                            color: '#000', // Distinct axis line color
                            width: 1, // Axis line thickness
                        },
                        ticks: {
                            maxRotation: 45, // Maximum rotation angle for labels (in degrees)
                            minRotation: 45, // Minimum rotation angle for labels (in degrees)
                            autoSkip: true, // Automatically skip labels to avoid overlap
                        }
                    },
                    y: {
                        title: {
                            text: yLabel,
                            display: true,
                            font: { size: 14 },
                        },
                        beginAtZero: true,
                        grid: {
                            display: true, // Show grid lines
                            drawBorder: false, // Prevent grid line from drawing on axis
                            borderDash: [5, 5], // Dashed pattern: 5px dash, 5px gap
                            drawOnChartArea: true, // Draw grid lines in the chart area
                            color: 'rgba(0, 0, 0, 0.1)', // Light grid lines
                        },
                        border: {
                            display: true, // Show y-axis line
                            color: '#000', // Distinct axis line color
                            width: 1, // Axis line thickness
                        },
                        ticks: {
                            stepSize: 10, // Control tick intervals
                        },
                    }
                },
                interaction: {
                    mode: 'nearest',
                    intersect: false,
                }
            }
        },
        transform(data, label) {
            return {
                labels: data.labels,
                datasets: [
                    {
                        label: label,
                        data: data.values,
                        backgroundColor: ['#165dfc'],
                    },
                ]
            };
        }
    },
    created() {
        if (this.store) {
            this.showAnalytics();
        }
    },
};

</script>
