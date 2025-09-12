<template>

    <div class="max-w-xl mx-auto">

        <LoadingDesignCards v-if="isLoadingStore || isLoadingDesignCards"></LoadingDesignCards>

        <template v-else>

            <Button
                size="xs"
                type="light"
                class="mt-8 m-4"
                v-if="isCheckout"
                :leftIcon="MoveLeft"
                :action="navigateToStorefront">
                <span>Shop</span>
            </Button>

            <DesignCards v-if="hasDesignCards"></DesignCards>

            <NoDesignCards v-else></NoDesignCards>

        </template>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { MoveLeft } from 'lucide-vue-next';
    import NoDesignCards from '@Pages/shop/_components/_components/NoDesignCards.vue';
    import DesignCards from '@Pages/shop/_components/_components/design-cards/DesignCards.vue';
    import LoadingDesignCards from '@Pages/shop/_components/_components/LoadingDesignCards.vue';

    export default {
        inject: ['formState', 'designState', 'storeState', 'notificationState'],
        components: {
            Button, NoDesignCards, DesignCards, LoadingDesignCards
        },
        data() {
            return {
                MoveLeft
            }
        },
        watch: {
            store() {
                this.setup();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isCheckout() {
                return this.designState.type == 'checkout';
            },
            designForm() {
                return this.designState.designForm;
            },
            designCards() {
                if(['edit-storefront', 'edit-checkout', 'edit-payment'].includes(this.$route.name)) {
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
                }else if(['show-shop-payment-methods', 'edit-payment'].includes(this.$route.name)) {
                    return 'payment';
                }
            }
        },
        methods: {
            async setup() {
                if(this.store && ['show-storefront', 'show-checkout', 'show-shop-payment-methods'].includes(this.$route.name)) {
                    if(!this.hasLoadedInitialdesignCards && !this.isLoadingDesignCards) {
                        this.showDesignCards();
                    }
                }
            },
            async navigateToStorefront() {
                await this.$router.push({
                    name: 'show-storefront',
                    params: {
                        alias: this.store.alias
                    }
                });
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
            }
        },
        beforeUnmount() {
            this.designState.reset();
        },
        created() {
            this.setup();
            this.designState.type = this.type;
        }
    }
</script>
