<template>

    <div class="space-y-2">

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

</template>

<script>

    import MapDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/MapDesignCard.vue';
    import TipsDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/TipsDesignCard.vue';
    import LinkDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/LinkDesignCard.vue';
    import TextDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/TextDesignCard.vue';
    import ImageDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/ImageDesignCard.vue';
    import VideoDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/VideoDesignCard.vue';
    import OrderSummaryCard from '@Pages/shop/_components/_components/design-cards/design-card/OrderSummaryCard.vue';
    import ContactDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/ContactDesignCard.vue';
    import SocialsDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/SocialsDesignCard.vue';
    import CustomerDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/CustomerDesignCard.vue';
    import DeliveryDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/DeliveryDesignCard.vue';
    import ProductsDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/ProductsDesignCard.vue';
    import PromoCodeDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/PromoCodeDesignCard.vue';
    import CountdownDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/CountdownDesignCard.vue';
    import ItemsDesignCard from '@Pages/shop/_components/_components/design-cards/design-card/items-design-card/ItemsDesignCard.vue';

    export default {
        inject: ['designState', 'storeState'],
        components: {
            MapDesignCard, TipsDesignCard, LinkDesignCard, TextDesignCard, ImageDesignCard, VideoDesignCard, OrderSummaryCard,
            ContactDesignCard, SocialsDesignCard, CustomerDesignCard, DeliveryDesignCard, ProductsDesignCard, ItemsDesignCard,
            PromoCodeDesignCard, CountdownDesignCard
        },
        computed: {
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
        }
    }
</script>
