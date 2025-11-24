<template>

    <div class="pt-24 px-8 relative select-none">

        <!-- No Reviews -->
        <div
            v-if="hasInitialResults == false"
            class="flex flex-col items-center justify-center bg-linear-to-b from-white p-8 rounded-2xl mb-8">

            <div class="bg-blue-200 text-blue-900 rounded-full p-10 mt-8 mb-16">
                <Star size="40"></Star>
            </div>

            <div class="text-center max-w-lg">

                <h1 class="text-3xl font-extrabold mb-3">
                Build Trust with Reviews
                </h1>

                <p class="text-base leading-relaxed">
                Customer feedback will show up here as they share their experience. Start by adding a review or inviting your first happy customer.
                </p>

            </div>

            <div class="mt-10">

                <Button
                size="lg"
                type="primary"
                :leftIcon="Plus"
                leftIconSize="20"
                :skeleton="!store"
                :action="onAddReview">
                <span class="ml-1">Add Review</span>
                </Button>

            </div>

        </div>

        <div class="relative bg-white p-4 rounded-md mb-4">

            <h1 class="font-semibold mb-4">Share and collect reviews</h1>

            <div v-if="addReviewLink" class="flex items-center space-x-2">

                <Copy :text="addReviewLink" class="min-w-96"></Copy>

                <Button
                    size="xs"
                    type="light"
                    :leftIcon="ExternalLink"
                    :action="openAddReviewLink">
                    <span class="ml-1">Reviews</span>
                </Button>

            </div>

        </div>

        <div
            v-if="hasInitialResults != false"
            class="relative bg-white/80 p-4 rounded-md mb-60">

            <h1 class="text-lg font-semibold mb-4">Reviews</h1>

            <!-- Reviews Table -->
            <Table
                @search="search"
                :columns="columns"
                :perPage="perPage"
                resource="reviews"
                @paginate="paginate"
                @refresh="showReviews"
                :searchTerm="searchTerm"
                :pagination="pagination"
                :isLoading="isLoadingReviews"
                @updatedColumns="updatedColumns"
                @updatedFilters="updatedFilters"
                @updatedSorting="updatedSorting"
                @updatedPerPage="updatedPerPage"
                :filterExpressions="filterExpressions"
                :sortingExpressions="sortingExpressions"
                searchPlaceholder="Name, comment or rating e.g 3">

                <template #afterRefreshButton>

                    <div class="flex items-center space-x-2">

                        <!-- Export Reviews Button -->
                        <Button
                            size="xs"
                            type="outline"
                            :action="showExportReviewsModal"
                            v-if="((pagination ?? {}).meta ?? {}).total > 0">
                            <span>Export</span>
                        </Button>

                        <!-- Add Review Button -->
                        <Button
                            size="xs"
                            type="primary"
                            :leftIcon="Plus"
                            :action="onAddReview">
                            <span>Add Review</span>
                        </Button>

                    </div>

                </template>

                <!-- Select Action -->
                <template #belowToolbar>
                    <div :class="[{ 'hidden' : totalCheckedRows == 0 }, 'bg-gray-50 border border-gray-200 flex items-center mb-2 p-4 rounded-lg shadow space-x-2']">
                        <span class="text-sm">Actions: </span>
                        <Dropdown
                            ref="actionDropdown"
                            dropdownClasses="w-72"
                            :options="bulkSelectionOptions">
                            <template #triggerText>
                                <span>Select Action ({{ `${totalCheckedRows} selected` }})</span>
                            </template>
                        </Dropdown>
                    </div>
                </template>

                <!-- Table Head -->
                <template #head>
                    <tr>
                        <th scope="col" class="whitespace-nowrap align-center px-4 py-4">
                            <Input
                                type="checkbox"
                                v-model="selectAll">
                            </Input>
                        </th>
                        <template
                            :key="index"
                            v-for="(column, index) in columns">
                            <th
                                scope="col"
                                v-if="column.active"
                                class="whitespace-nowrap align-center pr-4 py-4">
                                {{ column.name }}
                            </th>
                        </template>
                        <th scope="col" class="whitespace-nowrap align-center pr-4 py-4"></th>
                    </tr>
                </template>

                <!-- Table Body -->
                <template #body>
                    <tr
                        :key="review.id"
                        v-for="review in reviews"
                        @click.stop="onView(review)"
                        :class="[checkedRows[review.id] ? 'bg-blue-50' : 'bg-white hover:bg-gray-50', 'group cursor-pointer border-b border-blue-100']">
                        <template
                            :key="columnIndex"
                            v-for="(column, columnIndex) in columns">
                            <td
                                @click.stop
                                v-if="columnIndex == 0"
                                class="whitespace-nowrap align-center px-4 py-4">
                                <Input
                                    type="checkbox"
                                    v-model="checkedRows[review.id]">
                                </Input>
                            </td>
                            <template v-if="column.active">

                                <!-- Name -->
                                <td v-if="column.name == 'Name'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <span v-if="review.name">{{ review.name }}</span>
                                    <NoDataPlaceholder v-else></NoDataPlaceholder>
                                </td>

                                <!-- Mobile -->
                                <td v-else-if="column.name == 'Mobile'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <span v-if="review.mobile_number">{{ review.mobile_number.national }}</span>
                                    <NoDataPlaceholder v-else></NoDataPlaceholder>
                                </td>

                                <!-- Rating -->
                                <td v-else-if="column.name == 'Rating'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="flex space-x-1">
                                        <Star
                                            v-for="star in 5"
                                            :key="star"
                                            :size="16"
                                            :fill="star <= review.rating ? 'gold' : 'none'"
                                            :stroke="star <= review.rating ? 'gold' : 'gray'"
                                        />
                                    </div>
                                </td>
                                <!-- Comment -->
                                <td v-else-if="column.name == 'Comment'" class="align-center pr-4 py-4 text-sm">
                                    <div class="w-96">
                                        <span v-if="review.comment">{{ review.comment }}</span>
                                        <NoDataPlaceholder v-else></NoDataPlaceholder>
                                    </div>
                                </td>
                                <!-- Visibility -->
                                <td v-else-if="column.name == 'Visibility'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <Pill :type="review.visible ? 'success' : 'warning'" size="xs">{{ review.visible ? 'visible' : 'hidden'}}</Pill>
                                </td>
                                <!-- Reviewed At -->
                                <td v-else-if="column.name == 'Reviewed At'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="flex space-x-1 items-center">
                                        <span>{{ formattedDatetime(review.reviewed_at) }}</span>
                                        <Popover
                                            placement="top"
                                            class="opacity-0 group-hover:opacity-100"
                                            :content="formattedRelativeDate(review.reviewed_at)">
                                        </Popover>
                                    </div>
                                </td>
                                <!-- Created Date -->
                                <td v-else-if="column.name == 'Created Date'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="flex space-x-1 items-center">
                                        <span>{{ formattedDatetime(review.created_at) }}</span>
                                        <Popover
                                            placement="top"
                                            class="opacity-0 group-hover:opacity-100"
                                            :content="formattedRelativeDate(review.created_at)">
                                        </Popover>
                                    </div>
                                </td>
                            </template>
                            <!-- Actions -->
                            <td v-if="columnIndex == (columns.length - 1)" class="align-center pr-4 py-4">
                                <div class="flex items-center space-x-4">
                                    <span v-if="!isDeletingReview(review)" @click.stop.prevent="onView(review)" class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">View</span>
                                    <Loader v-if="isDeletingReview(review)" type="danger">
                                        <span class="text-xs ml-2">Deleting...</span>
                                    </Loader>
                                    <span v-else @click.stop.prevent="showDeleteConfirmationModal(review)" class="text-sm font-medium text-red-600 dark:text-red-500 hover:underline">Delete</span>
                                </div>
                            </td>
                        </template>
                    </tr>
                </template>

            </Table>
        </div>

        <!-- Export Reviews -->
        <Modal
            approveType="primary"
            :scrollOnContent="false"
            ref="exportReviewsModal"
            approveText="Export Reviews"
            :approveAction="exportReviews"
            :leftApproveIcon="ArrowDownToLine"
            :approveLoading="isExportingReviews">
            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Export Reviews</p>
                <div class="space-y-4 mb-8">
                    <Select
                        class="w-full"
                        :search="false"
                        v-model="exportLimit"
                        :options="exportLimits"
                        label="Number of reviews">
                    </Select>
                    <Select
                        class="w-full"
                        label="Format"
                        :search="false"
                        v-model="exportFormat"
                        :options="exportFormats">
                    </Select>
                    <Input
                        type="checkbox"
                        v-if="hasFilterExpressions"
                        v-model="exportWithFilters"
                        inputLabel="Apply Filters">
                    </Input>
                    <Input
                        type="checkbox"
                        v-if="hasSortingExpressions"
                        v-model="exportWithSorting"
                        inputLabel="Apply Sorting">
                    </Input>
                </div>
            </template>
        </Modal>

        <!-- Update Reviews -->
        <Modal
            approveType="primary"
            :scrollOnContent="false"
            ref="updateReviewsModal"
            :leftApproveIcon="RefreshCcw"
            approveText="Update Reviews"
            :approveLoading="isUpdatingReviews"
            :approveAction="() => updateReviews('visibility')">
            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Update Reviews</p>
                <div class="space-y-4 mb-8">

                    <Select
                        class="w-full"
                        :search="false"
                        v-model="visible"
                        label="Visibility"
                        :options="visibilityTypes"
                        tooltipContent="Set whether the review is visible to others">
                    </Select>

                </div>
            </template>
        </Modal>

        <!-- Delete Reviews -->
        <Modal
            approveType="danger"
            ref="deleteReviewsModal"
            :leftApproveIcon="Trash2"
            :approveAction="deleteReviews"
            :approveLoading="isDeletingReviews"
            :approveText="totalCheckedRows == 1 ? 'Delete Review' : 'Delete Reviews'">
            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Delete Reviews</p>
                <div class="flex space-x-2 items-center p-4 text-xs bg-red-50 rounded-lg mb-8">
                    <svg class="w-6 h-6 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span class="text-red-500">Are you sure you want to delete {{ totalCheckedRows > 1 ? `these ${totalCheckedRows} reviews` : 'this review' }}?</span>
                </div>
            </template>
        </Modal>

        <!-- Confirm Delete Review -->
        <Modal
            approveType="danger"
            ref="deleteReviewModal"
            :leftApproveIcon="Trash2"
            approveText="Delete Review"
            :approveAction="deleteReview"
            :approveLoading="isDeletingReview(deletableReview)">
            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Delete</p>
                <p v-if="deletableReview" class="mb-8">Are you sure you want to permanently delete the review by <span class="font-bold text-black">{{ deletableReview.user?.name }}</span>?</p>
            </template>
        </Modal>
    </div>
