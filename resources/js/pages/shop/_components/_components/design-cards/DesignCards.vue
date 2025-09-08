<template>

    <div class="space-y-2">

        <div :class="['p-2 space-y-2', { 'mb-20' : type == 'storefront' }]">

            <template
                :key="index"
                v-for="(designCard, index) in designCards">

                <template v-if="!designCard.hasOwnProperty('delete')">

                    <LinkDesignCard v-if="designCard.metadata.type == 'link'" :designCard="designCard"></LinkDesignCard>
                    <MapDesignCard v-else-if="designCard.metadata.type == 'map'" :designCard="designCard"></MapDesignCard>
                    <VideoDesignCard v-else-if="designCard.metadata.type == 'video'" :designCard="designCard"></VideoDesignCard>
                    <ContactDesignCard v-else-if="designCard.metadata.type == 'contact'" :designCard="designCard"></ContactDesignCard>
                    <SocialsDesignCard v-else-if="designCard.metadata.type == 'socials'" :designCard="designCard"></SocialsDesignCard>
                    <TextDesignCard v-else-if="designCard.metadata.type == 'text'" :designCard="designCard"></TextDesignCard>
                    <ImageDesignCard v-else-if="designCard.metadata.type == 'image'" :designCard="designCard"></ImageDesignCard>
                    <CountdownDesignCard v-else-if="designCard.metadata.type == 'countdown'" :designCard="designCard"></CountdownDesignCard>

                    <TipsDesignCard v-else-if="designCard.metadata.type == 'tips'" :designCard="designCard"></TipsDesignCard>
                    <ItemsDesignCard v-else-if="designCard.metadata.type == 'items'" :designCard="designCard"></ItemsDesignCard>
                    <ProductsDesignCard v-else-if="designCard.metadata.type == 'products'" :designCard="designCard"></ProductsDesignCard>
                    <CustomerDesignCard v-else-if="designCard.metadata.type == 'customer'" :designCard="designCard"></CustomerDesignCard>
                    <DeliveryDesignCard v-else-if="designCard.metadata.type == 'delivery'" :designCard="designCard"></DeliveryDesignCard>
                    <OrderSummaryCard v-else-if="designCard.metadata.type == 'order summary'" :designCard="designCard"></OrderSummaryCard>
                    <PromoCodeDesignCard v-else-if="designCard.metadata.type == 'promo code'" :designCard="designCard"></PromoCodeDesignCard>

                </template>

            </template>

        </div>

        <div
            v-if="type == 'storefront'"
            class="fixed left-0 right-0 bottom-0 p-4 bg-white shadow-sm border-t border-gray-200">

            <div class="relative">

                <Button
                    size="lg"
                    type="primary"
                    cvlass="w-full"
                    :action="viewCart"
                    buttonClass="w-full">
                    <span>View Cart</span>
                </Button>

                <div class="absolute -top-2 right-2 flex items-center justify-center rounded-full min-w-6 h-6 bg-red-500 text-white text-xs font-bold">
                    <span class="p-2">3 items</span>
                </div>

            </div>

        </div>

        <div
            v-if="type == 'checkout'"
            class="p-4 bg-white shadow-sm border-t border-gray-200">

            <Button
                size="lg"
                type="primary"
                :action="checkout"
                buttonClass="w-full">
                <span>Place order</span>
            </Button>

        </div>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { ShoppingCart } from 'lucide-vue-next';
    import MapDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/MapDesignCard.vue';
    import LinkDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/LinkDesignCard.vue';
    import TextDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/TextDesignCard.vue';
    import ImageDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/ImageDesignCard.vue';
    import VideoDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/VideoDesignCard.vue';
    import ContactDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/ContactDesignCard.vue';
    import SocialsDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/SocialsDesignCard.vue';
    import CustomerDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/CustomerDesignCard.vue';
    import ProductsDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/ProductsDesignCard.vue';
    import CountdownDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/CountdownDesignCard.vue';
    import TipsDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/tips-design-card/TipsDesignCard.vue';
    import ItemsDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/items-design-card/ItemsDesignCard.vue';
    import DeliveryDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/delivery-design-card/DeliveryDesignCard.vue';
    import OrderSummaryCard from '@Pages/shop/_components/_components/design-cards/design-card/order-summary-design-card/OrderSummaryCard.vue';
    import PromoCodeDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/promo-code-design-card/PromoCodeDesignCard.vue';

    export default {
        inject: ['designState', 'storeState'],
        components: {
            ShoppingCart, Button, MapDesignCard, TipsDesignCard, LinkDesignCard, TextDesignCard, ImageDesignCard, VideoDesignCard, ContactDesignCard,
            SocialsDesignCard, CustomerDesignCard, ProductsDesignCard, CountdownDesignCard, TipsDesignCard, ItemsDesignCard,
            DeliveryDesignCard, OrderSummaryCard, PromoCodeDesignCard
        },
        computed: {
            type() {
                return this.designState.type;
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
            }
        },
        methods: {
            viewCart() {

            },
            checkout() {

            }
        }
    }
</script>
