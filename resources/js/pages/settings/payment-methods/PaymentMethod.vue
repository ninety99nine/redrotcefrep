<template>

    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <!-- Back Button -->
        <Button
            size="xs"
            type="light"
            class="mb-4"
            :leftIcon="MoveLeft"
            :action="navigateToShowPaymentMethods">
            <span>Back</span>
        </Button>

        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">

            <!-- Logo -->
            <div class="flex items-center justify-between border-b border-gray-200 border-dashed pb-4 mb-4">

                <Skeleton v-if="isLoadingStore || isLoadingStorePaymentMethod || isLoadingPaymentMethod" width="w-16" height="h-16" rounded="rounded-full" :shine="true" class="shrink-0"></Skeleton>
                <div v-else class="w-16 h-16 rounded-full overflow-hidden shadow">
                    <img
                        alt="Payment Method Logo"
                        class="h-full object-contain"
                        :src="storePaymentMethodForm?.configs?.logo?.[0]?.path ?? paymentMethod.image_url"
                    />
                </div>

                <Skeleton v-if="isLoadingStore || isLoadingStorePaymentMethod || isLoadingPaymentMethod" width="w-16" height="h-4" rounded="rounded-full" :shine="true" class="shrink-0"></Skeleton>

                <!-- Requires Verification Status -->
                <Pill v-else-if="storePaymentMethod && storePaymentMethod.requires_verification" type="warning" size="xs">Requires verification</Pill>

                <Switch
                    v-else
                    size="xs"
                    prefixText="Active"
                    v-model="storePaymentMethodForm.active"
                    :errorText="formState.getFormError('active')"
                    @change="storePaymentMethodState.saveStateDebounced('Active status changed')"
                />

             </div>

            <div class="space-y-2">

                <!-- Name -->
                <Input
                    max="40"
                    type="text"
                    label="Name"
                    :placeholder="paymentMethod?.name ?? 'Name'"
                    v-model="storePaymentMethodForm.custom_name"
                    :errorText="formState.getFormError('custom_name')"
                    @input="storePaymentMethodState.saveStateDebounced('Name changed')"
                    :skeleton="isLoadingStore || isLoadingStorePaymentMethod || isLoadingPaymentMethod">
                </Input>

                <!-- Configurations -->
                <template v-if="paymentMethod">

                    <template
                        :key="index"
                        v-for="(configSchemaEntity, index) in paymentMethod.config_schema">

                        <template v-if="checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, storePaymentMethodForm.configs)">

                            <StringConfig
                                :configSchemaEntity="configSchemaEntity"
                                v-if="configSchemaEntity.type == 'string'"
                                v-model="storePaymentMethodForm.configs[configSchemaEntity.attribute]"
                                @change="storePaymentMethodState.saveStateDebounced(`${capitalize(configSchemaEntity.attribute)} changed`)">
                            </StringConfig>

                            <MobileNumberConfig
                                :configSchemaEntity="configSchemaEntity"
                                v-else-if="configSchemaEntity.type == 'mobile_number'"
                                v-model="storePaymentMethodForm.configs[configSchemaEntity.attribute]"
                                @change="storePaymentMethodState.saveStateDebounced(`${capitalize(configSchemaEntity.attribute)} changed`)">
                            </MobileNumberConfig>

                            <EmailConfig
                                :configSchemaEntity="configSchemaEntity"
                                v-else-if="configSchemaEntity.type == 'email'"
                                v-model="storePaymentMethodForm.configs[configSchemaEntity.attribute]"
                                @change="storePaymentMethodState.saveStateDebounced(`${capitalize(configSchemaEntity.attribute)} changed`)">
                            </EmailConfig>

                            <SelectConfig
                                :configSchemaEntity="configSchemaEntity"
                                v-else-if="configSchemaEntity.type == 'select'"
                                v-model="storePaymentMethodForm.configs[configSchemaEntity.attribute]"
                                @change="storePaymentMethodState.saveStateDebounced(`${capitalize(configSchemaEntity.attribute)} changed`)">
                            </SelectConfig>

                            <CurrencyConfig
                                :paymentMethod="paymentMethod"
                                :configSchemaEntity="configSchemaEntity"
                                v-else-if="configSchemaEntity.type == 'currency'"
                                v-model="storePaymentMethodForm.configs[configSchemaEntity.attribute]"
                                @change="storePaymentMethodState.saveStateDebounced(`${capitalize(configSchemaEntity.attribute)} changed`)">
                            </CurrencyConfig>

                            <ImageConfig
                                :uploadImages="uploadImages"
                                :configSchemaEntity="configSchemaEntity"
                                v-else-if="configSchemaEntity.type == 'image'"
                                v-model="storePaymentMethodForm.configs[configSchemaEntity.attribute]"
                                @change="storePaymentMethodState.saveStateDebounced(`${capitalize(configSchemaEntity.attribute)} changed`)">
                            </ImageConfig>

                            <ContentConfig
                                :configSchemaEntity="configSchemaEntity"
                                v-else-if="configSchemaEntity.type == 'content'">
                            </ContentConfig>

                            <!-- Validation Error Message -->
                            <p
                                v-if="getPaymentMethodFirstValidationError(configSchemaEntity, storePaymentMethodForm.configs)"
                                class="flex space-x-1 text-xs text-yellow-600 font-semibold bg-yellow-100 border border-yellow-300 p-3 rounded-lg shadow-md">
                                <Info size="14" class="shrink-0"></Info>
                                <span>{{ getPaymentMethodFirstValidationError(configSchemaEntity, storePaymentMethodForm.configs) }}</span>
                            </p>

                        </template>

                    </template>

                </template>

                <div
                    v-if="paymentMethod && !paymentMethod.automated_verification"
                    class="border-t border-gray-200 border-dashed space-y-4 mt-4 pt-4">

                    <!-- Instructions -->
                    <Input
                        rows="2"
                        type="textarea"
                        label="Instruction"
                        secondaryLabel="(optional)"
                        v-model="storePaymentMethodForm.instruction"
                        :errorText="formState.getFormError('instruction')"
                        placeholder="Provide your own instruction about how the customer must pay"
                        @input="storePaymentMethodState.saveStateDebounced('Instruction changed')"
                        :skeleton="isLoadingStore || isLoadingStorePaymentMethod || isLoadingPaymentMethod">
                    </Input>

                    <Input
                        type="checkbox"
                        inputLabel="Require payment proof"
                        v-model="storePaymentMethodForm.require_proof_of_payment"
                        :errorText="formState.getFormError('require_proof_of_payment')"
                        @change="storePaymentMethodState.saveStateDebounced('Require payment proof changed')"
                        inputDescription="To finalize the checkout, customers must upload a screenshot. This could be a receipt, bank transfer confirmation, or any other proof of payment.">
                    </Input>

                    <Input
                        type="checkbox"
                        inputLabel="Enable contact seller before payment"
                        v-model="storePaymentMethodForm.enable_contact_seller_before_payment"
                        :errorText="formState.getFormError('enable_contact_seller_before_payment')"
                        @change="storePaymentMethodState.saveStateDebounced('Enable contact seller before payment changed')"
                        inputDescription="Enables skipping payment to message the seller, increasing inquiries but building trust and reducing cancellations.">
                    </Input>

                    <Input
                        type="checkbox"
                        inputLabel="Mark as paid upon customer confirmation"
                        v-model="storePaymentMethodForm.mark_as_paid_on_customer_confirmation"
                        :errorText="formState.getFormError('mark_as_paid_on_customer_confirmation')"
                        @change="storePaymentMethodState.saveStateDebounced('Mark as paid upon customer confirmation changed')"
                        inputDescription="This will automatically update the payment status to 'Paid' once the customer confirms their payment. This feature is ideal if you trust your customers and wish to streamline the payment process.">
                    </Input>

                </div>

                <div
                    class="space-y-4 border-t border-gray-300 border-dashed mt-4 pt-4"
                    v-if="storePaymentMethod && storePaymentMethod.requires_verification">

                    <Alert
                        type="warning"
                        :dismissable="false"
                        title="Activation Pending">

                        <template #description>

                            <p class="text-xs text-justify">
                                To activate this payment method so that <span class="font-bold">{{ appName }}</span> collects payments on your behalf, our team needs to verify your business. Please contact us via <span @click="openWhatsappGroup" class="font-bold cursor-pointer hover:underline">WhatsApp</span> to provide documents confirming your business's legitimacy and refund policy. Include your Store ID, found below.
                            </p>

                        </template>

                    </Alert>

                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-900">Store ID</p>
                        <Copy :text="store.id"></Copy>
                    </div>

                    <!-- Join WhatsApp Group -->
                    <JoinOurWhatsappGroup :mockMessages="mockMessages"></JoinOurWhatsappGroup>

                </div>

            </div>

        </div>

        <div
            v-if="storePaymentMethod"
            :class="['flex items-center justify-between space-x-4 overflow-hidden rounded-lg p-4 border mt-4', isLoadingStore || isLoadingStorePaymentMethod ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

            <div class="space-y-2">
                <p>Delete <span class="font-bold text-black">{{ storePaymentMethod.custom_name }}</span>?</p>
            </div>

            <div class="flex justify-end">

                <Modal
                    triggerType="danger"
                    approveType="danger"
                    :approveLeftIcon="Trash2"
                    triggerText="Delete Payment Method"
                    approveText="Delete Payment Method"
                    :approveAction="deleteStorePaymentMethod"
                    :triggerLoading="isDeletingStorePaymentMethod"
                    :approveLoading="isDeletingStorePaymentMethod">

                    <template #content>
                        <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                        <p class="mb-8">Are you sure you want to delete <span class="font-bold text-black">{{ storePaymentMethod.custom_name }}</span>?</p>
                    </template>

                </Modal>

            </div>

        </div>

    </div>

