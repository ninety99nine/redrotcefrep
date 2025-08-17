<template>

    <div class="bg-white rounded-lg p-4 shadow-sm">

        <div class="flex items-center space-x-2 mb-4">

            <p class="text-lg text-gray-700 font-semibold">Products</p>

            <Pill type="light" size="xs">{{ `${totalCategoryProducts} total` }}</Pill>

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

                            <img class="w-full max-h-full object-contain rounded-lg flex-shrink-0" :src="props.option.product.photo.path">

                        </div>
                        <span class="truncate">{{ props.option.label }}</span>
                    </div>

                    <Pill :type="props.option.product.visible ? 'success' : 'warning'" size="xs">{{ props.option.product.visible ? 'visible' : 'hidden' }}</Pill>
                </div>

            </template>

        </Search>

        <draggable
            handle=".draggable-handle"
            ghost-class="bg-yellow-50"
            v-if="hasCategoryProducts"
            v-model="categoryForm.products"
            @change="categoryState.saveStateDebounced('Product arrangement changed')">

            <div
                :key="index"
                v-for="(categoryProduct, index) in categoryForm.products"
                class="flex items-center justify-between hover:bg-gray-100 rounded-lg py-2 px-4">

                <div class="flex space-x-4 items-center">
                    <div
                        v-if="categoryProduct.photo"
                        class="flex items-center justify-center w-10 h-10">

                        <img class="w-full max-h-full object-contain rounded-lg flex-shrink-0" :src="categoryProduct.photo.path">

                    </div>
                    <span class="truncate">{{ categoryProduct.name }}</span>
                    <Pill :type="categoryProduct.visible ? 'success' : 'warning'" size="xs">{{ categoryProduct.visible ? 'visible' : 'hidden' }}</Pill>
                </div>

                <div class="flex items-center space-x-4">

                    <Button
                        size="xs"
                        type="bareDanger"
                        :leftIcon="Trash2"
                        :action="() => removeProduct(index)">
                    </Button>

                    <!-- Drag & Drop Handle -->
                    <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                </div>

            </div>

        </draggable>

        <div v-else class="flex justify-center bg-gray-50 rounded-lg p-4">

            <p class="text-sm">No products added</p>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Button from '@Partials/Button.vue';
    import Search from '@Partials/Search.vue';
    import { Move, Trash2 } from 'lucide-vue-next';
    import { VueDraggableNext } from 'vue-draggable-next';

    export default {
        inject: ['formState', 'categoryState', 'storeState', 'notificationState'],
        components: { Move, Pill, Button, Search, draggable: VueDraggableNext },
        data() {
            return {
                Trash2,
                productOptions: [],
                latestRequestId: null,
                cancelTokenSource: null,
                isLoadingProducts: false
            }
        },
        watch: {
            store(newValue) {
                if(newValue) {
                    this.showProducts();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            categoryForm() {
                return this.categoryState.categoryForm;
            },
            categoryProducts() {
                return this.categoryForm.products;
            },
            totalCategoryProducts() {
                return this.categoryProducts.length;
            },
            hasCategoryProducts() {
                return this.totalCategoryProducts > 0;
            },
        },
        methods: {
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
                            _relationships: ['variant', 'photo'].join(','),
                            _countable_relationships: ['variants'].join(',')
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
            addProduct(option) {
                this.categoryState.addProduct(option.product);
            },
            removeProduct(index) {
                this.categoryState.removeProduct(index);
            }
        },
        created() {
            if(this.store) this.showProducts();
        }
    };

</script>
