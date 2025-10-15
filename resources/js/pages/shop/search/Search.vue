<template>

    <div>

        <Menu menuClass="max-w-4xl mx-auto"></Menu>

        <div class="max-w-4xl mx-auto pt-8 pb-40">

            <div
                v-if="isLoadingStore"
                class="pt-8 flex items-center justify-center">
                <Loader>
                    <span class="text-sm ml-2">Loading store</span>
                </Loader>
            </div>

            <template v-else>

                <div class="select-none grid grid-cols-1 md:grid-cols-4 md:gap-8">

                    <div class="col-span-1 space-y-4">

                        <div
                            v-if="hasCategories"
                            class="w-full bg-gray-50 p-4 border border-gray-300 rounded-lg relative">

                            <h1 class="text-gray-900 font-medium mb-4">Categories</h1>

                            <label
                                :key="index"
                                :for="category.id"
                                class="cursor-pointer"
                                v-for="(category, index) in categories">

                                <div
                                    class="flex items-center space-x-2 hover:scale-105 transition-all duration-300">

                                    <input
                                        type="checkbox"
                                        :id="category.id"
                                        class="cursor-pointer"
                                        v-model="checkedCategories[index]"
                                        @change="(event) => checkedCategories[index] = event.target.checked">

                                    <div class="space-x-2">
                                        <span class="text-sm text-gray-900">{{ category.name }}</span>
                                        <span class="text-sm text-gray-400">({{ category.products_count }})</span>
                                    </div>

                                </div>

                            </label>

                            <div v-if="categoryIds.length" class="border-t border-gray-300 pt-4 mt-4">
                                <Button
                                    size="xs"
                                    type="light"
                                    buttonClass="w-full"
                                    :action="clearFilters">
                                    Clear filters ({{ categoryIds.length }})
                                </Button>
                            </div>

                        </div>

                    </div>

                    <div class="col-span-3">

                        <div v-if="hasLoadedInitialProducts" class="flex justify-center items-center space-x-4">

                            <Input
                                type="search"
                                :debounced="true"
                                class="w-full mb-4"
                                v-model="searchTerm"
                                :skeleton="isLoadingStore"
                                placeholder="Search products"
                                @input="isLoadingProducts = true">
                            </Input>

                        </div>

                        <template v-if="isLoadingProducts">

                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

                                    <div
                                        :key="product.id"
                                        v-for="product in [...Array(8).keys()].map(i => i + 1)"
                                        class="relative flex flex-col space-y-2 p-2 cursor-pointer rounded-lg hover:scale-105 transition-all duration-300">

                                        <div class="w-full max-h-60 aspect-square relative flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden">

                                            <Image size="40" class="text-gray-300"></Image>

                                        </div>

                                        <Skeleton width="w-4/5" :shine="true"></Skeleton>

                                        <div class="flex items-center space-x-2 flex-wrap gap-2">

                                            <Skeleton width="w-3/5" height="h-2" :shine="true"></Skeleton>

                                        </div>

                                    </div>

                            </div>

                        </template>


                    <div
                        v-else-if="hasProducts"
                        class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

                        <div
                            :key="product.id"
                            v-for="product in products"
                            @click.stop="() => onView(product)"
                            class="relative flex flex-col space-y-2 p-2 cursor-pointer rounded-lg hover:scale-105 transition-all duration-300">

                            <div
                                v-if="getSelectedQuantity(product)"
                                class="absolute top-1 right-1 z-10 flex items-center justify-center min-w-6 w-fit h-6 p-1 bg-red-500 text-xs font-bold text-white rounded-full">
                                <span>{{ getSelectedQuantity(product) }}</span>
                            </div>

                            <div class="w-full max-h-60 aspect-square relative flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden">

                                <img
                                    :alt="(product ?? product.variant).name"
                                    class="absolute w-full h-full object-cover"
                                    :src="(product ?? product.variant).photo?.path"
                                    v-if="(product ?? product.variant).photo?.path">

                                <Image v-else size="40" class="text-gray-300"></Image>

                            </div>

                            <h4 class="text-gray-900 text-sm font-medium truncate">
                                {{ product.name }}
                            </h4>

                            <div class="flex items-center space-x-2 flex-wrap gap-2">

                                <Pill v-if="(product.variant ?? product).is_free" type="success" size="xs">free</Pill>

                                <template v-else>

                                    <span
                                        class="text-gray-900 text-sm font-semibold"
                                        v-if="(product.variant ?? product).on_sale">
                                        {{ (product.variant ?? product).unit_sale_price.amount_with_currency }}
                                    </span>

                                    <span
                                        :class="['text-sm', { 'text-gray-400 line-through': (product.variant ?? product).on_sale, 'text-gray-900 font-semibold': !(product.variant ?? product).on_sale }]">
                                        {{ (product.variant ?? product).unit_regular_price.amount_with_currency }}
                                    </span>

                                </template>

                                <Pill
                                    :type="(product.variant ?? product).has_stock ? 'success' : 'warning'" size="xs"
                                    v-if="(product.variant ?? product).stock_quantity_type == 'limited' && (((product.variant ?? product).has_stock && (product.variant ?? product).stock_quantity <= 5) || !(product.variant ?? product).has_stock)">
                                    {{ (product.variant ?? product).has_stock ? `${(product.variant ?? product).stock_quantity} left` : 'no stock' }}
                                </Pill>

                            </div>

                        </div>

                    </div>

                        <p v-else class="text-sm text-center p-4 rounded-lg bg-gray-50 mb-4">
                            No products found
                        </p>

                    </div>

                </div>

                <MyCartButton v-if="shoppingCart"></MyCartButton>

            </template>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import { Image } from 'lucide-vue-next';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isNotEmpty } from '@Utils/stringUtils.js';
    import Menu from '@Pages/shop/_components/menu/Menu.vue';
    import MyCartButton from '@Pages/shop/_components/design-card-manager/_components/my-cart/MyCartButton.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: {
            Pill, Image, Input, Loader, Button, Skeleton, Menu, MyCartButton
        },
        data() {
            return {
                products: [],
                categories: [],
                searchTerm: null,
                lastSearchTerm: null,
                checkedCategories: [],
                isLoadingProducts: false,
                isLoadingCategories: false,
                hasLoadedInitialProducts: false
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            },
            searchTerm() {
                this.showProducts();
            },
            categoryIds() {
                this.showProducts();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            hasSearchTerm() {
                return this.isNotEmpty(this.searchTerm);
            },
            hasProducts() {
                return this.products.length > 0;
            },
            hasCategories() {
                return this.categories.length > 0;
            },
            categoryIds() {
                return this.checkedCategories
                    .map((isChecked, index) => isChecked ? this.categories[index]?.id : null)
                    .filter(id => id !== null);
            }
        },
        methods: {
            isNotEmpty,
            setup() {
                if(this.store) {
                    if(!this.hasCategories) this.showCategories();
                    if(!this.hasLoadedInitialProducts) this.showProducts();
                }
            },
            async onView(product) {
                await this.$router.push({
                    name: 'show-shop-product',
                    params: {
                        alias: this.store.alias,
                        product_id: product.id
                    }
                });
            },
            clearFilters() {
                this.checkedCategories = this.checkedCategories.map(() => false);
            },
            getSelectedQuantity(product) {
                if(this.shoppingCart && product) {
                    const orderProduct = this.shoppingCart.order_products.find(orderProduct => orderProduct.product_id == product.id);
                    if(orderProduct) {
                        return orderProduct.quantity;
                    }
                }
                return null;
            },
            async showProducts() {
                try {

                    this.isLoadingProducts = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            association: 'team member',
                            category_ids: this.categoryIds,
                            _relationships: 'photo,variants'
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
            async showCategories() {
                try {

                    this.isLoadingCategories = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id,
                            _countable_relationships: ['products'].join(',')
                        }
                    };

                    const response = await axios.get('/api/categories', config);

                    const pagination = response.data;
                    this.categories = pagination.data;

                    this.checkedCategories = Array(this.categories.length).fill(false);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching categories';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch categories:', error);
                } finally {
                    this.isLoadingCategories = false;
                }
            },
        },
        created() {
            this.setup();
        }
    }
</script>
