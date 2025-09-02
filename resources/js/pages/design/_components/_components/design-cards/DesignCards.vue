<template>

    <draggable
        class="mt-4 space-y-2"
        handle=".draggable-handle"
        ghost-class="bg-yellow-50"
        v-model="designState.designForm.design_cards"
        @change="designState.saveStateDebounced('Design card moved')">

        <template
            :key="index"
            v-for="(designCard, index) in designState.designForm.design_cards">

            <div
                v-if="!designCard.hasOwnProperty('delete')"
                class="w-full bg-gray-50 p-4 border border-gray-300 rounded-lg">

                <div class="flex items-center justify-between mb-4">

                    <div class="flex items-center space-x-2 text-gray-500">

                        <Map v-if="designCard.metadata.type == 'map'" size="16"></Map>
                        <Link v-if="designCard.metadata.type == 'link'" size="16"></Link>
                        <Type v-if="designCard.metadata.type == 'text'" size="16"></Type>
                        <Box v-if="designCard.metadata.type == 'products'" size="16"></Box>
                        <Image v-if="designCard.metadata.type == 'image'" size="16"></Image>
                        <Video v-if="designCard.metadata.type == 'video'" size="16"></Video>
                        <AtSign v-if="designCard.metadata.type == 'socials'" size="16"></AtSign>
                        <Clock v-if="designCard.metadata.type == 'countdown'" size="16"></Clock>
                        <Contact v-if="designCard.metadata.type == 'contact'" size="16"></Contact>

                        <Truck v-if="designCard.metadata.type == 'delivery'" size="20"></Truck>
                        <HandCoins v-if="designCard.metadata.type == 'tips'" size="20"></HandCoins>
                        <UserRound v-if="designCard.metadata.type == 'customer'" size="20"></UserRound>
                        <ShoppingCart v-if="designCard.metadata.type == 'items'" size="20"></ShoppingCart>
                        <ReceiptText v-if="designCard.metadata.type == 'order summary'" size="20"></ReceiptText>
                        <TicketPercent v-if="designCard.metadata.type == 'promo code'" size="20"></TicketPercent>

                        <span class="text-xs whitespace-nowrap">{{ getDesignCardLabel(designCard) }}</span>

                    </div>


                    <div class="flex justify-end space-x-4">

                        <!-- Visible Toggle -->
                        <div class="cursor-pointer hover:text-blue-500 transition-all duration-500">
                            <Eye v-if="designCard.visible" size="16" @click.stop="() => toggleVisible(index)"></Eye>
                            <EyeOff v-else size="16" @click.stop="() => toggleVisible(index)"></EyeOff>
                        </div>

                        <!-- Drag & Drop Handle -->
                        <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                    </div>

                </div>

                <LinkDesignCard v-if="designCard.metadata.type == 'link'" :index="index" :designCard="designCard"></LinkDesignCard>
                <MapDesignCard v-else-if="designCard.metadata.type == 'map'" :index="index" :designCard="designCard"></MapDesignCard>
                <VideoDesignCard v-else-if="designCard.metadata.type == 'video'" :index="index" :designCard="designCard"></VideoDesignCard>
                <ContactDesignCard v-else-if="designCard.metadata.type == 'contact'" :index="index" :designCard="designCard"></ContactDesignCard>
                <SocialsDesignCard v-else-if="designCard.metadata.type == 'socials'" :index="index" :designCard="designCard"></SocialsDesignCard>
                <TextDesignCard v-else-if="designCard.metadata.type == 'text'" :index="index" :designCard="designCard" :editorOptions="editorOptions"></TextDesignCard>
                <ImageDesignCard v-else-if="designCard.metadata.type == 'image'" :index="index" :designCard="designCard" :editorOptions="editorOptions"></ImageDesignCard>
                <CountdownDesignCard v-else-if="designCard.metadata.type == 'countdown'" :index="index" :designCard="designCard" :editorOptions="editorOptions"></CountdownDesignCard>

                <TipsDesignCard v-else-if="designCard.metadata.type == 'tips'" :index="index" :designCard="designCard"></TipsDesignCard>
                <ItemsDesignCard v-else-if="designCard.metadata.type == 'items'" :index="index" :designCard="designCard"></ItemsDesignCard>
                <ProductsDesignCard v-else-if="designCard.metadata.type == 'products'" :index="index" :designCard="designCard"></ProductsDesignCard>
                <CustomerDesignCard v-else-if="designCard.metadata.type == 'customer'" :index="index" :designCard="designCard"></CustomerDesignCard>
                <DeliveryDesignCard v-else-if="designCard.metadata.type == 'delivery'" :index="index" :designCard="designCard"></DeliveryDesignCard>
                <OrderSummaryCard v-else-if="designCard.metadata.type == 'order summary'" :index="index" :designCard="designCard"></OrderSummaryCard>
                <PromoCodeDesignCard v-else-if="designCard.metadata.type == 'promo code'" :index="index" :designCard="designCard"></PromoCodeDesignCard>

                <div class="flex justify-end space-x-4 mt-4">

                    <Trash
                        size="16"
                        @click.stop="() => removeDesignCard(index)"
                        v-if="isDeletableDesignCardLabel(designCard)"
                        class="cursor-pointer text-red-500 hover:text-red-600 active:text-red-600 active:scale-95 transition-all">
                    </Trash>

                    <span v-else class="text-xs text-gray-400">not removable</span>

                </div>

            </div>

        </template>

    </draggable>

