<template>

    <div :style="{
            backgroundColor: designCard.metadata.design.bg_color,

            marginTop: `${designCard.metadata.design.t_margin ?? 0}px`,
            marginLeft: `${designCard.metadata.design.l_margin ?? 0}px`,
            marginRight: `${designCard.metadata.design.r_margin ?? 0}px`,
            marginBottom: `${designCard.metadata.design.b_margin ?? 0}px`,

            paddingTop: `${designCard.metadata.design.t_padding ?? 0}px`,
            paddingLeft: `${designCard.metadata.design.l_padding ?? 0}px`,
            paddingRight: `${designCard.metadata.design.r_padding ?? 0}px`,
            paddingBottom: `${designCard.metadata.design.b_padding ?? 0}px`,

            borderTopLeftRadius: `${designCard.metadata.design.tl_border_radius ?? 0}px`,
            borderTopRightRadius: `${designCard.metadata.design.tr_border_radius ?? 0}px`,
            borderBottomLeftRadius: `${designCard.metadata.design.bl_border_radius ?? 0}px`,
            borderBottomRightRadius: `${designCard.metadata.design.br_border_radius ?? 0}px`,

            borderTop: `${designCard.metadata.design.t_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderLeft: `${designCard.metadata.design.l_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderRight: `${designCard.metadata.design.r_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderBottom: `${designCard.metadata.design.b_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
         }"
        v-if="designCard.metadata.category_id && categoryData[designCard.metadata.category_id]?.category">

        <div class="space-y-4">

            <!-- Category Name and Description -->
            <div>
                <h3
                    class="text-lg font-semibold mb-0"
                    :style="{ color: designCard.metadata.design.title_color }">
                    {{ categoryData[designCard.metadata.category_id].category.name }}
                </h3>
                <p
                    :style="{ color: designCard.metadata.design.description_color }"
                    v-if="categoryData[designCard.metadata.category_id].category.description">
                    {{ categoryData[designCard.metadata.category_id].category.description }}
                </p>
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
                    :style="{ color: designCard.metadata.design.title_color }"
                    class="relative flex flex-col space-y-2 p-2 cursor-pointer rounded-lg hover:scale-105 transition-all duration-300"
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


                    <h4
                        class="text-sm font-medium truncate"
                        :style="{ color: designCard.metadata.design.product_name_color }">
                        {{ product.name }}
                    </h4>

                    <div class="flex items-center space-x-2 flex-wrap gap-2">

                        <Pill v-if="(product.variant ?? product).is_free" type="success" size="xs">free</Pill>

                        <template v-else>

                            <span
                                class="text-sm font-semibold"
                                v-if="(product.variant ?? product).on_sale"
                                :style="{ color: designCard.metadata.design.product_price_color }">
                                {{ (product.variant ?? product).unit_sale_price.amount_with_currency }}
                            </span>

                            <span
                                :style="{ color: (product.variant ?? product).on_sale ? designCard.metadata.design.product_cancelled_price_color : designCard.metadata.design.product_price_color }"
                                :class="['text-sm', { 'line-through': (product.variant ?? product).on_sale, 'font-semibold': !(product.variant ?? product).on_sale }]">
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
            },
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment'].includes(this.$route.name);
            }
        },
        methods: {
            async onView(product) {
                if(this.isDesigning) {
                    this.notificationState.showSuccessNotification(`Only opens on the actual store`);
                    return;
                }

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
