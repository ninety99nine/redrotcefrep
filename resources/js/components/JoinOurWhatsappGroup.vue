<template>

    <div class="bg-white p-4 shadow-sm rounded-xl">

        <div class="flex items-end justify-between mb-4">

            <div>
                <h2 class="font-semibold mb-2">{{ title }}</h2>
                <h3 class="text-sm text-gray-500">{{ subtitle }}</h3>
            </div>

            <Button
                size="lg"
                type="success"
                :action="openWhatsappGroup">
                <div class="flex items-center space-x-1">
                    <WhatsappIcon size="w-4 h-4" color="#ffffff" class="mx-2"></WhatsappIcon>
                    <span class="text-xs">{{ buttonText }}</span>
                </div>
            </Button>

        </div>

        <!-- Render animated messages -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <WhatsappMessage :messages="mockMessages" :animate="true" :loopAnimation="true" class="h-80" />
        </div>

    </div>

</template>

<script>

import dayjs from 'dayjs';
import Button from '@Partials/Button.vue';
import WhatsappIcon from '@Partials/WhatsappIcon.vue';
import WhatsappMessage from '@Partials/WhatsappMessage.vue';

export default {
    components: {
        Button, WhatsappIcon, WhatsappMessage
    },
    props: {
        title: {
            type: String,
            default: 'Join Our Whatsapp Group'
        },
        subtitle: {
            type: String,
            default: 'Ask questions, get help and grow your business 24/7'
        },
        buttonText: {
            type: String,
            default: 'Join Our Whatsapp'
        },
        mockMessages: {
            type: Array,
            default: () => [
                {
                    sender: 'Support Team',
                    text: 'Hello everyone! Our support team is here to help with your e-commerce platform questions. Feel free to ask! 😊', timestamp: dayjs().subtract(6, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                },
                {
                    sender: 'You', text: 'Hi, how do I add a discount code to my store?', timestamp: dayjs().subtract(5, 'minute').format('HH:mm'),
                    isOwnMessage: true
                },
                {
                    sender: 'Support Team', text: 'Great question! In your dashboard, go to *Settings > Discounts*, click *Create Discount*, and set the code, amount, and conditions. Check [this guide](https://yourstore.com/docs/discounts) for details.', timestamp: dayjs().subtract(4, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                },
                {
                    sender: 'Emma', text: 'I’m struggling with setting up shipping options. Any tips?', timestamp: dayjs().subtract(3, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#dc16fc'
                },
                {
                    sender: 'Support Team', text: 'Hi Emma! Navigate to *Settings > Shipping*, and add zones with rates. You can set flat rates or weight-based shipping. See [Shipping Setup](https://yourstore.com/docs/shipping) for a step-by-step. Let us know if you need more help!', timestamp: dayjs().subtract(2, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                },
                {
                    sender: 'You', text: 'Thanks! Can I also track my store’s analytics?', timestamp: dayjs().subtract(1, 'minute').format('HH:mm'),
                    isOwnMessage: true
                },
                {
                    sender: 'Support Team', text: 'Absolutely! Go to *Analytics* in your dashboard, select a date range, and view total views, orders, and sales. Try it out and let us know if you have questions!', timestamp: dayjs().format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                }
            ]
        }
    },
    data() {
        return {
            whatsappGroupLink: import.meta.env.VITE_WHATSAPP_GROUP_LINK
        };
    },
    methods: {
        openWhatsappGroup() {
            window.open(this.whatsappGroupLink, "_blank");
        }
    }
};
</script>
