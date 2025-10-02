<template>

    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <h1 class="text-lg font-bold mb-4">Checkout</h1>

        <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

            <h1 class="text-lg font-bold mb-1">Message channel</h1>

            <p class="text-xs text-gray-500 mb-4">Choose apps to show message button when checkout completed.</p>

            <div class="space-y-4">

                <!-- Whatsapp Channel Toggle Switch -->
                <Skeleton v-if="isLoadingStore || !store" width="w-8" :shine="true"></Skeleton>
                <Switch
                    v-else
                    size="xs"
                    suffixText="Whatsapp"
                    v-model="storeForm.show_whatsapp_channel"
                    :errorText="formState.getFormError('show_whatsapp_channel')"
                    @change="storeState.saveStateDebounced('Whatsapp channel status changed')"
                />

                <!-- Messenger Channel Toggle Switch -->
                <Skeleton v-if="isLoadingStore || !store" width="w-8" :shine="true"></Skeleton>
                <Switch
                    v-else
                    size="xs"
                    suffixText="Messenger"
                    v-model="storeForm.show_messenger_channel"
                    :errorText="formState.getFormError('show_messenger_channel')"
                    @change="storeState.saveStateDebounced('Messenger channel status changed')"
                />

                <!-- Messenger Channel Username Input -->
                <Input
                    type="text"
                    label="Facebook Page username"
                    :placeholder="appName.toLowerCase()"
                    :skeleton="isLoadingStore || !store"
                    v-if="storeForm.show_messenger_channel"
                    v-model="storeForm.messenger_channel_username"
                    :errorText="formState.getFormError('messenger_channel_username')"
                    @change="storeState.saveStateDebounced('Messenger channel username changed')"
                    description="Username below your Facebook Page's name and in your Page's URL (e.g. facebook.com/abc)">

                    <template #prefix>
                        <span class="text-gray-500 mr-2">@</span>
                    </template>
                </Input>

                <!-- Telegram Channel Toggle Switch -->
                <Skeleton v-if="isLoadingStore || !store" width="w-8" :shine="true"></Skeleton>
                <Switch
                    v-else
                    size="xs"
                    suffixText="Telegram"
                    v-model="storeForm.show_telegram_channel"
                    :errorText="formState.getFormError('show_telegram_channel')"
                    @change="storeState.saveStateDebounced('Telegram channel status changed')"
                />

                <!-- Telegram Channel Username Input -->
                <Input
                    type="text"
                    placeholder="username"
                    label="Telegram username"
                    externalLinkName="Learn more"
                    :skeleton="isLoadingStore || !store"
                    v-if="storeForm.show_telegram_channel"
                    description="Your public telegram username"
                    v-model="storeForm.telegram_channel_username"
                    :errorText="formState.getFormError('telegram_channel_username')"
                    @change="storeState.saveStateDebounced('Telegram channel username changed')"
                    externalLinkUrl="https://telegram.org/faq#q-what-are-usernames-how-do-i-get-one">

                    <template #prefix>
                        <span class="text-gray-500 mr-2">@</span>
                    </template>

                </Input>

                <!-- Line Channel Toggle Switch -->
                <Skeleton v-if="isLoadingStore || !store" width="w-8" :shine="true"></Skeleton>
                <Switch
                    v-else
                    size="xs"
                    suffixText="Line"
                    v-model="storeForm.show_line_channel"
                    :errorText="formState.getFormError('show_line_channel')"
                    @change="storeState.saveStateDebounced('Line channel status changed')"
                />

                <!-- Line Channel Username Input -->
                <Input
                    type="text"
                    placeholder="437fdhmq"
                    label="LINE official account ID"
                    v-if="storeForm.show_line_channel"
                    :skeleton="isLoadingStore || !store"
                    v-model="storeForm.line_channel_username"
                    :errorText="formState.getFormError('line_channel_username')"
                    @change="storeState.saveStateDebounced('Line channel username changed')">

                    <template #description>
                        <div class="leading-4">
                            <span class="text-xs text-gray-400">
                                <span>Find or create LINE official account ID from </span>
                                <a
                                    target="_blank"
                                    href="https://manager.line.biz"
                                    class="inline-block text-xs text-blue-700 hover:underline hover:text-blue-90">
                                    LINE Official Account Manager
                                </a>
                                <span> e.g. @437fdhmq or @{{ appName.toLowerCase().replace(' ', '') }} (Premium ID).</span>
                            </span>

                        </div>
                    </template>

                    <template #prefix>
                        <span class="text-gray-500 mr-2">@</span>
                    </template>

                </Input>

                <!-- SMS Channel Toggle Switch -->
                <Skeleton v-if="isLoadingStore || !store" width="w-8" :shine="true"></Skeleton>
                <Switch
                    v-else
                    size="xs"
                    suffixText="Text (SMS/iMessage)"
                    v-model="storeForm.show_sms_channel"
                    :errorText="formState.getFormError('show_sms_channel')"
                    @change="storeState.saveStateDebounced('SMS channel status changed')"
                />

                <!-- Message Footer Textarea -->
                <Input
                    rows="2"
                    type="textarea"
                    label="Message footer"
                    v-model="storeForm.message_footer"
                    :skeleton="isLoadingStore || !store"
                    description="Appears at the end of every message"
                    :errorText="formState.getFormError('message_footer')"
                    @change="storeState.saveStateDebounced('Message footer changed')">
                </Input>

            </div>

        </div>

        <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

            <h1 class="text-lg font-bold mb-4">Order number</h1>

            <div class="space-y-4">

                <div class="grid grid-cols-2 gap-8">

                    <!-- Order Number Prefix Input -->
                    <Input
                        type="text"
                        label="Order number prefix"
                        :skeleton="isLoadingStore || !store"
                        v-model="storeForm.order_number_prefix"
                        :errorText="formState.getFormError('order_number_prefix')"
                        @change="storeState.saveStateDebounced('Order number prefix changed')">
                    </Input>

                    <!-- Order Number Suffix Input -->
                    <Input
                        type="text"
                        label="Order number suffix"
                        :skeleton="isLoadingStore || !store"
                        v-model="storeForm.order_number_suffix"
                        :errorText="formState.getFormError('order_number_suffix')"
                        @change="storeState.saveStateDebounced('Order number suffix changed')">
                    </Input>

                </div>

                <!-- Minimum Numbers Input -->
                <Input
                    type="number"
                    placeholder="2"
                    label="Minimum numbers"
                    :skeleton="isLoadingStore || !store"
                    v-model="storeForm.order_number_padding"
                    :errorText="formState.getFormError('order_number_padding')"
                    @change="storeState.saveStateDebounced('Minimum numbers changed')">
                </Input>

                <div class="flex items-center justify-between border-t border-dashed border-gray-300 pt-4">

                    <span class="text-sm">Next order number: <span class="text-base font-bold">{{ exampleOrderNumber}}</span></span>

                    <Button
                        size="xs"
                        type="light"
                        :leftIcon="RefreshCcw"
                        :action="resetOrderNumberCounter">
                        <span>Reset Order Number</span>
                    </Button>

                </div>

            </div>

        </div>

        <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

            <h1 class="text-lg font-bold mb-4">Invoice</h1>

            <div class="space-y-4">

                <!-- Online Toggle Switch -->
                <Skeleton v-if="isLoadingStore || !store" width="w-8" :shine="true"></Skeleton>
                <Switch
                    v-else
                    size="xs"
                    suffixText="Show logo"
                    v-model="storeForm.invoice_show_logo"
                    :errorText="formState.getFormError('invoice_show_logo')"
                    @change="storeState.saveStateDebounced('Invoice logo status changed')"
                />

                <!-- Online Toggle Switch -->
                <Skeleton v-if="isLoadingStore || !store" width="w-8" :shine="true"></Skeleton>
                <Switch
                    v-else
                    size="xs"
                    suffixText="Show QR code"
                    v-model="storeForm.invoice_show_qr_code"
                    :errorText="formState.getFormError('invoice_show_qr_code')"
                    @change="storeState.saveStateDebounced('Invoice qr code status changed')"
                />

                <!-- Invoice Header Textarea -->
                <Input
                    rows="2"
                    label="Header"
                    type="textarea"
                    v-model="storeForm.invoice_header"
                    :skeleton="isLoadingStore || !store"
                    :errorText="formState.getFormError('invoice_header')"
                    @change="storeState.saveStateDebounced('Invoice header changed')">
                </Input>

                <!-- Invoice Footer Textarea -->
                <Input
                    rows="2"
                    label="Footer"
                    type="textarea"
                    v-model="storeForm.invoice_footer"
                    :skeleton="isLoadingStore || !store"
                    :errorText="formState.getFormError('invoice_footer')"
                    @change="storeState.saveStateDebounced('Invoice footer changed')">
                </Input>

                <!-- Company Name Input -->
                <Input
                    type="text"
                    label="Company name"
                    :skeleton="isLoadingStore || !store"
                    v-model="storeForm.invoice_company_name"
                    :errorText="formState.getFormError('invoice_company_name')"
                    @change="storeState.saveStateDebounced('Invoice company name changed')">
                </Input>

                <!-- Company Email Input -->
                <Input
                    type="email"
                    label="Company email"
                    :skeleton="isLoadingStore || !store"
                    v-model="storeForm.invoice_company_email"
                    :errorText="formState.getFormError('invoice_company_email')"
                    @change="storeState.saveStateDebounced('Invoice company email changed')">
                </Input>

                <!-- Company Mobile Number Input -->
                <Input
                    type="text"
                    label="Company phone"
                    placeholder="+26772000001"
                    :skeleton="isLoadingStore || !store"
                    v-model="storeForm.invoice_company_mobile_number"
                    :errorText="formState.getFormError('invoice_company_mobile_number')"
                    @change="storeState.saveStateDebounced('Invoice company mobile number changed')">
                </Input>

            </div>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';
    import Button from '@Partials/Button.vue';
    import { RefreshCcw } from 'lucide-vue-next';
    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['formState', 'storeState', 'changeHistoryState', 'notificationState'],
        components: {
            Input, Switch, Button, Skeleton
        },
        data() {
            return {
                RefreshCcw,
                appName: import.meta.env.VITE_APP_NAME,
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            storeForm() {
                return this.storeState.storeForm;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            exampleOrderNumber() {
                const prefix = this.storeForm.order_number_prefix || '';
                const suffix = this.storeForm.order_number_suffix || '';
                const padding = parseInt(this.storeForm.order_number_padding, 10) || 0;
                const paddedNumber = (parseInt(this.storeForm.order_number_counter) + 1).toString().padStart(padding, '0');
                return `${prefix}${paddedNumber}${suffix}`;
            }
        },
        methods: {
            setup() {
                if(this.store) {
                    this.storeState.setStoreForm(this.store, true);
                }else{
                    this.storeState.setStoreForm(null, false);
                }
            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    'Save Changes',
                    this.updateStore,
                    'primary',
                    null,
                );
            },
            resetOrderNumberCounter() {
                this.storeForm.order_number_counter = 0
                this.updateStore();
            },
            async updateStore() {

                try {

                    if(this.storeState.isUpdatingStore) return;

                    this.storeState.isUpdatingStore = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.storeForm,
                        store_id: this.store.id
                    }

                    await axios.put(`/api/stores/${this.store.id}`, data);

                    this.notificationState.showSuccessNotification(`Store updated`);
                    this.storeState.saveOriginalState('Original store');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update store:', error);
                } finally {
                    this.storeState.isUpdatingStore = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            setStoreForm(storeForm) {
                this.storeState.storeForm = storeForm;
            }
        },
        created() {

            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setStoreForm;
            }

        }
    };

</script>
