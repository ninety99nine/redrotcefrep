<template>

    <Dropdown
        ref="dropdown"
        class="w-full">

        <template #trigger="props">

            <Button
                size="md"
                type="light"
                class="w-full"
                :leftIcon="Plus"
                buttonClass="w-full"
                :action="props.toggleDropdown">
                <span>Add Design</span>
            </Button>

        </template>

        <template #content="props">

            <div class="z-20 bg-white rounded-lg shadow-md border-4 border-gray-100 overflow-hidden">

                <div
                    v-if="designCardCategories.length >= 2"
                    class="flex items-center justify-center space-x-2 bg-white p-2">

                    <Pill
                        size="sm"
                        :key="index"
                        :action="() => selectedMenu = index"
                        :type="selectedMenu == index ? 'primary' : 'light'"
                        v-for="(designCardCategory, index) in designCardCategories">
                        {{ designCardCategory.title }}
                    </Pill>

                </div>

                <p class="text-center text-xs text-black p-2 bg-gray-100 shadow">{{ designCardCategories[selectedMenu].description }}</p>

                <template
                    :key="index"
                    v-for="(designCardCategory, index) in designCardCategories">

                        <div
                            v-if="selectedMenu == index"
                            class="grid grid-cols-3 divide-x divide-y divide-gray-200">

                            <div
                                :key="index2"
                                v-for="(option, index2) in designCardCategory.options"
                                @click="(e) => addDesignCard(option, props.toggleDropdown, e)"
                                class="py-4 hover:bg-gray-100 cursor-pointer flex flex-col space-y-4 justify-between items-center">

                                <Map v-if="option == 'map'" size="20"></Map>
                                <Link v-if="option == 'link'" size="20"></Link>
                                <Type v-if="option == 'text'" size="20"></Type>
                                <Box v-if="option == 'products'" size="20"></Box>
                                <Clock v-if="option == 'time'" size="20"></Clock>
                                <Image v-if="option == 'image'" size="20"></Image>
                                <Video v-if="option == 'video'" size="20"></Video>
                                <List v-if="option == 'selection'" size="20"></List>
                                <Hexagon v-if="option == 'logo'" size="20"></Hexagon>
                                <Truck v-if="option == 'delivery'" size="20"></Truck>
                                <Hash v-if="option == 'number'" size="20"></Hash>
                                <AtSign v-if="option == 'socials'" size="20"></AtSign>
                                <Calendar v-if="option == 'date'" size="20"></Calendar>
                                <MapPin v-if="option == 'location'" size="20"></MapPin>
                                <Contact v-if="option == 'contact'" size="20"></Contact>
                                <HandCoins v-if="option == 'tips'" size="20"></HandCoins>
                                <Ungroup v-if="option == 'categories'" size="20"></Ungroup>
                                <Megaphone v-if="option == 'banner'" size="20"></Megaphone>
                                <UserRound v-if="option == 'customer'" size="20"></UserRound>
                                <CloudUpload v-if="option == 'media'" size="20"></CloudUpload>
                                <Hourglass v-if="option == 'countdown'" size="20"></Hourglass>
                                <Download v-if="option == 'install_app'" size="20"></Download>
                                <ShoppingCart v-if="option == 'items'" size="20"></ShoppingCart>
                                <SquareCheck v-if="option == 'checkbox'" size="20"></SquareCheck>
                                <ReceiptText v-if="option == 'order summary'" size="20"></ReceiptText>
                                <CreditCard v-if="option == 'payment methods'" size="20"></CreditCard>
                                <TicketPercent v-if="option == 'promo code'" size="20"></TicketPercent>
                                <Tally2 v-if="option == 'long answer'" size="20" class="rotate-90"></Tally2>
                                <Tally1 v-if="option == 'short answer'" size="20" class="rotate-90"></Tally1>
                                <SeparatorHorizontal v-if="option == 'divider'" size="20"></SeparatorHorizontal>

                                <span class="text-xs whitespace-nowrap">
                                    {{ capitalizeAll(option.replace('_', ' ')) }}
                                </span>

                            </div>

                        </div>

                 </template>

            </div>
        </template>

    </Dropdown>

</template>

