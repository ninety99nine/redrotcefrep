<template>

    <div class="bg-white rounded-lg p-4 mb-4">

        <div class="flex items-center space-x-2 mb-4">

            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>

            <span class="text-gray-700 font-semibold">Team Member</span>

        </div>

        <!-- Order Team Member (Loading Placeholder) -->
        <Skeleton
            v-if="isLoadingStore || isLoadingOrder || !hasOrder"
            width="w-full" height="h-8" rounded="rounded-md" :shine="true">
        </Skeleton>

        <template v-else>

            <!-- Team Members Select Input -->
            <Select
                :search="true"
                :options="teamMemberOptions"
                v-model="orderForm.assigned_to_user_id"
                :skeleton="isLoadingOrder || !hasOrder || isLoadingTeamMembers"
                @change="(assignedToUserId) => updateOrder({ assigned_to_user_id: assignedToUserId })">
            </Select>

        </template>

    </div>

</template>

<script>

    import axios from 'axios';
    import Select from '@Partials/Select.vue';
    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Select, Skeleton },
        data() {
            return {
                teamMembers: [],
                isLoadingTeamMembers: false
            }
        },
        watch: {
            isLoadingOrder(newValue) {
                if(!newValue && this.hasOrder) {
                    this.showTeamMembers()
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            teamMemberOptions() {
                let options = [
                    {
                        'label': 'Unassigned',
                        'value': null
                    }
                ];

                this.teamMembers.forEach((teamMember) => {
                    options.push({
                        'label': teamMember.name,
                        'value': teamMember.id
                    });
                });

                return options;
            }
        },
        methods: {
            async showTeamMembers() {
                try {

                    this.isLoadingTeamMembers = true;

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get('/api/users', config);

                    this.pagination = response.data;
                    this.teamMembers = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching team members';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch team members:', error);
                } finally {
                    this.isLoadingTeamMembers = false;
                }
            },
            async updateOrder(data) {

                try {

                    data = {
                        ...data,
                        store_id: this.store.id
                    };

                    await axios.put(`/api/orders/${this.order.id}`, data);

                    if(this.orderForm.assigned_to_user_id) {
                        const teamMember = this.teamMembers.find(teamMember => teamMember.id === this.orderForm.assigned_to_user_id);
                        this.notificationState.showSuccessNotification(`Order assigned to ${teamMember.first_name}`);
                    }else{
                        this.notificationState.showSuccessNotification(`No team member unassigned`);
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update order changes:', error);
                }

            }
        }
    };

</script>
