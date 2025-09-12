<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.metadata.type == 'products' && designCard.metadata.category_id && categoryData[designCard.metadata.category_id]?.category">

        <div class="space-y-4">

            <!-- Category Name and Description -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-0">{{ categoryData[designCard.metadata.category_id].category.name }}</h3>
                <p v-if="categoryData[designCard.metadata.category_id].category.description" class="text-gray-600">{{ categoryData[designCard.metadata.category_id].category.description }}</p>
            </div>

            <!-- Loader -->
            <div
                class="flex justify-center items-center"
                v-if="categoryData[designCard.metadata.category_id].loading">
                <Loader></Loader>
            </div>

            <!-- Products -->
            <div
                v-else-if="categoryData[designCard.metadata.category_id].category.products?.length"
                :class="[designCard.metadata.layout === 'list' ? 'flex flex-col space-y-4' : 'grid grid-cols-2 gap-4']">

                <div
                    :key="product.id"
                    @click.stop="() => onView(product)"
                    class="relative flex flex-col space-y-2 p-2 hover:bg-gray-100 cursor-pointer rounded-lg hover:scale-105 transition-all duration-300"
                    v-for="product in categoryData[designCard.metadata.category_id].category.products.slice(0, parseInt(designCard.metadata.feature))">

                    <div
                        v-if="getSelectedQuantity(product)"
                        class="absolute top-1 right-1 z-10 flex items-center justify-center min-w-6 w-fit h-6 p-1 bg-red-500 text-xs font-bold text-white rounded-full">
                        <span>{{ getSelectedQuantity(product) }}</span>
                    </div>

                    <div :class="[
                        'w-full aspect-square relative flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden',
                        designCard.metadata.layout === 'list' ? 'max-h-60' : 'max-h-60'
                    ]">

                        <img
                            :alt="(product ?? product.variant).name"
                            class="absolute w-full h-full object-cover"
                            :src="(product ?? product.variant).photo?.path"
                            v-if="(product ?? product.variant).photo?.path">

                        <Image v-else size="40" class="text-gray-300"></Image>

                    </div>


                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</h4>

                    <div class="flex items-center space-x-2 flex-wrap gap-2">

                        <Pill v-if="(product.variant ?? product).is_free" type="success" size="xs">free</Pill>

                        <template v-else>

                            <span v-if="(product.variant ?? product).on_sale" class="text-sm font-semibold text-gray-900">
                                {{ (product.variant ?? product).unit_sale_price.amount_with_currency }}
                            </span>

                            <span :class="['text-sm', { 'line-through text-gray-400': (product.variant ?? product).on_sale, 'text-gray-900 font-semibold': !(product.variant ?? product).on_sale }]">
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

            <p v-else class="text-gray-500 text-sm">No products available in this category.</p>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import { Image } from 'lucide-vue-next';
    import Loader from '@Partials/Loader.vue';

    export default {
        inject: ['formState', 'designState', 'orderState', 'storeState', 'notificationState'],
        components: { Image, Pill, Loader },
        props: {
            designCard: {
                type: Object
            }
        },
        watch: {
            categoryId() {
                this.showCategory();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            categoryData() {
                return this.designState.categoryData;
            },
            categoryId() {
                return this.designCard.metadata.category_id;
            }
        },
        methods: {
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
                if(this.shoppingCart && product) {
                    const orderProduct = this.shoppingCart.order_products.find(orderProduct => orderProduct.product_id == product.id);
                    if(orderProduct) {
                        return orderProduct.quantity;
                    }
                }
                return null;
            },
            async showCategory() {
                try {

                    // Skip if have already loaded, are loading or categoryId is invalid
                    if (!this.categoryId || this.categoryData[this.categoryId]?.category || this.categoryData[this.categoryId]?.loading) return;

                    // Initialize categoryData for this categoryId
                    this.categoryData[this.categoryId] = { loading: true, category: null };

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['photo', 'products.photo'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/categories/${this.categoryId}`, config);
                    this.categoryData[this.categoryId] = { loading: false, category: response.data };

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching category';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    this.categoryData[this.categoryId] = { loading: false, category: null };
                }
            },
        },
        created() {
            this.showCategory();
        }
    }
</script>
