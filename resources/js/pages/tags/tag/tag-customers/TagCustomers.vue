<template>

    <div class="bg-white rounded-lg p-4 shadow-sm">

        <div class="flex items-center space-x-2 mb-4">

            <p class="text-lg text-gray-700 font-semibold">Customers</p>

            <Pill v-if="pagination" type="light" size="xs">{{ `${pagination.meta.total} total` }}</Pill>

        </div>

        <Search
            class="mb-4"
            @selected="addCustomer"
            :options="customerOptions"
            :isLoading="isLoadingCustomers"
            placeholder="Search customers to add"
            :search="(searchTerm) => showCustomers(searchTerm)">

            <template #option="props">

                <span class="truncate">{{ props.option.label }}</span>

            </template>

        </Search>

        <template v-if="preparedCustomers.length || customersToAdd.length || customersToRemove.length">

            <div class="relative">

                <BackdropLoader v-if="isLoadingTagCustomers" :showBorder="false" :showSpinningLoader="true"></BackdropLoader>

                <div class="divide-y divide-gray-200">

                    <div
                        :key="index"
                        v-for="(customer, index) in preparedCustomers"
                        class="flex items-center justify-between hover:bg-gray-100 min-h-16 px-4">

                        <div>
                            <p class="text-sm truncate">{{ customer.name }}</p>

                            <div class="flex items-center space-x-2 text-xs">
                                <span v-if="customer.mobile_number">{{ customer.mobile_number.national }}</span>
                                <span v-if="customer.email">{{ customer.email }}</span>
                            </div>
                        </div>

                        <Button
                            size="xs"
                            type="bareDanger"
                            :leftIcon="Trash2"
                            :action="() => removeCustomer(customer)">
                        </Button>

                    </div>

                </div>

            </div>

            <div v-if="pagination" class="flex justify-end mt-4">

                <!-- Bottom Paginator -->
                <Paginator :pagination="pagination" @paginate="paginate"></Paginator>

            </div>

        </template>

        <div v-else class="flex justify-center bg-gray-50 rounded-lg p-4">

            <p v-if="isCreatingCustomerTag || (isEditingCustomerTag && pagination && totalTagCustomers == 0)" class="text-sm">No customers added</p>

            <div v-else class="flex items-center justify-center">
                <Loader>
                    <span class="text-sm ml-2">Loading customers</span>
                </Loader>
            </div>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import { Trash2 } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import Search from '@Partials/Search.vue';
    import BackdropLoader from '@Partials/BackdropLoader.vue';
    import Paginator from '@Partials/table/components/Paginator.vue';

    export default {
        inject: ['formState', 'tagState', 'storeState', 'notificationState'],
        components: { Pill, Button, Loader, Search, Paginator, BackdropLoader },
        data() {
            return {
                Trash2,
                page: 1,
                tagCustomers: [],
                pagination: null,
                customerOptions: [],
                latestRequestId: null,
                cancelTokenSource: null,
                isLoadingCustomers: false,
                isLoadingTagCustomers: false
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    if(!this.isLoadingCustomers && this.customerOptions.length == 0) this.showCustomers();
                }
            },
            tag(newValue) {
                if(newValue) {
                    if(!this.isLoadingCustomers && this.customerOptions.length == 0) this.showCustomers();
                    this.showTagCustomers();
                }
            },
            isUpdatingTag(newValue, oldValue) {
                if(oldValue && !newValue) {
                    this.showTagCustomers();
                }
            },
            customersReady(newValue) {
                if (newValue) {
                    this.$emit('tagCustomersReady');
                }
            }
        },
        computed: {
            tag() {
                return this.tagState.tag;
            },
            store() {
                return this.storeState.store;
            },
            isUpdatingTag() {
                return this.tagState.isUpdatingTag;
            },
            isEditingCustomerTag() {
                return this.$route.name == 'edit-customer-tag';
            },
            isCreatingCustomerTag() {
                return this.$route.name == 'create-customer-tag';
            },
            tagForm() {
                return this.tagState.tagForm;
            },
            totalTagCustomers() {
                return this.tagCustomers.length;
            },
            hasTagCustomers() {
                return this.totalTagCustomers > 0;
            },
            customersReady() {
                if(this.isCreatingCustomerTag) {
                    return !this.isLoadingCustomers;
                }else {
                    return !this.isLoadingCustomers && !this.isLoadingTagCustomers;
                }
            },
            customersToAdd() {
                return this.tagState.tagForm.customers_to_add;
            },
            customersToRemove() {
                return this.tagState.tagForm.customers_to_remove;
            },
            preparedCustomers() {
                if (!this.customersReady) return [];

                // Create Sets for efficient lookup
                const customersToAddIds = new Set(this.customersToAdd.map(p => String(p.id)));
                const customersToRemoveIds = new Set(this.customersToRemove.map(p => String(p.id)));

                // Combine customers based on page
                const customers = this.page == 1
                    ? [...this.customersToAdd, ...this.tagCustomers]
                    : this.tagCustomers.filter(
                        customer => !customersToAddIds.has(String(customer.id)) && !customersToRemoveIds.has(String(customer.id))
                    );

                // Deduplicate customers by id and filter out customers to remove
                const seenIds = new Set();
                return customers.filter(customer => {
                    if (customersToRemoveIds.has(String(customer.id))) {
                        return false; // Ignore removed customers
                    }
                    if (seenIds.has(String(customer.id))) {
                        return false; // Skip duplicate customers
                    }
                    seenIds.add(String(customer.id));
                    return true;
                });
            }
        },
        methods: {
            paginate(page) {
                this.showTagCustomers(page);
            },
            async showCustomers(searchTerm = null) {

                const currentRequestId = ++this.latestRequestId;

                try {

                    this.isLoadingCustomers = true;

                    if (this.cancelTokenSource) {
                        this.cancelTokenSource.cancel('Request superseded by a newer one'); // Cancel previous request if it exists
                    }

                    this.cancelTokenSource = axios.CancelToken.source(); // Create a new cancel token source

                    let config = {
                        params: {
                            store_id: this.store.id
                        },
                        cancelToken: this.cancelTokenSource.token // Attach cancel token
                    }

                    if(searchTerm) config.params['search'] = searchTerm;

                    const response = await axios.get(`/api/customers`, config);

                    // Only process response if it matches the latest request
                    if (currentRequestId !== this.latestRequestId) return;

                    const pagination = response.data;
                    const customers = pagination.data;

                    this.customerOptions = customers.map(customer => {
                        return {
                            label: customer.name,
                            customer: customer
                        }
                    });

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
            async showTagCustomers(page = 1) {
                try {

                    this.page = page;
                    this.isLoadingTagCustomers = true;

                    let config = {
                        params: {
                            per_page: 15,
                            page: this.page,
                            tag_id: this.tag.id,
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get('/api/customers', config);

                    this.pagination = response.data;
                    this.tagCustomers = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching customers';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch customers:', error);
                } finally {
                    this.isLoadingTagCustomers = false;
                }
            },
            addCustomer(option) {
                if(this.page > 1) this.showTagCustomers();
                this.tagState.addCustomer(option.customer);
            },
            removeCustomer(customer) {
                this.tagState.removeCustomer(customer);
            }
        },
        created() {
            if(this.store) this.showCustomers();
        }
    };

</script>
