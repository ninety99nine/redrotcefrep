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
        v-if="designCard.metadata.category_id && (category || isLoadingCategory)">

        <div class="space-y-4 group">

            <!-- Loader -->
            <div
                v-if="isLoadingCategory"
                class="flex justify-center items-center">
                <Loader></Loader>
            </div>

            <template v-else>

                <!-- Category Name and Description -->
                <div
                    @click.stop="navigateToShowShopCategory"
                    class="hover:bg-black/2.5 hover:rounded-lg hover:p-2 active:hover:bg-black/5 cursor-pointer transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-lg font-semibold mb-0"
                            :style="{ color: designCard.metadata.design.title_color }">
                            {{ category.name }}
                        </h3>
                        <ChevronRight size="16" class="opacity-0 group-hover:opacity-100 transition-all duration-300"></ChevronRight>
                    </div>
                    <p
                        :style="{ color: designCard.metadata.design.description_color }"
                        v-if="category.description">
                        {{ category.description }}
                    </p>
                </div>

                <!-- Products -->
                <div
                    v-if="filteredProducts.length"
                    :class="[
                        { 'flex flex-col divide-y divide-black/5' : designCard.metadata.layout === 'list'},
                        { 'grid grid-cols-2 gap-4' : designCard.metadata.layout === 'grid'}
                    ]">

                    <div
                        :key="product.id"
                        @click.stop="() => onView(product)"
                        v-for="(product, index) in filteredProducts"
                        :style="{ color: designCard.metadata.design.title_color }"
                        :class="[
                            { 'rounded-lg' : designCard.metadata.layout === 'grid'},
                            { 'pb-4 mb-4' : designCard.metadata.layout === 'list' && index != filteredProducts.length - 1},
                            'relative flex flex-col cursor-pointer hover:scale-[102%] transition-all duration-300'
                        ]">

                        <div
                            v-if="getSelectedQuantity(product)"
                            class="absolute top-1 right-1 z-10 flex items-center justify-center min-w-6 w-fit h-6 p-1 bg-red-500 text-xs font-bold text-white rounded-full">
                            <span>{{ getSelectedQuantity(product) }}</span>
                        </div>

                        <div
                            v-if="designCard.metadata.layout === 'grid' || (designCard.metadata.layout === 'list' && (product ?? product.variant).photo)"
                            :class="[
                                'w-full aspect-square relative flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden mb-4',
                                designCard.metadata.layout === 'list' ? 'max-h-60' : 'max-h-60'
                            ]">

                            <img
                                :alt="(product ?? product.variant).name"
                                class="absolute w-full h-full object-cover"
                                :src="(product ?? product.variant).photo?.path"
                                v-if="(product ?? product.variant).photo?.path">

                            <Image v-else size="40" class="text-gray-300"></Image>

                            <div
                                v-if="product.stock_quantity_type == 'sold out'"
                                class="absolute top-5 -left-11 -rotate-45">
                                <div class="w-40 select-none whitespace-nowrap px-2.5 py-2 text-xs text-center font-bold text-white bg-red-600 shadow-sm">
                                    SOLD OUT
                                </div>
                            </div>

                        </div>

                        <h4
                            class="font-medium truncate mb-2"
                            :style="{ color: designCard.metadata.design.product_name_color }">
                            {{ product.name }}
                        </h4>

                        <p
                            v-if="product.show_description"
                            class="text-sm line-clamp-2 mb-2"
                            :style="{ color: designCard.metadata.design.product_description_color }">
                            {{ product.description }}
                        </p>

                        <div
                            v-if="designCard.metadata.layout === 'list' && !(product ?? product.variant).photo && product.stock_quantity_type == 'sold out'"
                            class="w-fit select-none whitespace-nowrap px-2.5 py-0.5 text-xs text-center rounded-full font-bold text-white bg-red-600 shadow-sm">
                            SOLD OUT
                        </div>

                        <div class="flex items-center space-x-2 flex-wrap gap-2">

                            <Pill v-if="(product.variant ?? product).is_free" type="success" size="xs">free</Pill>

                            <div v-else class="flex items-center space-x-1">

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

                            </div>

                            <Pill
                                :type="(product.variant ?? product).has_stock ? 'success' : 'warning'" size="xs"
                                v-if="(product.variant ?? product).stock_quantity_type == 'limited' && (((product.variant ?? product).has_stock && (product.variant ?? product).stock_quantity <= 5) || !(product.variant ?? product).has_stock)">
                                {{ (product.variant ?? product).has_stock ? `${(product.variant ?? product).stock_quantity} left` : 'no stock' }}
                            </Pill>

                        </div>

                    </div>

                </div>

                <p v-else class="text-gray-500 text-sm">No products available in this category.</p>

                <button
                    @click.stop="navigateToShowShopCategory"
                    v-if="filteredProducts.length < category.products.length"
                    class="flex items-center justify-center space-x-2 w-full rounded-lg py-2 border border-gray-200 hover:bg-gray-100 cursor-pointer transition-all duration-300">
                    <span>View All</span>
                    <ArrowRight size="16"></ArrowRight>
                </button>

            </template>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Loader from '@Partials/Loader.vue';
    import { Image, ArrowRight, ChevronRight } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'designState', 'orderState', 'storeState', 'notificationState'],
        components: { ArrowRight, ChevronRight, Image, Pill, Loader },
        props: {
            designCard: {
                type: Object
            }
        },
        data() {
            return {
                category: null,
                lastCategoryId: null,
                isLoadingCategory: false
            }
        },
        watch: {
            categoryId(newValue, oldValue) {
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
            categoryId() {
                return this.designCard.metadata.category_id;
            },
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment', 'edit-menu'].includes(this.$route.name);
            },
            filteredProducts() {
                return this.category ? this.category.products.slice(0, parseInt(this.designCard.metadata.feature)) : [];
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
            navigateToShowShopCategory() {
                this.$router.push({
                    name: 'show-shop-category',
                    params: {
                        alias: this.store.alias,
                        category_id: this.categoryId
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
                    if (!this.categoryId) return;

                    this.isLoadingCategory = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['photo', 'products.photo'].join(',')
                        }
                    };

                    this.lastCategoryId = this.categoryId;

                    const response = await axios.get(`/api/categories/${this.categoryId}`, config);

                    if(this.categoryId == this.lastCategoryId) {
                        this.category = response.data;
                    }

                } catch (error) {
                    if(error.status != 404) {
                        const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching category';
                        this.notificationState.showWarningNotification(message);
                        this.formState.setServerFormErrors(error);
                    }
                    console.error('Failed to fetch category:', error);
                } finally {
                    if(this.categoryId == this.lastCategoryId) {
                        this.isLoadingCategory = false;
                    }
                }
            },
        },
        created() {
            this.showCategory();
        }
    }
</script>
