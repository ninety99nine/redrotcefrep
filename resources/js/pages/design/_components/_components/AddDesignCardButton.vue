<template>

    <Dropdown
        ref="dropdown"
        dropdownClasses="w-full">

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

            <div class="z-20 bg-white rounded-lg shadow-md">
                <div class="grid grid-cols-3 divide-x divide-y divide-gray-200">
                    <div
                        :key="index"
                        v-for="(designCardOption, index) in designCardOptions"
                        @click="(e) => addDesignCard(designCardOption.value, props.toggleDropdown, e)"
                        class="py-4 hover:bg-gray-100 cursor-pointer flex flex-col space-y-4 justify-between items-center">

                        <Map v-if="designCardOption.value == 'map'" size="20"></Map>
                        <Link v-if="designCardOption.value == 'link'" size="20"></Link>
                        <Type v-if="designCardOption.value == 'text'" size="20"></Type>
                        <Box v-if="designCardOption.value == 'products'" size="20"></Box>
                        <Image v-if="designCardOption.value == 'image'" size="20"></Image>
                        <Video v-if="designCardOption.value == 'video'" size="20"></Video>
                        <AtSign v-if="designCardOption.value == 'socials'" size="20"></AtSign>
                        <Clock v-if="designCardOption.value == 'countdown'" size="20"></Clock>
                        <Contact v-if="designCardOption.value == 'contact'" size="20"></Contact>

                        <Truck v-if="designCardOption.value == 'delivery'" size="20"></Truck>
                        <HandCoins v-if="designCardOption.value == 'tips'" size="20"></HandCoins>
                        <UserRound v-if="designCardOption.value == 'customer'" size="20"></UserRound>
                        <ShoppingCart v-if="designCardOption.value == 'items'" size="20"></ShoppingCart>
                        <ReceiptText v-if="designCardOption.value == 'order summary'" size="20"></ReceiptText>
                        <TicketPercent v-if="designCardOption.value == 'promo code'" size="20"></TicketPercent>

                        <span class="text-xs whitespace-nowrap">{{ designCardOption.label }}</span>

                    </div>
                </div>
            </div>

        </template>

    </Dropdown>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import { Plus, Map, Link, Type, Box, Image, Video, AtSign, Clock, Contact, Truck, HandCoins, UserRound, ShoppingCart, ReceiptText, TicketPercent } from 'lucide-vue-next';

    export default {
        inject: ['designState'],
        components: {
            Button, Dropdown, Map, Link, Type, Box, Image, Video, AtSign, Clock, Contact,
            Truck, HandCoins, UserRound, ShoppingCart, ReceiptText, TicketPercent
        },
        data() {
            return {
                Plus
            }
        },
        computed: {
            categories() {
                return this.designState.categories;
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
            addDesignCard(type, toggleDropdown, e) {

                let metadata;

                if(type == 'products') {
                    metadata = {
                        category_id: this.categories.length ? this.categories[0].value : null,
                        layout: 'grid',
                        feature: '4',
                        mode: 'content'
                    };
                }else if(type == 'link') {
                    metadata = {
                        title: '',
                        link: '',
                        mode: 'content'
                    };
                }else if(type == 'text') {

                    let markdown = `# Heading 1\n## Heading 2\n### Heading 3\n**bold**\n~strike~\n_italic_\n**_Bold italic_**`;

                    metadata = {
                        body: markdown,
                        mode: 'content'
                    };

                }else if(type == 'image') {
                    metadata = {
                        link: '',
                        upper_text: '',
                        lower_text: '',
                        mode: 'image',
                        design: {
                            image: {
                                //  image settings
                            },
                            card: {
                                //  card settings
                            }
                        }
                    };
                }else if(type == 'video') {
                    metadata = {
                        title: '',
                        link: '',
                        mode: 'content'
                    };
                }else if(type == 'contact') {
                    metadata = {
                        title: '',
                        mobile_number: '',
                        mode: 'content'
                    };
                }else if(type == 'countdown') {
                    metadata = {
                        date: '',
                        upper_text: '',
                        lower_text: '',
                        mode: 'countdown'
                    };
                }else if(type == 'map') {
                    metadata = {
                        address: null,
                        upper_text: '',
                        lower_text: '',
                        show_address: true,
                        mode: 'map'
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
                        mode: 'content'
                    };
                }else if(type == 'customer') {
                    metadata = {
                        title: 'Customer',
                        show_first_name: true,
                        first_name_required: true,
                        show_last_name: false,
                        last_name_required: false,
                        show_email: false,
                        email_required: false,
                        show_mobile_number: false,
                        mobile_number_required: false,
                    };
                }else if(type == 'items') {
                    metadata = {
                        title: 'Items',
                        show_items: true
                    };
                }else if(type == 'delivery') {
                    metadata = {
                        title: 'Delivery Methods',
                        show_delivery_methods: true,
                        schedule_title: 'Schedule',
                        address_title: 'Address',
                    };
                }else if(type == 'promo code') {
                    metadata = {
                        title: 'Promo code',
                        show_promo_code: true
                    };
                }else if(type == 'tips') {
                    metadata = {
                        title: 'Tip',
                        tips: [],
                        show_tips: true,
                        show_specify_tip: true
                    };
                }else if(type == 'order summary') {
                    metadata = {
                        title: 'Order Summary',
                        checkout_fees: [],
                        combine_fees: false,
                        combine_discounts: false,
                    };
                }

                metadata.type = type;

                let designCard = {
                    visible: true,
                    type: this.type,
                    metadata: metadata,
                };

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
