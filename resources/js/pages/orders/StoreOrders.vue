<template>

    <div class="pt-24 pb-80 px-8 relative select-none">

        <!-- Clouds Image -->
        <img :src="'/images/clouds.png'" class="absolute bottom-0">

        <div class="relative bg-white/80 p-4 rounded-md">

            <Tabs
                class="mb-4"
                :tabs="filterTabs"
                v-model="filterTab">
            </Tabs>

            <!-- Orders Table -->
            <Table
                @search="search"
                resource="orders"
                :columns="columns"
                :perPage="perPage"
                @paginate="paginate"
                @refresh="getOrders"
                :searchTerm="searchTerm"
                :pagination="pagination"
                :isLoading="isLoadingOrders"
                @updatedColumns="updatedColumns"
                @updatedFilters="updatedFilters"
                @updatedSorting="updatedSorting"
                @updatedPerPage="updatedPerPage"
                :filterExpressions="filterExpressions"
                :sortingExpressions="sortingExpressions"
                searchPlaceholder="Search by customer, product, order number or phone"
                v-if="isLoadingOrders || ((pagination ?? {}).meta ?? {}).total > 0 || hasSearchTerm || hasFilterExpressions || hasSortingExpressions">

                <template #afterRefreshButton>

                    <div class="flex items-center space-x-2">

                        <!-- Export Orders Button -->
                        <Button
                            size="xs"
                            type="outline"
                            :leftIcon="ArrowDownToLine"
                            :action="showExportOrdersModal"
                            v-if="((pagination ?? {}).meta ?? {}).total > 0">
                            <span>Export</span>
                        </Button>

                        <!-- Add Order Button -->
                        <Button
                            size="xs"
                            type="primary"
                            :leftIcon="Plus"
                            :action="onAddOrder">
                            <span>Add Order</span>
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
                        <th scope="col" class="whitespace-nowrap align-top px-4 py-4">
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
                                class="whitespace-nowrap align-top pr-4 py-4">
                                {{ column.name }}
                            </th>

                        </template>

                        <!-- Actions -->
                        <th scope="col" class="whitespace-nowrap align-top pr-4 py-4"></th>

                    </tr>

                </template>

                <!-- Table Body -->
                <template #body>

                    <tr
                        :key="order.id"
                        @click.stop="onView(order)" v-for="order in orders"
                        :class="[checkedRows[order.id] ? 'bg-blue-50' : 'bg-white hover:bg-gray-50', 'group cursor-pointer border-b border-blue-100']">

                        <template
                            :key="columnIndex"
                            v-for="(column, columnIndex) in columns">

                            <!-- Checkbox -->
                            <td
                                @click.stop
                                v-if="columnIndex == 0"
                                class="whitespace-nowrap align-top px-4 py-4">

                                <Input
                                    type="checkbox"
                                    v-model="checkedRows[order.id]">
                                </Input>

                            </td>

                            <template v-if="column.active">

                                <!-- Number -->
                                <td v-if="column.name == 'Number'" class="whitespace-nowrap align-top pr-4 py-4 text-sm">

                                    <div class="flex space-x-1 items-center">
                                        <span>#{{ order.number }}</span>
                                        <Popover
                                            placement="top"
                                            :content="order.summary"
                                            class="opacity-0 group-hover:opacity-100">
                                        </Popover>
                                    </div>

                                </td>

                                <!-- Customer -->
                                <td v-else-if="column.name == 'Customer'" class="whitespace-nowrap align-top pr-4 py-4 text-sm">
                                    <div class="w-40">
                                        <span v-if="order.customer_name">{{  order.customer_name }}</span>
                                        <NoDataPlaceholder v-else></NoDataPlaceholder>
                                    </div>
                                </td>

                                <!-- Summary -->
                                <td v-else-if="column.name == 'Summary'" class="align-top pr-4 py-4 text-sm">
                                    <div class="w-60">
                                        <span>{{  order.summary }}</span>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td v-else-if="column.name == 'Status'" class="align-top pr-4 py-4 text-sm">
                                    <Status :order="order" popoverWrapperClasses="opacity-0 group-hover:opacity-100"></Status>
                                </td>

                                <!-- Payment Status -->
                                <td v-else-if="column.name == 'Payment Status'" class="align-top pr-4 py-4 text-sm">
                                    <PaymentStatus :order="order" popoverWrapperClasses="opacity-0 group-hover:opacity-100"></PaymentStatus>
                                </td>

                                <!-- Collection Status -->
                                <td v-else-if="column.name == 'Collection Status'" class="align-top pr-4 py-4 text-sm">
                                    <CollectionStatus :order="order" popoverWrapperClasses="opacity-0 group-hover:opacity-100"></CollectionStatus>
                                </td>

                                <!-- Grand Total -->
                                <td v-else-if="column.name == 'Grand Total'" class="whitespace-nowrap align-top pr-4 py-4 text-sm">
                                    <div class="flex space-x-1 items-center">
                                        <span>{{ order.grand_total.amount_with_currency }}</span>
                                        <Popover
                                            placement="top"
                                            wrapperClasses="opacity-0 group-hover:opacity-100">

                                            <template #content>

                                                <div class="p-4 space-y-2 text-xs">

                                                    <p class="text-black font-bold">Cost Breakdown</p>

                                                    <div class="space-y-2 border-t border-b border-gray-100 py-2">
                                                        <p>Subtotal: <span>{{ order.subtotal.amount_with_currency }}</span></p>
                                                        <p>Discount: <span>{{ order.discount_total.amount_with_currency }}</span></p>
                                                    </div>

                                                    <p class=" text-black">Grand Total: <span class="font-bold">{{ order.grand_total.amount_with_currency }}</span></p>

                                                </div>

                                            </template>

                                        </Popover>
                                    </div>
                                </td>

                                <!-- Paid -->
                                <td v-else-if="column.name == 'Paid'" class="whitespace-nowrap align-top pr-4 py-4 text-sm">
                                    <div class="flex space-x-1 items-center">
                                        <span>{{ order.paid_total.amount_with_currency }}</span>
                                        <Popover
                                            placement="top"
                                            :content="`Paid: ${order.paid_percentage}%`"
                                            wrapperClasses="opacity-0 group-hover:opacity-100">
                                        </Popover>
                                    </div>
                                </td>

                                <!-- Pending -->
                                <td v-else-if="column.name == 'Pending'" class="whitespace-nowrap align-top pr-4 py-4 text-sm">
                                    <div class="flex space-x-1 items-center">
                                        <span>{{ order.pending_total.amount_with_currency }}</span>
                                        <Popover
                                            placement="top"
                                            :content="`Pending: ${order.pending_percentage}%`"
                                            wrapperClasses="opacity-0 group-hover:opacity-100">
                                        </Popover>
                                    </div>
                                </td>

                                <!-- Outstanding -->
                                <td v-else-if="column.name == 'Outstanding'" class="whitespace-nowrap align-top pr-4 py-4 text-sm">
                                    <div class="flex space-x-1 items-center">
                                        <span>{{ order.outstanding_total.amount_with_currency }}</span>
                                        <Popover
                                            placement="top"
                                            wrapperClasses="opacity-0 group-hover:opacity-100"
                                            :content="`Outstanding: ${order.outstanding_percentage}%`">
                                        </Popover>
                                    </div>
                                </td>

                                <!-- Customer Note -->
                                <td v-else-if="column.name == 'Customer Note'" class="align-top pr-4 py-4 text-sm">
                                    <div class="w-60">
                                        <span v-if="order.customer_note">{{ order.customer_note }}</span>
                                        <NoDataPlaceholder v-else></NoDataPlaceholder>
                                    </div>
                                </td>

                                <!-- Created Date -->
                                <td v-else-if="column.name == 'Created Date'" class="whitespace-nowrap align-top pr-4 py-4 text-sm">
                                    <div class="flex space-x-1 items-center">
                                        <span>{{ formattedDatetime(order.created_at) }}</span>
                                        <Popover
                                            placement="top"
                                            class="opacity-0 group-hover:opacity-100"
                                            :content="formattedRelativeDate(order.created_at)">
                                        </Popover>
                                    </div>
                                </td>

                            </template>

                            <!-- Actions -->
                            <td v-if="columnIndex == (columns.length - 1)" class="align-top pr-4 py-4 flex items-center space-x-4">

                                <!-- View Button -->
                                <span v-if="!isDeletingOrder(order)" @click.stop.prevent="onView(order)" class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">View</span>

                                <!-- Deleting Loader -->
                                <Loader v-if="isDeletingOrder(order)" type="danger">
                                    <span class="text-xs ml-2">Deleting...</span>
                                </Loader>

                                <!-- Delete Button -->
                                <span v-else @click.stop.prevent="showDeleteConfirmationModal(order)" class="text-sm font-medium text-red-600 dark:text-red-500 hover:underline">Delete</span>

                            </td>

                        </template>

                    </tr>

                </template>

            </Table>

            <!-- No Orders -->
            <div v-else class="flex justify-center">

                <div
                    class="animated-border-blue w-96 bg-white py-4 px-4 shadow-sm space-y-4 rounded-xl">

                    <h1 class="text-xl font-bold">
                        Ready For Your First Sale?
                    </h1>

                    <p class="text-sm text-gray-500">
                        Your orders will appear here once customers start shopping. Start promoting your store to attract buyers and generate sales. Promote your store on as many platforms as possible.
                    </p>

                    <div class="flex justify-end">

                        <!-- Add Button -->
                        <Button
                            size="sm"
                            type="primary"
                            :leftIcon="Plus"
                            :action="onAddOrder">
                            <span>Add Order</span>
                        </Button>

                    </div>
                </div>

            </div>

        </div>

        <!-- Export Orders -->
        <Modal
            approveType="primary"
            :scrollOnContent="false"
            ref="exportOrdersModal"
            approveText="Export Orders"
            :approveAction="exportOrders"
            :approveIcon="ArrowDownToLine"
            :approveLoading="isExportingOrders">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Export Orders</p>

                <div class="space-y-4 mb-8">

                    <!-- Limit -->
                    <Select
                        width="w-full"
                        :search="false"
                        v-model="exportLimit"
                        :options="exportLimits"
                        label="Number of orders">
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

        <!-- Assign Team Member -->
        <Modal
            approveText="Assign"
            approveType="primary"
            ref="assignTeamMemberModal"
            :approveLoading="isUpdatingOrders"
            :approveAction="() => updateOrders('Assign Team Member')">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Assign Team Member</p>

                <div class="space-y-4 mb-8">

                    <div v-if="isLoadingTeamMembers" class="flex items-center space-x-2">
                        <Loader></Loader>
                        <span class="text-sm text-gray-500">Preparing team members</span>
                    </div>

                    <!-- Team Members Select Input -->
                    <Select
                        v-else
                        width="w-full"
                        :search="false"
                        v-model="teamMemberId"
                        :options="teamMembers"
                        :label="`Assign ${totalCheckedRows == 1 ? 'Order' : 'Order(s)'} to`">
                    </Select>

                </div>

            </template>

        </Modal>

        <!-- Change Status -->
        <Modal
            approveType="primary"
            ref="updateOrdersModal"
            :approveIcon="RefreshCcw"
            :scrollOnContent="false"
            approveText="Change Status"
            :approveLoading="isUpdatingOrders"
            :approveAction="() => updateOrders('Change Status')">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Change Status</p>

                <div class="space-y-4 mb-8">

                    <!-- Status Select Input -->
                    <Select
                        width="w-full"
                        label="Status"
                        :search="false"
                        v-model="status"
                        :options="statuses">
                    </Select>

                    <!-- Payment Status Select Input -->
                    <Select
                        width="w-full"
                        :search="false"
                        label="Payment Status"
                        v-model="paymentStatus"
                        :options="paymentStatuses">
                    </Select>

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

                    <!-- Include Order field names -->
                    <Input
                        type="checkbox"
                        v-model="includeOrderFieldNames"
                        inputLabel="Include Order field names">
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
                                        <svg class="draggable-handle w-4 h-4 cursor-grab hover:text-yellow-500 visible:cursor-grabbing" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                        </svg>

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
            :approveIcon="ArrowDownToLine"
            :approveAction="downloadOrders"
            :approveLoading="isDownloadingOrders">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Download PDF</p>

                <div class="flex space-x-2 p-4 text-xs bg-blue-100 rounded-lg mb-8">

                    <Info size="20" class="shrink-0"></Info>

                    <span>Creating the PDF to download may take a moment depending on the number of orders. Please do not close this window.</span>

                </div>

            </template>

        </Modal>

        <!-- Print PDF -->
        <Modal
            ref="printPdfModal"
            approveType="primary"
            :approveIcon="Printer"
            approveText="Print PDF"
            :approveAction="printOrders"
            :approveLoading="isDownloadingOrders">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Print PDF</p>

                <div class="flex space-x-2 p-4 text-xs bg-blue-100 rounded-lg mb-8">

                    <Info size="20" class="shrink-0"></Info>

                    <span>Creating the PDF to print may take a moment depending on the number of orders. Please do not close this window.</span>

                </div>

            </template>

        </Modal>

        <!-- Delete Orders -->
        <Modal
            approveType="danger"
            :approveIcon="Trash"
            ref="deleteOrdersModal"
            :approveAction="deleteOrders"
            :approveLoading="isDeletingOrders"
            :approveText="totalCheckedRows == 1 ? 'Delete Order' : 'Delete Orders'">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed pb-4 mb-4">Delete Orders</p>

                <div class="flex space-x-2 items-center p-4 text-xs bg-red-50 border border-red-200 border-dashed rounded-lg mb-8">

                    <svg class="w-6 h-6 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>

                    <span class="text-red-500">Are you sure you want to delete {{ totalCheckedRows > 1 ? `these ${totalCheckedRows} orders` : 'this order' }}?</span>

                </div>

            </template>

        </Modal>

        <!-- Confirm Delete Order -->
        <Modal
            ref="deleteOrderModal"
            approveText="Delete Order"
            :approveAction="deleteOrder"
            :approveLoading="isDeletingOrder(deletableOrder)">

            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Delete</p>
                <p v-if="deletableOrder" class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">Order #{{ deletableOrder.number }}</span>?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import axios from 'axios';
    import Tabs from '@Partials/tabs.vue';
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
    import Status from '@Pages/orders/order/components/order-header/Status.vue';
    import NoDataPlaceholder from '@Partials/table/components/NoDataPlaceholder.vue';
    import { Info, Plus, Trash, Printer, RefreshCcw, ArrowDownToLine } from 'lucide-vue-next';
    import PaymentStatus from '@Pages/orders/order/components/order-header/PaymentStatus.vue';
    import CollectionStatus from '@Pages/orders/order/components/order-header/CollectionStatus.vue';

    import { formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Info, Tabs, Input, Modal, Loader, Button, Switch, Select, Popover, Dropdown, Table, draggable: VueDraggableNext,
            Status, NoDataPlaceholder, PaymentStatus, CollectionStatus
        },
        data() {
            return {
                Plus,
                Trash,
                Printer,
                RefreshCcw,
                ArrowDownToLine,

                orders: [],
                perPage: '15',
                checkedRows: [],
                teamMembers: [],
                pagination: null,
                searchTerm: null,
                selectAll: false,
                teamMemberId: null,
                exportLimit: '100',
                exportFormat: 'csv',
                status: 'no change',
                deletableOrder: null,
                filterExpressions: [],
                sortingExpressions: [],
                isDeletingOrderIds: [],
                isLoadingOrders: false,
                isUpdatingOrders: false,
                exportWithFilters: true,
                exportWithSorting: true,
                exportMode: 'by_orders',
                isExportingOrders: false,
                paymentStatus: 'no change',
                isDownloadingOrders: false,
                isLoadingTeamMembers: false,
                includeOrderFieldNames: true,
                columns: this.prepareColumns(),
                whatsappFields: this.prepareWhatsappFields(),
                filterTab: 'all',
                filterTabs: [
                    { label: 'All', value: 'all'},
                    { label: 'Paid', value: 'Paid'},
                    { label: 'Unpaid', value: 'unpaid'},
                    { label: 'Partially paid', value: 'partially paid'},
                    { label: 'Pending payment', value: 'pending payment'},
                ],
                exportLimits: [
                    { label: '100', value: '100'},
                    { label: '500', value: '500'},
                    { label: '1000', value: '1000'},
                    { label: '2000', value: '2000'},
                    { label: '3000', value: '3000'},
                    { label: '4000', value: '4000'},
                    { label: '5000', value: '5000'},
                ],
                statuses: [
                    { label: 'No change', value: 'no change'},
                    { label: 'Waiting', value: 'waiting'},
                    { label: 'Cancelled', value: 'cancelled'},
                    { label: 'Completed', value: 'completed'},
                    { label: 'On Its Way', value: 'on its way'},
                    { label: 'Ready For Pickup', value: 'ready for pickup'},
                ],
                paymentStatuses: [
                    { label: 'No change', value: 'no change'},
                    { label: 'Paid', value: 'paid'},
                    { label: 'Unpaid', value: 'unpaid'},
                    { label: 'Pending Payment', value: 'pending payment'},
                    { label: 'Partially Paid', value: 'partially paid'},
                ],
                exportModes: [
                    {
                        label: 'One order per row',
                        value: 'by_orders'
                    },
                    {
                        label: 'One product per row',
                        value: 'by_products'
                    },
                    {
                        label: 'One product per row (blanking)',
                        value: 'by_products_blanking'
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
                        label: 'Assign Team Member',
                        action: this.showAssignTeamMemberModal,
                    },
                    {
                        label: 'Change Status',
                        action: this.showUpdateOrdersModal,
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
                        action: this.showDeleteOrdersModal,
                    }
                ],
            }
        },
        watch: {
            store(newValue) {
                if(newValue) {
                    this.getOrders();
                }
            },
            selectAll(newValue) {
                this.checkedRows = this.orders.reduce((acc, order) => {
                    acc[order.id] = newValue;
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
            isDeletingOrders() {
                return this.isDeletingOrderIds.length > 0;
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
                const columnNames = ['Number', 'Customer', 'Summary', 'Status', 'Payment Status', 'Collection Status', 'Grand Total', 'Paid', 'Pending', 'Outstanding', 'Customer Note', 'Created Date'];
                const defaultColumnNames  = ['Number', 'Customer', 'Summary', 'Status', 'Grand Total', 'Created Date'];

                return columnNames.map(name => ({
                    name,
                    active: defaultColumnNames.includes(name),
                    priority: defaultColumnNames.includes(name)
                }));
            },
            prepareWhatsappFields() {
                const whatsappFieldNames = ['Number', 'Customer', 'Summary', 'Status', 'Payment Status', 'Collection Status', 'Grand Total', 'Paid', 'Pending', 'Outstanding', 'Customer Note', 'Created Date', 'Order Link'];
                const defaultWhatsappFieldNames  = ['Number', 'Customer', 'Summary', 'Status', 'Grand Total', 'Created Date', 'Order Link'];

                return whatsappFieldNames.map(name => ({
                    name,
                    linebreak: false,
                    active: defaultWhatsappFieldNames.includes(name),
                    priority: defaultWhatsappFieldNames.includes(name)
                }));
            },
            showExportOrdersModal() {
                this.$refs.exportOrdersModal.showModal();
            },
            showPrintPdfModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.printPdfModal.showModal();
            },
            showDeleteOrdersModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.deleteOrdersModal.showModal();
            },
            showDownloadPdfModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.downloadPdfModal.showModal();
            },
            showUpdateOrdersModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.updateOrdersModal.showModal();
            },
            showSendToWhatsappModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.sendToWhatsappModal.showModal();
            },
            showAssignTeamMemberModal() {
                this.getTeamMembers();
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.assignTeamMemberModal.showModal();
            },
            showDeleteConfirmationModal(order) {
                this.deletableOrder = order;
                this.$refs.deleteOrderModal.showModal();
            },
            isDeletingOrder(order) {
                if(order == null) return false;
                return this.isDeletingOrderIds.findIndex((id) => id == order.id) != -1;
            },
            onView(order) {
                this.$router.push({
                    name: 'show-order',
                    params: {
                        order_id: order.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|'),
                    }
                });
            },
            onAddOrder() {
                this.$router.push({
                    name: 'create-order',
                    query: { 'store_id': this.store.id }
                });
            },
            paginate(url) {
                this.getOrders(url);
            },
            search(searchTerm) {
                this.searchTerm = searchTerm;
                this.getOrders();
            },
            updatedColumns(columns) {
                this.columns = columns;
            },
            updatedFilters(filters) {
                const newFilterExpressions = filters.map((filter) => filter.expression);
                if(!isEqual(this.filterExpressions, newFilterExpressions)) {
                    this.filterExpressions = newFilterExpressions;
                    this.getOrders();
                }
            },
            updatedSorting(sorting) {
                const newSortingExpressions = sorting.map((sort) => sort.expression);
                if(!isEqual(this.sortingExpressions, newSortingExpressions)) {
                    this.sortingExpressions = newSortingExpressions;
                    this.getOrders();
                }
            },
            updatedPerPage(perPage) {
                this.perPage = perPage;
                this.getOrders();
            },
            async getOrders(url = null) {

                try {

                    this.isLoadingOrders = true;

                    let config = {};

                    if(url == null) {

                        url = `/api/orders`;

                        config = {
                            params: {
                                'per_page': this.perPage,
                                'store_id': this.store.id
                            }
                        }

                        if(this.hasSearchTerm) config.params['search'] = this.searchTerm;

                        if(this.hasFilterExpressions) {
                            config.params['_filters'] = this.filterExpressions.join('|');
                        }

                        if(this.hasSortingExpressions) {
                            config.params['_sort'] = this.sortingExpressions.join('|');
                        }

                    }

                    const response = await axios.get(url, config);

                    this.pagination = response.data;
                    this.orders = this.pagination.data;
                    this.checkedRows = this.orders.reduce((acc, order) => {
                        acc[order.id] = false;
                        return acc;
                    }, {});

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching orders';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch orders:', error);
                } finally {
                    this.isLoadingOrders = false;
                }

            },
            async exportOrders() {

                try {

                    if(this.isExportingOrders) return;
                    this.isExportingOrders = true;

                    let config = {
                        params: {
                            '_export': '1',
                            'store_id': this.store.id,
                            'export_mode': this.exportMode,
                            'export_limit': this.exportLimit,
                            'export_format': this.exportFormat
                        }
                    }

                    if(this.hasSearchTerm) config.params['search'] = this.searchTerm;

                    if(this.hasFilterExpressions) {
                        config.params['_filters'] = this.filterExpressions.join('|');
                    }

                    if(this.hasSortingExpressions) {
                        config.params['_sort'] = this.sortingExpressions.join('|');
                    }

                    const response = await axios.get(`/api/orders`, config);

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');

                    link.href = url;
                    link.setAttribute('download', `orders.${this.exportFormat}`);

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while exporting orders';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to export orders:', error);
                } finally {
                    this.isExportingOrders = false;
                    this.$refs.exportOrdersModal.hideModal();
                }

            },
            async updateOrders(type) {

                try {

                    if(this.isUpdatingOrders) return;

                    const orderIds = this.orders.filter(order => this.checkedRows[order.id]).map(order => order.id);

                    const data = {
                        order_ids: orderIds,
                        store_id: this.store.id
                    };

                    const isChangingStatus = (type == 'Change Status');
                    const isAssigningTeamMember = (type == 'Assign Team Member');

                    if(isChangingStatus) {
                        if(this.status != 'no change') data['status'] = this.status.toLowerCase();
                        if(this.paymentStatus != 'no change') data['payment_status'] = this.paymentStatus.toLowerCase();
                    }else if(isAssigningTeamMember) {
                        if(this.teamMemberId) data['assigned_to_user_id'] = this.teamMemberId;
                    }

                    if(Object.keys(data).length == 2) return;

                    this.isUpdatingOrders = true;

                    await axios.put(`/api/orders`, data);

                    this.getOrders();

                    if(isChangingStatus) {
                        this.notificationState.showSuccessNotification('Order status updated');
                    }else if(isAssigningTeamMember) {
                        const teamMember = this.teamMembers.find(teamMember => teamMember.value === this.teamMemberId);
                        if (teamMember) this.notificationState.showSuccessNotification(`Orders assigned to ${teamMember.first_name}`);
                    }

                    // Uncheck only the related rows
                    orderIds.forEach(orderId => {
                        if (this.checkedRows[orderId] !== undefined) {
                            this.checkedRows[orderId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update order:', error);
                } finally {
                    this.isUpdatingOrders = false;
                    this.$refs.updateOrdersModal.hideModal();
                }

            },
            async downloadOrders() {

                try {

                    if(this.isDownloadingOrders) return;

                    const orderIds = this.orders.filter(order => this.checkedRows[order.id]).map(order => order.id);

                    const data = {
                        store_id: this.store.id,
                        order_ids: orderIds
                    };

                    const config = {
                        responseType: "blob"
                    };

                    this.isDownloadingOrders = true;

                    const response = await axios.post(`/api/orders/download`, data, config);

                    const blob = new Blob([response.data], { type: "application/pdf" });
                    const link = document.createElement("a");

                    link.href = window.URL.createObjectURL(blob);
                    link.download = "orders.pdf";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Uncheck only the related rows
                    orderIds.forEach(orderId => {
                        if (this.checkedRows[orderId] !== undefined) {
                            this.checkedRows[orderId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while downloading orders';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to download orders:', error);
                } finally {
                    this.isDownloadingOrders = false;
                    this.$refs.downloadPdfModal.hideModal();
                }

            },
            async printOrders() {

                try {

                    if(this.isDownloadingOrders) return;

                    const orderIds = this.orders.filter(order => this.checkedRows[order.id]).map(order => order.id);

                    const data = {
                        store_id: this.store.id,
                        order_ids: orderIds
                    };

                    const config = {
                        responseType: "blob"
                    };

                    this.isDownloadingOrders = true;

                    const response = await axios.post(`/api/orders/download`, data, config);

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
                    orderIds.forEach(orderId => {
                        if (this.checkedRows[orderId] !== undefined) {
                            this.checkedRows[orderId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while printing orders';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to print orders:', error);
                } finally {
                    this.isDownloadingOrders = false;
                    this.$refs.printPdfModal.hideModal();
                }

            },
            async deleteOrders() {

                try {

                    const orderIds = this.orders.filter(order => this.checkedRows[order.id]).map(order => order.id)
                                                .filter(orderId => !this.isDeletingOrderIds.includes(orderId));

                    if (orderIds.length === 0) return;

                    this.isDeletingOrderIds.push(...orderIds);

                    const config = {
                        data: {
                            store_id: this.store.id,
                            order_ids: orderIds
                        }
                    }

                    await axios.delete(`/api/orders`, config);

                    this.notificationState.showSuccessNotification(orderIds == 1 ? 'Order deleted' : 'Orders deleted');
                    this.orders = this.orders.filter(order => !orderIds.includes(order.id));
                    if(this.orders.length == 0) this.getOrders();

                    this.isDeletingOrderIds = this.isDeletingOrderIds.filter(id => !orderIds.includes(id));

                    // Uncheck only the related rows
                    orderIds.forEach(orderId => {
                        if (this.checkedRows[orderId] !== undefined) {
                            this.checkedRows[orderId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting orders';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete orders:', error);
                } finally {
                    this.$refs.deleteOrdersModal.hideModal();
                }
            },
            async deleteOrder() {

                try {

                    if(this.isDeletingOrderIds.includes(this.deletableOrder.id)) return;

                    this.isDeletingOrderIds.push(this.deletableOrder.id);

                    await axios.delete(`/api/orders/${this.deletableOrder.id}`);

                    this.notificationState.showSuccessNotification('Order deleted');
                    this.orders = this.orders.filter(order => order.id != this.deletableOrder.id);
                    if(this.orders.length == 0) this.getOrders();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting orders';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete orders:', error);
                } finally {
                    this.isDeletingOrderIds.splice(this.isDeletingOrderIds.findIndex((id) => id == this.deletableOrder.id, 1));
                    this.$refs.deleteOrderModal.hideModal();
                }

            },
            async getTeamMembers() {

                this.isLoadingTeamMembers = true;

                await axios.get(this.store._links.showStoreTeamMembers).then(response => {

                    if(response.status == 200) {
                        const pagination = response.data;
                        this.teamMembers = pagination.data.map(function(teamMember) {
                            return {
                                value: teamMember.id,
                                label: teamMember.name,
                                first_name: teamMember.first_name
                            }
                        });
                        this.teamMemberId = this.teamMembers[0].value;
                    }else{

                        this.formState.setFormError('general', response.data.message);
                        this.notificationState.showWarningNotification(response.data.message);

                    }

                }).catch(errorException => {
                    this.formState.setServerFormErrors(errorException);
                });

                this.isLoadingTeamMembers = false;

            },
            async sendToWhatsapp() {

                const checkedOrders = this.orders.filter(order => this.checkedRows[order.id]);

                if (checkedOrders.length === 0) {
                    alert("No orders selected to send to WhatsApp.");
                    return;
                }

                let message = "";
                let tempMessage = "";
                const maxLength = 58000;

                for (let i = 0; i < checkedOrders.length; i++) {
                    let orderMessage = `Order #${i + 1}\n` +
                        `----------------------\n`;

                    this.whatsappFields.forEach(field => {

                        if (field.active) {

                            if(field.linebreak) orderMessage += `\n`;
                            if(this.includeOrderFieldNames) orderMessage += `${field.name}: `;

                            switch (field.name) {
                                case "Number":
                                    orderMessage += `${checkedOrders[i].number}\n`;
                                    break;
                                case "Customer":
                                    orderMessage += `${checkedOrders[i].customer_name}\n`;
                                    break;
                                case "Summary":
                                    orderMessage += `${checkedOrders[i].summary}\n`;
                                    break;
                                case "Status":
                                    orderMessage += `${checkedOrders[i].status}\n`;
                                    break;
                                case "Payment Status":
                                    orderMessage += `${checkedOrders[i].payment_status}\n`;
                                    break;
                                case "Collection Status":
                                    orderMessage += `${checkedOrders[i].collection_verified}\n`;
                                    break;
                                case "Grand Total":
                                    orderMessage += `${checkedOrders[i].grand_total.amount_with_currency}\n`;
                                    break;
                                case "Paid":
                                    orderMessage += `${checkedOrders[i].paid_total.amount_with_currency}\n`;
                                    break;
                                case "Pending":
                                    orderMessage += `${checkedOrders[i].pending_total.amount_with_currency}\n`;
                                    break;
                                case "Outstanding":
                                    orderMessage += `${checkedOrders[i].outstanding_total.amount_with_currency}\n`;
                                    break;
                                case "Customer Note":
                                    orderMessage += `${checkedOrders[i].customer_note || 'None'}\n`;
                                    break;
                                case "Created Date":
                                    orderMessage += `${checkedOrders[i].created_at}\n`;
                                    break;
                                case "Order Link":
                                    orderMessage += `${window.location.origin + this.$router.resolve({ name: 'show-order', params: { 'store_id': this.store.id, 'order_id': checkedOrders[i].id } }).href}\n`;
                                    break;
                            }

                        }

                    });

                    // Add separator only if it's not the last order
                    if (i < checkedOrders.length - 1) {
                        orderMessage += `\n\n`;
                    }

                    if ((tempMessage.length + orderMessage.length) > maxLength) {
                        break;
                    }

                    tempMessage += orderMessage;
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
            this.isLoadingOrders = true;
            this.searchTerm = this.$route.query.searchTerm;
            if(this.$route.query.filterExpressions) this.filterExpressions = this.$route.query.filterExpressions.split('|');
            if(this.$route.query.sortingExpressions) this.sortingExpressions = this.$route.query.sortingExpressions.split('|');
        }
    };

</script>
