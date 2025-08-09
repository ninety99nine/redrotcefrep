<template>

    <div class="pt-24 px-8 relative select-none">

        <!-- Clouds Image -->
        <img :src="'/images/clouds.png'" class="absolute bottom-0">

        <div class="relative bg-white/80 p-4 rounded-md">

            <h1 class="text-lg text-gray-700 font-semibold mb-4">Products</h1>

            <!-- Products Table -->
            <Table
                @search="search"
                resource="products"
                :columns="columns"
                :perPage="perPage"
                @paginate="paginate"
                @refresh="showProducts"
                :searchTerm="searchTerm"
                :pagination="pagination"
                :isLoading="isLoadingProducts"
                @updatedColumns="updatedColumns"
                @updatedFilters="updatedFilters"
                @updatedSorting="updatedSorting"
                @updatedPerPage="updatedPerPage"
                :filterExpressions="filterExpressions"
                :sortingExpressions="sortingExpressions"
                searchPlaceholder="Search by product, variant names or SKU">

                <template #afterRefreshButton>

                    <div class="flex items-center space-x-2">

                        <!-- Bulk Edit Products Button -->
                        <Button
                            size="xs"
                            type="outline"
                            :action="navigateToBulkEdit">
                            <span>Bulk Edit</span>
                        </Button>

                        <!-- Import Products Button -->
                        <Button
                            size="xs"
                            type="outline"
                            :action="navigateImportProducts">
                            <span>Import</span>
                        </Button>

                        <!-- Export Products Button -->
                        <Button
                            size="xs"
                            type="outline"
                            :action="showExportProductsModal"
                            v-if="((pagination ?? {}).meta ?? {}).total > 0">
                            <span>Export</span>
                        </Button>

                        <!-- Add Product Button -->
                        <Button
                            size="xs"
                            type="primary"
                            :leftIcon="Plus"
                            :action="onAddProduct">
                            <span>Add Product</span>
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
                <template #tbody>

                    <draggable
                        tag="tbody"
                        v-model="products"
                        handle=".draggable-handle"
                        ghost-class="bg-yellow-50"
                        @change="changeProductArrangement">

                        <tr
                            :key="product.id"
                            v-for="product in products"
                            @click.stop="onView(product)"
                            :class="[checkedRows[product.id] ? 'bg-blue-50' : 'bg-white hover:bg-gray-50', 'group cursor-pointer border-b border-blue-100']">

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
                                        v-model="checkedRows[product.id]">
                                    </Input>

                                </td>

                                <template v-if="column.active">

                                    <!-- Name -->
                                    <td v-if="column.name == 'Name'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                        <div class="flex space-x-1 items-center">

                                            <div class="flex space-x-2 items-center">
                                                <div
                                                    v-if="product.photo"
                                                    class="flex items-center justify-center w-10 h-10">

                                                    <img class="w-full max-h-full object-contain rounded-lg flex-shrink-0" :src="product.photo.path">

                                                </div>
                                                <span>{{ product.name }}</span>
                                            </div>

                                            <Popover
                                                placement="top"
                                                wrapperClasses="opacity-0 group-hover:opacity-100">

                                                <template #content>

                                                    <div class="min-w-40 p-4">

                                                        <p class="border-b border-gray-300 pb-2 mb-2">{{ product.name }}</p>

                                                        <div class="space-y-2">

                                                            <p class="flex items-center space-x-2 mb-2">
                                                                <span class="text-sm">SKU: </span>
                                                                <Pill type="light" size="xs">
                                                                    <span>{{ product.sku ?? 'None' }}</span>
                                                                </Pill>
                                                            </p>

                                                            <p class="flex items-center space-x-2">
                                                                <span class="text-sm">Barcode: </span>
                                                                <Pill type="light" size="xs">
                                                                    <span>{{ product.barcode ?? 'None' }}</span>
                                                                </Pill>
                                                            </p>

                                                            <template v-if="product.show_description && product.description != null">
                                                                <p class="text-sm border-t border-gray-300 pt-2 mt-2">{{ product.description }}</p>
                                                            </template>

                                                        </div>

                                                    </div>

                                                </template>

                                            </Popover>
                                        </div>

                                    </td>

                                    <!-- Description -->
                                    <td v-else-if="column.name == 'Description'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                        <div class="w-40">
                                            <span v-if="product.description">{{ product.description }}</span>
                                            <NoDataPlaceholder v-else></NoDataPlaceholder>
                                        </div>
                                    </td>

                                    <!-- Price -->
                                    <td v-else-if="column.name == 'Price'" class="align-center pr-4 py-4 text-sm">
                                        <div class="flex space-x-1 items-center">

                                            <Pill v-if="(product.variant ?? product).is_free" type="success" size="xs">free</Pill>

                                            <template v-else>
                                                <span>{{ (product.variant ?? product).unit_price.amount_with_currency }}</span>
                                                <Pill v-if="(product.variant ?? product).on_sale" type="success" size="xs">on sale</Pill>
                                            </template>

                                            <Popover
                                                placement="top"
                                                wrapperClasses="opacity-0 group-hover:opacity-100">

                                                <template #content>

                                                    <div class="min-w-40 p-4">

                                                        <p class="border-b border-gray-300 pb-2 mb-2">Pricing</p>

                                                        <div class="space-y-2">

                                                            <div v-if="(product.variant ?? product).on_sale" class="border-b border-gray-300 pb-2 mb-2">
                                                                <p>Regular Price: <span :class="['text-black', { 'line-through' : (product.variant ?? product).on_sale || (product.variant ?? product).is_free }]">{{ (product.variant ?? product).unit_regular_price.amount_with_currency }}</span></p>
                                                                <p v-if="(product.variant ?? product).on_sale">Sale Price: <span :class="['text-black', { 'line-through' : (product.variant ?? product).is_free }]">{{ (product.variant ?? product).unit_sale_price.amount_with_currency }}</span></p>
                                                            </div>

                                                            <template v-if="(product.variant ?? product).is_free">
                                                                <p>This product is free</p>
                                                                <p class="font-bold text-black">Price: <span class="text-green-500">Free</span></p>
                                                            </template>

                                                            <p v-else class=" text-black">Price: <span class="font-bold">{{ (product.variant ?? product).unit_price.amount_with_currency }}</span></p>

                                                        </div>

                                                    </div>

                                                </template>

                                            </Popover>
                                        </div>
                                    </td>

                                    <!-- Visibility -->
                                    <td v-else-if="column.name == 'Visibility'" class="align-center pr-4 py-4 text-sm">
                                        <Pill :type="product.visible ? 'success' : 'warning'" size="xs">{{ product.visible ? 'visible' : 'hidden' }}</Pill>
                                    </td>

                                    <!-- Stock -->
                                    <td v-else-if="column.name == 'Stock'" class="align-center pr-4 py-4 text-sm">
                                        <Pill :type="(product.variant ?? product).has_stock ? 'success' : 'warning'" size="xs">{{ (product.variant ?? product).has_stock ? ((product.variant ?? product).stock_quantity_type == 'unlimited' ? 'unlimited' : `${(product.variant ?? product).stock_quantity} left`) : 'no stock' }}</Pill>
                                    </td>

                                    <!-- Variants -->
                                    <td v-else-if="column.name == 'Variants'" class="align-center pr-4 py-4 text-sm">
                                        <Pill type="light" size="xs">{{ product.variants_count ? product.variants_count : 'none' }}</Pill>
                                    </td>

                                    <!-- Minimum Order Quantity -->
                                    <td v-else-if="column.name == 'Minimum Order Quantity'" class="align-center pr-4 py-4 text-sm">
                                        <div class="flex space-x-1 items-center">
                                            <Pill type="light" size="xs">{{ product.set_min_order_quantity ? product.min_order_quantity : 'none' }}</Pill>
                                            <Popover
                                                placement="top"
                                                wrapperClasses="opacity-0 group-hover:opacity-100">

                                                <template #content>

                                                    <div class="min-w-40 p-4">

                                                    <p class="border-b border-gray-300 pb-2 mb-2">Minimum Order Quantity</p>
                                                    <p>{{ product.set_min_order_quantity ? `Allows minimum of ${product.min_order_quantity} ${product.min_order_quantity == 1 ? 'quantity' : 'quantities' } per order` : 'Does not have any limits' }}</p>

                                                    </div>

                                                </template>

                                            </Popover>
                                        </div>
                                    </td>

                                    <!-- Maximum Order Quantity -->
                                    <td v-else-if="column.name == 'Maximum Order Quantity'" class="align-center pr-4 py-4 text-sm">
                                        <div class="flex space-x-1 items-center">
                                            <Pill type="light" size="xs">{{ product.set_max_order_quantity ? product.max_order_quantity : 'none' }}</Pill>
                                            <Popover
                                                placement="top"
                                                wrapperClasses="opacity-0 group-hover:opacity-100">

                                                <template #content>

                                                    <div class="min-w-40 p-4">

                                                    <p class="border-b border-gray-300 pb-2 mb-2">Maximum Order Quantity</p>
                                                    <p>{{ product.set_max_order_quantity ? `Allows maximum of ${product.max_order_quantity} ${product.max_order_quantity == 1 ? 'quantity' : 'quantities' } per order` : 'Does not have any limits' }}</p>

                                                    </div>

                                                </template>

                                            </Popover>
                                        </div>
                                    </td>

                                    <!-- Created Date -->
                                    <td v-else-if="column.name == 'Created Date'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                        <div class="flex space-x-1 items-center">
                                            <span>{{ formattedDatetime(product.created_at) }}</span>
                                            <Popover
                                                placement="top"
                                                class="opacity-0 group-hover:opacity-100"
                                                :content="formattedRelativeDate(product.created_at)">
                                            </Popover>
                                        </div>
                                    </td>

                                </template>

                                <!-- Actions -->
                                <td v-if="columnIndex == (columns.length - 1)" class="align-center pr-4 py-4">

                                    <div class="flex items-center space-x-4">

                                        <!-- View Button -->
                                        <span v-if="!isDeletingProduct(product)" @click.stop.prevent="onView(product)" class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">View</span>

                                        <!-- Deleting Loader -->
                                        <Loader v-if="isDeletingProduct(product)" type="danger">
                                            <span class="text-xs ml-2">Deleting...</span>
                                        </Loader>

                                        <!-- Delete Button -->
                                        <span v-else @click.stop.prevent="showDeleteConfirmationModal(product)" class="text-sm font-medium text-red-600 dark:text-red-500 hover:underline">Delete</span>

                                        <!-- Drag & Drop Handle -->
                                        <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                                    </div>

                                </td>

                            </template>

                        </tr>

                    </draggable>

                </template>

                <!-- No Products -->
                <template #noResults>

                    <div class="flex justify-between items-end p-10 bg-blue-50 border-t border-blue-200">

                        <div>

                            <h1 class="text-2xl font-bold mb-4">
                                Ready For Your First Sale?
                            </h1>

                            <p class="text-sm text-gray-500">
                                Your products will appear here once customers start shopping.
                            </p>

                        </div>

                        <div>

                            <!-- Add Button -->
                            <Button
                                size="lg"
                                type="primary"
                                :leftIcon="Plus"
                                :action="onAddProduct">
                                <span>Add Product</span>
                            </Button>

                        </div>

                    </div>

                </template>

            </Table>

        </div>

        <!-- Export Products -->
        <Modal
            approveType="primary"
            :scrollOnContent="false"
            ref="exportProductsModal"
            approveText="Export Products"
            :approveAction="exportProducts"
            :leftApproveIcon="ArrowDownToLine"
            :approveLoading="isExportingProducts">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Export Products</p>

                <div class="space-y-4 mb-8">

                    <!-- Limit -->
                    <Select
                        width="w-full"
                        :search="false"
                        v-model="exportLimit"
                        :options="exportLimits"
                        label="Number of products">
                    </Select>

                    <!-- Arrangement -->
                    <Select
                        width="w-full"
                        :search="false"
                        label="Arrangement"
                        v-model="exportMode"
                        :options="exportModes">
                    </Select>

                    <!-- Format -->
                    <Select
                        width="w-full"
                        label="Format"
                        :search="false"
                        v-model="exportFormat"
                        :options="exportFormats">
                    </Select>

                    <!-- Apply Filters -->
                    <Input
                        type="checkbox"
                        v-if="hasFilterExpressions"
                        v-model="exportWithFilters"
                        inputLabel="Apply Filters">
                    </Input>

                    <!-- Apply Sorting -->
                    <Input
                        type="checkbox"
                        v-if="hasSortingExpressions"
                        v-model="exportWithSorting"
                        inputLabel="Apply Sorting">
                    </Input>

                </div>

            </template>

        </Modal>

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

                    <!-- Include Product field names -->
                    <Input
                        type="checkbox"
                        v-model="includeProductFieldNames"
                        inputLabel="Include product field names">
                    </Input>

                    <div class="border border-gray-200 divide-y overflow-y-auto rounded-lg h-60 px-4 mb-4">

                        <!-- Draggable Whatsapp Fields -->
                        <draggable
                            class="divide-y divide-gray-200 mb-4"
                            v-model="whatsappFields"
                            handle=".draggable-handle"
                            ghost-class="bg-yellow-50">

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

        <!-- Download PDF -->
        <Modal
            approveType="primary"
            ref="downloadPdfModal"
            approveText="Download PDF"
            :approveAction="downloadProducts"
            :leftApproveIcon="ArrowDownToLine"
            :approveLoading="isDownloadingProducts">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Download PDF</p>

                <div class="flex space-x-2 p-4 text-xs bg-blue-100 rounded-lg mb-8">

                    <Info size="20" class="shrink-0"></Info>

                    <span>Creating the PDF to download may take a moment depending on the number of products. Please do not close this window.</span>

                </div>

            </template>

        </Modal>

        <!-- Print PDF -->
        <Modal
            ref="printPdfModal"
            approveType="primary"
            approveText="Print PDF"
            :leftApproveIcon="Printer"
            :approveAction="printProducts"
            :approveLoading="isDownloadingProducts">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Print PDF</p>

                <div class="flex space-x-2 p-4 text-xs bg-blue-100 rounded-lg mb-8">

                    <Info size="20" class="shrink-0"></Info>

                    <span>Creating the PDF to print may take a moment depending on the number of products. Please do not close this window.</span>

                </div>

            </template>

        </Modal>

        <!-- Delete Products -->
        <Modal
            approveType="danger"
            ref="deleteProductsModal"
            :leftApproveIcon="Trash2"
            :approveAction="deleteProducts"
            :approveLoading="isDeletingProducts"
            :approveText="totalCheckedRows == 1 ? 'Delete Product' : 'Delete Products'">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed pb-4 mb-4">Delete Products</p>

                <div class="flex space-x-2 items-center p-4 text-xs bg-red-50 border border-red-200 border-dashed rounded-lg mb-8">

                    <svg class="w-6 h-6 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>

                    <span class="text-red-500">Are you sure you want to delete {{ totalCheckedRows > 1 ? `these ${totalCheckedRows} products` : 'this product' }}?</span>

                </div>

            </template>

        </Modal>

        <!-- Confirm Delete Product -->
        <Modal
            approveType="danger"
            ref="deleteProductModal"
            :leftApproveIcon="Trash2"
            approveText="Delete Product"
            :approveAction="deleteProduct"
            :approveLoading="isDeletingProduct(deletableProduct)">

            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Delete</p>
                <p v-if="deletableProduct" class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ deletableProduct.name }}</span>?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import axios from 'axios';
    import isEqual from 'lodash/isEqual';
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
    import { VueDraggableNext } from 'vue-draggable-next';
    import { formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';
    import NoDataPlaceholder from '@Partials/table/components/NoDataPlaceholder.vue';
    import { Move, Info, Plus, Trash2, Printer, RefreshCcw, ArrowDownToLine } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Move, Info, Pill, Input, Modal, Loader, Button, Switch, Select, Popover, Dropdown, Table, draggable: VueDraggableNext,
            NoDataPlaceholder
        },
        data() {
            return {
                Plus,
                Trash2,
                Printer,
                RefreshCcw,
                ArrowDownToLine,

                csvFile: [],
                products: [],
                perPage: '15',
                checkedRows: [],
                pagination: null,
                searchTerm: null,
                selectAll: false,
                latestRequestId: 0,
                exportFormat: 'csv',
                filterExpressions: [],
                deletableProduct: null,
                sortingExpressions: [],
                isDeletingProductIds: [],
                cancelTokenSource: null,
                exportWithFilters: true,
                exportWithSorting: true,
                isLoadingProducts: false,
                isUpdatingProducts: false,
                isExportingProducts: false,
                exportLimit: 'all products',
                exportMode: 'with_variants',
                isDownloadingProducts: false,
                includeProductFieldNames: true,
                columns: this.prepareColumns(),
                isChangingProductArrangement: false,
                whatsappFields: this.prepareWhatsappFields(),
                exportLimits: [
                    { label: 'Current Page', value: 'current page'},
                    { label: 'All Products', value: 'all products' }
                ],
                exportModes: [
                    {
                        label: 'With variants',
                        value: 'with_variants'
                    },
                    {
                        label: 'Without variants',
                        value: 'without_variants'
                    },
                ],
                exportFormats: [
                    {
                        label: 'CSV (Plain Data File)',
                        value: 'csv'
                    },
                    {
                        label: 'Excel (XLSX)',
                        value: 'xlsx'
                    },
                    {
                        label: 'PDF (Printable Document)',
                        value: 'pdf'
                    },
                ],
                bulkSelectionOptions: [
                    {
                        label: 'Show',
                        action: () => this.updateProducts('show')
                    },
                    {
                        label: 'Hide',
                        action: () => this.updateProducts('hide')
                    },
                    {
                        label: 'Add to category',
                        action: null
                    },
                    {
                        label: 'Remove from category',
                        action: null
                    },
                    {
                        label: 'Send Whatsapp',
                        action: this.showSendToWhatsappModal,
                    },
                    {
                        label: 'Download PDF',
                        action: this.showDownloadPdfModal,
                    },
                    {
                        label: 'Print PDF',
                        action: this.showPrintPdfModal,
                    },
                    {
                        label: 'Delete',
                        action: this.showDeleteProductsModal,
                    }
                ],
            }
        },
        watch: {
            store(newValue) {
                if(newValue) {
                    this.showProducts();
                }
            },
            selectAll(newValue) {
                this.checkedRows = this.products.reduce((acc, product) => {
                    acc[product.id] = newValue;
                    return acc;
                }, {});
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasSearchTerm() {
                return this.searchTerm != null && this.searchTerm.trim() != '';
            },
            isDeletingProducts() {
                return this.isDeletingProductIds.length > 0;
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
            formattedDatetime: formattedDatetime,
            formattedRelativeDate: formattedRelativeDate,
            prepareColumns() {
                const columnNames = ['Name', 'Description', 'Price', 'Visibility', 'Stock', 'Variants', 'Minimum Order Quantity', 'Maximum Order Quantity', 'Created Date'];
                const defaultColumnNames  = ['Name', 'Description', 'Price', 'Visibility', 'Stock', 'Variants', 'Created Date'];

                return columnNames.map(name => ({
                    name,
                    active: defaultColumnNames.includes(name),
                    priority: defaultColumnNames.includes(name)
                }));
            },
            prepareWhatsappFields() {
                const whatsappFieldNames = ['Name', 'Description', 'Unit Regular Price', 'Unit Sale Price', 'Unit Price', 'Visibility', 'Stock', 'Variants', 'Minimum Order Quantity', 'Maximum Order Quantity', 'Position', 'Created Date', 'Product Link'];
                const defaultWhatsappFieldNames  = ['Name', 'Description', 'Unit Price', 'Visibility', 'Stock', 'Variants', 'Position', 'Created Date'];

                return whatsappFieldNames.map(name => ({
                    name,
                    linebreak: false,
                    active: defaultWhatsappFieldNames.includes(name),
                    priority: defaultWhatsappFieldNames.includes(name)
                }));
            },
            showExportProductsModal() {
                this.$refs.exportProductsModal.showModal();
            },
            showPrintPdfModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.printPdfModal.showModal();
            },
            showDeleteProductsModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.deleteProductsModal.showModal();
            },
            showDownloadPdfModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.downloadPdfModal.showModal();
            },
            showSendToWhatsappModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.sendToWhatsappModal.showModal();
            },
            showDeleteConfirmationModal(product) {
                this.deletableProduct = product;
                this.$refs.deleteProductModal.showModal();
            },
            isDeletingProduct(product) {
                if(product == null) return false;
                return this.isDeletingProductIds.findIndex((id) => id == product.id) != -1;
            },
            navigateToBulkEdit() {
                this.$router.push({
                    name: 'bulk-edit-products',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            navigateImportProducts() {
                this.$router.push({
                    name: 'import-products',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            onView(product) {
                this.$router.push({
                    name: 'edit-product',
                    params: {
                        product_id: product.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            onAddProduct() {
                this.$router.push({
                    name: 'create-product',
                    query: { store_id: this.store.id }
                });
            },
            paginate(page) {
                this.showProducts(page);
            },
            search(searchTerm) {
                this.searchTerm = searchTerm;
                this.showProducts();
            },
            updatedColumns(columns) {
                this.columns = columns;
            },
            updatedFilters(filters) {
                const newFilterExpressions = filters.map((filter) => filter.expression);
                if(!isEqual(this.filterExpressions, newFilterExpressions)) {
                    this.filterExpressions = newFilterExpressions;
                    this.showProducts();
                }
            },
            updatedSorting(sorting) {
                const newSortingExpressions = sorting.map((sort) => sort.expression);
                if(!isEqual(this.sortingExpressions, newSortingExpressions)) {
                    this.sortingExpressions = newSortingExpressions;
                    this.showProducts();
                }
            },
            updatedPerPage(perPage) {
                this.perPage = perPage;
                this.showProducts();
            },
            async showProducts(page = 1) {

                const currentRequestId = ++this.latestRequestId;

                try {

                    this.isLoadingProducts = true;

                    if (this.cancelTokenSource) {
                        this.cancelTokenSource.cancel('Request superseded by a newer one'); // Cancel previous request if it exists
                    }

                    this.cancelTokenSource = axios.CancelToken.source(); // Create a new cancel token source

                    const config = {
                        params: {
                            page: page,
                            per_page: this.perPage,
                            store_id: this.store.id,
                            association: 'team member',
                            _relationships: ['variant', 'photo'].join(','),
                            _countable_relationships: ['variants'].join(',')
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

                    const response = await axios.get(`/api/products`, config);

                    // Only process response if it matches the latest request
                    if (currentRequestId !== this.latestRequestId) return;

                    this.pagination = response.data;
                    this.products = this.pagination.data;

                    this.checkedRows = this.products.reduce((acc, product) => {
                        acc[product.id] = false;
                        return acc;
                    }, {});

                } catch (error) {

                    if (axios.isCancel(error)) return; // Ignore canceled requests

                    if (currentRequestId !== this.latestRequestId) return;

                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch products:', error);
                } finally {
                    if (currentRequestId !== this.latestRequestId) return;
                    this.isLoadingProducts = false;
                    this.cancelTokenSource = null;
                }

            },
            async exportProducts() {

                try {

                    if(this.isExportingProducts) return;
                    this.isExportingProducts = true;

                    let config = {
                        params: {
                            _export: '1',
                            store_id: this.store.id,
                            export_mode: this.exportMode,
                            export_format: this.exportFormat,
                            export_limit: this.exportLimit == 'current page' ? this.pagination.meta.to : null,
                            export_offset: this.exportLimit == 'current page' ? this.pagination.meta.from - 1 : null,
                        },
                        responseType: 'blob'
                    }

                    if(this.hasSearchTerm) config.params['search'] = this.searchTerm;

                    if(this.hasFilterExpressions) {
                        config.params['_filters'] = this.filterExpressions.join('|');
                    }

                    if(this.hasSortingExpressions) {
                        config.params['_sort'] = this.sortingExpressions.join('|');
                    }

                    const response = await axios.get(`/api/products`, config);

                    let url = window.URL.createObjectURL(new Blob([response.data]));

                    const link = document.createElement('a');

                    link.href = url;
                    link.setAttribute('download', `products.${this.exportFormat}`);

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while exporting products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to export products:', error);
                } finally {
                    this.isExportingProducts = false;
                    this.$refs.exportProductsModal.hideModal();
                }

            },
            async updateProducts(type) {

                try {

                    if(this.isUpdatingProducts) return;

                    const productIds = this.products.filter(product => this.checkedRows[product.id]).map(product => product.id);

                    const data = {
                        product_ids: productIds,
                        store_id: this.store.id
                    };

                    const isHiding = type == 'hide';
                    const isShowing = type == 'show';

                    if(isHiding) data['visible'] = false;
                    if(isShowing) data['visible'] = true;

                    if(Object.keys(data).length == 2) return;

                    this.isUpdatingProducts = true;

                    await axios.put(`/api/products`, data);

                    this.showProducts();

                    if(isShowing || isHiding) {
                        this.notificationState.showSuccessNotification('Product visibility updated');
                    }

                    // Uncheck only the related rows
                    productIds.forEach(productId => {
                        if (this.checkedRows[productId] !== undefined) {
                            this.checkedRows[productId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update product:', error);
                } finally {
                    this.isUpdatingProducts = false;
                    this.$refs.updateProductsModal.hideModal();
                }

            },
            async changeProductArrangement() {

                try {

                    if(this.isChangingProductArrangement) return;

                    const productIds = this.products.map((product) => product.id);

                    if(productIds.length == 0) return;

                    this.isChangingProductArrangement = true;

                    const data = {
                        store_id: this.store.id,
                        product_ids: productIds
                    };

                    await axios.post(`/api/products/arrangement`, data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating product arrangement';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update product arrangement:', error);
                } finally {
                    this.isChangingProductArrangement = false;
                }

            },
            async downloadProducts() {

                try {

                    if(this.isDownloadingProducts) return;

                    const productIds = this.products.filter(product => this.checkedRows[product.id]).map(product => product.id);

                    const data = {
                        store_id: this.store.id,
                        product_ids: productIds
                    };

                    const config = {
                        responseType: "blob"
                    };

                    this.isDownloadingProducts = true;

                    const response = await axios.post(`/api/products/download`, data, config);

                    const blob = new Blob([response.data], { type: "application/pdf" });
                    const link = document.createElement("a");

                    link.href = window.URL.createObjectURL(blob);
                    link.download = "products.pdf";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Uncheck only the related rows
                    productIds.forEach(productId => {
                        if (this.checkedRows[productId] !== undefined) {
                            this.checkedRows[productId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while downloading products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to download products:', error);
                } finally {
                    this.isDownloadingProducts = false;
                    this.$refs.downloadPdfModal.hideModal();
                }

            },
            async printProducts() {

                try {

                    if(this.isDownloadingProducts) return;

                    const productIds = this.products.filter(product => this.checkedRows[product.id]).map(product => product.id);

                    const data = {
                        store_id: this.store.id,
                        product_ids: productIds
                    };

                    const config = {
                        responseType: "blob"
                    };

                    this.isDownloadingProducts = true;

                    const response = await axios.post(`/api/products/download`, data, config);

                    const blob = new Blob([response.data], { type: "application/pdf" });
                    const blobUrl = window.URL.createObjectURL(blob);

                    const printWindow = window.open(blobUrl);
                    if (printWindow) {
                        printWindow.onload = () => {
                            printWindow.focus();
                            printWindow.print();
                        };
                    }

                    // Uncheck only the related rows
                    productIds.forEach(productId => {
                        if (this.checkedRows[productId] !== undefined) {
                            this.checkedRows[productId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while printing products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to print products:', error);
                } finally {
                    this.isDownloadingProducts = false;
                    this.$refs.printPdfModal.hideModal();
                }

            },
            async deleteProducts() {

                try {

                    const productIds = this.products.filter(product => this.checkedRows[product.id]).map(product => product.id)
                                                .filter(productId => !this.isDeletingProductIds.includes(productId));

                    if (productIds.length === 0) return;

                    this.isDeletingProductIds.push(...productIds);

                    const config = {
                        data: {
                            store_id: this.store.id,
                            product_ids: productIds
                        }
                    }

                    await axios.delete(`/api/products`, config);

                    this.notificationState.showSuccessNotification(productIds == 1 ? 'Product deleted' : 'Products deleted');
                    this.products = this.products.filter(product => !productIds.includes(product.id));
                    if(this.products.length == 0) this.showProducts();

                    this.isDeletingProductIds = this.isDeletingProductIds.filter(id => !productIds.includes(id));

                    // Uncheck only the related rows
                    productIds.forEach(productId => {
                        if (this.checkedRows[productId] !== undefined) {
                            this.checkedRows[productId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete products:', error);
                } finally {
                    this.$refs.deleteProductsModal.hideModal();
                }
            },
            async deleteProduct() {

                try {

                    if(this.isDeletingProductIds.includes(this.deletableProduct.id)) return;

                    this.isDeletingProductIds.push(this.deletableProduct.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/products/${this.deletableProduct.id}`, config);

                    this.notificationState.showSuccessNotification('Product deleted');
                    this.products = this.products.filter(product => product.id != this.deletableProduct.id);
                    if(this.products.length == 0) this.showProducts();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete product:', error);
                } finally {
                    this.isDeletingProductIds.splice(this.isDeletingProductIds.findIndex((id) => id == this.deletableProduct.id), 1);
                    this.$refs.deleteProductModal.hideModal();
                }

            },
            async sendToWhatsapp() {

                const checkedProducts = this.products.filter(product => this.checkedRows[product.id]);

                if (checkedProducts.length === 0) {
                    alert("No products selected to send to WhatsApp.");
                    return;
                }

                let message = "";
                let tempMessage = "";
                const maxLength = 58000;

                for (let i = 0; i < checkedProducts.length; i++) {
                    let productMessage = `Product #${i + 1}\n` +
                        `----------------------\n`;

                    this.whatsappFields.forEach(field => {

                        if (field.active) {

                            if(field.linebreak) productMessage += `\n`;
                            if(this.includeProductFieldNames) productMessage += `${field.name}: `;

                            switch (field.name) {
                                case "Name":
                                    productMessage += `${checkedProducts[i].name}\n`;
                                    break;
                                case "Description":
                                    productMessage += `${checkedProducts[i].show_description && checkedProducts[i].description != null ? checkedProducts[i].description : 'None'}\n`;
                                    break;
                                case "Visibility":
                                    productMessage += `${checkedProducts[i].visible ? 'visible' : 'hidden'}\n`;
                                    break;
                                case "Unit Regular Price":
                                    productMessage += `${(checkedProducts[i].variant ?? checkedProducts[i]).unit_regular_price.amount_with_currency}\n`;
                                    break;
                                case "Unit Sale Price":
                                    productMessage += `${(checkedProducts[i].variant ?? checkedProducts[i]).unit_sale_price.amount_with_currency}\n`;
                                    break;
                                case "Unit Price":
                                    productMessage += `${(checkedProducts[i].variant ?? checkedProducts[i]).unit_price.amount_with_currency}\n`;
                                    break;
                                case "Stock":
                                    productMessage += `${(checkedProducts[i].variant ?? checkedProducts[i]).has_stock ? ((checkedProducts[i].variant ?? checkedProducts[i]).stock_quantity_type == 'unlimited' ? 'unlimited' : `${(checkedProducts[i].variant ?? checkedProducts[i]).stock_quantity} left`) : 'no stock'}\n`;
                                    break;
                                case "Variants":
                                    productMessage += `${checkedProducts[i].variants_count ? checkedProducts[i].variants_count : 'none'}\n`;
                                    break;
                                case "Minimum Order Quantity":
                                    productMessage += `${checkedProducts[i].set_min_order_quantity ? checkedProducts[i].min_order_quantity : 'none'}\n`;
                                    break;
                                case "Maximum Order Quantity":
                                    productMessage += `${checkedProducts[i].set_max_order_quantity ? checkedProducts[i].max_order_quantity : 'none'}\n`;
                                    break;
                                case "Position":
                                    productMessage += `${checkedProducts[i].position}\n`;
                                    break;
                                case "Created Date":
                                    productMessage += `${checkedProducts[i].created_at}\n`;
                                    break;
                                case "Product Link":
                                    productMessage += `${window.location.origin + this.$router.resolve({ name: 'show-product', params: { store_id: this.store.id, product_id: checkedProducts[i].id } }).href}\n`;
                                    break;
                            }

                        }

                    });

                    // Add separator only if it's not the last product
                    if (i < checkedProducts.length - 1) {
                        productMessage += `\n\n`;
                    }

                    if ((tempMessage.length + productMessage.length) > maxLength) {
                        break;
                    }

                    tempMessage += productMessage;
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
            this.isLoadingProducts = true;
            this.searchTerm = this.$route.query.searchTerm;
            if(this.$route.query.filterExpressions) this.filterExpressions = this.$route.query.filterExpressions.split('|');
            if(this.$route.query.sortingExpressions) this.sortingExpressions = this.$route.query.sortingExpressions.split('|');
            if(this.store) this.showProducts();
        }
    };

</script>
