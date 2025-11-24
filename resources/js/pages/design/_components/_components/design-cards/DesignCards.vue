<template>

    <div>

        <p
            v-if="hasDesignCards"
            class="text-center text-xs text-gray-500 my-4">~ click any design card to edit ~</p>

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
                    :key="designCard.id"
                    class="group w-full bg-gray-50 border border-gray-300 rounded-lg relative">

                    <div
                        @click.stop="() => onToggleExpansion(index)"
                        :class="['flex items-center justify-between cursor-pointer shadow p-4 group-hover:bg-gray-100', designCard.expanded ? 'bg-gray-100 rounded-t-lg mb-4' : 'rounded-lg']">

                        <div class="flex items-center space-x-2 text-gray-500">

                            <Map v-if="designCard.type == 'map'" size="16"></Map>
                            <Link v-if="designCard.type == 'link'" size="16"></Link>
                            <Type v-if="designCard.type == 'text'" size="16"></Type>
                            <Box v-if="designCard.type == 'products'" size="16"></Box>
                            <Clock v-if="designCard.type == 'time'" size="16"></Clock>
                            <Image v-if="designCard.type == 'image'" size="16"></Image>
                            <Video v-if="designCard.type == 'video'" size="16"></Video>
                            <Hexagon v-if="designCard.type == 'logo'" size="16"></Hexagon>
                            <AtSign v-if="designCard.type == 'socials'" size="16"></AtSign>
                            <MapPin v-if="designCard.type == 'location'" size="16"></MapPin>
                            <Contact v-if="designCard.type == 'contact'" size="16"></Contact>
                            <Ungroup v-if="designCard.type == 'categories'" size="20"></Ungroup>
                            <Megaphone v-if="designCard.type == 'banner'" size="16"></Megaphone>
                            <Download v-if="designCard.type == 'install_app'" size="16"></Download>
                            <Hourglass v-if="designCard.type == 'countdown'" size="16"></Hourglass>
                            <SeparatorHorizontal v-if="designCard.type == 'divider'" size="20"></SeparatorHorizontal>

                            <Tally1 v-if="designCard.type == 'short answer'" size="20" class="rotate-90 translate-y-2"></Tally1>
                            <Tally2 v-if="designCard.type == 'long answer'" size="20" class="rotate-90 translate-y-1"></Tally2>
                            <Hash v-if="designCard.type == 'number'" size="20"></Hash>
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

                            <span
                                class="text-xs whitespace-nowrap"
                                v-if="['link'].includes(designCard.type) && isNotEmpty(designCard.metadata.title)">
                                {{ designCard.metadata.title }}
                            </span>

                            <span v-else class="text-xs whitespace-nowrap">{{ designCard.type.replace('_', ' ') }}</span>

                        </div>

                        <div class="flex items-center justify-end space-x-4">

                            <div
                                class="flex items-center space-x-0.5"
                                v-if="isRequiredDesignCard(designCard)">

                                <Pill
                                    size="xs"
                                    type="primary">
                                    standard
                                </Pill>

                                <Tooltip
                                    trigger="hover"
                                    v-if="isRequiredDesignCard(designCard)"
                                    content="This design card is a standard design that cannot be hidden or removed.">x
                                </Tooltip>

                            </div>

                            <template v-else>

                                <Pill
                                    size="xs"
                                    type="primary"
                                    :action="() => copyStyles(index)"
                                    class="opacity-0 group-hover:opacity-100">
                                    copy styles
                                </Pill>

                                <Pill
                                    size="xs"
                                    type="success"
                                    v-if="copiedStyles"
                                    :action="() => pasteStyles(index)"
                                    class="opacity-0 group-hover:opacity-100">
                                    paste styles
                                </Pill>

                                <!-- Delete Icon -->
                                <Trash
                                    size="16"
                                    @click.stop="() => removeDesignCard(index)"
                                    class="cursor-pointer text-gray-500 hover:text-red-500 active:text-red-600 active:scale-95 transition-all duration-300">
                                </Trash>

                                <!-- Duplicate Icon -->
                                <Copy
                                    size="16"
                                    @click.stop="() => duplicateDesignCard(index)"
                                    class="cursor-pointer text-gray-500 hover:text-blue-500 active:text-blue-600 active:scale-95 transition-all duration-300">
                                </Copy>

                                <!-- Visibility Icon -->
                                <div
                                    @click.stop="() => toggleVisible(index)"
                                    :class="[designCard.visible ? 'opacity-100': 'opacity-30', 'cursor-pointer text-gray-500 hover:text-blue-500 active:text-blue-600 active:scale-95 transition-all duration-300']">
                                    <Eye v-if="designCard.visible" size="16"></Eye>
                                    <EyeOff v-else size="16"></EyeOff>
                                </div>

                            </template>

                            <!-- Drag & Drop Handle -->
                            <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                        </div>

                    </div>

                    <vue-slide-up-down :active="designCard.expanded">

                        <div v-if="designCard.expanded" class="px-4 pb-4">

                            <LogoDesignCard v-if="designCard.type == 'logo'" :index="index" :designCard="designCard"></LogoDesignCard>
                            <LinkDesignCard v-if="designCard.type == 'link'" :index="index" :designCard="designCard"></LinkDesignCard>
                            <MapDesignCard v-else-if="designCard.type == 'map'" :index="index" :designCard="designCard"></MapDesignCard>
                            <BannerDesignCard v-if="designCard.type == 'banner'" :index="index" :designCard="designCard"></BannerDesignCard>
                            <VideoDesignCard v-else-if="designCard.type == 'video'" :index="index" :designCard="designCard"></VideoDesignCard>
                            <ContactDesignCard v-else-if="designCard.type == 'contact'" :index="index" :designCard="designCard"></ContactDesignCard>
                            <SocialsDesignCard v-else-if="designCard.type == 'socials'" :index="index" :designCard="designCard"></SocialsDesignCard>
                            <DividerDesignCard v-else-if="designCard.type == 'divider'" :index="index" :designCard="designCard"></DividerDesignCard>
                            <CategoriesDesignCard v-if="designCard.type == 'categories'" :index="index" :designCard="designCard"></CategoriesDesignCard>
                            <InstallAppDesignCard v-if="designCard.type == 'install_app'" :index="index" :designCard="designCard"></InstallAppDesignCard>
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

                        </div>

                    </vue-slide-up-down>

                </div>

            </template>

        </draggable>

    </div>

