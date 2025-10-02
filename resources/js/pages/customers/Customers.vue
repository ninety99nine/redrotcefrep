<template>

    <div class="pt-24 px-8 relative select-none">

        <!-- Clouds Image -->
        <img :src="'/images/clouds.png'" class="absolute bottom-0">

        <div class="relative bg-white/80 p-4 rounded-md">

            <h1 class="text-lg text-gray-700 font-semibold mb-4">Customers</h1>

            <!-- Customers Table -->
            <Table
                @search="search"
                :columns="columns"
                :perPage="perPage"
                resource="customers"
                @paginate="paginate"
                @refresh="showCustomers"
                :searchTerm="searchTerm"
                :pagination="pagination"
                :isLoading="isLoadingCustomers"
                @updatedColumns="updatedColumns"
                @updatedFilters="updatedFilters"
                @updatedSorting="updatedSorting"
                @updatedPerPage="updatedPerPage"
                :filterExpressions="filterExpressions"
                :sortingExpressions="sortingExpressions"
                searchPlaceholder="Search by customer name, phone, email or notes">

                <template #afterRefreshButton>

                    <div class="flex items-center space-x-2">

                        <!-- Bulk Edit Customers Button -->
                        <Button
                            size="xs"
                            type="outline"
                            :action="navigateToBulkEdit">
                            <span>Bulk Edit</span>
                        </Button>

                        <!-- Import Customers Button -->
                        <Button
                            size="xs"
                            type="outline"
                            :action="navigateImportCustomers">
                            <span>Import</span>
                        </Button>

                        <!-- Export Customers Button -->
                        <Button
                            size="xs"
                            type="outline"
                            :action="showExportCustomersModal"
                            v-if="((pagination ?? {}).meta ?? {}).total > 0">
                            <span>Export</span>
                        </Button>

                        <!-- Add Customer Button -->
                        <Button
                            size="xs"
                            type="primary"
                            :leftIcon="Plus"
                            :action="onAddCustomer">
                            <span>Add Customer</span>
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
                                :class="['whitespace-nowrap align-center pr-4 py-4', { 'text-center' : column.name == 'Orders' }]">
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
                        :key="customer.id"
                        v-for="customer in customers"
                        @click.stop="onView(customer)"
                        :class="[checkedRows[customer.id] ? 'bg-blue-50' : 'bg-white hover:bg-gray-50', 'group cursor-pointer border-b border-blue-100']">

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
                                    v-model="checkedRows[customer.id]">
                                </Input>

                            </td>

                            <template v-if="column.active">

                                <!-- Name -->
                                <td v-if="column.name == 'Name'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex space-x-1 items-center">

                                        <span>{{ customer.name }}</span>

                                        <Popover
                                            placement="top"
                                            v-if="customer.notes"
                                            wrapperClasses="opacity-0 group-hover:opacity-100">

                                            <template #content>

                                                <div class="min-w-40 p-4">

                                                    <p class="border-b border-gray-300 pb-2 mb-2">{{ customer.name }}</p>
                                                    <p class="text-sm">{{ customer.notes }}</p>

                                                </div>

                                            </template>

                                        </Popover>
                                    </div>

                                </td>

                                <!-- Mobile -->
                                <td v-else-if="column.name == 'Mobile'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <span v-if="customer.mobile_number">{{ customer.mobile_number.international }}</span>
                                    <NoDataPlaceholder v-else></NoDataPlaceholder>
                                </td>

                                <!-- Email -->
                                <td v-else-if="column.name == 'Email'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <span v-if="customer.email">{{ customer.email }}</span>
                                    <NoDataPlaceholder v-else></NoDataPlaceholder>
                                </td>

                                <!-- Birthday -->
                                <td v-else-if="column.name == 'Birthday'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <span v-if="customer.birthday">{{ formattedDate(customer.birthday) }}</span>
                                    <NoDataPlaceholder v-else></NoDataPlaceholder>
                                </td>

                                <!-- Notes -->
                                <td v-else-if="column.name == 'Notes'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="w-40">
                                        <span v-if="customer.notes">{{ customer.notes }}</span>
                                        <NoDataPlaceholder v-else></NoDataPlaceholder>
                                    </div>
                                </td>

                                <!-- Orders -->
                                <td v-else-if="column.name == 'Orders'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="w-40 text-center">
                                        <span>{{ customer.total_orders }}</span>
                                    </div>
                                </td>

                                <!-- Total Spend -->
                                <td v-else-if="column.name == 'Total Spend'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="w-40">
                                        <span>{{ customer.total_spend.amount_with_currency }}</span>
                                    </div>
                                </td>

                                <!-- Total Avg Spend -->
                                <td v-else-if="column.name == 'Total Avg Spend'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="w-40">
                                        <span>{{ customer.total_average_spend.amount_with_currency }}</span>
                                    </div>
                                </td>

                                <!-- Last Order Date -->
                                <td v-else-if="column.name == 'Last Order Date'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="flex space-x-1 items-center">
                                        <span>{{ formattedDatetime(customer.last_order_at) }}</span>
                                        <Popover
                                            placement="top"
                                            class="opacity-0 group-hover:opacity-100"
                                            :content="formattedRelativeDate(customer.last_order_at)">
                                        </Popover>
                                    </div>
                                </td>

                                <!-- Referral Code -->
                                <td v-else-if="column.name == 'Referral Code'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <span v-if="customer.referral_code">{{ customer.referral_code }}</span>
                                    <NoDataPlaceholder v-else></NoDataPlaceholder>
                                </td>

                                <!-- Created Date -->
                                <td v-else-if="column.name == 'Created Date'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div class="flex space-x-1 items-center">
                                        <span>{{ formattedDatetime(customer.created_at) }}</span>
                                        <Popover
                                            placement="top"
                                            class="opacity-0 group-hover:opacity-100"
                                            :content="formattedRelativeDate(customer.created_at)">
                                        </Popover>
                                    </div>
                                </td>

                            </template>

                            <!-- Actions -->
                            <td v-if="columnIndex == (columns.length - 1)" class="align-center pr-4 py-4">

                                <div class="flex items-center space-x-4">

                                    <!-- View Button -->
                                    <span v-if="!isDeletingCustomer(customer)" @click.stop.prevent="onView(customer)" class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">View</span>

                                    <!-- Deleting Loader -->
                                    <Loader v-if="isDeletingCustomer(customer)" type="danger">
                                        <span class="text-xs ml-2">Deleting...</span>
                                    </Loader>

                                    <!-- Delete Button -->
                                    <span v-else @click.stop.prevent="showDeleteConfirmationModal(customer)" class="text-sm font-medium text-red-600 dark:text-red-500 hover:underline">Delete</span>

                                </div>

                            </td>

                        </template>

                    </tr>

                </template>

                <!-- No Customers -->
                <template #noResults>

                    <div class="flex justify-between items-end p-10 bg-blue-50 border-t border-blue-200">

                        <div>

                            <h1 class="text-2xl font-bold mb-4">
                                Ready For Your First Sale?
                            </h1>

                            <p class="text-sm text-gray-500">
                                Your customers will appear here once customers start shopping.
                            </p>

                        </div>

                        <div>

                            <!-- Add Button -->
                            <Button
                                size="lg"
                                type="primary"
                                :leftIcon="Plus"
                                :action="onAddCustomer">
                                <span>Add Customer</span>
                            </Button>

                        </div>

                    </div>

                </template>

            </Table>

        </div>

        <!-- Export Customers -->
        <Modal
            approveType="primary"
            :scrollOnContent="false"
            ref="exportCustomersModal"
            approveText="Export Customers"
            :approveAction="exportCustomers"
            :leftApproveIcon="ArrowDownToLine"
            :approveLoading="isExportingCustomers">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Export Customers</p>

                <div class="space-y-4 mb-8">

                    <!-- Limit -->
                    <Select
                        class="w-full"
                        :search="false"
                        v-model="exportLimit"
                        :options="exportLimits"
                        label="Number of customers">
                    </Select>

                    <!-- Format -->
                    <Select
                        class="w-full"
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

        <!-- Update Customers -->
        <Modal
            approveType="primary"
            :scrollOnContent="false"
            ref="updateCustomersModal"
            :leftApproveIcon="RefreshCcw"
            approveText="Update Customers"
            :approveLoading="isUpdatingCustomers"
            :approveAction="() => updateCustomers('notes')">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Update Customers</p>

                <div class="space-y-4 mb-8">

                    <!-- Note Input -->
                    <Input
                        rows="2"
                        class="w-full"
                        type="textarea"
                        v-model="notes"
                        label="Customer Notes"
                        :placeholder="`Say something about the ${ totalCheckedRows == 1 ? 'customer' : 'customers' }`">
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

                    <!-- Include Customer field names -->
                    <Input
                        type="checkbox"
                        v-model="includeCustomerFieldNames"
                        inputLabel="Include customer field names">
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

        <!-- Delete Customers -->
        <Modal
            approveType="danger"
            ref="deleteCustomersModal"
            :leftApproveIcon="Trash2"
            :approveAction="deleteCustomers"
            :approveLoading="isDeletingCustomers"
            :approveText="totalCheckedRows == 1 ? 'Delete Customer' : 'Delete Customers'">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Delete Customers</p>

                <div class="flex space-x-2 items-center p-4 text-xs bg-red-50 rounded-lg mb-8">

                    <svg class="w-6 h-6 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>

                    <span class="text-red-500">Are you sure you want to delete {{ totalCheckedRows > 1 ? `these ${totalCheckedRows} customers` : 'this customer' }}?</span>

                </div>

            </template>

        </Modal>

        <!-- Confirm Delete Customer -->
        <Modal
            approveType="danger"
            ref="deleteCustomerModal"
            :leftApproveIcon="Trash2"
            approveText="Delete Customer"
            :approveAction="deleteCustomer"
            :approveLoading="isDeletingCustomer(deletableCustomer)">

            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Delete</p>
                <p v-if="deletableCustomer" class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ deletableCustomer.name }}</span>?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import axios from 'axios';
    import isEqual from 'lodash/isEqual';
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
    import NoDataPlaceholder from '@Partials/table/components/NoDataPlaceholder.vue';
    import { Move, Info, Plus, Trash2, RefreshCcw, ArrowDownToLine } from 'lucide-vue-next';
    import { formattedDate, formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Move, Info, Input, Modal, Loader, Button, Switch, Select, Popover, Dropdown, Table, draggable: VueDraggableNext,
            NoDataPlaceholder
        },
        data() {
            return {
                Plus,
                Trash2,
                RefreshCcw,
                ArrowDownToLine,

                notes: '',
                customers: [],
                perPage: '15',
                checkedRows: [],
                pagination: null,
                searchTerm: null,
                selectAll: false,
                latestRequestId: 0,
                exportFormat: 'csv',
                filterExpressions: [],
                deletableCustomer: null,
                sortingExpressions: [],
                cancelTokenSource: null,
                exportWithFilters: true,
                exportWithSorting: true,
                isDeletingCustomerIds: [],
                isLoadingCustomers: false,
                isUpdatingCustomers: false,
                isExportingCustomers: false,
                exportLimit: 'all customers',
                columns: this.prepareColumns(),
                includeCustomerFieldNames: true,
                whatsappFields: this.prepareWhatsappFields(),
                exportLimits: [
                    { label: 'Current Page', value: 'current page'},
                    { label: 'All Customers', value: 'all customers' }
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
                        label: 'Add Customer Notes',
                        action: this.showUpdateCustomersModal
                    },
                    {
                        label: 'Send as Whatsapp',
                        action: this.showSendToWhatsappModal,
                    },
                    {
                        label: 'Delete',
                        action: this.showDeleteCustomersModal,
                    }
                ],
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.showCustomers();
                }
            },
            selectAll(newValue) {
                this.checkedRows = this.customers.reduce((acc, customer) => {
                    acc[customer.id] = newValue;
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
            isDeletingCustomers() {
                return this.isDeletingCustomerIds.length > 0;
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
            formattedDate: formattedDate,
            formattedDatetime: formattedDatetime,
            formattedRelativeDate: formattedRelativeDate,
            prepareColumns() {
                const columnNames = ['Name', 'Mobile', 'Email', 'Birthday', 'Notes', 'Orders', 'Total Spend', 'Total Avg Spend', 'Last Order Date', 'Referral Code', 'Created Date'];
                const defaultColumnNames  = ['Name', 'Mobile', 'Email', 'Orders', 'Total Spend', 'Created Date'];

                return columnNames.map(name => ({
                    name,
                    active: defaultColumnNames.includes(name),
                    priority: defaultColumnNames.includes(name)
                }));
            },
            prepareWhatsappFields() {
                const whatsappFieldNames = ['Name', 'Mobile', 'Email', 'Birthday', 'Notes', 'Orders', 'Total Spend', 'Total Avg Spend', 'Last Order Date', 'Referral Code', 'Created Date', 'Customer Link'];
                const defaultWhatsappFieldNames  = ['Name', 'Mobile', 'Email', 'Orders', 'Total Spend', 'Created Date'];

                return whatsappFieldNames.map(name => ({
                    name,
                    linebreak: false,
                    active: defaultWhatsappFieldNames.includes(name),
                    priority: defaultWhatsappFieldNames.includes(name)
                }));
            },
            showExportCustomersModal() {
                this.$refs.exportCustomersModal.showModal();
            },
            showDeleteCustomersModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.deleteCustomersModal.showModal();
            },
            showUpdateCustomersModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.updateCustomersModal.showModal();
            },
            showSendToWhatsappModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.sendToWhatsappModal.showModal();
            },
            showDeleteConfirmationModal(customer) {
                this.deletableCustomer = customer;
                this.$refs.deleteCustomerModal.showModal();
            },
            isDeletingCustomer(customer) {
                if(customer == null) return false;
                return this.isDeletingCustomerIds.findIndex((id) => id == customer.id) != -1;
            },
            navigateToBulkEdit() {
                this.$router.push({
                    name: 'bulk-edit-customers',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            navigateImportCustomers() {
                this.$router.push({
                    name: 'import-customers',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            onView(customer) {
                this.$router.push({
                    name: 'edit-customer',
                    params: {
                        customer_id: customer.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            onAddCustomer() {
                this.$router.push({
                    name: 'create-customer',
                    query: { store_id: this.store.id }
                });
            },
            paginate(page) {
                this.showCustomers(page);
            },
            search(searchTerm) {
                this.searchTerm = searchTerm;
                this.showCustomers();
            },
            updatedColumns(columns) {
                this.columns = columns;
            },
            updatedFilters(filters) {
                const newFilterExpressions = filters.map((filter) => filter.expression);
                if(!isEqual(this.filterExpressions, newFilterExpressions)) {
                    this.filterExpressions = newFilterExpressions;
                    this.showCustomers();
                }
            },
            updatedSorting(sorting) {
                const newSortingExpressions = sorting.map((sort) => sort.expression);
                if(!isEqual(this.sortingExpressions, newSortingExpressions)) {
                    this.sortingExpressions = newSortingExpressions;
                    this.showCustomers();
                }
            },
            updatedPerPage(perPage) {
                this.perPage = perPage;
                this.showCustomers();
            },
            async showCustomers(page = 1) {

                const currentRequestId = ++this.latestRequestId;

                try {

                    this.isLoadingCustomers = true;

                    if (this.cancelTokenSource) {
                        this.cancelTokenSource.cancel('Request superseded by a newer one'); // Cancel previous request if it exists
                    }

                    this.cancelTokenSource = axios.CancelToken.source(); // Create a new cancel token source

                    let config = {
                        params: {
                            page: page,
                            per_page: this.perPage,
                            store_id: this.store.id
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

                    const response = await axios.get(`/api/customers`, config);

                    // Only process response if it matches the latest request
                    if (currentRequestId !== this.latestRequestId) return;

                    this.pagination = response.data;
                    this.customers = this.pagination.data;

                    this.checkedRows = this.customers.reduce((acc, customer) => {
                        acc[customer.id] = false;
                        return acc;
                    }, {});

                } catch (error) {

                    if (axios.isCancel(error)) return; // Ignore canceled requests

                    if (currentRequestId !== this.latestRequestId) return;

                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching customers';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch customers:', error);
                } finally {
                    if (currentRequestId !== this.latestRequestId) return;
                    this.isLoadingCustomers = false;
                    this.cancelTokenSource = null;
                }

            },
            async exportCustomers() {

                try {

                    if(this.isExportingCustomers) return;
                    this.isExportingCustomers = true;

                    let config = {
                        params: {
                            _export: '1',
                            store_id: this.store.id,
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

                    const response = await axios.get(`/api/customers`, config);

                    let url = window.URL.createObjectURL(new Blob([response.data]));

                    const link = document.createElement('a');

                    link.href = url;
                    link.setAttribute('download', `customers.${this.exportFormat}`);

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while exporting customers';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to export customers:', error);
                } finally {
                    this.isExportingCustomers = false;
                    this.$refs.exportCustomersModal.hideModal();
                }

            },
            async updateCustomers(type) {

                try {

                    if(this.isUpdatingCustomers) return;

                    const data = {
                        store_id: this.store.id
                    };

                    data['customers'] = this.customers.filter(customer => this.checkedRows[customer.id]).map(customer => ({ id: customer.id }));

                    const changeNotes = (type == 'notes');

                    if(changeNotes) {
                        data['notes'] = this.notes;
                    }

                    if(data['customers'].length > 0) {

                        this.isUpdatingCustomers = true;

                        await axios.put(`/api/customers`, data);

                        this.showCustomers();

                        this.notificationState.showSuccessNotification('Customers updated');

                    }

                    // Uncheck only the related rows
                    data['customers'].forEach(customer => {
                        if (this.checkedRows[customer.id] !== undefined) {
                            this.checkedRows[customer.id] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating customer';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update customer:', error);
                } finally {
                    this.isUpdatingCustomers = false;
                    this.$refs.updateCustomersModal.hideModal();
                }

            },
            async deleteCustomers() {

                try {

                    const customerIds = this.customers.filter(customer => this.checkedRows[customer.id]).map(customer => customer.id)
                                                .filter(customerId => !this.isDeletingCustomerIds.includes(customerId));

                    if (customerIds.length === 0) return;

                    this.isDeletingCustomerIds.push(...customerIds);

                    const config = {
                        data: {
                            store_id: this.store.id,
                            customer_ids: customerIds
                        }
                    }

                    await axios.delete(`/api/customers`, config);

                    this.notificationState.showSuccessNotification(customerIds == 1 ? 'Customer deleted' : 'Customers deleted');
                    this.customers = this.customers.filter(customer => !customerIds.includes(customer.id));
                    if(this.customers.length == 0) this.showCustomers();

                    this.isDeletingCustomerIds = this.isDeletingCustomerIds.filter(id => !customerIds.includes(id));

                    // Uncheck only the related rows
                    customerIds.forEach(customerId => {
                        if (this.checkedRows[customerId] !== undefined) {
                            this.checkedRows[customerId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting customers';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete customers:', error);
                } finally {
                    this.$refs.deleteCustomersModal.hideModal();
                }
            },
            async deleteCustomer() {

                try {

                    if(this.isDeletingCustomerIds.includes(this.deletableCustomer.id)) return;

                    this.isDeletingCustomerIds.push(this.deletableCustomer.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/customers/${this.deletableCustomer.id}`, config);

                    this.notificationState.showSuccessNotification('Customer deleted');
                    this.customers = this.customers.filter(customer => customer.id != this.deletableCustomer.id);
                    if(this.customers.length == 0) this.showCustomers();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting customer';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete customer:', error);
                } finally {
                    this.isDeletingCustomerIds.splice(this.isDeletingCustomerIds.findIndex((id) => id == this.deletableCustomer.id), 1);
                    this.$refs.deleteCustomerModal.hideModal();
                }

            },
            async sendToWhatsapp() {

                const checkedCustomers = this.customers.filter(customer => this.checkedRows[customer.id]);

                if (checkedCustomers.length === 0) {
                    alert("No customers selected to send to WhatsApp.");
                    return;
                }

                let message = "";
                let tempMessage = "";
                const maxLength = 58000;

                for (let i = 0; i < checkedCustomers.length; i++) {
                    let customerMessage = `Customer #${i + 1}\n` +
                        `----------------------\n`;

                    this.whatsappFields.forEach(field => {

                        if (field.active) {

                            if(field.linebreak) customerMessage += `\n`;
                            if(this.includeCustomerFieldNames) customerMessage += `${field.name}: `;

                            switch (field.name) {
                                case "Name":
                                    customerMessage += `${checkedCustomers[i].name}\n`;
                                    break;
                                case "Mobile":
                                    customerMessage += `${checkedCustomers[i].mobile_number.international}\n`;
                                    break;
                                case "Email":
                                    customerMessage += `${checkedCustomers[i].email}\n`;
                                    break;
                                case "Birthday":
                                    customerMessage += `${checkedCustomers[i].birthday}\n`;
                                    break;
                                case "Notes":
                                    customerMessage += `${checkedCustomers[i].notes}\n`;
                                    break;
                                case "Orders":
                                    customerMessage += `${checkedCustomers[i].total_orders}\n`;
                                    break;
                                case "Total Spend":
                                    customerMessage += `${checkedCustomers[i].total_spend.amount_with_currency}\n`;
                                    break;
                                case "Total Avg Spend":
                                    customerMessage += `${checkedCustomers[i].total_average_spend.amount_with_currency}\n`;
                                    break;
                                case "Last Order Date":
                                    customerMessage += `${checkedCustomers[i].last_order_at}\n`;
                                    break;
                                case "Referral Code":
                                    customerMessage += `${checkedCustomers[i].referral_code}\n`;
                                    break;
                                case "Created Date":
                                    customerMessage += `${checkedCustomers[i].created_at}\n`;
                                    break;
                                case "Customer Link":
                                    customerMessage += `${window.location.origin + this.$router.resolve({ name: 'show-customer', params: { store_id: this.store.id, customer_id: checkedCustomers[i].id } }).href}\n`;
                                    break;
                            }

                        }

                    });

                    // Add separator only if it's not the last customer
                    if (i < checkedCustomers.length - 1) {
                        customerMessage += `\n\n`;
                    }

                    if ((tempMessage.length + customerMessage.length) > maxLength) {
                        break;
                    }

                    tempMessage += customerMessage;
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
            this.isLoadingCustomers = true;
            this.searchTerm = this.$route.query.searchTerm;
            if(this.$route.query.filterExpressions) this.filterExpressions = this.$route.query.filterExpressions.split('|');
            if(this.$route.query.sortingExpressions) this.sortingExpressions = this.$route.query.sortingExpressions.split('|');
            if(this.store) this.showCustomers();
        }
    };

</script>
