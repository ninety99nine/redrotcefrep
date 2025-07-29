<template>

    <div class="flex justify-center">

        <Modal
            ref="modal"
            :onShow="onShow"
            triggerSize="sm"
            :leftTriggerIcon="Plus"
            contentClass="px-4"
            triggerType="light"
            header="Add Product"
            :scrollOnContent="false"
            triggerText="Add Product"
            :showApproveButton="false"
            :triggerLoading="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)">

            <template #content>

                <div v-if="hasLoadedInitialProducts && !selectedProduct" class="flex justify-center items-center space-x-4 my-2">

                    <Input
                        type="search"
                        class="w-full"
                        :debounced="true"
                        v-model="searchTerm"
                        placeholder="Search products"
                        @input="isLoadingProducts = true"
                        :skeleton="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)">
                    </Input>

                    <Button
                        size="xs"
                        type="light"
                        :leftIcon="Plus"
                        :action="addNew"
                        buttonClass="my-2">
                        <span>Add New</span>
                    </Button>

                </div>

                <template v-if="isLoadingProducts">

                    <div
                        class="space-y-2 mb-4">

                        <div
                            :key="index"
                            v-for="(_, index) in [1, 2, 3]"
                            class="flex items-center space-x-2 border-b border-gray-300 shadow-sm rounded-lg p-2 bg-gray-50 w-full">

                            <!-- Skeleton Loading -->
                            <Skeleton width="w-10" height="h-10" rounded="rounded-lg" :shine="true" class="flex-shrink-0"></Skeleton>

                            <div class="w-full space-y-2">
                                <Skeleton width="w-2/3" :shine="true"></Skeleton>
                                <Skeleton width="w-1/3" :shine="true"></Skeleton>
                            </div>

                        </div>

                    </div>

                </template>

                <template v-else-if="selectedProduct">

                    <Button
                        size="xs"
                        type="light"
                        buttonClass="mt-4"
                        :leftIcon="ArrowLeft"
                        :action="removeSelectedProduct">
                        <span>Back</span>
                    </Button>

                    <p class="text-lg font-bold border-b border-dashed my-4 pb-4">
                        {{ selectedProduct.name }}
                    </p>

                    <ProductVariationOptions
                        :onSelectProduct=onSelectProduct
                        :selectedProduct="selectedProduct">
                    </ProductVariationOptions>

                </template>

                <ProductOptions
                    :products="products"
                    v-else-if="hasProducts"
                    :onSelectProduct=onSelectProduct>
                </ProductOptions>

                <p v-else class="text-sm text-center p-4 rounded-lg bg-gray-50 mb-4">
                    No products found
                </p>

            </template>

        </Modal>

    </div>

</template>

<script>

    import axios from 'axios';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { Plus, ArrowLeft } from 'lucide-vue-next';
    import ProductOptions from '@Pages/orders/order/editable/components/order-products/add-product/product-options/ProductOptions.vue';
    import ProductVariationOptions from '@Pages/orders/order/editable/components/order-products/add-product/product-variation-options/ProductVariationOptions.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Input, Modal, Button, Skeleton, ProductOptions, ProductVariationOptions },
        data() {
            return {
                Plus,
                ArrowLeft,
                products: [],
                searchTerm: null,
                lastSearchTerm: null,
                selectedProduct: null,
                isLoadingProducts: false,
                hasLoadedInitialProducts: false
            }
        },
        watch: {
            searchTerm() {
                this.showProducts();
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
            hasProducts() {
                return this.products.length > 0;
            },
        },
        methods: {
            onShow() {
                this.hasLoadedInitialProducts = false;
                this.selectedProduct = null;
                this.lastSearchTerm = null;
                this.searchTerm = null;
                this.products = [];
                this.showProducts();
            },
            async showProducts() {
                try {

                    this.isLoadingProducts = true;

                    let config = {
                        params: {
                            _relationships: 'photo',
                            store_id: this.store.id,
                            association: 'team member'
                        }
                    };

                    if (this.hasSearchTerm) {
                        config.params['search'] = this.searchTerm;
                    }

                    this.lastSearchTerm = this.searchTerm;

                    const response = await axios.get(`/api/products`, config);

                    if (this.searchTerm === this.lastSearchTerm) {

                        const pagination = response.data;
                        this.products = pagination.data;

                        this.isLoadingProducts = false;
                        this.hasLoadedInitialProducts = true;

                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch products:', error);
                } finally {
                    this.isLoadingProducts = false;
                }
            },
            onSelectProduct(product) {

                const allowVariations = product.allow_variations.status;

                if(allowVariations) {
                    this.selectedProduct = product;
                }else{
                    this.$refs.modal.hideModal();
                    this.orderState.addCartProductUsingProduct(product);
                }

            },
            removeSelectedProduct() {
                this.selectedProduct = null;
            },
            addNew() {
                this.$refs.modal.hideModal();
                this.orderState.addCartProduct();
            },
        }
    };

</script>
