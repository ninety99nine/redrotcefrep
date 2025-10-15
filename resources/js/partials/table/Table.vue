<template>

    <!-- Table -->
    <div>

        <slot name="header"></slot>

        <div class="flex justify-between items-center space-x-4 mb-4">

            <!-- Search Input -->
            <Input
                type="search"
                class="w-full"
                :debounced="true"
                v-model="localSearchTerm"
                @input="onInputSearchTerm"
                :placeholder="searchPlaceholder">
            </Input>

            <div v-if="pagination" class="flex justify-center items-center space-x-4">

                <!-- Filter Drawer Button -->
                <FilterDrawer ref="filterDrawer" :resource="resource" :filterExpressions="filterExpressions" @updatedFilters="updatedFilters"></FilterDrawer>

                <!-- Sorting Drawer Button -->
                <SortingDrawer ref="sortingDrawer" :resource="resource" :sortingExpressions="sortingExpressions" @updatedSorting="updatedSorting"></SortingDrawer>

                <!-- Columns Drawer Button -->
                <ColumnsDrawer ref="columnsDrawer" :columns="columns" @updatedColumns="updatedColumns"></ColumnsDrawer>

                <div v-if="$slots.beforeRefreshButton">

                    <!-- Slot Before Refresh Button -->
                    <slot name="beforeRefreshButton"></slot>

                </div>

                <!-- Refresh Button -->
                <Button :action="refresh" type="bare" size="xs" :leftIcon="RotateCcw"></Button>

                <div v-if="$slots.afterRefreshButton">

                    <!-- Slot After Refresh Button -->
                    <slot name="afterRefreshButton"></slot>

                </div>

            </div>

        </div>

        <transition name="fade-1" mode="out-in">
            <div v-if="hasFilterExpressions && !hasFilters" class="flex flex-col items-center mb-4">
                <div class="flex items-center space-x-2">
                    <Loader></Loader>
                    <span class="text-sm text-gray-500">Preparing filters</span>
                </div>
            </div>
            <div v-else-if="hasSortingExpressions && !hasSorting" class="flex flex-col items-center mb-4">
                <div class="flex items-center space-x-2">
                    <Loader></Loader>
                    <span class="text-sm text-gray-500">Preparing sorting</span>
                </div>
            </div>
            <div v-else-if="hasFilters || hasSorting" class="flex flex-wrap gap-2 mb-4">
                <Pill :key="index" v-for="(filter, index) in filters" type="primary" size="xs" :closableAction="() => removeAppliedFilter(filter)">{{ filter.label }}</Pill>
                <Pill :key="index" v-for="(sort, index) in sorting" type="success" size="xs" :closableAction="() => removeAppliedSort(sort)">{{ sort.label }}</Pill>
            </div>
        </transition>

        <!-- Below Toolbar -->
        <slot name="belowToolbar"></slot>

        <div class="relative w-full overflow-x-auto border border-blue-200 rounded-lg">

            <!-- Table Loader -->
            <div v-if="pagination && pagination.meta.total > 0 && (localIsLoading || isSearching)" class="absolute top-0 bottom-0 left-0 right-0 bg-white/50 flex justify-center items-center">
                <Loader></Loader>
            </div>

            <table class="w-full text-left rtl:text-right">

                <!-- Table Head -->
                <thead class="text-sm bg-blue-50" ref="tablehead">
                    <slot name="head"></slot>
                </thead>

                <!-- Pulsing Placeholder Rows -->
                <tbody v-if="!pagination && localIsLoading && totalTableHeaders > 0">

                    <tr v-for="(row, index) in [1,2,3]" :key="index" class="animate-pulse">
                        <td v-for="(column, index) in totalTableHeaders" :key="index">
                            <div class="h-2 bg-blue-200 rounded-full mx-4 my-4"></div>
                        </td>
                    </tr>

                </tbody>

                <!--
                    Table Body Without <tbody> wrapper tag.
                    This slot is mainly used by the Vue Draggable Component which will define the <tbody> wrapper tag itself e.g:

                    The component would define this as follows:

                    <BasicTable>

                        <template #thead>
                            <tr>
                                ...
                            </tr>
                        </template>

                        <template #tbody>
                            <draggable tag="tbody">
                                <tr v-for="item in items">
                                    ...
                                </td>
                            </draggable>
                        </template>

                    </BasicTable>
                -->
                <slot v-else-if="$slots.tbody" name="tbody"></slot>

                <!--
                    Table Body With <tbody> wrapper tag.
                    This slot is maily used by most Components to simply set the <tr> tags only e.g:

                    The component would define this as follows:

                    <BasicTable>

                        <template #thead>
                            <tr>
                                ...
                            </tr>
                        </template>

                        <template #tbody>
                            <tr v-for="item in items">
                                ...
                            </td>
                        </template>

                    </BasicTable>
                -->
                <tbody v-else>
                    <slot name="body"></slot>
                </tbody>

            </table>

            <!-- No Results Desclaimer -->
            <slot v-if="$slots.noResults && !localIsLoading && pagination && pagination.meta.total == 0 && !hasSearchTerm" name="noResults"></slot>

            <div v-else-if="pagination && pagination.meta.total == 0" class="text-sm text-gray-700 text-center py-16 bg-blue-50 border-t border-blue-200">
                <div
                    v-if="localIsLoading || isSearching"
                    class="flex items-center justify-center space-x-2">
                    <Loader></Loader>
                    <span class="text-sm text-gray-500">{{ isSearching ? 'Searching' : 'Loading' }}</span>
                </div>
                <span v-else>No results found</span>
            </div>

        </div>

        <div v-if="pagination && pagination.meta.total > 0" class="flex justify-between items-center mt-4">

            <!-- Results -->
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Showing <span class="font-semibold text-gray-900 dark:text-white">{{ pagination.meta.from }}-{{ pagination.meta.to }}</span> of <span class="font-semibold text-gray-900 dark:text-white">{{ pagination.meta.total }}</span></span>

            <div class="flex items-center space-x-4">

                <Select
                    class="w-40"
                    :search="false"
                    v-model="localPerPage"
                    :options="perPageOptions">
                </Select>

                <!-- Bottom Paginator -->
                <Paginator v-if="pagination" :pagination="pagination" @paginate="paginate"></Paginator>

            </div>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue'
    import Input from '@Partials/Input.vue';
    import Select from '@Partials/Select.vue';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import { RotateCcw } from 'lucide-vue-next';
    import { isNotEmpty } from '@Utils/stringUtils';
    import { generateUniqueId } from '@Utils/generalUtils.js';
    import Paginator from '@Partials/table/components/Paginator.vue';
    import FilterDrawer from '@Partials/table/components/FilterDrawer.vue';
    import ColumnsDrawer from '@Partials/table/components/ColumnsDrawer.vue';
    import SortingDrawer from '@Partials/table/components/SortingDrawer.vue';

    export default {
        components: { Pill, Button, Input, Select, Paginator, Loader, FilterDrawer, ColumnsDrawer, SortingDrawer },
        props: {
            isLoading: {
                type: Boolean,
                default: false
            },
            searchTerm: {
                type: String,
                default: null
            },
            searchPlaceholder: {
                type: String,
                default: null
            },
            pagination: {
                type: Object
            },
            perPage: {
                type: String,
                default: '15'
            },
            resource: {
                type: String
            },
            columns: {
                type: Array,
                default: () => []
            },
            filterExpressions: {
                type: Array,
                default: () => []
            },
            sortingExpressions: {
                type: Array,
                default: () => []
            }
        },
        emits: ['search', 'refresh', 'paginate', 'updatedColumns', 'updatedFilters', 'updatedSorting', 'updatedPerPage'],
        data() {
            return {
                RotateCcw,
                modal: null,
                filters: [],
                sorting: [],
                filterDrawer: null,
                isSearching: false,
                sortingDrawer: null,
                totalTableHeaders: 0,
                localPerPage: this.perPage,
                localIsLoading: this.isLoading,
                localSearchTerm: this.searchTerm,
                uniqueModalId: generateUniqueId('modal')
            }
        },
        watch: {
            isLoading(newValue) {
                this.localIsLoading = newValue;
                this.isSearching = newValue && this.hasSearchTerm;
            },
            localSearchTerm(newValue) {
                this.$emit('search', newValue);
            },
            localPerPage(newValue) {
                this.$emit('updatedPerPage', newValue);
            },
            pagination(newValue) {
                if(newValue) {
                    this.$nextTick(() => {
                        this.filterDrawer = this.$refs.filterDrawer;
                        this.sortingDrawer = this.$refs.sortingDrawer;
                    });
                }
            },
            columns: {
                handler() {
                    this.$nextTick(() => {
                        this.totalTableHeaders = this.$refs.tablehead.querySelectorAll('th').length;
                    });
                },
                deep: true
            },
        },
        computed: {
            hasSearchTerm() {
                return this.isNotEmpty(this.localSearchTerm);
            },
            hasFilters() {
                return this.filters.length > 0;
            },
            hasSorting() {
                return this.sorting.length > 0;
            },
            hasFilterExpressions() {
                return this.filterExpressions.length > 0;
            },
            hasSortingExpressions() {
                return this.sortingExpressions.length > 0;
            },
            perPageOptions() {
                return ['15', '50', '100', '200'].map(value => ({
                    label: `${value} per page`,
                    value: value
                }));
            }
        },
        methods: {
            isNotEmpty,
            refresh() {
                if(!this.localIsLoading) this.$emit('refresh');
            },
            paginate(url) {
                this.$emit('paginate', url);
            },
            updatedColumns(columns) {
                this.$emit('updatedColumns', columns);
            },
            updatedFilters(filters) {
                this.filters = filters;
                this.$emit('updatedFilters', filters);
            },
            updatedSorting(sorting) {
                this.sorting = sorting;
                this.$emit('updatedSorting', sorting);
            },
            removeAppliedFilter(filter) {
                this.filterDrawer.removeAppliedFilter(filter);
            },
            removeAppliedSort(sort) {
                this.sortingDrawer.removeAppliedSort(sort);
            },
            onInputSearchTerm(event) {
                const searchTerm = event.target.value;
                if(searchTerm.length) {
                    this.isSearching = true;
                }else{
                    this.isSearching = false;
                }
                this.localIsLoading = true;
            }
        },
        mounted() {
            this.totalTableHeaders = this.$refs.tablehead.querySelectorAll('th').length;
        }
    };
</script>