<script>

    import { v4 as uuidv4 } from 'uuid';
    import Pill from '@Partials/Pill.vue';
    import Button from '@Partials/Button.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import { capitalizeAll } from '@Utils/stringUtils.js';
    import { Plus, Map, Link, Type, Box, Image, Video, AtSign, Clock, Tally1, Tally2, Hash, Download, Calendar, SquareCheck, Ungroup, Megaphone, List, Hexagon, CloudUpload, Contact, Truck, Pencil, MapPin, HandCoins, UserRound, Hourglass, ShoppingCart, ReceiptText, CreditCard, TicketPercent, SeparatorHorizontal } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'designState', 'notificationState'],
        components: {
            Pill, Button, Dropdown, Map, Link, Type, Box, Image, Video, AtSign, Clock, Tally1, Tally2, Hash, Download, Calendar, SquareCheck, Ungroup, Megaphone, List, Hexagon, CloudUpload, Contact,
            Truck, Pencil, MapPin, HandCoins, UserRound, Hourglass, ShoppingCart, ReceiptText, CreditCard, TicketPercent, SeparatorHorizontal
        },
        props: {
            placement: {
                type: String
            }
        },
        data() {
            return {
                Plus,
                selectedMenu: 0,
                designCardConfigurations: [],
                isLoadingDesignCardConfigurations: false,
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            categories() {
                return this.designState.categories;
            },
            designForm() {
                return this.designState.designForm;
            },
            designCards() {
                return this.designForm.design_cards;
            },
            designCardCategories() {

                if(this.placement === 'storefront') {

                    return [
                        {
                            description: 'Choose the content the shopper must see',
                            options: ['logo', 'products', 'text', 'image', 'video', 'link', 'contact', 'countdown', 'map', 'socials', 'divider', 'banner', 'install_app']
                        }
                    ];

                }else if (this.placement === 'checkout') {

                    return [
                        {
                            title: 'Get Information',
                            description: 'Choose the information the shopper must give',
                            options: ['short answer', 'long answer', 'number', 'date', 'time', 'checkbox', 'selection', 'location', 'media']
                        },
                        {
                            title: 'Show Content',
                            description: 'Choose the content the shopper must see',
                            options: [
                                'logo', 'products', 'text', 'image', 'video', 'link', 'contact', 'countdown', 'map', 'socials', 'divider', 'banner',
                                /* 'customer', 'items', 'delivery', 'promo code', 'tips', 'order summary' */
                            ]
                        }
                    ];

                }else if (this.placement === 'payment') {

                    return [
                        {
                            description: 'Choose what the shopper must see',
                            options: [
                                'logo', 'products', 'text', 'image', 'video', 'link', 'contact', 'countdown', 'map', 'socials', 'divider',
                                'payment methods'
                            ]
                        }
                    ];

                }else if(this.placement === 'menu') {

                    return [
                        {
                            description: 'Choose the content the shopper must see',
                            options: ['logo', 'text', 'image', 'video', 'link', 'contact', 'socials', 'divider', 'banner', 'categories', 'install_app']
                        }
                    ];

                }else{

                    return [];

                }

            }
        },
        methods: {
            capitalizeAll: capitalizeAll,
            addDesignCard(type, toggleDropdown, e) {

                let designCardConfiguration = this.designCardConfigurations.find(designCardConfiguration => designCardConfiguration.type == type);

                const  designCard = {
                    mode: '1',
                    visible: true,
                    expanded: true,
                    temporary_id: uuidv4(),
                    ...designCardConfiguration
                }

                this.designCards.unshift(designCard);

                for (let i = 0; i < this.designCards.length; i++) {
                    if(i === 0) {
                        this.designCards[i].expanded = true;
                    }else{
                        this.designCards[i].expanded = false;
                    }
                }

                this.designState.saveStateDebounced('Design card added');
                toggleDropdown(e);
            },
            async showDesignCardConfigurations() {
                try {

                    this.isLoadingDesignCardConfigurations = true;

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get('/api/design-cards/configurations', config);
                    this.designCardConfigurations = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching design card options';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch design card options:', error);
                } finally {
                    this.isLoadingDesignCardConfigurations = false;
                }
            },
        },
        created() {
            this.showDesignCardConfigurations();
        }
    }
</script>
