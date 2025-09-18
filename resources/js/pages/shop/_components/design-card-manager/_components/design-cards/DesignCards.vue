<template>

    <div class="space-y-2">

        <div :class="[{ 'mb-20' : showingStorefront }]">

            <template
                v-for="(designCard, index) in designCards"
                :key="designCard?.id ?? designCard.temporary_id">

                <template v-if="!designCard.hasOwnProperty('delete') && designCard.visible">

                    <LogoDesignCard v-if="designCard.type == 'logo'" :designCard="designCard"></LogoDesignCard>
                    <LinkDesignCard v-if="designCard.type == 'link'" :designCard="designCard"></LinkDesignCard>
                    <MapDesignCard v-else-if="designCard.type == 'map'" :designCard="designCard"></MapDesignCard>
                    <TextDesignCard v-else-if="designCard.type == 'text'" :designCard="designCard"></TextDesignCard>
                    <ImageDesignCard v-else-if="designCard.type == 'image'" :designCard="designCard"></ImageDesignCard>
                    <VideoDesignCard v-else-if="designCard.type == 'video'" :designCard="designCard"></VideoDesignCard>
                    <DividerDesignCard v-if="designCard.type == 'divider'" :designCard="designCard"></DividerDesignCard>
                    <BannerDesignCard v-else-if="designCard.type == 'banner'" :designCard="designCard"></BannerDesignCard>
                    <ContactDesignCard v-else-if="designCard.type == 'contact'" :designCard="designCard"></ContactDesignCard>
                    <SocialsDesignCard v-else-if="designCard.type == 'socials'" :designCard="designCard"></SocialsDesignCard>
                    <CountdownDesignCard v-else-if="designCard.type == 'countdown'" :designCard="designCard"></CountdownDesignCard>

                    <TipsDesignCard v-else-if="designCard.type == 'tips'" :designCard="designCard"></TipsDesignCard>
                    <ItemsDesignCard v-else-if="designCard.type == 'items'" :designCard="designCard"></ItemsDesignCard>
                    <ProductsDesignCard v-else-if="designCard.type == 'products'" :designCard="designCard"></ProductsDesignCard>
                    <CustomerDesignCard v-else-if="designCard.type == 'customer'" :designCard="designCard"></CustomerDesignCard>
                    <DeliveryDesignCard v-else-if="designCard.type == 'delivery'" :designCard="designCard"></DeliveryDesignCard>
                    <OrderSummaryCard v-else-if="designCard.type == 'order summary'" :designCard="designCard"></OrderSummaryCard>
                    <PromoCodeDesignCard v-else-if="designCard.type == 'promo code'" :designCard="designCard"></PromoCodeDesignCard>
                    <DataCollectionDesignCard v-else-if="isDataCollectionField(designCard)" :index="index" :designCard="designCard"></DataCollectionDesignCard>

                    <PaymentMethodsDesignCard v-else-if="designCard.type == 'payment methods'" :designCard="designCard"></PaymentMethodsDesignCard>

                </template>

            </template>

        </div>

        <MyCartButton
            v-if="showingStorefront && shoppingCart">
        </MyCartButton>

        <PlaceOrderButton
            :placement="placement"
            :designCards="designCards"
            v-else-if="showingCheckout && shoppingCart">
        </PlaceOrderButton>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { ShoppingCart } from 'lucide-vue-next';
    import MyCartButton from '@Pages/shop/_components/design-card-manager/_components/my-cart/MyCartButton.vue';
    import PlaceOrderButton from '@Pages/shop/_components/design-card-manager/_components/place-order/PlaceOrderButton.vue';
    import MapDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/MapDesignCard.vue';
    import LogoDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/LogoDesignCard.vue';
    import LinkDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/LinkDesignCard.vue';
    import TextDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/TextDesignCard.vue';
    import ImageDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/ImageDesignCard.vue';
    import VideoDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/VideoDesignCard.vue';
    import BannerDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/BannerDesignCard.vue';
    import ContactDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/ContactDesignCard.vue';
    import DividerDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/DividerDesignCard.vue';
    import SocialsDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/SocialsDesignCard.vue';
    import CustomerDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/CustomerDesignCard.vue';
    import ProductsDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/ProductsDesignCard.vue';
    import CountdownDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/CountdownDesignCard.vue';
    import TipsDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/tips-design-card/TipsDesignCard.vue';
    import DataCollectionDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/DataCollectionDesignCard.vue';
    import PaymentMethodsDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/PaymentMethodsDesignCard.vue';
    import ItemsDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/items-design-card/ItemsDesignCard.vue';
    import DeliveryDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/delivery-design-card/DeliveryDesignCard.vue';
    import OrderSummaryCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/order-summary-design-card/OrderSummaryCard.vue';
    import PromoCodeDesignCard from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/promo-code-design-card/PromoCodeDesignCard.vue';

    export default {
        inject: ['designState', 'orderState', 'storeState'],
        components: {
            ShoppingCart, Button, MyCartButton, PlaceOrderButton, MapDesignCard, LogoDesignCard, LinkDesignCard, TextDesignCard, ImageDesignCard, VideoDesignCard, ContactDesignCard,
            BannerDesignCard, DividerDesignCard, SocialsDesignCard, CustomerDesignCard, ProductsDesignCard, CountdownDesignCard, TipsDesignCard, DataCollectionDesignCard,
            PaymentMethodsDesignCard, ItemsDesignCard, DeliveryDesignCard, OrderSummaryCard, PromoCodeDesignCard
        },
        props: {
            placement: {
                type: String
            },
            designCards: {
                type: Array
            }
        },
        data() {
            return {
                localDesignCards: []
            }
        },
        computed: {
            designForm() {
                return this.designState.designForm;
            },
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment', 'edit-menu'].includes(this.$route.name);
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            showingCheckout() {
                return this.$route.name == 'show-checkout';
            },
            showingStorefront() {
                return this.$route.name == 'show-storefront';
            },
            grandTotal() {
                return this.shoppingCart ? this.shoppingCart.totals.grand_total.amount_with_currency : null;
            },
            totalUncancelledProductQuantities() {
                return this.shoppingCart ? this.shoppingCart.totals_summary.order_products.total_uncancelled_product_quantities : null;
            }
        },
        methods: {
            isDataCollectionField(designCard) {
                return ['short answer', 'long answer', 'number', 'date', 'time', 'checkbox', 'selection', 'location', 'media'].includes(designCard.type);
            }
        }
    }
</script>
