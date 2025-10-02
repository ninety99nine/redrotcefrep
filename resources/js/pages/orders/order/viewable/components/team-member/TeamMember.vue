<template>

    <div class="bg-white rounded-lg p-4 mb-4">

        <!-- Order Team Member (Loading Placeholder) -->
        <Skeleton
            v-if="isLoadingStore || isLoadingOrder || !hasOrder"
            width="w-full" height="h-8" rounded="rounded-md" :shine="true">
        </Skeleton>

        <div v-else class="space-y-4">

            <!-- Team Members Select -->
            <Select
                :search="true"
                label="Assigned To"
                :options="teamMemberOptions"
                v-model="orderForm.assigned_to_user_id"
                :skeleton="isLoadingOrder || !hasOrder || isLoadingTeamMembers"
                @change="(assignedToUserId) => updateOrder({ assigned_to_user_id: assignedToUserId }, 'team member')">
            </Select>

            <!-- Internal Note Textarea -->
            <Input
                rows="2"
                type="textarea"
                label="Internal Note"
                v-model="orderForm.internal_note"
                description="Visible to team members"
                :errorText="formState.getFormError('internal_note')"
                @blur="(internalNote) => updateOrder({ internal_note: internalNote }, 'internal note')"
                tooltipContent="Internal information about this order only visible to you and other team members">
            </Input>

        </div>

    </div>

</template>

<script>

    import axios from 'axios';
    import Input from '@Partials/Input.vue';
    import Select from '@Partials/Select.vue';
    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Input, Select, Skeleton },
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
            async updateOrder(data, type) {

                try {

                    data = {
                        ...data,
                        store_id: this.store.id
                    };

                    await axios.put(`/api/orders/${this.order.id}`, data);

                    if(type == 'team member') {
                        if(this.orderForm.assigned_to_user_id) {
                            const teamMember = this.teamMembers.find(teamMember => teamMember.id === this.orderForm.assigned_to_user_id);
                            this.notificationState.showSuccessNotification(`Order assigned to ${teamMember.first_name}`);
                        }else{
                            this.notificationState.showSuccessNotification(`No team member unassigned`);
                        }
                    }else if(type == 'internal note') {
                        if(this.orderForm.internal_note) {
                            this.notificationState.showSuccessNotification(`Internal note updated`);
                        }else{
                            this.notificationState.showSuccessNotification(`Internal note removed`);
                        }
                        this.orderState.order.internal_note = this.orderForm.internal_note;
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
