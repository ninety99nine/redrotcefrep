<template>

    <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

        <div class="space-y-4">

            <!-- Alias Text Input -->
            <Input
                type="text"
                label="Store Link"
                placeholder="baby-cakes"
                v-model="storeForm.alias"
                :errorText="formState.getFormError('alias')"
                @change="storeState.saveStateDebounced('Store link changed')"
                description="Your store’s shopping link. Custom domains can be set later.">
                <template #prefix>
                    <div class="flex items-center space-x-1 pl-2 rounded-l-md bg-gray-50 text-gray-500 whitespace-nowrap">
                        <Earth size="18" class="mt-0.5"></Earth>
                        <span class="text-sm">perfectorder.shop</span>
                        <span class="text-gray-400 text-sm">/</span>
                    </div>
                </template>
            </Input>

            <div class="space-y-4 p-4 border border-gray-200 rounded-lg">

                <h1 class="flex items-center font-lg font-bold">
                    <WhatsappIcon class="flex-shrink-0 mr-2"></WhatsappIcon>
                    <span>WhatsApp Notifications</span>
                </h1>

                <p class="text-sm text-gray-500">
                    Set the primary WhatsApp number that your store will use
                    to automatically send updates and notifications using
                    <Pill
                        size="xs"
                        type="primary"
                        :showDot="false"
                        :action="navigateToShowWorkflows">workflows</Pill>,
                    ensuring clear and timely communication
                </p>

                <!-- WhatsApp Number Input -->
                <Input
                    type="text"
                    label="WhatsApp Number"
                    placeholder="+26772000001"
                    v-model="storeForm.whatsapp_mobile_number"
                    :errorText="formState.getFormError('whatsapp_mobile_number')"
                    @change="storeState.saveStateDebounced('Whatsapp mobile number changed')"
                    tooltipContent="The Whatsapp mobile number that will be used to send notifications via whatsapp to customers or team members">
                </Input>

            </div>

            <!-- Email Input -->
            <Input
                type="email"
                label="Store Email"
                v-model="storeForm.email"
                :errorText="formState.getFormError('email')"
                :placeholder="`sales@${storeForm.alias || 'example'}.com`"
                @change="storeState.saveStateDebounced('Store email changed')"
                :tooltipContent="'The store email address used to receive updates such as new orders'">
            </Input>

            <!-- SMS Sender Name Input -->
            <Input
                type="text"
                maxLength="11"
                label="SMS Sender Name"
                placeholder="Baby Cakes"
                v-model="storeForm.sms_sender_name"
                description="Must be 11 characters or shorter"
                :errorText="formState.getFormError('sms_sender_name')"
                @change="storeState.saveStateDebounced('SMS sender name changed')"
                tooltipContent="The name that will appear as the sender when your store sends SMS messages to customers (e.g. order updates or notifications).">
                <template #prefix>
                    <MessageCircle size="20" class="text-slate-400 mr-2"></MessageCircle>
                </template>
            </Input>

            <!-- USSD Mobile Number Input -->
            <Input
                type="text"
                label="USSD Number"
                placeholder="+26772000001"
                v-model="storeForm.ussd_mobile_number"
                :errorText="formState.getFormError('ussd_mobile_number')"
                @change="storeState.saveStateDebounced('USSD mobile number changed')"
                tooltipContent="The mobile number that will be used by customers to dial your store, shop and place orders">
            </Input>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import WhatsappIcon from '@Partials/WhatsappIcon.vue';
    import { Earth, MessageCircle } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState'],
        components: {
            Pill, Earth, Input, Switch, Skeleton, MessageCircle, WhatsappIcon
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            storeForm() {
                return this.storeState.storeForm;
            }
        },
        methods: {
            async navigateToShowWorkflows() {
                await this.$router.push({
                    name: 'show-workflows',
                    query: { store_id: this.store.id }
                });
            },
            setAddress(address) {
                this.customerState.storeForm.address = address;
                this.customerState.saveStateDebounced('Address changed');
            },
            unsetAddress() {
                this.customerState.storeForm.address = null;
                this.customerState.saveStateDebounced('Address removed');
            }
        }
    };

</script>
