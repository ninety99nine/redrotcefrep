<template>

    <div>

        <!-- Filter Button -->
        <Button
            size="xs"
            type="bare"
            :leftIcon="Funnel"
            :action="openFilterDrawer">
        </Button>

        <Drawer
            placement="right"
            ref="filterDrawer"
            :showFooter="false"
            :scrollOnContent="false">

            <template #content>

                <!-- Header -->
                <div class="flex items-center space-x-2 bg-gray-100 border-b border-gray-300 shadow-sm p-4">

                    <!-- Filter Icon -->
                    <Funnel size="20"></Funnel>

                    <!-- Heading -->
                    <h2>Filters</h2>

                </div>

                <p class="p-4 text-sm bg-indigo-100">
                    Find exactly what you need by applying filters to your data
                </p>

                <div v-if="isLoadingFilters" class="flex justify-center my-8">

                    <!-- Loader -->
                    <Loader></Loader>

                </div>

                <div v-else class="divide-y mb-4">

                    <template
                        :key="index"
                        v-for="(filter, index) in localFilters">

                        <div v-if="showMore ? true : filter.priority">

                            <div
                                @click="toggleFilterVisibility(index)"
                                class="text-sm p-4 bg-gray-50 hover:bg-gray-100 cursor-pointer flex items-center justify-between">

                                <span>{{ filter.label }}</span>

                                <div class="flex items-center space-x-2">

                                    <Pill v-if="countActiveOptions(filter)" type="primary" size="xs" class="flex-shrink-0">{{ countActiveOptions(filter) }}</Pill>

                                    <ChevronUp v-if="filter.active" size="16"></ChevronUp>
                                    <ChevronDown v-else size="16"></ChevronDown>

                                </div>

                            </div>

                            <div v-if="filter.active" class="text-sm p-4">

                                <!-- Filter Options -->
                                <div v-for="(option, index2) in filter.options" :key="index2">

                                    <Input
                                        @click.stop
                                        type="checkbox"
                                        v-model="option.active"
                                        :inputLabel="option.label">
                                    </Input>

                                    <!-- Input Fields for Active Options -->
                                    <div v-if="option.active" class="my-4">

                                        <!-- Date Filter -->
                                        <div v-if="filter.type === 'date'" class="space-y-4">
                                            <Datepicker @click.stop v-model="option.value"></Datepicker>
                                            <Datepicker v-if="['bt', 'bt_ex'].includes(option.operator)" @click.stop v-model="option.value2"></Datepicker>
                                        </div>

                                        <!-- Money Filter -->
                                        <div v-else-if="filter.type === 'money'" class="space-y-4">
                                            <Input type="money" @click.stop v-model="option.value" />
                                            <Input type="money" v-if="['bt', 'bt_ex'].includes(option.operator)" @click.stop v-model="option.value2" />
                                        </div>

                                        <!-- Country Filter -->
                                        <div v-else-if="filter.target === 'country'" class="space-y-4">
                                            <SelectCountry
                                                @click.stop
                                                v-model="option.value"
                                                placeholder="Select country" />
                                            <Input v-if="['in', 'not_in'].includes(option.operator)" type="text" @click.stop v-model="option.value2" placeholder="Enter comma-separated country codes" />
                                        </div>

                                        <!-- Text Filter -->
                                        <div v-else-if="filter.type === 'text'" class="space-y-4">
                                            <Input type="text" @click.stop v-model="option.value" placeholder="Enter value" />
                                        </div>

                                        <!-- Number Filter -->
                                        <div v-else-if="filter.type === 'number'" class="space-y-4">
                                            <Input type="number" @click.stop v-model="option.value" placeholder="Enter number" />
                                            <Input type="number" v-if="['bt', 'bt_ex'].includes(option.operator)" @click.stop v-model="option.value2" placeholder="Enter number" />
                                        </div>

                                        <!-- UUID Filter -->
                                        <div v-else-if="filter.type === 'uuid'" class="space-y-4">
                                            <Input type="text" @click.stop v-model="option.value" placeholder="Enter UUID" />
                                            <Input v-if="['in', 'not_in'].includes(option.operator)" type="text" @click.stop v-model="option.value2" placeholder="Enter comma-separated UUIDs" />
                                        </div>

                                        <!-- Select Filter -->
                                        <div v-else-if="filter.type === 'select'" class="space-y-4">
                                            <Select
                                                @click.stop
                                                v-model="option.value"
                                                :options="filter.options"
                                                placeholder="Select value" />
                                            <Input v-if="['in', 'not_in'].includes(option.operator)" type="text" @click.stop v-model="option.value2" placeholder="Enter comma-separated values" />
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </template>

                </div>

                <!-- Clear Filters Button -->
                <div v-if="!isLoadingFilters" class="flex flex-col items-center px-4 space-y-8 mb-60">

                    <Button
                        size="xs"
                        type="outline"
                        :action="showMoreOrLess"
                        :leftIcon="showMore ? ArrowUp : ArrowDown"
                        v-if="hasPriorityFilters && hasNonPriorityFilters">
                        <span>{{ showMore ? 'show less options' : 'show more options' }}</span>
                    </Button>

                    <Button
                        size="sm"
                        type="light"
                        buttonClass="w-full"
                        :action="clearFilters"
                        v-if="totalActiveFilters">
                        <span>Clear Filters</span>
                    </Button>

                </div>

            </template>

        </Drawer>

    </div>