</template>

<script>

    import { v4 as uuidv4 } from 'uuid';
    import Pill from '@Partials/Pill.vue';
    import cloneDeep from 'lodash.clonedeep';
    import Tooltip from '@Partials/Tooltip.vue';
    import VueSlideUpDown from 'vue-slide-up-down';
    import { isNotEmpty } from '@Utils/stringUtils';
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
    import DividerDesignCard from '@Pages/design/_components/_components/design-cards/design-card/DividerDesignCard.vue';
    import SocialsDesignCard from '@Pages/design/_components/_components/design-cards/design-card/SocialsDesignCard.vue';
    import CustomerDesignCard from '@Pages/design/_components/_components/design-cards/design-card/CustomerDesignCard.vue';
    import DeliveryDesignCard from '@Pages/design/_components/_components/design-cards/design-card/DeliveryDesignCard.vue';
    import PaymentMethodsCard from '@Pages/design/_components/_components/design-cards/design-card/PaymentMethodsCard.vue';
    import ProductsDesignCard from '@Pages/design/_components/_components/design-cards/design-card/ProductsDesignCard.vue';
    import PromoCodeDesignCard from '@Pages/design/_components/_components/design-cards/design-card/PromoCodeDesignCard.vue';
    import CountdownDesignCard from '@Pages/design/_components/_components/design-cards/design-card/CountdownDesignCard.vue';
    import InstallAppDesignCard from '@Pages/design/_components/_components/design-cards/design-card/InstallAppDesignCard.vue';
    import CategoriesDesignCard from '@Pages/design/_components/_components/design-cards/design-card/CategoriesDesignCard.vue';
    import DataCollectionDesignCard from '@Pages/design/_components/_components/design-cards/design-card/DataCollectionDesignCard.vue';
    import { Eye, EyeOff, Move, Trash, Map, Link, Copy, Type, Box, Image, Video, AtSign, Clock, MapPin, Contact, Truck, Pencil, Download, Hexagon, HandCoins, UserRound, Hourglass, ShoppingCart, ReceiptText, CreditCard, TicketPercent, SeparatorHorizontal, Tally1, Tally2, Hash, Calendar, SquareCheck, Ungroup, Megaphone, List, CloudUpload } from 'lucide-vue-next';

    export default {
        inject: ['designState', 'storeState'],
        components: {
            Eye, EyeOff, Move, Trash, Map, Link, Copy, Type, Box, Image, Video, AtSign, Clock, MapPin, Contact, Truck, Pencil, Download, Hexagon, HandCoins, UserRound, Hourglass, ShoppingCart, ReceiptText, CreditCard, TicketPercent,SeparatorHorizontal,
            Tally1, Tally2, Hash, Calendar, SquareCheck, Ungroup, Megaphone, List, CloudUpload,
            Pill, Tooltip, VueSlideUpDown, draggable: VueDraggableNext, InstallAppDesignCard, CategoriesDesignCard, MapDesignCard, LogoDesignCard, TipsDesignCard, LinkDesignCard, TextDesignCard, ImageDesignCard, ItemsDesignCard, VideoDesignCard, BannerDesignCard,
            OrderSummaryCard, ContactDesignCard, SocialsDesignCard, CustomerDesignCard, DeliveryDesignCard, PaymentMethodsCard, ProductsDesignCard,
            DividerDesignCard, PromoCodeDesignCard, CountdownDesignCard, DataCollectionDesignCard
        },
        data() {
            return {
                copiedStyles: null,
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
            hasDesignCards() {
                return this.designCards.length > 0;
            }
        },
        methods: {
            isNotEmpty,
            onToggleExpansion(index) {
                for (let i = 0; i < this.designCards.length; i++) {
                    if(i === index) {
                        this.designCards[i].expanded = !this.designCards[i].expanded;
                    }else{
                        this.designCards[i].expanded = false;
                    }
                    this.designCards[i].mode = '1';
                }
            },
            duplicateDesignCard(index) {
                let duplicateDesignCard = cloneDeep(this.designCards[index]);
                duplicateDesignCard.temporary_id = uuidv4();
                delete duplicateDesignCard.id;
                const newIndex = index + 1;

                this.designCards.splice(newIndex, 0, duplicateDesignCard);

                for (let i = 0; i < this.designCards.length; i++) {
                    if(i === newIndex) {
                        this.designCards[i].expanded = true;
                    }else{
                        this.designCards[i].expanded = false;
                    }
                }
            },
            copyStyles(index) {
                if (index >= 0 && index < this.designCards.length) {
                    this.copiedStyles = cloneDeep(this.designCards[index].metadata.design);
                    const storageData = {
                        styles: this.copiedStyles,
                        timestamp: Date.now()
                    };
                    localStorage.setItem('copiedDesignCardStyles', JSON.stringify(storageData));
                }
            },
            pasteStyles(index) {
                const newDesign = {};
                const design = this.designCards[index].metadata.design;

                // Only copy properties that exist in the design
                for (const key in design) {
                    if (Object.prototype.hasOwnProperty.call(this.copiedStyles, key)) {
                        newDesign[key] = this.copiedStyles[key];
                    } else {
                        newDesign[key] = design[key];
                    }
                }

                this.designCards[index].metadata.design = newDesign;
                this.designState.saveStateDebounced('Pasted styles');
            },
            isRequiredDesignCard(designCard) {
                return ['customer', 'items', 'delivery', 'promo code', 'tips', 'order summary', 'payment methods'].includes(designCard.type);
            },
            isDataCollectionField(designCard) {
                return ['short answer', 'long answer', 'number', 'date', 'time', 'checkbox', 'selection', 'location', 'media'].includes(designCard.type);
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
        },
        created() {
            const copiedDesignCardStyles = localStorage.getItem('copiedDesignCardStyles');

            if (copiedDesignCardStyles) {

                const { styles, timestamp } = JSON.parse(copiedDesignCardStyles);
                const fiveMinutes = 5 * 60 * 1000; // 5 minutes in milliseconds

                if (Date.now() - timestamp <= fiveMinutes) {
                    this.copiedStyles = styles;
                } else {
                    this.copiedStyles = null;
                    localStorage.removeItem('copiedDesignCardStyles');
                }

            } else {
                this.copiedStyles = null;
            }
        }
    }
</script>
