<template>

    <div class="pt-24 px-8 relative select-none">

        <!-- Clouds Image -->
        <img :src="'/images/clouds.png'" class="absolute bottom-0">

        <div class="relative bg-white/80 p-4 rounded-md">

            <h1 class="text-lg text-gray-700 font-semibold mb-4">Bulk Edit</h1>

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
                        :key="customerForm.id"
                        v-for="(customerForm, customerIndex) in customerForms"
                        :class="[checkedRows[customerForm.id] ? 'bg-blue-50' : 'bg-white hover:bg-gray-50', 'group cursor-pointer border-b border-blue-100']">

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
                                    v-model="checkedRows[customerForm.id]">
                                </Input>

                            </td>

                            <template v-if="column.active">

                                <!-- First Name -->
                                <td v-if="column.name == 'First Name'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="text"
                                        class="min-w-40"
                                        v-model="customerForms[customerIndex].first_name"
                                        @input="captureChange(customerIndex, 'First name changed')"
                                        :errorText="formState.getFormError(`customer.${customerIndex}.first_name`)">
                                    </Input>

                                </td>

                                <!-- Last Name -->
                                <td v-if="column.name == 'Last Name'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="text"
                                        class="min-w-40"
                                        v-model="customerForms[customerIndex].last_name"
                                        @input="captureChange(customerIndex, 'Last name changed')"
                                        :errorText="formState.getFormError(`customer.${customerIndex}.last_name`)">
                                    </Input>

                                </td>

                                <!-- Mobile -->
                                <td v-if="column.name == 'Mobile'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="text"
                                        class="min-w-40"
                                        v-model="customerForms[customerIndex].mobile_number"
                                        @input="captureChange(customerIndex, 'Mobile number changed')"
                                        :errorText="formState.getFormError(`customer.${customerIndex}.mobile_number`)">
                                    </Input>

                                </td>

                                <!-- Email -->
                                <td v-if="column.name == 'Email'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="text"
                                        class="min-w-60"
                                        v-model="customerForms[customerIndex].email"
                                        @input="captureChange(customerIndex, 'Email changed')"
                                        :errorText="formState.getFormError(`customer.${customerIndex}.email`)">
                                    </Input>

                                </td>

                                <!-- Birthday -->
                                <td v-if="column.name == 'Birthday'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="date"
                                        class="min-w-40"
                                        v-model="customerForms[customerIndex].birthday"
                                        @input="captureChange(customerIndex, 'Birthday changed')"
                                        :errorText="formState.getFormError(`customer.${customerIndex}.birthday`)">
                                    </Input>

                                </td>

                                <!-- Referral Code -->
                                <td v-if="column.name == 'Referral Code'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="text"
                                        class="min-w-40"
                                        v-model="customerForms[customerIndex].referral_code"
                                        @input="captureChange(customerIndex, 'Referral code changed')"
                                        :errorText="formState.getFormError(`customer.${customerIndex}.referral_code`)">
                                    </Input>

                                </td>

                                <!-- Notes -->
                                <td v-if="column.name == 'Notes'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="text"
                                        class="min-w-80"
                                        v-model="customerForms[customerIndex].notes"
                                        @input="captureChange(customerIndex, 'Notes changed')"
                                        :errorText="formState.getFormError(`customer.${customerIndex}.notes`)">
                                    </Input>

                                </td>

                            </template>

                            <!-- Actions -->
                            <td v-if="columnIndex == (columns.length - 1)" class="align-center pr-4 py-4">

                                <div class="flex items-center space-x-4">

                                    <!-- View Button -->
                                    <span v-if="!isDeletingCustomer(customerForms[customerIndex])" @click.stop.prevent="onView(customerForms[customerIndex])" class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">View</span>

                                    <!-- Deleting Loader -->
                                    <Loader v-if="isDeletingCustomer(customerForms[customerIndex])" type="danger">
                                        <span class="text-xs ml-2">Deleting...</span>
                                    </Loader>

                                    <!-- Delete Button -->
                                    <span v-else @click.stop.prevent="showDeleteConfirmationModal(customerForms[customerIndex])" class="text-sm font-medium text-red-600 dark:text-red-500 hover:underline">Delete</span>

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
    import Dropdown from '@Partials/Dropdown.vue';
    import Table from '@Partials/table/Table.vue';
    import { Plus, Trash2, RefreshCcw } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'customerState', 'storeState', 'changeHistoryState', 'notificationState'],
        components: {
            Input, Modal, Loader, Button, Dropdown, Table
        },
        data() {
            return {
                Plus,
                Trash2,
                RefreshCcw,

                notes: null,
                perPage: '15',
                checkedRows: [],
                pagination: null,
                searchTerm: null,
                selectAll: false,
                latestRequestId: 0,
                filterExpressions: [],
                sortingExpressions: [],
                isDeletingProducIds: [],
                deletableCustomer: null,
                cancelTokenSource: null,
                isLoadingCustomers: false,
                isUpdatingCustomers: false,
                columns: this.prepareColumns(),
                includeCustomerFieldNames: true,
                bulkSelectionOptions: [
                    {
                        label: 'Add Customer Notes',
                        action: this.showUpdateCustomersModal
                    },
                    {
                        label: 'Delete',
                        action: this.showDeleteCustomersModal
                    }
                ]
            }
        },
        watch: {
            store(newValue) {
                if(newValue) {
                    this.showCustomers();
                }
            },
            selectAll(newValue) {
                this.checkedRows = this.customerForms.reduce((acc, customerForm) => {
                    acc[customerForm.id] = newValue;
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
                return this.isDeletingProducIds.length > 0;
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
            customerForms() {
                return this.customerState.customerForms;
            },
        },
        methods: {
            prepareColumns() {
                const columnNames = ['First Name', 'Last Name', 'Mobile', 'Email', 'Birthday', 'Notes', 'Referral Code'];
                const defaultColumnNames  = ['First Name', 'Last Name', 'Mobile', 'Email', 'Birthday', 'Notes', 'Referral Code'];

                return columnNames.map(name => ({
                    name,
                    active: defaultColumnNames.includes(name),
                    priority: defaultColumnNames.includes(name)
                }));
            },
            showDeleteCustomersModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.deleteCustomersModal.showModal();
            },
            showUpdateCustomersModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.updateCustomersModal.showModal();
            },
            showDeleteConfirmationModal(customer) {
                this.deletableCustomer = customer;
                this.$refs.deleteCustomerModal.showModal();
            },
            isDeletingCustomer(customer) {
                if(customer == null) return false;
                return this.isDeletingProducIds.findIndex((id) => id == customer.id) != -1;
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
            captureChange(customerIndex, actionName) {
                this.customerForms[customerIndex].modified = true;
                this.customerState.saveStateDebounced(actionName);
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
                    const customers = this.pagination.data;

                    this.customerState.setCustomerForms(customers);

                    this.checkedRows = this.customerForms.reduce((acc, customerForm) => {
                        acc[customerForm.id] = false;
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
            async updateCustomers(type = null) {

                try {

                    if(this.isUpdatingCustomers) return;

                    const data = {
                        store_id: this.store.id
                    };

                    if(type == null) {
                        data['customers'] = this.customerForms.filter(customerForm => customerForm.modified);
                    }else{
                        data['customers'] = this.customerForms.filter(customerForm => this.checkedRows[customerForm.id]).map(customerForm => ({ id: customerForm.id }));
                    }

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
                    data['customers'].forEach(customerForm => {
                        if (this.checkedRows[customerForm.id] !== undefined) {
                            this.checkedRows[customerForm.id] = false;
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

                    const customerFormIds = this.customerForms.filter(customerForm => this.checkedRows[customerForm.id]).map(customerForm => customerForm.id)
                                                .filter(customerFormId => !this.isDeletingProducIds.includes(customerFormId));

                    if (customerFormIds.length === 0) return;

                    this.isDeletingProducIds.push(...customerFormIds);

                    const config = {
                        data: {
                            store_id: this.store.id,
                            customer_ids: customerFormIds
                        }
                    }

                    await axios.delete(`/api/customers`, config);

                    this.notificationState.showSuccessNotification(customerFormIds == 1 ? 'Customer deleted' : 'Customers deleted');
                    this.customerState.customerForms = this.customerForms.filter(customerForm => !customerFormIds.includes(customerForm.id));
                    if(this.customerForms.length == 0) this.showCustomers();

                    this.isDeletingProducIds = this.isDeletingProducIds.filter(id => !customerFormIds.includes(id));

                    // Uncheck only the related rows
                    customerFormIds.forEach(customerFormId => {
                        if (this.checkedRows[customerFormId] !== undefined) {
                            this.checkedRows[customerFormId] = false;
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

                    if(this.isDeletingProducIds.includes(this.deletableCustomer.id)) return;

                    this.isDeletingProducIds.push(this.deletableCustomer.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/customers/${this.deletableCustomer.id}`, config);

                    this.notificationState.showSuccessNotification('Customer deleted');
                    this.customerState.customerForms = this.customerForms.filter(customerForm => customerForm.id != this.deletableCustomer.id);
                    if(this.customerForms.length == 0) this.showCustomers();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting customer';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete customer:', error);
                } finally {
                    this.isDeletingProducIds.splice(this.isDeletingProducIds.findIndex((id) => id == this.deletableCustomer.id), 1);
                    this.$refs.deleteCustomerModal.hideModal();
                }

            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton(this.onDiscard);
                this.changeHistoryState.addActionButton(
                    'Save Changes',
                    this.updateCustomers,
                    'primary',
                    null
                );
            },
            setCustomerForms(customerForms) {
                this.customerState.customerForms = customerForms;
            }
        },
        unmounted() {
            this.customerState.reset();
            this.changeHistoryState.reset();
        },
        created() {

            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setCustomerForms;
            }

            this.isLoadingCustomers = true;
            this.searchTerm = this.$route.query.searchTerm;
            if(this.$route.query.filterExpressions) this.filterExpressions = this.$route.query.filterExpressions.split('|');
            if(this.$route.query.sortingExpressions) this.sortingExpressions = this.$route.query.sortingExpressions.split('|');
            if(this.store) this.showCustomers();
        }
    };

</script>