</template>

<script>
import dayjs from 'dayjs';
import isEqual from 'lodash/isEqual';
import Pill from '@Partials/Pill.vue';
import Input from '@Partials/Input.vue';
import cloneDeep from 'lodash/cloneDeep';
import Button from '@Partials/Button.vue';
import Drawer from '@Partials/Drawer.vue';
import Loader from '@Partials/Loader.vue';
import Select from '@Partials/Select.vue';
import SelectCountry from '@Partials/SelectCountry.vue';
import Datepicker from '@Partials/Datepicker.vue';
import { Funnel, ArrowUp, ArrowDown, ChevronUp, ChevronDown } from 'lucide-vue-next';

export default {
    inject: ['notificationState'],
    components: { Pill, Input, Button, Drawer, Datepicker, Loader, Select, SelectCountry, Funnel, ChevronUp, ChevronDown },
    props: {
        filterExpressions: {
            type: Array,
            default: () => []
        },
        resource: {
            type: String
        },
    },
    emits: ['updatedFilters'],
    data() {
        return {
            Funnel,
            ArrowUp,
            ArrowDown,
            showMore: false,
            localFilters: null,
            filterDrawer: null,
            originalFilters: null,
            isLoadingFilters: false,
            lastEmittedFilters: null,
        };
    },
    watch: {
        localFilters: {
            handler(newVal) {
                this.createFilterExpressions();
            },
            deep: true
        },
    },
    computed: {
        hasFilterExpressions() {
            return this.filterExpressions.length > 0;
        },
        totalActiveFilters() {
            if (this.localFilters == null) return 0;
            return this.localFilters.filter((filter) => {
                return filter.options.some((option) => option.active);
            }).length;
        },
        hasPriorityFilters() {
            if (this.localFilters == null) return false;
            return this.localFilters.filter((filter) => {
                return filter.priority;
            }).length > 0;
        },
        hasNonPriorityFilters() {
            if (this.localFilters == null) return false;
            return this.localFilters.filter((filter) => {
                return !filter.priority;
            }).length > 0;
        },
    },
    methods: {
        countActiveOptions(filter) {
            return filter.options.filter((option) => option.active).length;
        },
        createFilterExpressions() {
            let filters = [];

            for (let filterIndex = 0; filterIndex < this.localFilters.length; filterIndex++) {

                const localFilter = this.localFilters[filterIndex];
                const hasActiveOptions = localFilter.options.some((option) => option.active);

                if (hasActiveOptions) {

                    if (localFilter.type === 'checkboxes') {

                        const activeOptions = localFilter.options.filter((option) => option.active);
                        const optionValues = activeOptions.map((option) => option.value);

                        const label = `${localFilter.label} = ${optionValues.join(', ')}`;
                        const expression = `${localFilter.target}:in:${optionValues.join(',')}`;

                        filters.push({
                            label: label,
                            expression: expression,
                            filterIndex: filterIndex
                        });

                    }else{

                        for (let optionIndex = 0; optionIndex < localFilter.options.length; optionIndex++) {

                            const filterOption = localFilter.options[optionIndex];
                            const isActiveFilterOption = filterOption.active;

                            if (isActiveFilterOption) {

                                let label = null;
                                let expression = null;
                                let value = filterOption.value;
                                let value2 = filterOption.value2;
                                let operator = filterOption.operator;

                                if (['bt', 'bt_ex'].includes(operator)) {
                                    if (!value || !value2) continue; // Skip if values are missing
                                    label = `${localFilter.label} between ${filterOption.value} - ${filterOption.value2} ${
                                        operator == 'bt' ? '(including)' : '(excluding)'
                                    }`;
                                    expression = `${localFilter.target}:${operator}:${value}:${value2}`;
                                } else if (['in', 'not_in'].includes(operator)) {
                                    if (!value && !value2) continue; // Skip if no values
                                    const values = value2 ? value2.split(',').map(v => v.trim()) : [value];
                                    const valuesStr = values.join(',');
                                    label = `${localFilter.label} ${operator === 'in' ? 'in' : 'not in'} ${valuesStr}`;
                                    expression = `${localFilter.target}:${operator}:${valuesStr}`;
                                } else {
                                    label = `${localFilter.label} ${operator ?? '='} ${filterOption.value}`;
                                    expression = `${localFilter.target}:${operator ?? '='}:${value}`;
                                }

                                filters.push({
                                    label: label,
                                    expression: expression,
                                    filterIndex: filterIndex,
                                    optionIndex: optionIndex
                                });
                            }
                        }

                    }

                }
            }

            if (this.filtersHaveChanged(filters, this.lastEmittedFilters)) {
                this.$emit('updatedFilters', filters);
                this.lastEmittedFilters = cloneDeep(filters);
            }
        },
        applyFilterExpressions() {
            this.filterExpressions.forEach((filterExpression) => {
                const parts = filterExpression.split(':');
                const target = parts[0]; // e.g., 'country'
                const operator = parts[1]; // e.g., 'eq'
                const value = parts[2]; // e.g., 'US'
                const value2 = parts[3] ?? null; // e.g., 'US,CA' for 'in'

                const matchingFilter = this.localFilters.find(filter => filter.target === target);
                if (matchingFilter) {
                    matchingFilter.options.forEach((option) => {
                        if (option.value === operator) {
                            option.active = true;
                            if (matchingFilter.type === 'date' && value && dayjs(value).isValid()) {
                                option.value = dayjs(value).format('DD MMM YYYY');
                                if (value2 && dayjs(value2).isValid()) {
                                    option.value2 = dayjs(value2).format('DD MMM YYYY');
                                }
                            } else if (['in', 'not_in'].includes(operator)) {
                                option.value = value.split(',')[0];
                                option.value2 = value;
                            } else {
                                option.value = value;
                                option.value2 = value2;
                            }
                        }
                    });
                }
            });
        },
        filtersHaveChanged(filter1, filter2) {
            var a = cloneDeep(filter1);
            var b = cloneDeep(filter2);
            return !isEqual(a, b);
        },
        openFilterDrawer() {
            if (this.localFilters == null) this.getFilters();
            this.filterDrawer.showDrawer();
        },
        closeFilterDrawer() {
            this.filterDrawer.hideDrawer();
        },
        toggleFilterVisibility(index) {
            if (!this.localFilters[index].active) {
                this.localFilters.forEach((filter, i) => {
                    this.localFilters[i].active = false;
                });
            }
            this.localFilters[index].active = !this.localFilters[index].active;
        },
        showMoreOrLess() {
            this.showMore = !this.showMore;
        },
        removeAppliedFilter(filter) {
            if (filter.hasOwnProperty('filterIndex') && filter.hasOwnProperty('optionIndex')) {
                this.localFilters[filter.filterIndex].options[filter.optionIndex].active = false;
            } else {
                this.localFilters[filter.filterIndex].options.forEach((option) => {
                    option.active = false;
                });
            }
        },
        clearFilters() {
            this.localFilters = cloneDeep(this.originalFilters);
        },
        async getFilters() {
            try {
                if (this.isLoadingFilters) return;
                this.isLoadingFilters = true;
                const config = {
                    params: {
                        'type': this.resource
                    }
                };
                const response = await axios.get('/api/filters', config);
                this.localFilters = response.data.map(filter => {
                    return {
                        ...filter,
                        active: false,
                        options: filter.options.map((option) => {
                            let _option = {
                                ...option,
                                active: false
                            };
                            if (filter.type === 'money') {
                                _option.value = '0.00';
                                _option.value2 = '0.00';
                            } else if (filter.type === 'date') {
                                _option.value = dayjs().subtract(7, 'day').format('YYYY-MM-DD');
                                _option.value2 = dayjs().add(7, 'day').format('YYYY-MM-DD');
                            } else if (['text', 'number', 'uuid'].includes(filter.type) || filter.target === 'country') {
                                _option.value = '';
                                _option.value2 = '';
                            } else if (filter.type === 'select') {
                                _option.value = '';
                                _option.value2 = '';
                            }

                            return _option;
                        })
                    };
                });
                this.originalFilters = cloneDeep(this.localFilters);
                this.applyFilterExpressions();
            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching the filter options';
                this.notificationState.showWarningNotification(message);
                console.error('Failed to fetch filter options:', error);
            } finally {
                this.isLoadingFilters = false;
            }
        }
    },
    mounted() {
        this.filterDrawer = this.$refs.filterDrawer;
        if (this.hasFilterExpressions) this.getFilters();
    },
};
</script>
