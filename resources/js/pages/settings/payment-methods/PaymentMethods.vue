<template>

    <div class="max-w-2xl mx-auto pt-32 pb-40">

        <div
            class="flex justify-end">

            <Modal
                size="md"
                :onShow="onShow"
                contentClass="px-4"
                :scrollOnContent="false"
                :showApproveButton="false"
                ref="addPaymentMethodModal"
                header="Add Payment Method">

                <template #content v-if="!isLoadingStore && !isLoadingStorePaymentMethods">

                    <div class="flex justify-center space-x-1 py-2">

                        <Pill
                            size="md"
                            :key="index"
                            v-for="(paymentMethodType, index) in paymentMethodTypes"
                            :action="() => selectedPaymentMethodType = paymentMethodType"
                            :type="selectedPaymentMethodType == paymentMethodType ? 'primary' : 'light'">{{ capitalize(paymentMethodType) }}</Pill>

                    </div>

                    <div v-if="hasLoadedInitialPaymentMethods" class="flex justify-center items-center space-x-4 my-2">

                        <Input
                            type="search"
                            class="w-full"
                            :debounced="true"
                            v-model="searchTerm"
                            :skeleton="isLoadingStore"
                            placeholder="Search payment methods"
                            @input="isLoadingPaymentMethods = true">
                        </Input>

                    </div>

                    <Alert
                        class="mb-2"
                        type="primary"
                        :dismissable="false"
                        v-if="selectedPaymentMethodType !== 'all'"
                        :description="selectedPaymentMethodType === 'automated' ? 'Showing payment methods that automatically verify' : 'Showing payment methods that require manual verification'" />

                    <template v-if="isLoadingPaymentMethods">

                        <div class="space-y-2 mb-4">

                            <div
                                :key="index"
                                v-for="(_, index) in [1, 2, 3]"
                                class="flex items-center space-x-2 border-b border-gray-300 shadow-sm rounded-lg p-2 bg-gray-50 w-full">

                                <Skeleton width="w-10" height="h-10" rounded="rounded-full" :shine="true" class="shrink-0"></Skeleton>

                                <Skeleton width="w-1/3" :shine="true"></Skeleton>

                            </div>

                        </div>

                    </template>

                    <div
                        class="space-y-2 mb-4"
                        v-else-if="hasPaymentMethods">

                        <div
                            :key="index"
                            v-for="(paymentMethod, index) in paymentMethods"
                            @click="() => navigateToAddPaymentMethod(paymentMethod)"
                            class="flex items-center space-x-2 cursor-pointer p-3 border border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100">

                            <!-- Logo -->
                            <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center">
                                <img
                                    alt="Payment Method Logo"
                                    class="h-full object-contain"
                                    :src="paymentMethod.image_url"
                                />
                            </div>

                            <div class="w-full flex items-center justify-between">

                                <p class="text-sm font-semibold">{{ paymentMethod.name }}</p>

                                <Pill
                                    tooltipTriggerClass="w-4 h-4 text-gray-400 hover:text-gray-500"
                                    :type="paymentMethod.automated_verification ? 'primary' : 'light'" size="xs">
                                    <span class="mr-1">{{ paymentMethod.automated_verification ? 'automated' : 'manual'}}</span>
                                    <template #tooltipContent>
                                        <p class="w-60 text-xs text-center leading-5 px-2 py-1 whitespace-normal">
                                            {{ paymentMethod.automated_verification ? 'Automatic payment verification; no manual action needed' : 'Requires manual verification after customer payment' }}
                                        </p>
                                    </template>
                                </Pill>

                            </div>

                        </div>

                    </div>

                    <p v-else class="text-sm text-center p-4 rounded-lg bg-gray-50 mb-4">
                        No payment methods found
                    </p>

                </template>

            </Modal>

        </div>

        <!-- Loading Placeholder -->
        <div v-if="isLoadingStore || isLoadingStorePaymentMethods" class="space-y-3 mt-4">

            <div
                :key="index"
                v-for="(_, index) in [1, 2, 3]"
                class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300">

                <div class="flex justify-between items-center">

                    <div class="flex items-center space-x-2 font-bold">

                        <!-- Logo -->
                        <Skeleton width="w-8" height="h-8" rounded="rounded-full" :shine="true"></Skeleton>

                        <!-- Name -->
                        <Skeleton width="w-40" :shine="true"></Skeleton>

                    </div>

                    <div class="flex items-center space-x-4">

                        <Skeleton width="w-16" :shine="true"></Skeleton>

                        <Skeleton width="w-4" height="h-4" rounded="rounded-md" :shine="true"></Skeleton>

                        <Skeleton width="w-4" height="h-4" rounded="rounded-md" :shine="true"></Skeleton>

                    </div>

                </div>

            </div>

        </div>

        <template v-else-if="hasStorePaymentMethods">

            <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

                <h1 class="text-lg font-bold mb-4">Payment</h1>

                <div class="flex items-center justify-between">

                    <Switch
                        size="xs"
                        suffixText="Skip payment page"
                        v-model="storeForm.skip_payment_page"
                        :errorText="formState.getFormError('skip_payment_page')"
                        tooltipContent="Skip the payment page when placing an order"
                        @change="storeState.saveStateDebounced('Skip payment page status changed')"
                    />

                    <Button
                        size="md"
                        type="primary"
                        :leftIcon="Plus"
                        :action="() => $refs.addPaymentMethodModal.showModal()">
                        <span>Add Payment Method</span>
                    </Button>


                </div>

            </div>

            <!-- Payment Methods -->
            <draggable
                class="space-y-3 mt-4"
                handle=".draggable-handle"
                ghost-class="bg-yellow-50"
                v-model="storePaymentMethods"
                @change="changeStorePaymentMethodArrangement">

                <div
                    :key="storePaymentMethod.id"
                    v-for="storePaymentMethod in storePaymentMethods"
                    @click.stop="() => navigateToEditPaymentMethod(storePaymentMethod)"
                    class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                    <div class="flex justify-between items-center">

                        <div class="flex items-center space-x-2 font-bold">

                            <!-- Logo -->
                            <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center">
                                <img
                                    alt="Payment Method Logo"
                                    class="h-full object-contain"
                                    :src="storePaymentMethod.logo ? storePaymentMethod.logo.path : storePaymentMethod.payment_method.image_url"
                                />
                            </div>

                            <!-- Name -->
                            <span class="text-sm">{{ storePaymentMethod.custom_name }}</span>

                        </div>

                        <div class="flex items-center space-x-4">

                            <!-- Requires Verification Status -->
                            <Pill v-if="storePaymentMethod.requires_verification" type="warning" size="xs">Requires verification</Pill>

                            <!-- Active Status -->
                            <Pill v-else :type="storePaymentMethod.active ? 'success' : 'warning'" size="xs">{{ storePaymentMethod.active ? 'active' : 'inactive'}}</Pill>

                            <!-- Delete Button -->
                            <Button
                                size="xs"
                                type="bareDanger"
                                :leftIcon="Trash2"
                                :action="() => showDeleteStorePaymentMethodModal(storePaymentMethod)">
                            </Button>

                            <!-- Drag & Drop Handle -->
                            <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                        </div>

                    </div>

                </div>

            </draggable>

        </template>

        <div
            v-else
            class="flex flex-col items-center justify-center bg-linear-to-br from-indigo-50 to-purple-50 border border-gray-300 shadow-lg rounded-2xl py-16 px-8 space-y-6">

            <div class="relative">
                <div class="bg-linear-to-br from-white-50 to-indigo-50 text-indigo-500 rounded-full p-2">
                    <CircleDollarSign size="60"></CircleDollarSign>
                </div>
                <div class="absolute inset-0 bg-indigo-300 opacity-20 rounded-full animate-ping"></div>
            </div>

            <!-- Engaging headline and description -->
            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-800">Get Started with Payments!</h3>
                <span class="text-sm text-gray-600 mt-2 block max-w-sm">
                    Add a payment method to unlock seamless transactions and grow your business.
                </span>
            </div>

            <!-- Interactive button with gradient and hover effect -->
            <button
                size="lg"
                type="bare"
                @click.stop="() => $refs.addPaymentMethodModal.showModal()"
                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium py-3 px-6 rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 cursor-pointer">
                <span>Add Payment Method Now</span>
            </button>

        </div>

        <Modal
            approveType="danger"
            :approveLeftIcon="Trash2"
            approveText="Delete Payment Method"
            ref="deleteStorePaymentMethodModal"
            :approveAction="deleteStorePaymentMethod"
            :triggerLoading="isDeletingStorePaymentMethod"
            :approveLoading="isDeletingStorePaymentMethod">

            <template #content
                v-if="deletableStorePaymentMethod">
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                <p class="mb-8">Are you sure you want to delete <span class="font-bold text-black">{{ deletableStorePaymentMethod.custom_name }}</span>?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Switch from '@Partials/Switch.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import { isNotEmpty, capitalize } from '@Utils/stringUtils.js';
    import { Plus, Move, Trash2, CircleDollarSign } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'changeHistoryState', 'notificationState'],
        components: { CircleDollarSign, Pill, Alert, Input, Modal, Switch, Button, Skeleton, Move, draggable: VueDraggableNext },
        data() {
            return {
                Plus,
                Trash2,
                pagination: null,
                searchTerm: null,
                paymentMethods: [],
                lastSearchTerm: null,
                storePaymentMethods: [],
                isLoadingPaymentMethods: false,
                selectedPaymentMethodType: 'all',
                deletableStorePaymentMethod: null,
                isDeletingStorePaymentMethod: false,
                isLoadingStorePaymentMethods: false,
                hasLoadedInitialPaymentMethods: false,
                isChangingStorePaymentMethodArrangement: false,
                paymentMethodTypes: ['all', 'automated', 'manual']
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            },
            selectedPaymentMethodType() {
                this.showPaymentMethods();
            },
            searchTerm() {
                this.showPaymentMethods();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            storeForm() {
                return this.storeState.storeForm;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            hasPaymentMethods() {
                return this.paymentMethods.length > 0;
            },
            hasStorePaymentMethods() {
                return this.storePaymentMethods.length > 0;
            },
            hasSearchTerm() {
                return this.isNotEmpty(this.searchTerm);
            },
        },
        methods: {
            isNotEmpty,
            capitalize: capitalize,
            setup() {
                if(this.store) {
                    this.showStorePaymentMethods();
                    this.storeState.setStoreForm(this.store, true);
                }else{
                    this.storeState.setStoreForm(null, false);
                }
            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    'Save Changes',
                    this.updateStore,
                    'primary',
                    null,
                );
            },
            onShow() {
                this.hasLoadedInitialPaymentMethods = false;
                this.lastSearchTerm = null;
                this.paymentMethods = [];
                this.searchTerm = null;
                this.showPaymentMethods();
            },
            showDeleteStorePaymentMethodModal(storePaymentMethod) {
                this.deletableStorePaymentMethod = storePaymentMethod;
                this.$refs.deleteStorePaymentMethodModal.showModal();
            },
            async navigateToAddPaymentMethod(paymentMethod) {
                await this.$router.push({
                    name: 'add-payment-method',
                    query: {
                        store_id: this.store.id,
                        payment_method_id: paymentMethod.id
                    }
                });
            },
            async navigateToEditPaymentMethod(storePaymentMethod) {
                await this.$router.push({
                    name: 'edit-payment-method',
                    query: {
                        store_id: this.store.id,
                    },
                    params: {
                        store_payment_method_id: storePaymentMethod.id
                    }
                });
            },
            async showPaymentMethods() {
                try {

                    this.isLoadingPaymentMethods = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id,
                            association: 'unassociated'
                        }
                    };

                    if(this.selectedPaymentMethodType == 'automated') {
                        config.params['automated_verification'] = '1';
                    }else if(this.selectedPaymentMethodType == 'manual') {
                        config.params['automated_verification'] = '0';
                    }

                    if (this.hasSearchTerm) {
                        config.params['search'] = this.searchTerm;
                    }

                    this.lastSearchTerm = this.searchTerm;

                    const response = await axios.get('/api/payment-methods', config);

                    if (this.searchTerm === this.lastSearchTerm) {
                        const pagination = response.data;
                        this.paymentMethods = pagination.data;
                        this.isLoadingPaymentMethods = false;
                        this.hasLoadedInitialPaymentMethods = true;
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching payment methods';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch payment methods:', error);
                } finally {
                    this.isLoadingPaymentMethods = false;
                }
            },
            async showStorePaymentMethods() {
                try {

                    this.isLoadingStorePaymentMethods = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id,
                            association: 'team member',
                            _relationships: ['logo', 'paymentMethod'].join(',')
                        }
                    };

                    const response = await axios.get('/api/store-payment-methods', config);

                    this.pagination = response.data;
                    this.storePaymentMethods = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching store payment methods';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch store payment methods:', error);
                } finally {
                    this.isLoadingStorePaymentMethods = false;
                }
            },
            async changeStorePaymentMethodArrangement() {

                try {

                    if(this.isChangingStorePaymentMethodArrangement) return;

                    const storePaymentMethodIds = this.storePaymentMethods.map((storePaymentMethod) => storePaymentMethod.id);

                    if(storePaymentMethodIds.length == 0) return;

                    this.isChangingStorePaymentMethodArrangement = true;

                    const data = {
                        store_id: this.store.id,
                        store_payment_method_ids: storePaymentMethodIds
                    };

                    await axios.post(`/api/store-payment-methods/arrangement`, data);
                    this.notificationState.showSuccessNotification('Arrangement changed');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating payment method arrangement';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update payment method arrangement:', error);
                } finally {
                    this.isChangingStorePaymentMethodArrangement = false;
                }

            },
            async deleteStorePaymentMethod() {

                try {

                    if(this.isDeletingStorePaymentMethod) return;

                    this.isDeletingStorePaymentMethod = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/store-payment-methods/${this.deletableStorePaymentMethod.id}`, config);

                    this.showStorePaymentMethods();
                    this.$refs.deleteStorePaymentMethodModal.hideModal();
                    this.notificationState.showSuccessNotification('Payment method deleted');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting payment method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete payment method:', error);
                    hideModal();
                } finally {
                    this.isDeletingStorePaymentMethod = false;
                }

            },
            async updateStore() {

                try {

                    if(this.storeState.isUpdatingStore) return;

                    this.storeState.isUpdatingStore = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.storeForm,
                        store_id: this.store.id
                    }

                    await axios.put(`/api/stores/${this.store.id}`, data);

                    this.notificationState.showSuccessNotification(`Store updated`);
                    this.storeState.saveOriginalState('Original store');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update store:', error);
                } finally {
                    this.storeState.isUpdatingStore = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            setStoreForm(storeForm) {
                this.storeState.storeForm = storeForm;
            },
        },
        created() {

            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setStoreForm;
            }

        }
    };

</script>
