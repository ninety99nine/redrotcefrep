<template>

    <div class="bg-white rounded-lg p-4 shadow-sm">

        <div class="flex items-center space-x-2 mb-4">

            <p class="text-lg text-gray-700 font-semibold">Products</p>

            <Pill v-if="pagination" type="light" size="xs">{{ `${pagination.meta.total} total` }}</Pill>

        </div>

        <Search
            class="mb-4"
            @selected="addProduct"
            :options="productOptions"
            :isLoading="isLoadingProducts"
            placeholder="Search products to add"
            :search="(searchTerm) => showProducts(searchTerm)">

            <template #option="props">

                <div class="w-full flex justify-between items-center">
                    <div class="flex space-x-4 items-center">
                        <div
                            v-if="props.option.product.photo"
                            class="flex items-center justify-center w-10 h-10">

                            <img class="w-full max-h-full object-contain rounded-lg shrink-0" :src="props.option.product.photo.path">

                        </div>
                        <span class="truncate">{{ props.option.label }}</span>
                    </div>

                    <Pill :type="props.option.product.visible ? 'success' : 'warning'" size="xs">{{ props.option.product.visible ? 'visible' : 'hidden' }}</Pill>
                </div>

            </template>

        </Search>

        <template v-if="preparedProducts.length || productsToAdd.length || productsToRemove.length">

            <div class="relative">

                <BackdropLoader v-if="isLoadingTagProducts" :showBorder="false" :showSpinningLoader="true"></BackdropLoader>

                <div class="divide-y divide-gray-200">

                    <div
                        :key="index"
                        v-for="(product, index) in preparedProducts"
                        class="flex items-center justify-between hover:bg-gray-100 min-h-16 px-4">

                        <div class="flex space-x-4 items-center">
                            <div
                                v-if="product.photo"
                                class="flex items-center justify-center w-10 h-10">

                                <img class="w-full max-h-full object-contain rounded-lg shrink-0" :src="product.photo.path">

                            </div>
                            <span class="text-sm truncate">{{ product.name }}</span>
                            <Pill :type="product.visible ? 'success' : 'warning'" size="xs">{{ product.visible ? 'visible' : 'hidden' }}</Pill>
                        </div>

                        <Button
                            size="xs"
                            type="bareDanger"
                            :leftIcon="Trash2"
                            :action="() => removeProduct(product)">
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

            <p v-if="isCreatingProductTag || (isEditingProductTag && pagination && preparedProducts.length == 0 && productsToAdd.length == 0)" class="text-sm">No products added</p>

            <div v-else class="flex items-center justify-center">
                <Loader>
                    <span class="text-sm ml-2">Loading products</span>
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
                tagProducts: [],
                pagination: null,
                productOptions: [],
                latestRequestId: null,
                cancelTokenSource: null,
                isLoadingProducts: false,
                isLoadingTagProducts: false
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    if(!this.isLoadingProducts && this.productOptions.length == 0) this.showProducts();
                }
            },
            tag(newValue) {
                if(newValue) {
                    if(!this.isLoadingProducts && this.productOptions.length == 0) this.showProducts();
                    this.showTagProducts();
                }
            },
            isUpdatingTag(newValue, oldValue) {
                if(oldValue && !newValue) {
                    this.showTagProducts();
                }
            },
            productsReady(newValue) {
                if (newValue) {
                    this.$emit('tagProductsReady');
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
            isEditingProductTag() {
                return this.$route.name == 'edit-product-tag';
            },
            isCreatingProductTag() {
                return this.$route.name == 'create-product-tag';
            },
            tagForm() {
                return this.tagState.tagForm;
            },
            totalTagProducts() {
                return this.tagProducts.length;
            },
            hasTagProducts() {
                return this.totalTagProducts > 0;
            },
            productsReady() {
                if(this.isCreatingProductTag) {
                    return !this.isLoadingProducts;
                }else {
                    return !this.isLoadingProducts && !this.isLoadingTagProducts;
                }
            },
            productsToAdd() {
                return this.tagState.tagForm.products_to_add;
            },
            productsToRemove() {
                return this.tagState.tagForm.products_to_remove;
            },
            preparedProducts() {
                if (!this.productsReady) return [];

                // Create Sets for efficient lookup
                const productsToAddIds = new Set(this.productsToAdd.map(p => String(p.id)));
                const productsToRemoveIds = new Set(this.productsToRemove.map(p => String(p.id)));

                // Combine products based on page
                const products = this.page == 1
                    ? [...this.productsToAdd, ...this.tagProducts]
                    : this.tagProducts.filter(
                        product => !productsToAddIds.has(String(product.id)) && !productsToRemoveIds.has(String(product.id))
                    );

                // Deduplicate products by id and filter out products to remove
                const seenIds = new Set();
                return products.filter(product => {
                    if (productsToRemoveIds.has(String(product.id))) {
                        return false; // Ignore removed products
                    }
                    if (seenIds.has(String(product.id))) {
                        return false; // Skip duplicate products
                    }
                    seenIds.add(String(product.id));
                    return true;
                });
            }
        },
        methods: {
            paginate(page) {
                this.showTagProducts(page);
            },
            async showProducts(searchTerm = null) {

                const currentRequestId = ++this.latestRequestId;

                try {

                    this.isLoadingProducts = true;

                    if (this.cancelTokenSource) {
                        this.cancelTokenSource.cancel('Request superseded by a newer one'); // Cancel previous request if it exists
                    }

                    this.cancelTokenSource = axios.CancelToken.source(); // Create a new cancel token source

                    let config = {
                        params: {
                            store_id: this.store.id,
                            association: 'team member',
                            _relationships: ['photo'].join(',')
                        },
                        cancelToken: this.cancelTokenSource.token // Attach cancel token
                    }

                    if(searchTerm) config.params['search'] = searchTerm;

                    const response = await axios.get(`/api/products`, config);

                    // Only process response if it matches the latest request
                    if (currentRequestId !== this.latestRequestId) return;

                    const pagination = response.data;
                    const products = pagination.data;

                    this.productOptions = products.map(product => {
                        return {
                            label: product.name,
                            product: product
                        }
                    });

                } catch (error) {

                    if (axios.isCancel(error)) return; // Ignore canceled requests

                    if (currentRequestId !== this.latestRequestId) return;

                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch products:', error);
                } finally {
                    if (currentRequestId !== this.latestRequestId) return;
                    this.isLoadingProducts = false;
                    this.cancelTokenSource = null;
                }

            },
            async showTagProducts(page = 1) {
                try {

                    this.page = page;
                    this.isLoadingTagProducts = true;

                    let config = {
                        params: {
                            per_page: 15,
                            page: this.page,
                            tag_id: this.tag.id,
                            store_id: this.store.id,
                            _relationships: ['photo'].join(',')
                        }
                    };

                    const response = await axios.get('/api/products', config);

                    this.pagination = response.data;
                    this.tagProducts = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch products:', error);
                } finally {
                    this.isLoadingTagProducts = false;
                }
            },
            addProduct(option) {
                if(this.page > 1) this.showTagProducts();
                this.tagState.addProduct(option.product);
            },
            removeProduct(product) {
                this.tagState.removeProduct(product);
            }
        },
        created() {
            if(this.store) this.showProducts();
        }
    };

</script>
