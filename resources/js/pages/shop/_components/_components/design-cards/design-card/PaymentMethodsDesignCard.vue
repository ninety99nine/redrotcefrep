<template>

    <div
        :style="{
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
        }">

        <div class="flex flex-col items-center">

            <!-- Heading -->
            <h2
                v-if="designCard.metadata.title"
                class="text-xl font-semibold text-center mb-4"
                :style="{ color: designCard.metadata.design.title_color }">
                {{ designCard.metadata.title }}
            </h2>

            <!-- Sub Heading -->
            <h2
                class="text-center"
                v-if="designCard.metadata.subtitle"
                :style="{ color: designCard.metadata.design.subtitle_color }">
                {{ designCard.metadata.subtitle }}
            </h2>

            <!-- Amount -->
            <Skeleton v-if="isLoadingOrder" width="w-40" height="h-8" :shine="true" class="my-4"></Skeleton>
            <h2 v-else
                :style="{ color: designCard.metadata.design.amount_color }"
                class="text-4xl font-semibold text-center mb-8">
                {{ order?.outstanding_total?.amount_with_currency ?? fakeOutstandingTotal }}
            </h2>

        </div>

        <div class="space-y-3 mb-4">

            <!-- Payment Methods (Loading Placeholder) -->
            <div v-if="isLoadingOrder" class="space-y-2">

                <div v-for="(_, index) in [1, 2, 3]" :key="index" class="flex items-center space-x-4 border-b shadow-sm rounded-lg py-6 px-4 bg-gray-50">

                    <Skeleton width="w-8" height="h-8" :shine="true" class="flex-shrink-0"></Skeleton>

                    <div class="w-full flex justify-between items-center">
                        <Skeleton width="w-40" :shine="true"></Skeleton>
                        <Skeleton width="w-8" :shine="true"></Skeleton>
                    </div>

                </div>

            </div>

            <!-- Payment Methods -->
            <template v-else>

                <div
                    :key="index"
                    v-for="(storePaymentMethod, index) in storePaymentMethods"
                    @click="() => navigateToStorePaymentMethod(storePaymentMethod)"
                    class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-gray-100 hover:border-gray-300 hover:shadow-lg cursor-pointer"s>

                    <div class="flex justify-between items-center">

                        <div class="flex items-center space-x-2 font-bold">

                            <!-- Logo -->
                            <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center">
                                <img
                                    alt="Payment Method Logo"
                                    class="h-full object-contain"
                                    :src="storePaymentMethod.logo ? storePaymentMethod.logo.path : storePaymentMethod.payment_method.image_url"
                                />
                            </div>

                            <!-- Name -->
                            <span class="text-sm">{{ storePaymentMethod.payment_method.type == 'other' && storePaymentMethod.custom_name ? storePaymentMethod.custom_name : storePaymentMethod.payment_method.name }}</span>

                        </div>

                        <!-- Arrow Icon -->
                        <ArrowRight size="20"></ArrowRight>

                    </div>

                </div>

            </template>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Loader from '@Partials/Loader.vue';
    import { ArrowRight } from 'lucide-vue-next';
    import Skeleton from '@Partials/Skeleton.vue';
    import { convertToMoneyWithSymbol } from '@Utils/numberUtils.js';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { ArrowRight, Pill, Loader, Skeleton },
        props: {
            designCard: {
                type: Object
            }
        },
        data() {
            return {
                pagination: null,
                storePaymentMethods: [],
                isLoadingStorePaymentMethods: false
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.showStorePaymentMethods();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            fakeOutstandingTotal() {
                return convertToMoneyWithSymbol('100.00', this.store.currency);
            },
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment'].includes(this.$route.name);
            }
        },
        methods: {
            navigateToStorePaymentMethod(storePaymentMethod) {
                if(this.isDesigning) {
                    this.notificationState.showSuccessNotification(`Only opens on the actual store`);
                    return;
                }
                this.$router.push({
                    name: 'show-shop-payment-method',
                    params: {
                        'alias': this.store.alias,
                        'order_id': this.order.id,
                        'store_payment_method_id': storePaymentMethod.id
                     }
                });
            },
            async showStorePaymentMethods() {
                try {

                    this.isLoadingStorePaymentMethods = true;

                    let config = {
                        params: {
                            association: 'shopper',
                            store_id: this.store.id,
                            _relationships: ['logo', 'paymentMethod'].join(',')
                        }
                    };

                    const response = await axios.get('/api/store-payment-methods', config);

                    this.pagination = response.data;
                    this.storePaymentMethods = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching payment methods';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch payment methods:', error);
                } finally {
                    this.isLoadingStorePaymentMethods = false;
                }
            },
        },
        created() {
            if(this.store) this.showStorePaymentMethods();
        }
    }
</script>