</template>

<script>

    import { VueDraggableNext } from 'vue-draggable-next';
    import MapDesignCard from '@Pages/design/_components/_components/design-cards/design-card/MapDesignCard.vue';
    import TipsDesignCard from '@Pages/design/_components/_components/design-cards/design-card/TipsDesignCard.vue';
    import LinkDesignCard from '@Pages/design/_components/_components/design-cards/design-card/LinkDesignCard.vue';
    import TextDesignCard from '@Pages/design/_components/_components/design-cards/design-card/TextDesignCard.vue';
    import ImageDesignCard from '@Pages/design/_components/_components/design-cards/design-card/ImageDesignCard.vue';
    import ItemsDesignCard from '@Pages/design/_components/_components/design-cards/design-card/ItemsDesignCard.vue';
    import VideoDesignCard from '@Pages/design/_components/_components/design-cards/design-card/VideoDesignCard.vue';
    import OrderSummaryCard from '@Pages/design/_components/_components/design-cards/design-card/OrderSummaryCard.vue';
    import ContactDesignCard from '@Pages/design/_components/_components/design-cards/design-card/ContactDesignCard.vue';
    import SocialsDesignCard from '@Pages/design/_components/_components/design-cards/design-card/SocialsDesignCard.vue';
    import CustomerDesignCard from '@Pages/design/_components/_components/design-cards/design-card/CustomerDesignCard.vue';
    import DeliveryDesignCard from '@Pages/design/_components/_components/design-cards/design-card/DeliveryDesignCard.vue';
    import ProductsDesignCard from '@Pages/design/_components/_components/design-cards/design-card/ProductsDesignCard.vue';
    import PromoCodeDesignCard from '@Pages/design/_components/_components/design-cards/design-card/PromoCodeDesignCard.vue';
    import CountdownDesignCard from '@Pages/design/_components/_components/design-cards/design-card/CountdownDesignCard.vue';
    import { Eye, EyeOff, Move, Trash, Map, Link, Type, Box, Image, Video, AtSign, Clock, Contact, Truck, HandCoins, UserRound, ShoppingCart, ReceiptText, TicketPercent } from 'lucide-vue-next';

    export default {
        inject: ['designState', 'storeState'],
        components: {
            Eye, EyeOff, Move, Trash, Map, Link, Type, Box, Image, Video, AtSign, Clock, Contact, Truck, HandCoins, UserRound, ShoppingCart, ReceiptText, TicketPercent,
            draggable: VueDraggableNext, MapDesignCard, TipsDesignCard, LinkDesignCard, TextDesignCard, ImageDesignCard, ItemsDesignCard, VideoDesignCard,
            OrderSummaryCard, ContactDesignCard, SocialsDesignCard, CustomerDesignCard, DeliveryDesignCard, ProductsDesignCard,
            PromoCodeDesignCard, CountdownDesignCard
        },
        data() {
            return {
                editorOptions: {
                    toolbar: [
                        'bold', 'italic', 'strikethrough', '|',
                        'heading-1', 'heading-2', 'heading-3', '|',
                        'unordered-list', 'ordered-list', '|',
                        'link', 'image', 'quote', 'code'
                    ],
                    status: false,
                    autofocus: false,
                    minHeight: '200px',
                    spellChecker: false,
                    placeholder: 'Type your content... Use the toolbar for formatting',
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            designForm() {
                return this.designState.designForm;
            },
            designCards() {
                return this.designForm.design_cards;
            },
            designCardOptions() {
                return this.designState.designCardOptions;
            },
        },
        methods: {
            isDeletableDesignCardLabel(designCard) {
                return !['customer', 'items', 'delivery', 'promo code', 'tips', 'order summary'].includes(designCard.metadata.type);
            },
            getDesignCardLabel(designCard) {
                return this.designCardOptions.find(designCardOption => designCardOption.value == designCard.metadata.type).label;
            },
            toggleVisible(index) {
                this.designCards[index].visible = !this.designCards[index].visible;
                this.designState.saveStateDebounced(this.designCards[index].visible ? 'Design card hidden' : 'Design card visible');
            },
            removeDesignCard(index) {
                let designCard = this.designCards[index];

                if(designCard.id) {
                    this.designCards[index].delete = true;
                }else{
                    this.designCards.splice(index, 1);
                }
                this.designState.saveStateDebounced('Design card removed');
            },
        }
    }
</script>
