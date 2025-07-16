<template>

    <div class="flex justify-center">

        <Modal
            ref="modal"
            :onShow="onShow"
            triggerSize="sm"
            :triggerIcon="Plus"
            contentClass="px-4"
            triggerType="light"
            header="Add Promotion"
            :scrollOnContent="false"
            :showApproveButton="false"
            triggerText="Add Promotion"
            :triggerLoading="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)">

            <template #content>

                <div v-if="hasLoadedInitialPromotions" class="flex justify-center items-center space-x-4">

                    <Input
                        type="search"
                        class="w-full"
                        :debounced="true"
                        v-model="searchTerm"
                        placeholder="Search promotions"
                        @input="isLoadingPromotions = true"
                        :skeleton="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)">
                    </Input>

                    <Button
                        size="xs"
                        type="light"
                        :action="addNew"
                        :leftIcon="Plus"
                        buttonClass="my-2">
                        <span>Add New</span>
                    </Button>

                </div>

                <template v-if="isLoadingPromotions">

                    <div
                        class="space-y-2 mb-4">

                        <div
                            :key="index"
                            v-for="(_, index) in [1, 2, 3]"
                            class="flex items-center space-x-2 border-b shadow-sm rounded-lg p-2 bg-gray-50 w-full">

                            <!-- Skeleton Loading -->
                            <Skeleton width="w-10" height="h-10" rounded="rounded-lg" :shine="true" class="flex-shrink-0"></Skeleton>

                            <div class="w-full space-y-2">
                                <Skeleton width="w-2/3" :shine="true"></Skeleton>
                                <Skeleton width="w-1/3" :shine="true"></Skeleton>
                            </div>

                        </div>

                    </div>

                </template>

                <PromotionOptions
                    v-if="hasPromotions"
                    :promotions="promotions"
                    :onSelectPromotion=onSelectPromotion>
                </PromotionOptions>

                <p v-else class="text-sm text-center p-4 rounded-lg bg-gray-50 mb-4">
                    No promotions found
                </p>

            </template>

        </Modal>

    </div>

</template>

<script>

    import axios from 'axios';
    import { Plus } from 'lucide-vue-next';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import PromotionOptions from '@Pages/orders/order/editable/components/promotions/add-promotion/promotion-options/PromotionOptions.vue';

    export default {
        inject: ['formState', 'orderState', 'shoppingCartState', 'storeState', 'notificationState'],
        components: { Input, Modal, Button, Skeleton, PromotionOptions },
        data() {
            return {
                Plus,
                promotions: [],
                searchTerm: null,
                lastSearchTerm: null,
                isLoadingPromotions: false,
                hasLoadedInitialPromotions: false
            }
        },
        watch: {
            searchTerm() {
                this.showPromotions();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isEditting() {
                return this.$route.name === 'edit-order';
            },
            hasSearchTerm() {
                return this.searchTerm != null && this.searchTerm.trim() != '';
            },
            hasPromotions() {
                return this.promotions.length > 0;
            },
        },
        methods: {
            onShow() {
                this.hasLoadedInitialPromotions = false;
                this.lastSearchTerm = null;
                this.searchTerm = null;
                this.promotions = [];
                this.showPromotions();
            },
            async showPromotions() {
                try {

                    this.isLoadingPromotions = true;

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    if (this.hasSearchTerm) {
                        config.params['search'] = this.searchTerm;
                    }

                    this.lastSearchTerm = this.searchTerm;

                    const response = await axios.get(`/api/promotions`, config);

                    if (this.searchTerm === this.lastSearchTerm) {

                        const pagination = response.data;
                        this.promotions = pagination.data;

                        this.isLoadingPromotions = false;
                        this.hasLoadedInitialPromotions = true;

                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching promotions';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch promotions:', error);
                } finally {
                    this.isLoadingPromotions = false;
                }
            },
            onSelectPromotion(promotion) {
                this.$refs.modal.hideModal();
                this.shoppingCartState.addCartPromotionUsingPromotion(promotion);
            },
            addNew() {
                this.$refs.modal.hideModal();
                this.shoppingCartState.addCartPromotion();
            },
        }
    };

</script>
