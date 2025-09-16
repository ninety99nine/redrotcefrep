<template>

    <draggable
        class="mt-4 space-y-2"
        handle=".draggable-handle"
        ghost-class="bg-yellow-50"
        v-model="designState.designForm.design_cards"
        @change="designState.saveStateDebounced('Design card moved')">

        <template
            :key="designCard?.id ?? designCard.temporary_id"
            v-for="(designCard, index) in designState.designForm.design_cards">

            <div
                v-if="!designCard.hasOwnProperty('delete')"
                class="w-full bg-gray-50 p-4 border border-gray-300 rounded-lg relative">

                <div :class="['flex items-center justify-between', { 'mb-4' : !wantsToArrangeDesignCards }]">

                    <div class="flex items-center space-x-2 text-gray-500">

                        <Map v-if="designCard.type == 'map'" size="16"></Map>
                        <Link v-if="designCard.type == 'link'" size="16"></Link>
                        <Type v-if="designCard.type == 'text'" size="16"></Type>
                        <Box v-if="designCard.type == 'products'" size="16"></Box>
                        <Image v-if="designCard.type == 'image'" size="16"></Image>
                        <Video v-if="designCard.type == 'video'" size="16"></Video>
                        <Hexagon v-if="designCard.type == 'logo'" size="16"></Hexagon>
                        <AtSign v-if="designCard.type == 'socials'" size="16"></AtSign>
                        <Clock v-if="designCard.type == 'countdown'" size="16"></Clock>
                        <MapPin v-if="designCard.type == 'location'" size="16"></MapPin>
                        <Contact v-if="designCard.type == 'contact'" size="16"></Contact>
                        <Megaphone v-if="designCard.type == 'banner'" size="16"></Megaphone>
                        <SeparatorHorizontal v-if="designCard.type == 'divider'" size="20"></SeparatorHorizontal>

                        <Tally1 v-if="designCard.type == 'short text'" size="20" class="rotate-90 translate-y-2"></Tally1>
                        <Tally2 v-if="designCard.type == 'long text'" size="20" class="rotate-90 translate-y-1"></Tally2>
                        <Binary v-if="designCard.type == 'number'" size="20"></Binary>
                        <Calendar v-if="designCard.type == 'date'" size="20"></Calendar>
                        <SquareCheck v-if="designCard.type == 'checkbox'" size="20"></SquareCheck>
                        <List v-if="designCard.type == 'selection'" size="20"></List>
                        <CloudUpload v-if="designCard.type == 'media'" size="20"></CloudUpload>

                        <Truck v-if="designCard.type == 'delivery'" size="20"></Truck>
                        <HandCoins v-if="designCard.type == 'tips'" size="20"></HandCoins>
                        <UserRound v-if="designCard.type == 'customer'" size="20"></UserRound>
                        <ShoppingCart v-if="designCard.type == 'items'" size="20"></ShoppingCart>
                        <CreditCard v-if="designCard.type == 'payment methods'" size="20"></CreditCard>
                        <ReceiptText v-if="designCard.type == 'order summary'" size="20"></ReceiptText>
                        <TicketPercent v-if="designCard.type == 'promo code'" size="20"></TicketPercent>

                        <span class="text-xs whitespace-nowrap">{{ designCard.type }}</span>

                    </div>

                    <div class="flex items-center justify-end space-x-4">

                        <div
                            class="flex items-center space-x-0.5"
                            v-if="isRequiredDesignCard(designCard)">

                            <Pill
                                size="xs"
                                type="primary"
                                tooltipContent="This design card is a standard design that cannot be hidden or removed">
                                standard
                            </Pill>

                            <Tooltip
                                trigger="hover"
                                v-if="isRequiredDesignCard(designCard)"
                                content="This design card is a standard design that cannot be hidden or removed.">x
                            </Tooltip>

                        </div>

                        <template v-else>

                            <!-- Delete Icon -->
                            <Trash
                                size="16"
                                v-if="wantsToArrangeDesignCards"
                                @click.stop="() => removeDesignCard(index)"
                                class="cursor-pointer text-gray-500 hover:text-red-600 active:text-red-600 active:scale-95 transition-all">
                            </Trash>

                            <!-- Visibility Icon -->
                            <div
                                :class="[designCard.visible ? 'opacity-100': 'opacity-30', 'cursor-pointer text-gray-500 hover:text-blue-500 transition-all duration-500']">
                                <Eye v-if="designCard.visible" size="16" @click.stop="() => toggleVisible(index)"></Eye>
                                <EyeOff v-else size="16" @click.stop="() => toggleVisible(index)"></EyeOff>
                            </div>

                        </template>

                        <!-- Drag & Drop Handle -->
                        <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                    </div>

                </div>

                <template v-if="!wantsToArrangeDesignCards">

                    <LogoDesignCard v-if="designCard.type == 'logo'" :index="index" :designCard="designCard"></LogoDesignCard>
                    <LinkDesignCard v-if="designCard.type == 'link'" :index="index" :designCard="designCard"></LinkDesignCard>
                    <MapDesignCard v-else-if="designCard.type == 'map'" :index="index" :designCard="designCard"></MapDesignCard>
                    <BannerDesignCard v-if="designCard.type == 'banner'" :index="index" :designCard="designCard"></BannerDesignCard>
                    <VideoDesignCard v-else-if="designCard.type == 'video'" :index="index" :designCard="designCard"></VideoDesignCard>
                    <ContactDesignCard v-else-if="designCard.type == 'contact'" :index="index" :designCard="designCard"></ContactDesignCard>
                    <SocialsDesignCard v-else-if="designCard.type == 'socials'" :index="index" :designCard="designCard"></SocialsDesignCard>
                    <DividerDesignCard v-else-if="designCard.type == 'divider'" :index="index" :designCard="designCard"></DividerDesignCard>
                    <TextDesignCard v-else-if="designCard.type == 'text'" :index="index" :designCard="designCard" :editorOptions="editorOptions"></TextDesignCard>
                    <ImageDesignCard v-else-if="designCard.type == 'image'" :index="index" :designCard="designCard" :editorOptions="editorOptions"></ImageDesignCard>
                    <CountdownDesignCard v-else-if="designCard.type == 'countdown'" :index="index" :designCard="designCard" :editorOptions="editorOptions"></CountdownDesignCard>

                    <TipsDesignCard v-else-if="designCard.type == 'tips'" :index="index" :designCard="designCard"></TipsDesignCard>
                    <ItemsDesignCard v-else-if="designCard.type == 'items'" :index="index" :designCard="designCard"></ItemsDesignCard>
                    <ProductsDesignCard v-else-if="designCard.type == 'products'" :index="index" :designCard="designCard"></ProductsDesignCard>
                    <CustomerDesignCard v-else-if="designCard.type == 'customer'" :index="index" :designCard="designCard"></CustomerDesignCard>
                    <DeliveryDesignCard v-else-if="designCard.type == 'delivery'" :index="index" :designCard="designCard"></DeliveryDesignCard>
                    <OrderSummaryCard v-else-if="designCard.type == 'order summary'" :index="index" :designCard="designCard"></OrderSummaryCard>
                    <PromoCodeDesignCard v-else-if="designCard.type == 'promo code'" :index="index" :designCard="designCard"></PromoCodeDesignCard>
                    <DataCollectionDesignCard v-else-if="isDataCollectionField(designCard)" :index="index" :designCard="designCard"></DataCollectionDesignCard>

                    <PaymentMethodsCard v-else-if="designCard.type == 'payment methods'" :index="index" :designCard="designCard"></PaymentMethodsCard>

                    <div class="flex justify-end space-x-4 mt-4">

                        <Trash
                            size="16"
                            v-if="!isRequiredDesignCard(designCard)"
                            @click.stop="() => removeDesignCard(index)"
                            class="cursor-pointer text-red-500 hover:text-red-600 active:text-red-600 active:scale-95 transition-all">
                        </Trash>

                        <span v-else class="text-xs text-gray-400">not removable</span>

                    </div>

                </template>

            </div>

        </template>

    </draggable>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Tooltip from '@Partials/Tooltip.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import MapDesignCard from '@Pages/design/_components/_components/design-cards/design-card/MapDesignCard.vue';
    import LogoDesignCard from '@Pages/design/_components/_components/design-cards/design-card/LogoDesignCard.vue';
    import TipsDesignCard from '@Pages/design/_components/_components/design-cards/design-card/TipsDesignCard.vue';
    import LinkDesignCard from '@Pages/design/_components/_components/design-cards/design-card/LinkDesignCard.vue';
    import TextDesignCard from '@Pages/design/_components/_components/design-cards/design-card/TextDesignCard.vue';
    import ImageDesignCard from '@Pages/design/_components/_components/design-cards/design-card/ImageDesignCard.vue';
    import ItemsDesignCard from '@Pages/design/_components/_components/design-cards/design-card/ItemsDesignCard.vue';
    import VideoDesignCard from '@Pages/design/_components/_components/design-cards/design-card/VideoDesignCard.vue';
    import BannerDesignCard from '@Pages/design/_components/_components/design-cards/design-card/BannerDesignCard.vue';
    import OrderSummaryCard from '@Pages/design/_components/_components/design-cards/design-card/OrderSummaryCard.vue';
    import ContactDesignCard from '@Pages/design/_components/_components/design-cards/design-card/ContactDesignCard.vue';
    import SocialsDesignCard from '@Pages/design/_components/_components/design-cards/design-card/SocialsDesignCard.vue';
    import CustomerDesignCard from '@Pages/design/_components/_components/design-cards/design-card/CustomerDesignCard.vue';
    import DeliveryDesignCard from '@Pages/design/_components/_components/design-cards/design-card/DeliveryDesignCard.vue';
    import PaymentMethodsCard from '@Pages/design/_components/_components/design-cards/design-card/PaymentMethodsCard.vue';
    import ProductsDesignCard from '@Pages/design/_components/_components/design-cards/design-card/ProductsDesignCard.vue';
    import DividerDesignCard from '@Pages/design/_components/_components/design-cards/design-card/DividerDesignCard.vue';
    import PromoCodeDesignCard from '@Pages/design/_components/_components/design-cards/design-card/PromoCodeDesignCard.vue';
    import CountdownDesignCard from '@Pages/design/_components/_components/design-cards/design-card/CountdownDesignCard.vue';
    import DataCollectionDesignCard from '@Pages/design/_components/_components/design-cards/design-card/DataCollectionDesignCard.vue';
    import { Eye, EyeOff, Move, Trash, Map, Link, Type, Box, Image, Video, AtSign, Clock, MapPin, Contact, Truck, Pencil, Hexagon, HandCoins, UserRound, ShoppingCart, ReceiptText, CreditCard, TicketPercent, SeparatorHorizontal, Tally1, Tally2, Binary, Calendar, SquareCheck, Megaphone, List, CloudUpload } from 'lucide-vue-next';

    export default {
        inject: ['designState', 'storeState'],
        components: {
            Eye, EyeOff, Move, Trash, Map, Link, Type, Box, Image, Video, AtSign, Clock, MapPin, Contact, Truck, Pencil, Hexagon, HandCoins, UserRound, ShoppingCart, ReceiptText, CreditCard, TicketPercent,SeparatorHorizontal,
            Tally1, Tally2, Binary, Calendar, SquareCheck, Megaphone, List, CloudUpload,
            Pill, Tooltip, draggable: VueDraggableNext, MapDesignCard, LogoDesignCard, TipsDesignCard, LinkDesignCard, TextDesignCard, ImageDesignCard, ItemsDesignCard, VideoDesignCard, BannerDesignCard,
            OrderSummaryCard, ContactDesignCard, SocialsDesignCard, CustomerDesignCard, DeliveryDesignCard, PaymentMethodsCard, ProductsDesignCard,
            DividerDesignCard, PromoCodeDesignCard, CountdownDesignCard, DataCollectionDesignCard
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
            wantsToArrangeDesignCards() {
                return this.designState.wantsToArrangeDesignCards;
            },
        },
        methods: {
            isRequiredDesignCard(designCard) {
                return ['customer', 'items', 'delivery', 'promo code', 'tips', 'order summary', 'payment methods'].includes(designCard.type);
            },
            isDataCollectionField(designCard) {
                return ['short text', 'long text', 'number', 'date', 'checkbox', 'selection', 'location', 'media'].includes(designCard.type);
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
