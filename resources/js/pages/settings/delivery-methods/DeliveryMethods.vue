<template>

    <div class="max-w-2xl mx-auto pt-32 pb-40">

        <div
            class="flex justify-end"
            v-if="!isLoadingStore && !isLoadingDeliveryMethods && hasDeliveryMethods">

            <Button
                size="md"
                class="mb-4"
                type="primary"
                :leftIcon="Plus"
                :action="navigateToAddDeliveryMethod">
                <span>Add Delivery Method</span>
            </Button>

        </div>

        <!-- Loading Placeholder -->
        <div v-if="isLoadingStore || isLoadingDeliveryMethods" class="space-y-3 mt-4">

            <div
                :key="index"
                v-for="(_, index) in [1, 2, 3]"
                class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300">

                <div class="flex justify-between items-center">

                    <Skeleton width="w-40" :shine="true"></Skeleton>

                    <div class="flex items-center space-x-4">

                        <Skeleton width="w-16" :shine="true"></Skeleton>

                        <Skeleton width="w-4" height="h-4" rounded="rounded-md" :shine="true"></Skeleton>

                        <Skeleton width="w-4" height="h-4" rounded="rounded-md" :shine="true"></Skeleton>

                    </div>

                </div>

            </div>

        </div>

        <!-- Delivery Methods -->
        <draggable
            class="space-y-3 mt-4"
            handle=".draggable-handle"
            ghost-class="bg-yellow-50"
            v-model="deliveryMethods"
            v-else-if="hasDeliveryMethods"
            @change="changeDeliveryMethodArrangement">

            <div
                :key="deliveryMethod.id"
                v-for="deliveryMethod in deliveryMethods"
                @click.stop="() => navigateToEditDeliveryMethod(deliveryMethod)"
                class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                <div class="flex justify-between items-center">

                    <!-- Name -->
                    <span class="text-sm">{{ deliveryMethod.name }}</span>

                    <div class="flex items-center space-x-4">

                        <!-- Active Status -->
                        <Pill :type="deliveryMethod.active ? 'success' : 'warning'" size="xs">{{ deliveryMethod.active ? 'active' : 'inactive'}}</Pill>

                        <!-- Delete Button -->
                        <Button
                            size="xs"
                            type="bareDanger"
                            :leftIcon="Trash2"
                            :action="() => showDeleteDeliveryMethodModal(deliveryMethod)">
                        </Button>

                        <!-- Drag & Drop Handle -->
                        <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                    </div>

                </div>

            </div>

        </draggable>

        <div
            v-else
            class="flex flex-col items-center justify-center bg-linear-to-br from-indigo-50 to-purple-50 border border-gray-300 shadow-lg rounded-2xl py-16 px-8 space-y-6">

            <div class="relative">
                <div class="bg-linear-to-br from-white-50 to-indigo-50 text-indigo-500 rounded-full p-2">
                    <Truck size="60"></Truck>
                </div>
                <div class="absolute inset-0 bg-indigo-300 opacity-20 rounded-full animate-ping"></div>
            </div>

            <!-- Engaging headline and description -->
            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-800">Set Up Your Delivery Methods!</h3>
                <span class="text-sm text-gray-600 mt-2 block max-w-sm">
                    Add a delivery method to ensure fast and reliable delivery for your customers.
                </span>
            </div>

            <!-- Interactive button with gradient and hover effect -->
            <button
                size="lg"
                type="bare"
                @click.stop="navigateToAddDeliveryMethod"
                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium py-3 px-6 rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 cursor-pointer">
                <span>Add Delivery Method Now</span>
            </button>

        </div>

        <Modal
            approveType="danger"
            :approveLeftIcon="Trash2"
            ref="deleteDeliveryMethodModal"
            approveText="Delete Delivery Method"
            :approveAction="deleteDeliveryMethod"
            :triggerLoading="isDeletingDeliveryMethod"
            :approveLoading="isDeletingDeliveryMethod">

            <template #content
                v-if="deletableDeliveryMethod">
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                <p class="mb-8">Are you sure you want to delete <span class="font-bold text-black">{{ deletableDeliveryMethod.name }}</span>?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import { Plus, Move, Trash2, Truck } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: { Truck, Pill, Modal, Button, Skeleton, Move, draggable: VueDraggableNext },
        data() {
            return {
                Plus,
                Trash2,
                pagination: null,
                deliveryMethods: [],
                deletableDeliveryMethod: null,
                isDeletingDeliveryMethod: false,
                isLoadingDeliveryMethods: false,
                isChangingDeliveryMethodArrangement: false
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            hasDeliveryMethods() {
                return this.deliveryMethods.length > 0;
            }
        },
        methods: {
            setup() {
                if(this.store) {
                    this.showDeliveryMethods();
                }
            },
            showDeleteDeliveryMethodModal(deliveryMethod) {
                this.deletableDeliveryMethod = deliveryMethod;
                this.$refs.deleteDeliveryMethodModal.showModal();
            },
            async navigateToAddDeliveryMethod() {
                await this.$router.push({
                    name: 'add-delivery-method',
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            async navigateToEditDeliveryMethod(deliveryMethod) {
                await this.$router.push({
                    name: 'edit-delivery-method',
                    query: {
                        store_id: this.store.id,
                    },
                    params: {
                        delivery_method_id: deliveryMethod.id
                    }
                });
            },
            async showDeliveryMethods() {
                try {

                    this.isLoadingDeliveryMethods = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id,
                            association: 'team member'
                        }
                    };

                    const response = await axios.get('/api/delivery-methods', config);

                    this.pagination = response.data;
                    this.deliveryMethods = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching delivery methods';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch delivery methods:', error);
                } finally {
                    this.isLoadingDeliveryMethods = false;
                }
            },
            async changeDeliveryMethodArrangement() {

                try {

                    if(this.isChangingDeliveryMethodArrangement) return;

                    const deliveryMethodIds = this.deliveryMethods.map((deliveryMethod) => deliveryMethod.id);

                    if(deliveryMethodIds.length == 0) return;

                    this.isChangingDeliveryMethodArrangement = true;

                    const data = {
                        store_id: this.store.id,
                        delivery_method_ids: deliveryMethodIds
                    };

                    await axios.post(`/api/delivery-methods/arrangement`, data);
                    this.notificationState.showSuccessNotification('Arrangement changed');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating delivery method arrangement';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update delivery method arrangement:', error);
                } finally {
                    this.isChangingDeliveryMethodArrangement = false;
                }

            },
            async deleteDeliveryMethod() {

                try {

                    if(this.isDeletingDeliveryMethod) return;

                    this.isDeletingDeliveryMethod = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/delivery-methods/${this.deletableDeliveryMethod.id}`, config);

                    this.showDeliveryMethods();
                    this.$refs.deleteDeliveryMethodModal.hideModal();
                    this.notificationState.showSuccessNotification('Delivery method deleted');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting delivery method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete delivery method:', error);
                    hideModal();
                } finally {
                    this.isDeletingDeliveryMethod = false;
                }

            },
        },
        created() {
            this.setup();
        }
    };

</script>