</template>

<script>
    import axios from 'axios';
    import isEqual from 'lodash.isequal';
    import Copy from '@Partials/Copy.vue';
    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import Table from '@Partials/table/Table.vue';
    import { isNotEmpty } from '@Utils/stringUtils';
    import NoDataPlaceholder from '@Partials/table/components/NoDataPlaceholder.vue';
    import { formattedDate, formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';
    import { Move, Star, Plus, Trash2, RefreshCcw, ExternalLink, ArrowDownToLine } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Move, Copy, Star, Pill, Input, Modal, Loader, Button, Select, Popover, Dropdown, Table, NoDataPlaceholder
        },
        data() {
            return {
                Plus,
                Star,
                Trash2,
                RefreshCcw,
                ExternalLink,
                reviews: [],
                perPage: '15',
                visible: true,
                ArrowDownToLine,
                checkedRows: [],
                pagination: null,
                searchTerm: null,
                selectAll: false,
                latestRequestId: 0,
                exportFormat: 'csv',
                filterExpressions: [],
                deletableReview: null,
                sortingExpressions: [],
                hasInitialResults: null,
                cancelTokenSource: null,
                exportWithFilters: true,
                exportWithSorting: true,
                isDeletingReviewIds: [],
                isLoadingReviews: false,
                isUpdatingReviews: false,
                isExportingReviews: false,
                exportLimit: 'all reviews',
                columns: this.prepareColumns(),
                exportLimits: [
                    { label: 'Current Page', value: 'current page'},
                    { label: 'All Reviews', value: 'all reviews' }
                ],
                exportFormats: [
                    { label: 'CSV (Plain Data File)', value: 'csv' },
                    { label: 'Excel (XLSX)', value: 'xlsx' },
                    { label: 'PDF (Printable Document)', value: 'pdf' }
                ],
                bulkSelectionOptions: [
                    {
                        label: 'Change Visibility',
                        action: this.showUpdateReviewsModal
                    },
                    {
                        label: 'Delete',
                        action: this.showDeleteReviewsModal
                    }
                ],
                visibilityTypes: [
                    { label: 'Visible', value: true },
                    { label: 'Hidden', value: false }
                ]
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.showReviews();
                }
            },
            selectAll(newValue) {
                this.checkedRows = this.reviews.reduce((acc, review) => {
                    acc[review.id] = newValue;
                    return acc;
                }, {});
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasSearchTerm() {
                return this.isNotEmpty(this.searchTerm);
            },
            isDeletingReviews() {
                return this.isDeletingReviewIds.length > 0;
            },
            totalCheckedRows() {
                return Object.values(this.checkedRows).filter(checked => checked).length;
            },
            hasFilterExpressions() {
                return this.filterExpressions.length > 0;
            },
            hasSortingExpressions() {
                return this.sortingExpressions.length > 0;
            },
            reviewsLink() {
                return this.store ? `${this.store.web_link}/reviews` : null
            },
            addReviewLink() {
                return this.store ? `${this.store.web_link}/reviews/create` : null
            }
        },
        methods: {
            isNotEmpty,
            formattedDate,
            formattedDatetime,
            formattedRelativeDate,
            prepareColumns() {
                const columnNames = ['Name', 'Mobile', 'Rating', 'Comment', 'Visibility', 'Reviewed At', 'Created Date'];
                const defaultColumnNames = ['Name', 'Mobile', 'Rating', 'Comment', 'Visibility', 'Created Date'];
                return columnNames.map(name => ({
                    name,
                    active: defaultColumnNames.includes(name),
                    priority: defaultColumnNames.includes(name)
                }));
            },
            showExportReviewsModal() {
                this.$refs.exportReviewsModal.showModal();
            },
            showUpdateReviewsModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.updateReviewsModal.showModal();
            },
            showDeleteReviewsModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.deleteReviewsModal.showModal();
            },
            showDeleteConfirmationModal(review) {
                this.deletableReview = review;
                this.$refs.deleteReviewModal.showModal();
            },
            isDeletingReview(review) {
                if(review == null) return false;
                return this.isDeletingReviewIds.findIndex((id) => id == review.id) != -1;
            },
            openAddReviewLink() {
                window.open(this.reviewsLink, '_blank');
            },
            onView(review) {
                this.$router.push({
                    name: 'edit-review',
                    params: {
                        review_id: review.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            onAddReview() {
                this.$router.push({
                    name: 'create-review',
                    query: { store_id: this.store.id }
                });
            },
            paginate(page) {
                this.showReviews(page);
            },
            search(searchTerm) {
                this.searchTerm = searchTerm;
                this.showReviews();
            },
            updatedColumns(columns) {
                this.columns = columns;
            },
            updatedFilters(filters) {
                const newFilterExpressions = filters.map((filter) => filter.expression);
                if(!isEqual(this.filterExpressions, newFilterExpressions)) {
                    this.filterExpressions = newFilterExpressions;
                    this.showReviews();
                }
            },
            updatedSorting(sorting) {
                const newSortingExpressions = sorting.map((sort) => sort.expression);
                if(!isEqual(this.sortingExpressions, newSortingExpressions)) {
                    this.sortingExpressions = newSortingExpressions;
                    this.showReviews();
                }
            },
            updatedPerPage(perPage) {
                this.perPage = perPage;
                this.showReviews();
            },
            async showReviews(page = 1) {

                const currentRequestId = ++this.latestRequestId;

                try {

                    this.isLoadingReviews = true;

                    if (this.cancelTokenSource) {
                        this.cancelTokenSource.cancel('Request superseded by a newer one');
                    }

                    this.cancelTokenSource = axios.CancelToken.source();

                    let config = {
                        params: {
                            page: page,
                            per_page: this.perPage,
                            store_id: this.store.id,
                            association: 'team member'
                        },
                        cancelToken: this.cancelTokenSource.token
                    };

                    if(this.hasSearchTerm) config.params['search'] = this.searchTerm;

                    if(this.hasFilterExpressions) {
                        config.params['_filters'] = this.filterExpressions.join('|');
                    }

                    if(this.hasSortingExpressions) {
                        config.params['_sort'] = this.sortingExpressions.join('|');
                    }

                    const response = await axios.get(`/api/reviews`, config);

                    if (currentRequestId !== this.latestRequestId) return;

                    if(this.pagination == null) {
                        this.hasInitialResults = response.data.meta.total > 0;
                    }

                    this.pagination = response.data;
                    this.reviews = this.pagination.data;

                    this.checkedRows = this.reviews.reduce((acc, review) => {
                        acc[review.id] = false;
                        return acc;
                    }, {});

                } catch (error) {
                    if (axios.isCancel(error)) return;
                    if (currentRequestId !== this.latestRequestId) return;
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching reviews';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch reviews:', error);
                } finally {
                    if (currentRequestId !== this.latestRequestId) return;
                    this.isLoadingReviews = false;
                    this.cancelTokenSource = null;
                }
            },
            async exportReviews() {
                try {
                    if(this.isExportingReviews) return;
                    this.isExportingReviews = true;
                    let config = {
                        params: {
                            _export: '1',
                            store_id: this.store.id,
                            export_format: this.exportFormat,
                            export_limit: this.exportLimit == 'current page' ? this.pagination.meta.to : null,
                            export_offset: this.exportLimit == 'current page' ? this.pagination.meta.from - 1 : null,
                        },
                        responseType: 'blob'
                    };
                    if(this.hasSearchTerm) config.params['search'] = this.searchTerm;
                    if(this.hasFilterExpressions) {
                        config.params['_filters'] = this.filterExpressions.join('|');
                    }
                    if(this.hasSortingExpressions) {
                        config.params['_sort'] = this.sortingExpressions.join('|');
                    }
                    const response = await axios.get(`/api/reviews`, config);
                    let url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `reviews.${this.exportFormat}`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while exporting reviews';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to export reviews:', error);
                } finally {
                    this.isExportingReviews = false;
                    this.$refs.exportReviewsModal.hideModal();
                }
            },
            async updateReviews(type) {
                try {

                    if(this.isUpdatingReviews) return;

                    const data = {
                        store_id: this.store.id,
                        reviews: this.reviews.filter(review => this.checkedRows[review.id]).map(review => ({ id: review.id }))
                    };

                    if(type == 'visibility') {

                        data['reviews'] = data['reviews'].map(review => ({
                            ...review,
                            visible: this.visible
                        }));

                    }

                    if(data['reviews'].length > 0) {

                        this.isUpdatingReviews = true;
                        await axios.put(`/api/reviews`, data);

                        this.showReviews();

                        this.notificationState.showSuccessNotification('Reviews updated');

                        data['reviews'].forEach(review => {
                            if (this.checkedRows[review.id] !== undefined) {
                                this.checkedRows[review.id] = false;
                            }
                        });

                        this.selectAll = false;

                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating reviews';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update reviews:', error);
                } finally {
                    this.isUpdatingReviews = false;
                    this.$refs.updateReviewsModal.hideModal();
                }
            },
            async deleteReviews() {
                try {
                    const reviewIds = this.reviews.filter(review => this.checkedRows[review.id]).map(review => review.id)
                        .filter(reviewId => !this.isDeletingReviewIds.includes(reviewId));
                    if (reviewIds.length === 0) return;
                    this.isDeletingReviewIds.push(...reviewIds);
                    const config = {
                        data: {
                            store_id: this.store.id,
                            review_ids: reviewIds
                        }
                    };
                    await axios.delete(`/api/reviews`, config);
                    this.notificationState.showSuccessNotification(reviewIds.length == 1 ? 'Review deleted' : 'Reviews deleted');
                    this.reviews = this.reviews.filter(review => !reviewIds.includes(review.id));
                    if(this.reviews.length == 0) this.showReviews();
                    this.isDeletingReviewIds = this.isDeletingReviewIds.filter(id => !reviewIds.includes(id));
                    reviewIds.forEach(reviewId => {
                        if (this.checkedRows[reviewId] !== undefined) {
                            this.checkedRows[reviewId] = false;
                        }
                    });
                    this.selectAll = false;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting reviews';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete reviews:', error);
                } finally {
                    this.$refs.deleteReviewsModal.hideModal();
                }
            },
            async deleteReview() {
                try {
                    if(this.isDeletingReviewIds.includes(this.deletableReview.id)) return;
                    this.isDeletingReviewIds.push(this.deletableReview.id);
                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    };
                    await axios.delete(`/api/reviews/${this.deletableReview.id}`, config);
                    this.notificationState.showSuccessNotification('Review deleted');
                    this.reviews = this.reviews.filter(review => review.id != this.deletableReview.id);
                    if(this.reviews.length == 0) this.showReviews();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting review';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete review:', error);
                } finally {
                    this.isDeletingReviewIds.splice(this.isDeletingReviewIds.findIndex((id) => id == this.deletableReview.id), 1);
                    this.$refs.deleteReviewModal.hideModal();
                }
            }
        },
        created() {
            this.isLoadingReviews = true;
            this.searchTerm = this.$route.query.searchTerm;
            if(this.$route.query.filterExpressions) this.filterExpressions = this.$route.query.filterExpressions.split('|');
            if(this.$route.query.sortingExpressions) this.sortingExpressions = this.$route.query.sortingExpressions.split('|');
            if(this.store) this.showReviews();
        }
    };
</script>
