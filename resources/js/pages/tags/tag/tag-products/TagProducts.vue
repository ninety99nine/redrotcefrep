<template>

    <div class="bg-white rounded-lg p-4 shadow-sm">

        <div class="flex items-center space-x-2 mb-4">

            <p class="text-lg text-gray-700 font-semibold">Products</p>

            <Pill type="light" size="xs">{{ `${totalTagProducts} total` }}</Pill>

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

        <div v-if="hasTagProducts">

            <div
                :key="index"
                v-for="(tagProduct, index) in tagProducts"
                class="flex items-center justify-between hover:bg-gray-100 rounded-lg py-2 px-4">

                <div class="flex space-x-4 items-center">
                    <div
                        v-if="tagProduct.photo"
                        class="flex items-center justify-center w-10 h-10">

                        <img class="w-full max-h-full object-contain rounded-lg flex-shrink-0" :src="tagProduct.photo.path">

                    </div>
                    <span class="truncate">{{ tagProduct.name }}</span>
                    <Pill :type="tagProduct.visible ? 'success' : 'warning'" size="xs">{{ tagProduct.visible ? 'visible' : 'hidden' }}</Pill>
                </div>

                <Button
                    size="xs"
                    type="bareDanger"
                    :leftIcon="Trash2"
                    :action="() => removeProduct(index)">
                </Button>

            </div>

        </div>

        <div v-else class="flex justify-center bg-gray-50 rounded-lg p-4">

            <p class="text-sm">No products added</p>

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
            tagForm() {
                return this.tagState.tagForm;
            },
            tagProducts() {
                return this.tagForm.products;
            },
            totalTagProducts() {
                return this.tagProducts.length;
            },
            hasTagProducts() {
                return this.totalTagProducts > 0;
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
                this.tagState.addProduct(option.product);
            },
            removeProduct(index) {
                this.tagState.removeProduct(index);
            }
        },
        created() {
            if(this.store) this.showProducts();
        }
    };

</script>
