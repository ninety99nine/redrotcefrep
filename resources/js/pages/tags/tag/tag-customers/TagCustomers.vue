<template>

    <div class="bg-white rounded-lg p-4 shadow-sm">

        <div class="flex items-center space-x-2 mb-4">

            <p class="text-lg text-gray-700 font-semibold">Customers</p>

            <Pill type="light" size="xs">{{ `${totalTagCustomers} total` }}</Pill>

        </div>

        <Search
            class="mb-4"
            @selected="addCustomer"
            :options="customerOptions"
            :isLoading="isLoadingCustomers"
            placeholder="Search customers to add"
            :search="(searchTerm) => showCustomers(searchTerm)">
        </Search>

        <div v-if="hasTagCustomers">

            <div
                :key="index"
                v-for="(tagCustomer, index) in tagCustomers"
                class="flex items-center justify-between hover:bg-gray-100 rounded-lg py-2 px-4">

                <span class="truncate">{{ tagCustomer.name }}</span>

                <Button
                    size="xs"
                    type="bareDanger"
                    :leftIcon="Trash2"
                    :action="() => removeCustomer(index)">
                </Button>

            </div>

        </div>

        <div v-else class="flex justify-center bg-gray-50 rounded-lg p-4">

            <p class="text-sm">No customers added</p>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import { Trash2 } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import Search from '@Partials/Search.vue';

    export default {
        inject: ['formState', 'tagState', 'storeState', 'notificationState'],
        components: { Pill, Button, Search },
        data() {
            return {
                Trash2,
                customerOptions: [],
                latestRequestId: null,
                cancelTokenSource: null,
                isLoadingCustomers: false
            }
        },
        watch: {
            store(newValue) {
                if(newValue) {
                    this.showCustomers();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            tagForm() {
                return this.tagState.tagForm;
            },
            tagCustomers() {
                return this.tagForm.customers;
            },
            totalTagCustomers() {
                return this.tagCustomers.length;
            },
            hasTagCustomers() {
                return this.totalTagCustomers > 0;
            },
        },
        methods: {
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
                            store_id: this.store.id,
                            association: 'team member'
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
            addCustomer(option) {
                this.tagState.addCustomer(option.customer);
            },
            removeCustomer(index) {
                this.tagState.removeCustomer(index);
            }
        },
        created() {
            if(this.store) this.showCustomers();
        }
    };

</script>
