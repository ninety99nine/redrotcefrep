<template>

    <div class="max-w-4xl mx-auto pb-40">
        <div
            v-if="isLoadingStore"
            class="pt-8 flex items-center justify-center">
            <Loader>
                <span class="text-sm ml-2">Loading store</span>
            </Loader>
        </div>

        <template v-else>
            <div class="select-none">

                <!-- Breadcrumb Trail -->
                <div v-if="isLoadingCategory" class="flex items-center space-x-2 mt-4">
                    <Skeleton width="w-20" :shine="true" class="my-2"></Skeleton>
                    <ChevronRight size="16" class="text-gray-300 text-sm"></ChevronRight>
                    <Skeleton width="w-20" :shine="true" class="my-2"></Skeleton>
                </div>
                <div v-else-if="breadcrumbTrail.length" class="flex items-center space-x-2 mt-4">
                    <template v-for="(cat, index) in breadcrumbTrail" :key="cat.id">
                        <h1
                            v-if="index == breadcrumbTrail.length - 1"
                            class="text-lg font-semibold">{{ category?.name }}</h1>
                        <div
                            v-else
                            @click.stop="navigateToShowShopCategory(cat)"
                            class="cursor-pointer group bg-gray-100 border border-gray-200 hover:bg-gray-200 py-1 px-4 rounded-lg">
                            <span class="text-gray-900 text-sm">{{ cat.name }}</span>
                        </div>
                        <ChevronRight
                            size="16"
                            :key="'separator-' + index"
                            class="text-gray-500 text-sm"
                            v-if="index < breadcrumbTrail.length - 1">
                        </ChevronRight>
                    </template>
                </div>

                <Skeleton v-if="isLoadingCategory" width="w-60" :shine="true" class="mt-2"></Skeleton>
                <p v-else-if="category?.description" class="text-sm mt-4">{{ category.description }}</p>

                <!-- Subcategories Section -->
                <div v-if="isLoadingCategory" class="flex flex-wrap space-x-2 mt-4">
                    <Skeleton v-for="i in 3" :key="i" width="w-20" height="h-8" rounded="rounded-md" :shine="true" class="my-2"></Skeleton>
                </div>
                <div v-else-if="category?.sub_categories?.length" class="flex flex-wrap space-x-2 mt-4">
                    <div
                        :key="subCategory.id"
                        v-for="subCategory in category.sub_categories"
                        @click.stop="navigateToShowShopCategory(subCategory)"
                        class="cursor-pointer group bg-gray-100 border border-gray-200 hover:bg-gray-200 py-1 px-4 rounded-lg">
                        <span class="text-gray-900 text-sm">{{ subCategory.name }}</span>
                    </div>
                </div>

                <div v-if="hasLoadedInitialProducts" class="flex justify-center items-center space-x-4 mt-4 mb-2">
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

            <MyCartButton v-if="shoppingCart"></MyCartButton>
        </template>
    </div>

</template>

<script>
    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isNotEmpty } from '@Utils/stringUtils.js';
    import { Image, ChevronRight } from 'lucide-vue-next';
    import MyCartButton from '@Pages/shop/_components/design-card-manager/_components/my-cart/MyCartButton.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: {
            Pill, Image, Input, Loader, Button, Skeleton, ChevronRight, MyCartButton
        },
        data() {
            return {
                products: [],
                category: null,
                searchTerm: null,
                lastSearchTerm: null,
                isLoadingProducts: false,
                isLoadingCategory: false,
                hasLoadedInitialProducts: false
            }
        },
        watch: {
            store(newValue, oldValue) {
                if (!oldValue && newValue) {
                    this.setup();
                }
            },
            categoryId() {
                this.showCategory();
                this.showProducts();
            },
            searchTerm() {
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
            categoryId() {
                return this.$route.params.category_id;
            },
            breadcrumbTrail() {
                const trail = [];
                let current = this.category;
                while (current && current.parent_category) {
                    trail.unshift(current.parent_category);
                    current = current.parent_category;
                }
                if (this.category) {
                    trail.push(this.category);
                }
                return trail;
            }
        },
        methods: {
            isNotEmpty,
            setup() {
                if (this.store) {
                    if (!this.category) this.showCategory();
                    if (!this.hasLoadedInitialProducts) this.showProducts();
                }
            },
            navigateToShowShopCategory(category) {
                this.$router.push({
                    name: 'show-shop-category',
                    params: {
                        alias: this.store.alias,
                        category_id: category.id
                    }
                });
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
            getSelectedQuantity(product) {
                if (this.shoppingCart && product) {
                    const orderProduct = this.shoppingCart.order_products.find(orderProduct => orderProduct.product_id == product.id);
                    if (orderProduct) {
                        return orderProduct.quantity;
                    }
                }
                return null;
            },
            async showCategory() {
                try {
                    this.isLoadingCategory = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['subCategories', 'parentCategory.parentCategory.parentCategory'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/categories/${this.categoryId}`, config);

                    this.category = response.data;

                } catch (error) {
                    if(error.status != 404) {
                        const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching category';
                        this.notificationState.showWarningNotification(message);
                        this.formState.setServerFormErrors(error);
                    }
                    console.error('Failed to fetch category:', error);
                } finally {
                    this.isLoadingCategory = false;
                }
            },
            async showProducts() {
                try {
                    this.isLoadingProducts = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            association: 'team member',
                            category_ids: [this.categoryId],
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
            }
        },
        created() {
            this.setup();
        }
    }
</script>
