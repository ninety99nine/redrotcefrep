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
                                <Image v-if="option == 'image'" size="20"></Image>
                                <Video v-if="option == 'video'" size="20"></Video>
                                <List v-if="option == 'selection'" size="20"></List>
                                <Hexagon v-if="option == 'logo'" size="20"></Hexagon>
                                <Truck v-if="option == 'delivery'" size="20"></Truck>
                                <Binary v-if="option == 'number'" size="20"></Binary>
                                <AtSign v-if="option == 'socials'" size="20"></AtSign>
                                <Clock v-if="option == 'countdown'" size="20"></Clock>
                                <Calendar v-if="option == 'date'" size="20"></Calendar>
                                <MapPin v-if="option == 'location'" size="20"></MapPin>
                                <Contact v-if="option == 'contact'" size="20"></Contact>
                                <HandCoins v-if="option == 'tips'" size="20"></HandCoins>
                                <Megaphone v-if="option == 'banner'" size="20"></Megaphone>
                                <UserRound v-if="option == 'customer'" size="20"></UserRound>
                                <CloudUpload v-if="option == 'media'" size="20"></CloudUpload>
                                <ShoppingCart v-if="option == 'items'" size="20"></ShoppingCart>
                                <SquareCheck v-if="option == 'checkbox'" size="20"></SquareCheck>
                                <ReceiptText v-if="option == 'order summary'" size="20"></ReceiptText>
                                <CreditCard v-if="option == 'payment methods'" size="20"></CreditCard>
                                <TicketPercent v-if="option == 'promo code'" size="20"></TicketPercent>
                                <Tally2 v-if="option == 'long text'" size="20" class="rotate-90"></Tally2>
                                <Tally1 v-if="option == 'short text'" size="20" class="rotate-90"></Tally1>
                                <SeparatorHorizontal v-if="option == 'divider'" size="20"></SeparatorHorizontal>

                                <span class="text-xs whitespace-nowrap">
                                    {{ capitalizeAll(option) }}
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
    import { Plus, Map, Link, Type, Box, Image, Video, AtSign, Clock, Tally1, Tally2, Binary, Calendar, SquareCheck, Megaphone, List, Hexagon, CloudUpload, Contact, Truck, Pencil, MapPin, HandCoins, UserRound, ShoppingCart, ReceiptText, CreditCard, TicketPercent, SeparatorHorizontal } from 'lucide-vue-next';

    export default {
        inject: ['designState'],
        components: {
            Pill, Button, Dropdown, Map, Link, Type, Box, Image, Video, AtSign, Clock, Tally1, Tally2, Binary, Calendar, SquareCheck, Megaphone, List, Hexagon, CloudUpload, Contact,
            Truck, Pencil, MapPin, HandCoins, UserRound, ShoppingCart, ReceiptText, CreditCard, TicketPercent, SeparatorHorizontal
        },
        data() {
            return {
                Plus,
                selectedMenu: 0
            }
        },
        computed: {
            placement() {
                return this.designState.placement;
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
                            description: 'Choose the content shopper must see',
                            options: ['logo', 'products', 'text', 'image', 'video', 'link', 'contact', 'countdown', 'map', 'socials', 'divider', 'banner']
                        }
                    ];

                }else if (this.placement === 'checkout') {

                    return [
                        {
                            title: 'Get Information',
                            description: 'Choose the information the shopper must give',
                            options: ['short text', 'long text', 'number', 'date', 'checkbox', 'selection', 'location', 'media']
                        },
                        {
                            title: 'Show Content',
                            description: 'Choose the content shopper must see',
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

                }else{

                    return [];

                }

            }
        },
        methods: {
            capitalizeAll: capitalizeAll,
            addDesignCard(type, toggleDropdown, e) {

                let metadata;

                if(type == 'logo') {
                    metadata = {
                        alignment: 'center',
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '0',
                            r_margin: '0',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '0',
                            br_border_radius: '0',
                            tr_border_radius: '0',
                            bl_border_radius: '0',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#000000',

                            bg_color: null
                        }
                    };
                }else if(type == 'products') {
                    metadata = {
                        category_id: this.categories.length ? this.categories[0].value : null,
                        layout: 'grid',
                        feature: '4',
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '8',
                            br_border_radius: '8',
                            tr_border_radius: '8',
                            bl_border_radius: '8',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#000000',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            description_color: '#4B5563',
                            product_name_color: '#111827',
                            product_price_color: '#111827',
                            product_cancelled_price_color: '#9CA3AF',
                        }
                    };
                }else if(type == 'link') {
                    metadata = {
                        title: 'Our Blog',
                        link: 'https://www.example.com',
                        mode: 'link',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '1',
                            b_border: '1',
                            l_border: '1',
                            r_border: '1',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            icon_color: '#6B7280',
                            title_color: '#111827',
                        }
                    };
                }else if(type == 'text') {

                    let markdown = `# Heading 1\n## Heading 2\n### Heading 3\n**bold**\n~strike~\n_italic_\n**_Bold italic_**`;

                    metadata = {
                        body: markdown,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '8',
                            br_border_radius: '8',
                            tr_border_radius: '8',
                            bl_border_radius: '8',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#000000',

                            bg_color: '#ffffff',
                            text_color: '#111827',
                        }
                    };

                }else if(type == 'image') {
                    metadata = {
                        link: '',
                        upper_text: '',
                        lower_text: '',
                        mode: 'image',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#000000',

                            bg_color: '#ffffff',
                            text_color: '#111827',
                        }
                    };
                }else if(type == 'video') {
                    metadata = {
                        title: 'Our Video',
                        link: 'https://www.youtube.com/watch?v=eHJnEHyyN1Y',
                        mode: 'video',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#000000',

                            bg_color: '#ffffff',
                            icon_color: '#6B7280',
                            title_color: '#111827',
                        }
                    };
                }else if(type == 'contact') {
                    metadata = {
                        title: 'Contact Us',
                        mobile_number: '+26772000001',
                        mode: 'contact',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '1',
                            b_border: '1',
                            l_border: '1',
                            r_border: '1',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            icon_color: '#6B7280',
                            title_color: '#111827',
                        }
                    };
                }else if(type == 'countdown') {
                    metadata = {
                        date: '',
                        upper_text: '',
                        lower_text: '',
                        mode: 'countdown',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            text_color: '#111827',
                            countdown_text_color: '#111827',
                        }
                    };
                }else if(type == 'map') {
                    metadata = {
                        address: null,
                        upper_text: '',
                        lower_text: '',
                        show_address: true,
                        mode: 'map',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            text_color: '#111827',
                            address_color: '#111827'
                        }
                    };
                }else if(type == 'socials') {
                    let socialPlatforms = [
                        'Whatsapp', 'Facebook', 'Instagram', 'Youtube', 'LinkedIn', 'Tiktok', 'Messenger',
                        'Telegram', 'Snapchat', 'Threads', 'Twitch', 'X'
                    ];

                    metadata = {
                        title: '',
                        show_more: false,
                        platforms: socialPlatforms.map((platform) => ({
                            name: platform,
                            link: ''
                        })),
                        mode: 'socials',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            icon_color: '#6B7280',
                            title_color: '#111827',
                        }
                    };
                }else if(type == 'divider') {
                    metadata = {
                        divider: 'solid',
                        thickness: '1',
                        mode: 'divider',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '0',
                            r_margin: '0',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '0',
                            r_padding: '0',

                            tl_border_radius: '0',
                            br_border_radius: '0',
                            tr_border_radius: '0',
                            bl_border_radius: '0',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: null,
                            divider_color: '#6B7280'
                        }
                    };
                }else if(type == 'banner') {
                    metadata = {
                        title: 'Join Our Whatsapp Group',
                        link: 'https://wa.me',
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '0',
                            r_margin: '0',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '0',
                            br_border_radius: '0',
                            tr_border_radius: '0',
                            bl_border_radius: '0',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#000000',

                            bg_color: '#01a045',
                            text_color: '#ffffff',
                        }
                    };
                }else if(type == 'short text') {
                    metadata = {
                        title: 'Enter a short answer',
                        required: false,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            optional_text_color: '#9CA3AF'
                        }
                    };
                }else if(type == 'long text') {
                    metadata = {
                        title: 'Enter a long answer',
                        required: false,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            optional_text_color: '#9CA3AF'
                        }
                    };
                }else if(type == 'number') {
                    metadata = {
                        title: 'Enter a number',
                        required: false,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            optional_text_color: '#9CA3AF'
                        }
                    };
                }else if(type == 'date') {
                    metadata = {
                        title: 'Select a date',
                        required: false,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            optional_text_color: '#9CA3AF'
                        }
                    };
                }else if(type == 'checkbox') {
                    metadata = {
                        min: '1',
                        max: '2',
                        title: 'Select an option',
                        options: [],
                        required: false,
                        validation: 'not applicable',
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            checkbox_color: '#111827',
                            optional_text_color: '#9CA3AF'
                        }
                    };
                }else if(type == 'selection') {
                    metadata = {
                        title: 'Select an option',
                        options: [],
                        required: false,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            radio_color: '#111827',
                            optional_text_color: '#9CA3AF'
                        }
                    };
                }else if(type == 'media') {
                    metadata = {
                        title: 'Attach an image',
                        required: false,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            media_bg_color: null,
                            title_color: '#111827',
                            media_text_color: '#111827',
                            optional_text_color: '#9CA3AF'
                        }
                    };
                }else if(type == 'location') {
                    metadata = {
                        title: 'Select location',
                        trigger_text: 'Add Address',
                        required: false,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            optional_text_color: '#9CA3AF'
                        }
                    };
                }else if(type == 'customer') {
                    metadata = {
                        title: 'Customer',
                        description: '',
                        show_first_name: true,
                        first_name_required: true,
                        show_last_name: false,
                        last_name_required: false,
                        show_email: false,
                        email_required: false,
                        show_mobile_number: false,
                        mobile_number_required: false,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            description_color: '#111827',
                            optional_text_color: '#9CA3AF'
                        }
                    };
                }else if(type == 'items') {
                    metadata = {
                        title: 'Items',
                        description: '',
                        show_items: true,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            description_color: '#111827'
                        }
                    };
                }else if(type == 'delivery') {
                    metadata = {
                        title: 'Delivery Methods',
                        description: '',
                        show_delivery_methods: true,
                        schedule_title: 'Schedule',
                        address_title: 'Address',
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            description_color: '#111827'
                        }
                    };
                }else if(type == 'promo code') {
                    metadata = {
                        title: 'Promo code',
                        description: '',
                        show_promo_code: true,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            description_color: '#111827'
                        }
                    };
                }else if(type == 'tips') {
                    metadata = {
                        title: 'Tip',
                        description: '',
                        tips: ['5', '10'],
                        show_tips: true,
                        show_specify_tip: true,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            pill_bg_color: '#DBEAFE',
                            pill_text_color: '#1E40AF',
                            description_color: '#111827',
                        }
                    };
                }else if(type == 'order summary') {
                    metadata = {
                        title: 'Order Summary',
                        description: '',
                        checkout_fees: [],
                        combine_fees: false,
                        combine_discounts: false,
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: '#ffffff',
                            title_color: '#111827',
                            description_color: '#111827'
                        }
                    };
                }else if(type == 'payment methods') {
                    metadata = {
                        title: 'Complete Your Payment',
                        subtitle: 'Amount to pay',
                        mode: 'content',
                        design: {
                            t_margin: '0',
                            b_margin: '8',
                            l_margin: '4',
                            r_margin: '4',

                            t_padding: '16',
                            b_padding: '16',
                            l_padding: '16',
                            r_padding: '16',

                            tl_border_radius: '16',
                            br_border_radius: '16',
                            tr_border_radius: '16',
                            bl_border_radius: '16',

                            t_border: '0',
                            b_border: '0',
                            l_border: '0',
                            r_border: '0',
                            border_color: '#E5E7EB',

                            bg_color: null,
                            title_color: '#111827',
                            subtitle_color: '#111827',
                            amount_color: '#111827'
                        }
                    };
                }

                let designCard = {
                    type: type,
                    visible: true,
                    metadata: metadata,
                    temporary_id: uuidv4()
                };

                if(['map'].includes(type)) {
                    designCard.address = null;
                }

                if(['image', 'countdown'].includes(type)) {
                    designCard.photos = [];
                }

                this.designCards.unshift(designCard);
                this.designState.saveStateDebounced('Design card added');
                toggleDropdown(e);
            }
        }
    }
</script>
