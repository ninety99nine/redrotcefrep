<template>

    <div class="max-w-xl mx-auto">

        <LoadingDesignCards v-if="isLoadingStore || isLoadingDesignCards"></LoadingDesignCards>

        <template v-else>

            <DesignCards v-if="hasDesignCards"></DesignCards>

            <NoDesignCards v-else></NoDesignCards>

        </template>

    </div>

</template>

<script>

    import debounce from 'lodash/debounce';
    import NoDesignCards from '@Pages/shop/_components/_components/NoDesignCards.vue';
    import DesignCards from '@Pages/shop/_components/_components/design-cards/DesignCards.vue';
    import LoadingDesignCards from '@Pages/shop/_components/_components/LoadingDesignCards.vue';

    export default {
        inject: ['formState', 'designState', 'orderState', 'storeState', 'changeHistoryState', 'notificationState'],
        components: {
            NoDesignCards, DesignCards, LoadingDesignCards
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            },
            orderForm: {
                handler(newVal) {
                    if(newVal) {
                        if(this.shoppingCartReady) {
                            this.orderState.setIsInspectingShoppingCart(true);
                            this.inspectShoppingCartDelayed();
                        }else{
                            this.shoppingCartReady = true;
                        }
                    }
                },
                deep: true
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            designForm() {
                return this.designState.designForm;
            },
            designCards() {
                if(this.$route.name.startsWith('edit-')) {
                    return this.designForm?.design_cards ?? [];
                }else{
                    return this.designState.designCards;
                }
            },
            hasDesignCards() {
                return this.designCards.filter(designCard => !designCard.hasOwnProperty('delete')).length > 0;
            },
            isLoadingDesignCards() {
                return this.designState.isLoadingDesignCards;
            },
            hasLoadedInitialdesignCards() {
                return this.designState.hasLoadedInitialdesignCards;
            },
            type() {
                if(['show-storefront', 'edit-storefront'].includes(this.$route.name)) {
                    return 'storefront';
                }else if(['show-checkout', 'edit-checkout'].includes(this.$route.name)) {
                    return 'checkout';
                }
            }
        },
        methods: {
            async setup() {
                if(this.store && ['show-storefront', 'show-checkout'].includes(this.$route.name)) {
                    if(!this.hasLoadedInitialdesignCards && !this.isLoadingDesignCards) {
                        this.showDesignCards();
                    }
                }
            },
            async showDesignCards() {
                try {

                    this.designState.isLoadingDesignCards = true;

                    let config = {
                        params: {
                            per_page: 100,
                            type: this.type,
                            store_id: this.store.id,
                            _relationships: ['photos'].join(',')
                        }
                    };

                    const response = await axios.get('/api/design-cards', config);
                    this.designState.designCards = response.data.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching design cards';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch design cards:', error);
                } finally {
                    this.designState.isLoadingDesignCards = false;
                    this.designState.hasLoadedInitialdesignCards = true;
                }
            },
            inspectShoppingCartDelayed: debounce(function () {

                if(this.changeHistoryState.hasChanges) {
                    this.inspectShoppingCart();
                }else{
                    this.orderState.setShoppingCart(null);
                    this.orderState.setIsInspectingShoppingCart(false);
                }

            }, 1000),
            async inspectShoppingCart() {

                try {

                    this.orderState.setIsInspectingShoppingCart(true);

                    const data = {
                        inspect: true,
                        ...this.orderForm,
                        guest_id: this.guestId,
                        store_id: this.store.id,
                        association: 'team member'
                    };

                    const response = await axios.post(`/api/orders`, data);
                    this.orderState.setShoppingCart(response.data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while inspecting shopping cart';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to inspect shopping cart:', error);
                } finally {
                    this.orderState.setIsInspectingShoppingCart(false);
                }

            }
        },
        created() {
            this.setup();
        }
    }
</script>
