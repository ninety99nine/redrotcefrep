<template>

    <div class="pt-24 px-8 relative select-none">

        <!-- No Categories -->
        <div
            v-if="hasInitialResults == false"
            class="flex flex-col items-center justify-center bg-linear-to-b from-white p-8 rounded-2xl">

            <div class="bg-blue-200 text-blue-900 rounded-full p-10 mt-8 mb-16">
                <Boxes size="40"></Boxes>
            </div>

            <div class="text-center max-w-md">

                <h1 class="text-3xl font-extrabold mb-3">
                    Organize with Product Categories
                </h1>

                <p class="text-base leading-relaxed">
                    Categories help you group similar products together. Create your first category to make browsing easier for customers.
                </p>

            </div>

            <div class="mt-10">

                <Button
                    size="lg"
                    type="primary"
                    :leftIcon="Plus"
                    leftIconSize="20"
                    :skeleton="!store"
                    :action="onAddCategory">
                    <span class="ml-1">Create Category</span>
                </Button>

            </div>

        </div>

        <div
            v-else
            class="relative bg-white/80 p-4 rounded-md mb-60">

            <h1 class="text-lg font-semibold mb-4">Categories</h1>

            <!-- Categories Table -->
            <Table
                @search="search"
                :columns="columns"
                :perPage="perPage"
                @paginate="paginate"
                resource="categories"
                @refresh="showCategories"
                :searchTerm="searchTerm"
                :pagination="pagination"
                :isLoading="isLoadingCategories"
                @updatedColumns="updatedColumns"
                @updatedFilters="updatedFilters"
                @updatedSorting="updatedSorting"
                @updatedPerPage="updatedPerPage"
                searchPlaceholder="Category name"
                :filterExpressions="filterExpressions"
                :sortingExpressions="sortingExpressions">

                <template #afterRefreshButton>

                    <div class="flex items-center space-x-2">

                        <!-- Add Category Button -->
                        <Button
                            size="xs"
                            type="primary"
                            :leftIcon="Plus"
                            :action="onAddCategory">
                            <span>Add Category</span>
                        </Button>

                    </div>

                </template>

                <!-- Select Action -->
                <template #belowToolbar>

                    <div :class="[{ 'hidden' : totalCheckedRows == 0 }, 'bg-gray-50 border border-gray-200 flex items-center mb-2 p-4 rounded-lg shadow space-x-2']">

                        <span class="text-sm">Actions: </span>

                        <!-- Action Trigger -->
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

                        <!-- Checkbox -->
                        <th scope="col" class="whitespace-nowrap align-center px-4 py-4">
                            <Input
                                type="checkbox"
                                v-model="selectAll">
                            </Input>
                        </th>

                        <!-- Table Column Names -->
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

                        <!-- Actions -->
                        <th scope="col" class="whitespace-nowrap align-center pr-4 py-4"></th>

                    </tr>

                </template>

                <!-- Table Body -->
                <template #body>

                    <tr
                        :key="category.id"
                        @click.stop="onView(category)" v-for="category in categories"
                        :class="[checkedRows[category.id] ? 'bg-blue-50' : 'bg-white hover:bg-gray-50', 'group cursor-pointer border-b border-blue-100']">

                        <template
                            :key="columnIndex"
                            v-for="(column, columnIndex) in columns">

                            <!-- Checkbox -->
                            <td
                                @click.stop
                                v-if="columnIndex == 0"
                                class="whitespace-nowrap align-center px-4 py-4">

                                <Input
                                    type="checkbox"
                                    v-model="checkedRows[category.id]">
                                </Input>

                            </td>

                            <template v-if="column.active">

                                <!-- Name -->
                                <td v-if="column.name == 'Name'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex space-x-1 items-center">

                                        <div class="flex space-x-2 items-center">
                                            <div
                                                v-if="category.photo"
                                                class="flex items-center justify-center w-10 h-10">

                                                <img class="w-full max-h-full object-contain rounded-lg shrink-0" :src="category.photo.path">

                                            </div>
                                            <span>{{ category.name }}</span>
                                        </div>

                                        <Popover
                                            placement="top"
                                            v-if="category.description"
                                            wrapperClasses="opacity-0 group-hover:opacity-100">

                                            <template #content>

                                                <div class="min-w-40 p-4">

                                                    <p class="text-sm border-b border-gray-300 pb-2 mb-2">{{ category.name }}</p>
                                                    <p class="text-sm">{{ category.description }}</p>

                                                </div>

                                            </template>

                                        </Popover>
                                    </div>

                                </td>

                                <!-- Description -->
                                <td v-else-if="column.name == 'Description'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="w-40">
                                        <span v-if="category.description">{{ category.description }}</span>
                                        <NoDataPlaceholder v-else></NoDataPlaceholder>
                                    </div>
                                </td>

                                <!-- Products -->
                                <td v-else-if="column.name == 'Products'" class="align-center pr-4 py-4 text-sm">
                                    <Pill type="light" size="xs">{{ category.products_count ? category.products_count : 'none' }}</Pill>
                                </td>

                                <!-- Visibility -->
                                <td v-else-if="column.name == 'Visibility'" class="align-center pr-4 py-4 text-sm">
                                    <Pill :type="category.visible ? 'success' : 'warning'" size="xs">{{ category.visible ? 'visible' : 'hidden' }}</Pill>
                                </td>

                                <!-- Created Date -->
                                <td v-else-if="column.name == 'Created Date'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="flex space-x-1 items-center">
                                        <span>{{ formattedDatetime(category.created_at) }}</span>
                                        <Popover
                                            placement="top"
                                            class="opacity-0 group-hover:opacity-100"
                                            :content="formattedRelativeDate(category.created_at)">
                                        </Popover>
                                    </div>
                                </td>

                            </template>

                            <!-- Actions -->
                            <td v-if="columnIndex == (columns.length - 1)" class="align-center pr-4 py-4">

                                <div class="flex items-center space-x-4">

                                    <!-- View Button -->
                                    <span v-if="!isDeletingCategory(category)" @click.stop.prevent="onView(category)" class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">View</span>

                                    <!-- Deleting Loader -->
                                    <Loader v-if="isDeletingCategory(category)" type="danger">
                                        <span class="text-xs ml-2">Deleting...</span>
                                    </Loader>

                                    <!-- Delete Button -->
                                    <span v-else @click.stop.prevent="showDeleteConfirmationModal(category)" class="text-sm font-medium text-red-600 dark:text-red-500 hover:underline">Delete</span>

                                </div>

                            </td>

                        </template>

                    </tr>

                </template>

            </Table>

        </div>

        <!-- Send To Whatsapp -->
        <Modal
            approveText="Send"
            approveType="success"
            :scrollOnContent="false"
            ref="sendToWhatsappModal"
            :approveAction="sendToWhatsapp">

            <template #approveIcon>
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48" width="96px" height="96px" fill-rule="evenodd" clip-rule="evenodd">
                    <path fill="#fff" d="M4.868,43.303l2.694-9.835C5.9,30.59,5.026,27.324,5.027,23.979C5.032,13.514,13.548,5,24.014,5c5.079,0.002,9.845,1.979,13.43,5.566c3.584,3.588,5.558,8.356,5.556,13.428c-0.004,10.465-8.522,18.98-18.986,18.98c-0.001,0,0,0,0,0h-0.008c-3.177-0.001-6.3-0.798-9.073-2.311L4.868,43.303z"/>
                    <path fill="#fff" d="M4.868,43.803c-0.132,0-0.26-0.052-0.355-0.148c-0.125-0.127-0.174-0.312-0.127-0.483l2.639-9.636c-1.636-2.906-2.499-6.206-2.497-9.556C4.532,13.238,13.273,4.5,24.014,4.5c5.21,0.002,10.105,2.031,13.784,5.713c3.679,3.683,5.704,8.577,5.702,13.781c-0.004,10.741-8.746,19.48-19.486,19.48c-3.189-0.001-6.344-0.788-9.144-2.277l-9.875,2.589C4.953,43.798,4.911,43.803,4.868,43.803z"/>
                    <path fill="#cfd8dc" d="M24.014,5c5.079,0.002,9.845,1.979,13.43,5.566c3.584,3.588,5.558,8.356,5.556,13.428c-0.004,10.465-8.522,18.98-18.986,18.98h-0.008c-3.177-0.001-6.3-0.798-9.073-2.311L4.868,43.303l2.694-9.835C5.9,30.59,5.026,27.324,5.027,23.979C5.032,13.514,13.548,5,24.014,5 M24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974 M24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974 M24.014,4C24.014,4,24.014,4,24.014,4C12.998,4,4.032,12.962,4.027,23.979c-0.001,3.367,0.849,6.685,2.461,9.622l-2.585,9.439c-0.094,0.345,0.002,0.713,0.254,0.967c0.19,0.192,0.447,0.297,0.711,0.297c0.085,0,0.17-0.011,0.254-0.033l9.687-2.54c2.828,1.468,5.998,2.243,9.197,2.244c11.024,0,19.99-8.963,19.995-19.98c0.002-5.339-2.075-10.359-5.848-14.135C34.378,6.083,29.357,4.002,24.014,4L24.014,4z"/>
                    <path fill="#40c351" d="M35.176,12.832c-2.98-2.982-6.941-4.625-11.157-4.626c-8.704,0-15.783,7.076-15.787,15.774c-0.001,2.981,0.833,5.883,2.413,8.396l0.376,0.597l-1.595,5.821l5.973-1.566l0.577,0.342c2.422,1.438,5.2,2.198,8.032,2.199h0.006c8.698,0,15.777-7.077,15.78-15.776C39.795,19.778,38.156,15.814,35.176,12.832z"/>
                    <path fill="#fff" fill-rule="evenodd" d="M19.268,16.045c-0.355-0.79-0.729-0.806-1.068-0.82c-0.277-0.012-0.593-0.011-0.909-0.011c-0.316,0-0.83,0.119-1.265,0.594c-0.435,0.475-1.661,1.622-1.661,3.956c0,2.334,1.7,4.59,1.937,4.906c0.237,0.316,3.282,5.259,8.104,7.161c4.007,1.58,4.823,1.266,5.693,1.187c0.87-0.079,2.807-1.147,3.202-2.255c0.395-1.108,0.395-2.057,0.277-2.255c-0.119-0.198-0.435-0.316-0.909-0.554s-2.807-1.385-3.242-1.543c-0.435-0.158-0.751-0.237-1.068,0.238c-0.316,0.474-1.225,1.543-1.502,1.859c-0.277,0.317-0.554,0.357-1.028,0.119c-0.474-0.238-2.002-0.738-3.815-2.354c-1.41-1.257-2.362-2.81-2.639-3.285c-0.277-0.474-0.03-0.731,0.208-0.968c0.213-0.213,0.474-0.554,0.712-0.831c0.237-0.277,0.316-0.475,0.474-0.791c0.158-0.317,0.079-0.594-0.04-0.831C20.612,19.329,19.69,16.983,19.268,16.045z" clip-rule="evenodd"/>
                </svg>
            </template>

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Send To Whatsapp</p>

                <div class="space-y-4 mb-8">

                    <div class="flex space-x-2 p-4 text-sm bg-green-100 rounded-lg">

                        <svg class="w-16 h-8" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48" width="96px" height="96px" fill-rule="evenodd" clip-rule="evenodd">
                            <path fill="#fff" d="M4.868,43.303l2.694-9.835C5.9,30.59,5.026,27.324,5.027,23.979C5.032,13.514,13.548,5,24.014,5c5.079,0.002,9.845,1.979,13.43,5.566c3.584,3.588,5.558,8.356,5.556,13.428c-0.004,10.465-8.522,18.98-18.986,18.98c-0.001,0,0,0,0,0h-0.008c-3.177-0.001-6.3-0.798-9.073-2.311L4.868,43.303z"/>
                            <path fill="#fff" d="M4.868,43.803c-0.132,0-0.26-0.052-0.355-0.148c-0.125-0.127-0.174-0.312-0.127-0.483l2.639-9.636c-1.636-2.906-2.499-6.206-2.497-9.556C4.532,13.238,13.273,4.5,24.014,4.5c5.21,0.002,10.105,2.031,13.784,5.713c3.679,3.683,5.704,8.577,5.702,13.781c-0.004,10.741-8.746,19.48-19.486,19.48c-3.189-0.001-6.344-0.788-9.144-2.277l-9.875,2.589C4.953,43.798,4.911,43.803,4.868,43.803z"/>
                            <path fill="#cfd8dc" d="M24.014,5c5.079,0.002,9.845,1.979,13.43,5.566c3.584,3.588,5.558,8.356,5.556,13.428c-0.004,10.465-8.522,18.98-18.986,18.98h-0.008c-3.177-0.001-6.3-0.798-9.073-2.311L4.868,43.303l2.694-9.835C5.9,30.59,5.026,27.324,5.027,23.979C5.032,13.514,13.548,5,24.014,5 M24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974 M24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974 M24.014,4C24.014,4,24.014,4,24.014,4C12.998,4,4.032,12.962,4.027,23.979c-0.001,3.367,0.849,6.685,2.461,9.622l-2.585,9.439c-0.094,0.345,0.002,0.713,0.254,0.967c0.19,0.192,0.447,0.297,0.711,0.297c0.085,0,0.17-0.011,0.254-0.033l9.687-2.54c2.828,1.468,5.998,2.243,9.197,2.244c11.024,0,19.99-8.963,19.995-19.98c0.002-5.339-2.075-10.359-5.848-14.135C34.378,6.083,29.357,4.002,24.014,4L24.014,4z"/>
                            <path fill="#40c351" d="M35.176,12.832c-2.98-2.982-6.941-4.625-11.157-4.626c-8.704,0-15.783,7.076-15.787,15.774c-0.001,2.981,0.833,5.883,2.413,8.396l0.376,0.597l-1.595,5.821l5.973-1.566l0.577,0.342c2.422,1.438,5.2,2.198,8.032,2.199h0.006c8.698,0,15.777-7.077,15.78-15.776C39.795,19.778,38.156,15.814,35.176,12.832z"/>
                            <path fill="#fff" fill-rule="evenodd" d="M19.268,16.045c-0.355-0.79-0.729-0.806-1.068-0.82c-0.277-0.012-0.593-0.011-0.909-0.011c-0.316,0-0.83,0.119-1.265,0.594c-0.435,0.475-1.661,1.622-1.661,3.956c0,2.334,1.7,4.59,1.937,4.906c0.237,0.316,3.282,5.259,8.104,7.161c4.007,1.58,4.823,1.266,5.693,1.187c0.87-0.079,2.807-1.147,3.202-2.255c0.395-1.108,0.395-2.057,0.277-2.255c-0.119-0.198-0.435-0.316-0.909-0.554s-2.807-1.385-3.242-1.543c-0.435-0.158-0.751-0.237-1.068,0.238c-0.316,0.474-1.225,1.543-1.502,1.859c-0.277,0.317-0.554,0.357-1.028,0.119c-0.474-0.238-2.002-0.738-3.815-2.354c-1.41-1.257-2.362-2.81-2.639-3.285c-0.277-0.474-0.03-0.731,0.208-0.968c0.213-0.213,0.474-0.554,0.712-0.831c0.237-0.277,0.316-0.475,0.474-0.791c0.158-0.317,0.079-0.594-0.04-0.831C20.612,19.329,19.69,16.983,19.268,16.045z" clip-rule="evenodd"/>
                        </svg>
                        <span>Show, hide and move your data they way you want to see it on whatsapp</span>

                    </div>

                    <!-- Include Category field names -->
                    <Input
                        type="checkbox"
                        v-model="includeCategoryFieldNames"
                        inputLabel="Include category field names">
                    </Input>

                    <div class="border border-gray-200 divide-y overflow-y-auto rounded-lg h-60 px-4 mb-4">

                        <!-- Draggable Whatsapp Fields -->
                        <draggable
                            v-model="whatsappFields"
                            handle=".draggable-handle"
                            ghost-class="bg-yellow-50"
                            class="divide-y divide-gray-200 mb-4">

                            <template
                                :key="index"
                                v-for="(whatsappField, index) in whatsappFields">

                                <div class="flex items-center justify-between p-4">

                                    <!-- Active Toogle Switch -->
                                    <Switch
                                        size="xs"
                                        v-model="whatsappField.active"
                                        :suffixText="whatsappField.name">
                                    </Switch>

                                    <div class="flex items-center space-x-4">

                                        <!-- Gap Checkbox -->
                                        <Input
                                            type="checkbox"
                                            inputLabel="Gap"
                                            v-if="whatsappField.active"
                                            v-model="whatsappField.linebreak">
                                        </Input>

                                        <!-- Drag & Drop Handle -->
                                        <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                                    </div>

                                </div>

                            </template>

                        </draggable>

                    </div>

                </div>

            </template>

        </Modal>

        <!-- Delete Categories -->
        <Modal
            approveType="danger"
            ref="deleteCategoriesModal"
            :leftApproveIcon="Trash2"
            :approveAction="deleteCategories"
            :approveLoading="isDeletingCategories"
            :approveText="totalCheckedRows == 1 ? 'Delete Category' : 'Delete Categories'">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Delete Categories</p>

                <div class="flex space-x-2 items-center p-4 text-xs bg-red-50 rounded-lg mb-8">

                    <svg class="w-6 h-6 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>

                    <span class="text-red-500">Are you sure you want to delete {{ totalCheckedRows > 1 ? `these ${totalCheckedRows} categories` : 'this category' }}?</span>

                </div>

            </template>

        </Modal>

        <!-- Confirm Delete Category -->
        <Modal
            approveType="danger"
            ref="deleteCategoryModal"
            :leftApproveIcon="Trash2"
            approveText="Delete Category"
            :approveAction="deleteCategory"
            :approveLoading="isDeletingCategory(deletableCategory)">

            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Delete</p>
                <p v-if="deletableCategory" class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ deletableCategory.name }}</span>?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import axios from 'axios';
    import isEqual from 'lodash.isequal';
    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import Switch from '@Partials/Switch.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import Table from '@Partials/table/Table.vue';
    import { isNotEmpty } from '@Utils/stringUtils';
    import { VueDraggableNext } from 'vue-draggable-next';
    import { Move, Info, Plus, Trash2, Boxes } from 'lucide-vue-next';
    import { formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';
    import NoDataPlaceholder from '@Partials/table/components/NoDataPlaceholder.vue';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Move, Info, Boxes, Pill, Input, Modal, Loader, Button, Switch, Select, Popover, Dropdown, Table, draggable: VueDraggableNext,
            NoDataPlaceholder
        },
        data() {
            return {
                Plus,
                Trash2,
                categories: [],
                perPage: '15',
                checkedRows: [],
                pagination: null,
                searchTerm: null,
                selectAll: false,
                latestRequestId: 0,
                filterExpressions: [],
                sortingExpressions: [],
                hasInitialResults: null,
                deletableCategory: null,
                cancelTokenSource: null,
                isDeletingCategoryIds: [],
                isLoadingCategories: false,
                isUpdatingCategories: false,
                columns: this.prepareColumns(),
                includeCategoryFieldNames: true,
                whatsappFields: this.prepareWhatsappFields(),
                bulkSelectionOptions: [
                    {
                        label: 'Show',
                        action: () => this.updateCategories('show')
                    },
                    {
                        label: 'Hide',
                        action: () => this.updateCategories('hide')
                    },
                    {
                        label: 'Send as Whatsapp',
                        action: this.showSendToWhatsappModal,
                    },
                    {
                        label: 'Delete',
                        action: this.showDeleteCategoriesModal,
                    }
                ],
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.showCategories();
                }
            },
            selectAll(newValue) {
                this.checkedRows = this.categories.reduce((acc, category) => {
                    acc[category.id] = newValue;
                    return acc;
                }, {});
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasSearchTerm() {
                return this.isNotEmpty(this.searchTerm);
            },
            isDeletingCategories() {
                return this.isDeletingCategoryIds.length > 0;
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
        },
        methods: {
            isNotEmpty,
            formattedDatetime,
            formattedRelativeDate,
            prepareColumns() {
                const columnNames = ['Name', 'Description', 'Visibility', 'Products', 'Created Date'];
                const defaultColumnNames  = ['Name', 'Visibility', 'Products', 'Created Date'];

                return columnNames.map(name => ({
                    name,
                    active: defaultColumnNames.includes(name),
                    priority: defaultColumnNames.includes(name)
                }));
            },
            prepareWhatsappFields() {
                const whatsappFieldNames = ['Name', 'Description', 'Visibility', 'Products', 'Created Date', 'Category Link'];
                const defaultWhatsappFieldNames  = ['Name', 'Visibility', 'Products', 'Created Date'];

                return whatsappFieldNames.map(name => ({
                    name,
                    linebreak: false,
                    active: defaultWhatsappFieldNames.includes(name),
                    priority: defaultWhatsappFieldNames.includes(name)
                }));
            },
            showDeleteCategoriesModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.deleteCategoriesModal.showModal();
            },
            showSendToWhatsappModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.sendToWhatsappModal.showModal();
            },
            showDeleteConfirmationModal(category) {
                this.deletableCategory = category;
                this.$refs.deleteCategoryModal.showModal();
            },
            isDeletingCategory(category) {
                if(category == null) return false;
                return this.isDeletingCategoryIds.findIndex((id) => id == category.id) != -1;
            },
            onView(category) {
                this.$router.push({
                    name: 'edit-category',
                    params: {
                        category_id: category.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            onAddCategory() {
                this.$router.push({
                    name: 'create-category',
                    query: { store_id: this.store.id }
                });
            },
            paginate(page) {
                this.showCategories(page);
            },
            search(searchTerm) {
                this.searchTerm = searchTerm;
                this.showCategories();
            },
            updatedColumns(columns) {
                this.columns = columns;
            },
            updatedFilters(filters) {
                const newFilterExpressions = filters.map((filter) => filter.expression);
                if(!isEqual(this.filterExpressions, newFilterExpressions)) {
                    this.filterExpressions = newFilterExpressions;
                    this.showCategories();
                }
            },
            updatedSorting(sorting) {
                const newSortingExpressions = sorting.map((sort) => sort.expression);
                if(!isEqual(this.sortingExpressions, newSortingExpressions)) {
                    this.sortingExpressions = newSortingExpressions;
                    this.showCategories();
                }
            },
            updatedPerPage(perPage) {
                this.perPage = perPage;
                this.showCategories();
            },
            async showCategories(page = 1) {

                const currentRequestId = ++this.latestRequestId;

                try {

                    this.isLoadingCategories = true;

                    if (this.cancelTokenSource) {
                        this.cancelTokenSource.cancel('Request superseded by a newer one'); // Cancel previous request if it exists
                    }

                    this.cancelTokenSource = axios.CancelToken.source(); // Create a new cancel token source

                    let config = {
                        params: {
                            page: page,
                            per_page: this.perPage,
                            store_id: this.store.id,
                            association: 'team member',
                            _relationships: ['photo'].join(','),
                            _countable_relationships: ['products'].join(',')
                        },
                        cancelToken: this.cancelTokenSource.token // Attach cancel token
                    }

                    if(this.hasSearchTerm) config.params['search'] = this.searchTerm;

                    if(this.hasFilterExpressions) {
                        config.params['_filters'] = this.filterExpressions.join('|');
                    }

                    if(this.hasSortingExpressions) {
                        config.params['_sort'] = this.sortingExpressions.join('|');
                    }

                    const response = await axios.get(`/api/categories`, config);

                    // Only process response if it matches the latest request
                    if (currentRequestId !== this.latestRequestId) return;

                    if(this.pagination == null) {
                        this.hasInitialResults = response.data.meta.total > 0;
                    }

                    this.pagination = response.data;
                    this.categories = this.pagination.data;

                    this.checkedRows = this.categories.reduce((acc, category) => {
                        acc[category.id] = false;
                        return acc;
                    }, {});

                } catch (error) {

                    if (axios.isCancel(error)) return; // Ignore canceled requests

                    if (currentRequestId !== this.latestRequestId) return;

                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching categories';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch categories:', error);
                } finally {
                    if (currentRequestId !== this.latestRequestId) return;
                    this.isLoadingCategories = false;
                    this.cancelTokenSource = null;
                }

            },
            async updateCategories(type) {

                try {

                    if(this.isUpdatingCategories) return;

                    const categoryIds = this.categories.filter(category => this.checkedRows[category.id]).map(category => category.id);

                    const data = {
                        category_ids: categoryIds,
                        store_id: this.store.id
                    };

                    const isHiding = type == 'hide';
                    const isShowing = type == 'show';

                    if(isHiding) data['visible'] = false;
                    if(isShowing) data['visible'] = true;

                    if(Object.keys(data).length == 2) return;

                    this.isUpdatingCategories = true;

                    await axios.put(`/api/categories`, data);

                    this.showCategories();

                    //  Update store silently
                    this.storeState.silentUpdate();

                    if(isShowing || isHiding) {
                        this.notificationState.showSuccessNotification('Category visibility updated');
                    }

                    // Uncheck only the related rows
                    categoryIds.forEach(categoryId => {
                        if (this.checkedRows[categoryId] !== undefined) {
                            this.checkedRows[categoryId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating category';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update category:', error);
                } finally {
                    this.isUpdatingCategories = false;
                    this.$refs.updateCategoriesModal.hideModal();
                }

            },
            async deleteCategories() {

                try {

                    const categoryIds = this.categories.filter(category => this.checkedRows[category.id]).map(category => category.id)
                                                .filter(categoryId => !this.isDeletingCategoryIds.includes(categoryId));

                    if (categoryIds.length === 0) return;

                    this.isDeletingCategoryIds.push(...categoryIds);

                    const config = {
                        data: {
                            store_id: this.store.id,
                            category_ids: categoryIds
                        }
                    }

                    await axios.delete(`/api/categories`, config);

                    //  Update store silently
                    this.storeState.silentUpdate();

                    this.notificationState.showSuccessNotification(categoryIds == 1 ? 'Category deleted' : 'Categories deleted');
                    this.categories = this.categories.filter(category => !categoryIds.includes(category.id));
                    if(this.categories.length == 0) this.showCategories();

                    this.isDeletingCategoryIds = this.isDeletingCategoryIds.filter(id => !categoryIds.includes(id));

                    // Uncheck only the related rows
                    categoryIds.forEach(categoryId => {
                        if (this.checkedRows[categoryId] !== undefined) {
                            this.checkedRows[categoryId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting categories';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete categories:', error);
                } finally {
                    this.$refs.deleteCategoriesModal.hideModal();
                }
            },
            async deleteCategory() {

                try {

                    if(this.isDeletingCategoryIds.includes(this.deletableCategory.id)) return;

                    this.isDeletingCategoryIds.push(this.deletableCategory.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/categories/${this.deletableCategory.id}`, config);

                    //  Update store silently
                    this.storeState.silentUpdate();

                    this.notificationState.showSuccessNotification('Category deleted');
                    this.categories = this.categories.filter(category => category.id != this.deletableCategory.id);
                    if(this.categories.length == 0) this.showCategories();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting category';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete category:', error);
                } finally {
                    this.isDeletingCategoryIds.splice(this.isDeletingCategoryIds.findIndex((id) => id == this.deletableCategory.id), 1);
                    this.$refs.deleteCategoryModal.hideModal();
                }

            },
            async sendToWhatsapp() {

                const checkedCategories = this.categories.filter(category => this.checkedRows[category.id]);

                if (checkedCategories.length === 0) {
                    alert("No categories selected to send to WhatsApp.");
                    return;
                }

                let message = "";
                let tempMessage = "";
                const maxLength = 58000;

                for (let i = 0; i < checkedCategories.length; i++) {
                    let categoryMessage = `Category #${i + 1}\n` +
                        `----------------------\n`;

                    this.whatsappFields.forEach(field => {

                        if (field.active) {

                            if(field.linebreak) categoryMessage += `\n`;
                            if(this.includeCategoryFieldNames) categoryMessage += `${field.name}: `;

                            switch (field.name) {
                                case "Name":
                                    categoryMessage += `${checkedCategories[i].name}\n`;
                                    break;
                                case "Description":
                                    categoryMessage += `${this.isNotEmpty(checkedCategories[i].description) ? checkedCategories[i].description : 'None'}\n`;
                                    break;
                                case "Visibility":
                                    categoryMessage += `${checkedCategories[i].visible ? 'visible' : 'hidden'}\n`;
                                    break;
                                case "Products":
                                    categoryMessage += `${checkedCategories[i].products_count}\n`;
                                    break;
                                case "Created Date":
                                    categoryMessage += `${checkedCategories[i].created_at}\n`;
                                    break;
                                case "Category Link":
                                    categoryMessage += `${window.location.origin + this.$router.resolve({ name: 'show-category', params: { store_id: this.store.id, category_id: checkedCategories[i].id } }).href}\n`;
                                    break;
                            }

                        }

                    });

                    // Add separator only if it's not the last category
                    if (i < checkedCategories.length - 1) {
                        categoryMessage += `\n\n`;
                    }

                    if ((tempMessage.length + categoryMessage.length) > maxLength) {
                        break;
                    }

                    tempMessage += categoryMessage;
                }

                message = tempMessage.trim();

                if (message.length > 0) {
                    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
                    window.open(whatsappUrl, "_blank");
                }

                this.$refs.sendToWhatsappModal.hideModal();
            }
        },
        created() {
            this.isLoadingCategories = true;
            this.searchTerm = this.$route.query.searchTerm;
            if(this.$route.query.filterExpressions) this.filterExpressions = this.$route.query.filterExpressions.split('|');
            if(this.$route.query.sortingExpressions) this.sortingExpressions = this.$route.query.sortingExpressions.split('|');
            if(this.store) this.showCategories();
        }
    };

</script>
