<template>

    <div>

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

            <DesignCards
                v-if="hasDesignCards"
                :placement="placement"
                :designCards="designCards">
            </DesignCards>

            <NoDesignCards v-else></NoDesignCards>

        </template>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { MoveLeft } from 'lucide-vue-next';
    import NoDesignCards from '@Pages/shop/_components/design-card-manager/_components/NoDesignCards.vue';
    import DesignCards from '@Pages/shop/_components/design-card-manager/_components/design-cards/DesignCards.vue';
    import LoadingDesignCards from '@Pages/shop/_components/design-card-manager/_components/LoadingDesignCards.vue';

    export default {
        inject: ['formState', 'designState', 'storeState', 'notificationState'],
        components: {
            Button, NoDesignCards, DesignCards, LoadingDesignCards
        },
        props: {
            placement: {
                type: String
            }
        },
        data() {
            return {
                MoveLeft,
                apiDesignCards: [],
                isLoadingDesignCards: false
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
                return this.placement == 'checkout';
            },
            designForm() {
                return this.designState.designForm;
            },
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment', 'edit-menu'].includes(this.$route.name);
            },
            designCards() {
                if(this.isDesigning) {
                    return this.designForm?.design_cards ?? [];
                }else{
                    return this.apiDesignCards;
                }
            },
            hasDesignCards() {
                return this.designCards.filter(designCard => !designCard.hasOwnProperty('delete')).length > 0;
            }
        },
        methods: {
            async setup() {
                if(this.store && ['show-storefront', 'show-checkout', 'show-shop-payment-methods'].includes(this.$route.name)) {
                    this.showDesignCards();
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

                    this.isLoadingDesignCards = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id,
                            placement: this.placement,
                            _relationships: ['address', 'photos'].join(',')
                        }
                    };

                    const response = await axios.get('/api/design-cards', config);
                    this.apiDesignCards = response.data.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching design cards';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch design cards:', error);
                } finally {
                    this.isLoadingDesignCards = false;
                }
            }
        },
        beforeUnmount() {
            if(['show-storefront', 'show-checkout', 'show-shop-payment-methods'].includes(this.$route.name)) {
                this.designState.reset();
            }
        },
        created() {
            this.setup();
        }
    }
</script>