</template>

<script>

    import dayjs from 'dayjs';
    import Copy from '@Partials/Copy.vue';
    import Pill from '@Partials/Pill.vue';
    import Modal from '@Partials/Modal.vue';
    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isEmpty, capitalize } from '@Utils/stringUtils.js';
    import { Info, Trash2, MoveLeft, Plus } from 'lucide-vue-next';
    import JoinOurWhatsappGroup from '@Components/JoinOurWhatsappGroup.vue';
    import EmailConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/EmailConfig.vue';
    import ImageConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/ImageConfig.vue';
    import SelectConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/SelectConfig.vue';
    import StringConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/StringConfig.vue';
    import ContentConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/ContentConfig.vue';
    import CurrencyConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/CurrencyConfig.vue';
    import MobileNumberConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/MobileNumberConfig.vue';

    export default {
        inject: ['formState', 'authState', 'storeState', 'storePaymentMethodState', 'changeHistoryState', 'notificationState'],
        components: {
            Copy, Pill, Info, Modal, Alert, Input, Switch, Button, Skeleton, JoinOurWhatsappGroup, EmailConfig,
            ImageConfig, SelectConfig, StringConfig, ContentConfig, CurrencyConfig, MobileNumberConfig
        },
        data() {
            return {
                Plus,
                Trash2,
                MoveLeft,
                isLoadingPaymentMethod: false,
                appName: import.meta.env.VITE_APP_NAME
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
            authUser() {
                return this.authState.user;
            },
            store() {
                return this.storeState.store;
            },
            isEditing() {
                return this.storePaymentMethodId != null;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            paymentMethodId() {
                return this.$route.query.payment_method_id;
            },
            storePaymentMethodId() {
                return this.$route.params.store_payment_method_id;
            },
            paymentMethod() {
                return this.storePaymentMethodState.paymentMethod;
            },
            storePaymentMethod() {
                return this.storePaymentMethodState.storePaymentMethod;
            },
            storePaymentMethodForm() {
                return this.storePaymentMethodState.storePaymentMethodForm;
            },
            isLoadingStorePaymentMethod() {
                return this.storePaymentMethodState.isLoadingStorePaymentMethod;
            },
            isDeletingStorePaymentMethod() {
                return this.storePaymentMethodState.isDeletingStorePaymentMethod;
            },
            mockMessages() {
                return [
                    {
                        sender: 'You',
                        text: `Hello, I would like to activate *${this.storePaymentMethod.custom_name}* for *${this.store.name}* so that we can take payments`,
                        timestamp: dayjs().subtract(7, 'minute').format('HH:mm'),
                        isOwnMessage: true
                    },
                    {
                        sender: 'Support Team',
                        text: `Hi ${this.authUser.first_name || 'there'}! Could you share your company profile and refund policy with us`,
                        timestamp: dayjs().subtract(6, 'minute').format('HH:mm'),
                        isOwnMessage: false,
                        nameColor: '#165dfc'
                    },
                    {
                        sender: 'You',
                        text: 'Here is our company profile 👆',
                        timestamp: dayjs().subtract(5, 'minute').format('HH:mm'),
                        isOwnMessage: true,
                        attachments: [
                            { name: 'CompanyProfile.pdf', pages: 3, size: 950 }
                        ]
                    },
                    {
                        sender: 'You',
                        text: 'Here is our refund policy 👆',
                        timestamp: dayjs().subtract(4, 'minute').format('HH:mm'),
                        isOwnMessage: true,
                        attachments: [
                            { name: 'RefundPolicy.pdf', pages: 1, size: 325 }
                        ]
                    },
                    {
                        sender: 'Support Team',
                        text: `Great! Can you share the store ID from your dashboard`,
                        timestamp: dayjs().subtract(3, 'minute').format('HH:mm'),
                        isOwnMessage: false,
                        nameColor: '#165dfc'
                    },
                    {
                        sender: 'You',
                        text: `It's ${this.store.id}`,
                        timestamp: dayjs().subtract(2, 'minute').format('HH:mm'),
                        isOwnMessage: true
                    },
                    {
                        sender: 'Support Team',
                        text: `*${this.storePaymentMethod.custom_name}* is now activated to take payments for *${this.store.name}* 👏`,
                        timestamp: dayjs().subtract(1, 'minute').format('HH:mm'),
                        isOwnMessage: false,
                        nameColor: '#165dfc'
                    },
                    {
                        sender: 'You',
                        text: 'Thank you 🙏',
                        timestamp: dayjs().format('HH:mm'),
                        isOwnMessage: true
                    },
                ];
            },
        },
        methods: {
            isEmpty,
            capitalize: capitalize,
            setup() {
                this.storePaymentMethodState.setStorePaymentMethodForm(null, null, true);
                if(this.store && this.paymentMethodId) this.showPaymentMethod();
                if(this.store && this.storePaymentMethodId) this.showStorePaymentMethod();
            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    this.isEditing ? 'Save Changes' : 'Add Payment Method',
                    this.isEditing ? this.updateStorePaymentMethod : this.createStorePaymentMethod,
                    'primary',
                    this.isEditing ? null : Plus,
                );
            },
            async navigateToShowPaymentMethods() {
                await this.$router.push({
                    name: 'show-payment-methods',
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            getPaymentMethodFirstValidationError(configSchemaEntity, configs) {
                return this.storePaymentMethodState.getPaymentMethodFirstValidationError(configSchemaEntity, configs);
            },
            checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, configs) {
                return this.storePaymentMethodState.checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, configs);
            },
            async showPaymentMethod() {
                try {

                    this.isLoadingPaymentMethod = true;

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get(`/api/payment-methods/${this.paymentMethodId}`, config);

                    const paymentMethod = response.data;
                    this.storePaymentMethodState.setPaymentMethod(paymentMethod);
                    this.storePaymentMethodState.setStorePaymentMethodForm(null, paymentMethod, true);
                    this.changeHistoryState.showActionButtons = true;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching payment method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch payment method:', error);

                    if (error.response?.status === 404) {
                        await this.navigateToShowPaymentMethods();
                    }

                } finally {
                    this.isLoadingPaymentMethod = false;
                }
            },
            async showStorePaymentMethod() {
                try {

                    this.storePaymentMethodState.isLoadingStorePaymentMethod = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['logo', 'photo', 'paymentMethod'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/store-payment-methods/${this.storePaymentMethodId}`, config);

                    const storePaymentMethod = response.data;
                    const paymentMethod = storePaymentMethod.payment_method;
                    this.storePaymentMethodState.setPaymentMethod(paymentMethod);
                    this.storePaymentMethodState.setStorePaymentMethod(storePaymentMethod);
                    this.storePaymentMethodState.setStorePaymentMethodForm(storePaymentMethod, paymentMethod, true);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching store payment method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch store payment method:', error);

                    if (error.response?.status === 404) {
                        await this.navigateToShowPaymentMethods();
                    }
                } finally {
                    this.storePaymentMethodState.isLoadingStorePaymentMethod = false;
                }
            },
            async createStorePaymentMethod() {

                try {

                    if(this.storePaymentMethodState.isCreatingStorePaymentMethod) return;

                    this.formState.hideFormErrors();

                    if(this.paymentMethod.type == 'other' && this.isEmpty(this.storePaymentMethodForm.custom_name)) {
                        this.formState.setFormError('custom_name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    for (let index = 0; index < this.paymentMethod.config_schema.length; index++) {
                        const configSchemaEntity = this.paymentMethod.config_schema[index];

                        if(this.checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, this.storePaymentMethodForm.configs)) {

                            const error = this.getPaymentMethodFirstValidationError(configSchemaEntity, this.storePaymentMethodForm.configs);

                            if(error) {
                                this.notificationState.showWarningNotification(error);
                                return
                            }

                        }
                    }

                    this.storePaymentMethodState.isCreatingStorePaymentMethod = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        payment_method_id: this.paymentMethod.id,
                        ...this.storePaymentMethodForm,
                        store_id: this.store.id,
                    }

                    const response = await axios.post(`/api/store-payment-methods`, data);
                    const storePaymentMethod = response.data.store_payment_method;

                    this.storePaymentMethodState.setStorePaymentMethod(storePaymentMethod);
                    await this.uploadImages();

                    this.notificationState.showSuccessNotification(`Payment method created`);
                    this.storePaymentMethodState.saveOriginalState('Original payment method');

                    this.navigateToShowPaymentMethods();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating payment method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create payment method:', error);
                } finally {
                    this.storePaymentMethodState.isCreatingStorePaymentMethod = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async updateStorePaymentMethod() {

                try {

                    if(this.storePaymentMethodState.isUpdatingStorePaymentMethod) return;

                    this.formState.hideFormErrors();

                    if(this.isEmpty(this.storePaymentMethodForm.custom_name)) {
                        this.formState.setFormError('custom_name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    for (let index = 0; index < this.paymentMethod.config_schema.length; index++) {
                        const configSchemaEntity = this.paymentMethod.config_schema[index];

                        if(this.checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, this.storePaymentMethodForm.configs)) {

                            const error = this.getPaymentMethodFirstValidationError(configSchemaEntity, this.storePaymentMethodForm.configs);

                            if(error) {
                                this.notificationState.showWarningNotification(error);
                                return
                            }

                        }
                    }

                    this.storePaymentMethodState.isUpdatingStorePaymentMethod = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.storePaymentMethodForm,
                        store_id: this.store.id
                    }

                    const response = await axios.put(`/api/store-payment-methods/${this.storePaymentMethodId}`, data);
                    const storePaymentMethod = response.data.store_payment_method;

                    this.storePaymentMethodState.setStorePaymentMethod(storePaymentMethod);
                    await this.uploadImages();

                    this.notificationState.showSuccessNotification(`Payment method updated`);
                    this.storePaymentMethodState.saveOriginalState('Original payment method');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating payment method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update payment method:', error);
                } finally {
                    this.storePaymentMethodState.isUpdatingStorePaymentMethod = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async deleteStorePaymentMethod(hideModal) {

                try {

                    if(this.storePaymentMethodState.isDeletingStorePaymentMethod) return;

                    this.storePaymentMethodState.isDeletingStorePaymentMethod = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/store-payment-methods/${this.storePaymentMethodId}`, config);

                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500));    //  Wait for modal to close

                    this.notificationState.showSuccessNotification('Payment method deleted');

                    await this.navigateToShowPaymentMethods();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting payment method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete payment method:', error);
                    hideModal();
                } finally {
                    this.storePaymentMethodState.isDeletingStorePaymentMethod = false;
                }

            },
            async uploadImages(photoIndex = null) {

                let photos = this.paymentMethod.config_schema
                        .filter(configSchema => configSchema.type === 'image')
                        .map(configSchema => {
                            if(this.storePaymentMethodForm.configs[configSchema.attribute].length) {
                                return [configSchema.attribute, this.storePaymentMethodForm.configs[configSchema.attribute][0]];
                            }
                            return null;
                        })
                        .filter(photo => photo != null);

                let imageUploadPromises = [];

                for (let index = 0; index < photos.length; index++) {
                    let attribute = photos[index][0];
                    let photo = photos[index][1];

                    if (photo.id == null && photo.uploading == false && (photo.uploaded === null || photo.uploaded === false)) {
                        if (photoIndex == null || photoIndex == index) {
                            imageUploadPromises.push(
                                this.uploadSingleImage(attribute, photo, index)
                            );
                        }
                    }
                }

                if (imageUploadPromises.length === 0) {
                    this.storePaymentMethodState.isUploading = false;
                    return;
                }

                this.storePaymentMethodState.isUploading = true;

                return Promise.allSettled(imageUploadPromises).then((results) => {
                    let failedUploads = results.filter(result => result.status === 'rejected').length;

                    if (failedUploads > 0) {
                        this.notificationState.showWarningNotification(`⚠️ ${failedUploads} image(s) failed to upload. Try again or change the image.`);
                    }

                    this.storePaymentMethodState.isUploading = false;
                });
            },
            async uploadSingleImage(attribute, photo, index, retryCount = 0, error = null) {

                try{

                    if (retryCount > 2) {
                        console.log(`❌ Image ${index + 1} permanently failed after 3 attempts.`);
                        photo.uploaded = false;
                        photo.uploading = false;
                        photo.error_message = error?.response?.data?.message || error?.message || `Upload failed`;

                        return Promise.reject(`Failed after 3 attempts`);
                    }

                    let formData = new FormData();
                    formData.append('file', photo.file_ref);
                    formData.append('store_id', this.store.id);
                    formData.append('mediable_type', 'store payment method');
                    formData.append('mediable_id', this.storePaymentMethod.id);
                    formData.append('upload_folder_name', `store_payment_method_${attribute}`);

                    photo.uploading = true;
                    photo.error_message = null;

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    const response = await axios.post('/api/media-files', formData, config);
                    const mediaFile = response.data.media_file;

                    photo.uploaded = true;
                    photo.uploading = false;
                    photo.id = mediaFile.id;
                    photo.path = mediaFile.path;

                    console.log(`✅ Image ${index + 1} uploaded successfully.`);

                    return response;

                } catch (error) {
                    console.error(`⚠️ Image ${index + 1} upload attempt ${retryCount + 1} failed.`, error);

                    // Don't retry on 504 or timeout error
                    if (error.code === 'ECONNABORTED' || error.response?.status === 504) return;

                    return this.uploadSingleImage(attribute, photo, index, retryCount + 1, error);
                }
            },
            async deletePaymentMethodImage(paymentMethod, attribute) {

                try {

                    if(paymentMethod.configs[attribute].deleting) return;

                    paymentMethod.configs[attribute].deleting = true;

                    let config = {
                        data: {
                            store_id: this.store.id
                        }
                    };

                    await axios.delete(`/api/media-files/${paymentMethod.configs[attribute].id}`, config);

                    paymentMethod.configs[attribute].path = null;
                    paymentMethod.configs[attribute].uploaded = false;

                    console.log(`✅ Successfully deleted ${attribute} for '${paymentMethod.name}'`);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || `Something went wrong while deleting ${paymentMethod.name} ${attribute}`;
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error(`Failed to delete ${attribute} for '${paymentMethod.name}':`, error);
                } finally {
                    paymentMethod.configs[attribute].deleting = false;
                }
            },
            setStorePaymentMethodForm(storePaymentMethodForm) {
                this.storePaymentMethodState.storePaymentMethodForm = storePaymentMethodForm;
            }
        },
        beforeUnmount() {
            this.storePaymentMethodState.reset();
            this.changeHistoryState.showActionButtons = false;
        },
        created() {

            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setStorePaymentMethodForm;
            }

        }
    };

</script>
