<template>

    <div class="min-h-screen flex flex-col items-center pt-20 pb-40">

        <div class="w-full max-w-lg">

            <!-- Store Logo -->
            <StoreLogo :showButton="false" class="flex justify-center mb-4"></StoreLogo>

            <!-- Heading -->
            <h2 class="text-2xl font-semibold text-center mb-2">Add Payment Methods</h2>

            <!-- Instruction -->
            <p class="text-gray-500 text-center mb-6">Make it easy for customers to pay! Pick the payment options you’d like to accept</p>

        </div>

        <div class="w-full max-w-lg">

            <!-- Payment Methods -->
            <div class="space-y-3 mb-4">

                <div
                    :key="paymentMethodIndex"
                    v-for="(paymentMethod, paymentMethodIndex) in paymentMethods"
                    class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                    <div class="flex justify-between items-center">

                        <div class="flex items-center space-x-2 font-bold">

                            <!-- Logo -->
                            <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center">
                                <img
                                    alt="Payment Method Logo"
                                    class="h-full object-contain"
                                    :src="paymentMethod.configs.logo && paymentMethod.configs.logo.file_path ? paymentMethod.configs.logo.file_path : paymentMethod.logo"
                                />
                            </div>

                            <!-- Name -->
                            <span class="text-sm">{{ paymentMethod.type == 'other' && paymentMethod.custom_name ? paymentMethod.custom_name : paymentMethod.name }}</span>

                        </div>

                        <!-- Active Toogle Switch -->
                        <Switch
                            size="md"
                            v-model="paymentMethod.active">
                        </Switch>

                    </div>

                    <!-- Configurations -->
                    <PaymentMethodConfigInputs
                        :paymentMethod="paymentMethod"
                        :paymentMethodIndex="paymentMethodIndex"
                        :deletePaymentMethodImage="deletePaymentMethodImage"
                        :uploadSinglePaymentMethodImage="uploadSinglePaymentMethodImage"
                        :getPaymentMethodValidationErrors="getPaymentMethodValidationErrors"
                        :getPaymentMethodFirstValidationError="getPaymentMethodFirstValidationError"
                        :checkIfPaymentMethodConfigSchemaEntityPassesCondition="checkIfPaymentMethodConfigSchemaEntityPassesCondition">
                    </PaymentMethodConfigInputs>

                </div>

            </div>

            <!-- Validation Error Messages -->
            <div
                v-if="hasPaymentMethodValidationErrors"
                class="list-disc text-xs text-yellow-600 bg-yellow-100 border border-yellow-300 py-3 px-4 rounded-lg shadow-md mb-4">

                <!-- Heading -->
                <p class="text-lg font-semibold mb-2">Resolve these to continue</p>

                <!-- Errors -->
                <ul class="space-y-1">
                    <li
                        :key="index"
                        v-for="(paymentMethodValidationError, index) in paymentMethodValidationErrors">
                        {{ paymentMethodValidationError }}
                    </li>
                </ul>

            </div>

            <!-- Total Supported Payment Methods -->
            <div
                v-else
                class="text-blue-600 bg-blue-100 border border-blue-300 p-3 rounded-lg shadow-md mb-4">

                <div class="flex items-center space-x-2">
                    <Banknote size="36"></Banknote>
                    <div>
                        <p v-if="hasSelectedPaymentMethods" class="text-sm font-semibold">{{ store.name }} supports <span class="font-bold">{{ totalSelectedPaymentMethods }} {{ totalSelectedPaymentMethods == 1 ? 'payment method' : 'payment methods' }}</span></p>
                        <p :class="[hasSelectedPaymentMethods ? 'text-xs' : 'text-sm font-semibold']">You can always {{ hasSelectedPaymentMethods ? 'add more' : 'add' }} payment options later! 😊</p>
                    </div>
                </div>
            </div>

            <!-- Global Upload Progress Bar -->
            <div v-if="isSubmittingPaymentMethods || isUploading || (progressPercentage === 100)" class="mb-4">
                <div class="w-full max-w-lg bg-gray-200 rounded-full h-2 mb-2">
                    <div
                        class="h-2 rounded-full transition-all duration-500"
                        :class="progressPercentage === 100 ? 'bg-green-500' : 'bg-blue-500'"
                        :style="{ width: progressPercentage + '%' }">
                    </div>
                </div>
                <p class="text-xs text-center mt-2 font-bold">
                    {{ progressPercentage === 100 ? 'Payment Methods Ready 🎉' : `${uploadMessage} (${progressPercentage}%)` }}
                </p>
            </div>

            <div
                v-if="hasFailedUploads"
                class="text-xs text-blue-600 font-semibold bg-blue-100 border border-blue-300 p-3 rounded-lg shadow-md mb-4">

                <div class="flex items-center space-x-2">
                    <CloudUpload size="36"></CloudUpload>
                    <div>
                        <p>
                            We could not upload
                            <span v-if="totalFailedUploads">
                                {{ totalFailedUploads }} {{ totalFailedUploads == 1 ? 'photo' : 'photos' }}.
                            </span>
                        </p>
                        <p>But no worries—you can always add more later! 😊</p>
                    </div>
                </div>

                <div class="mt-2 p-2 border-t border-dotted border-blue-300">
                    <ul class="font-normal space-y-1">
                        <li>✅ Make sure you’re uploading images (jpeg, jpg, png, gif or svg).</li>
                        <li>✅ Ensure your images are not too large (we accept up to 5MB).</li>
                        <li>✅ Use
                            <a href="https://tinypng.com/" target="_blank" class="underline inline-flex items-center">
                                tinypng.com
                                <ExternalLink size="10" class="ml-1"></ExternalLink>
                            </a>
                            to reduce image size (it’s free).
                        </li>
                    </ul>
                </div>

            </div>

            <div class="flex justify-end">

                <transition name="fade-1" mode="out-in">

                    <!-- Continue -->
                    <Button
                        size="md"
                        type="primary"
                        buttonClass="w-full"
                        :action="submitPaymentMethods"
                        :loading="isSubmittingPaymentMethods"
                        v-if="hasSelectedPaymentMethods || hasAssociatedPaymentMethods"
                        :disabled="isLoadingAssociatedPaymentMethods || isLoadingUnassociatedPaymentMethods || isSubmittingPaymentMethods || hasPaymentMethodValidationErrors">
                        <span>Continue</span>
                    </Button>

                    <!-- Skip -->
                    <Button
                        v-else
                        size="md"
                        type="light"
                        rightIconSize="24"
                        buttonClass="w-full"
                        :rightIcon="MoveRight"
                        :action="navigateToAddSocials">
                        <span>Skip</span>
                    </Button>

                </transition>

            </div>

        </div>

    </div>

</template>

<script>

    import isEqual from 'lodash/isEqual';
    import cloneDeep from 'lodash/cloneDeep';
    import Button from '@Partials/Button.vue';
    import Switch from '@Partials/Switch.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
    import { MoveRight, Banknote, CloudUpload, ExternalLink } from 'lucide-vue-next';
    import PaymentMethodConfigInputs from '@Pages/store-onboarding/steps/add-payments/PaymentMethodConfigInputs/Index.vue';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: { Button, Switch, StoreLogo, MoveRight, Banknote, CloudUpload, ExternalLink, PaymentMethodConfigInputs },
        data() {
            return {
                MoveRight,
                uploadMessage: '',
                paymentMethods: [],
                isUploading: false,
                totalCompletedSteps: 0,
                totalCompletedUploads: 0,
                uploadsFailedBefore: false,
                originalPaymentMethods: [],
                associatedPaymentMethods: [],
                unassociatedPaymentMethods: [],
                isSubmittingPaymentMethods: false,
                isLoadingAssociatedPaymentMethods: false,
                isLoadingUnassociatedPaymentMethods: false,
            };
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            totalUploads() {

                return this.changedPaymentMethods.reduce((sum, paymentMethod) => {

                    return sum + paymentMethod.config_schema.filter(configSchemaEntity => {
                        return paymentMethod.configs[configSchemaEntity.attribute] &&
                            paymentMethod.configs[configSchemaEntity.attribute].file_path &&
                            paymentMethod.configs[configSchemaEntity.attribute].hasOwnProperty('uploaded');
                    }).length;

                }, 0);

            },
            totalFailedUploads() {
                return this.changedPaymentMethods.reduce((sum, paymentMethod) => {
                    return sum + paymentMethod.config_schema.filter(config_schema_entity => {
                        const config = paymentMethod.configs[config_schema_entity.attribute];

                        // Ensure config exists, has a file_path, and was NOT uploaded successfully
                        return config?.file_path?.startsWith("blob:") && config.uploaded === false;
                    }).length;
                }, 0);
            },
            totalCompletionSteps() {
                return this.changedPaymentMethods.filter((paymentMethod) => {

                    if(paymentMethod.store_payment_method_id) {
                        return true;
                    }else if(paymentMethod.active) {
                        return true;
                    }

                    return false;

                }).length + this.totalUploads;
            },
            progressPercentage() {
                return this.totalCompletionSteps === 0 ? 0 : Math.round((this.totalCompletedSteps / this.totalCompletionSteps) * 100);
            },
            hasFailedUploads() {
                return this.totalFailedUploads > 0;
            },
            changedPaymentMethods() {
                return this.paymentMethods.filter((paymentMethod, index) => {
                    return !isEqual(paymentMethod, this.originalPaymentMethods[index]);
                });
            },
            hasChangedPaymentMethods() {
                return this.changedPaymentMethods.length > 0;
            },
            hasSelectedPaymentMethods() {
                return this.totalSelectedPaymentMethods > 0;
            },
            hasAssociatedPaymentMethods() {
                return this.associatedPaymentMethods.length > 0;
            },
            totalSelectedPaymentMethods() {
                return this.paymentMethods.filter((paymentMethod) => paymentMethod.active).length;
            },
            paymentMethodValidationErrors() {
                return this.paymentMethods
                    .filter(paymentMethod => paymentMethod.active)
                    .flatMap(paymentMethod =>
                        paymentMethod.config_schema
                            .filter(config_schema_entity => {
                                return this.checkIfPaymentMethodConfigSchemaEntityPassesCondition(config_schema_entity, paymentMethod.configs);
                            })
                            .map(config_schema_entity => {
                                return this.getPaymentMethodFirstValidationError(config_schema_entity, paymentMethod.configs);
                            })
                            .filter(error => error !== null)
                    );
            },
            hasPaymentMethodValidationErrors() {
                return this.paymentMethodValidationErrors.length > 0;
            },
        },
        methods: {
            reset() {
                this.pagination = null;
                this.uploadMessage = '';
                this.paymentMethods = [];
                this.isUploading = false;
                this.totalCompletedSteps = 0;
                this.totalCompletedUploads = 0;
                this.originalPaymentMethods = [];
                this.associatedPaymentMethods = [];
                this.unassociatedPaymentMethods = [];
                this.isSubmittingPaymentMethods = false;
                this.isLoadingUnassociatedPaymentMethods = false;
            },
            async showAssociatedPaymentMethods() {

                try {

                    this.isLoadingAssociatedPaymentMethods = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: 'paymentMethod,logo,photo'
                        }
                    };

                    const response = await axios.get('/api/store-payment-methods', config);

                    this.pagination = response.data;
                    this.associatedPaymentMethods = this.pagination.data.map((storePaymentMethod) => {

                        const logo = storePaymentMethod.logo;
                        const photo = storePaymentMethod.photo;
                        const paymentMethod = storePaymentMethod.payment_method;

                        // Create initial config object from config_schema
                        let configs = Object.fromEntries(
                            paymentMethod.config_schema
                                .filter(config_schema => !['content'].includes(config_schema.type))
                                .map(config_schema => [
                                    config_schema.attribute,
                                    config_schema.default ?? null
                                ])
                        );

                        // Merge existing configs from storePaymentMethod
                        configs = { ...configs, ...(storePaymentMethod.configs ?? {}) };

                        let result = {
                            configs: configs,
                            id: paymentMethod.id,
                            name: paymentMethod.name,
                            type: paymentMethod.type,
                            logo: paymentMethod.image_url,
                            active: storePaymentMethod.active,
                            currencies: paymentMethod.currencies,
                            config_schema: paymentMethod.config_schema,
                            custom_name: storePaymentMethod.custom_name,
                            store_payment_method_id: storePaymentMethod.id
                        };

                        // Iterate through the config_schema and handle image type
                        paymentMethod.config_schema.forEach(config_schema_entity => {

                            if (config_schema_entity.type === 'image' && config_schema_entity.attribute == 'logo') {

                                result.configs[config_schema_entity.attribute] = {
                                    id: logo?.id,  //  Not available when the logo has been deleted
                                    deleting: false,
                                    file_path: logo?.file_path  //  Not available when the logo has been deleted
                                };

                            }else if (config_schema_entity.type === 'image' && config_schema_entity.attribute == 'photo') {

                                result.configs[config_schema_entity.attribute] = {
                                    id: photo?.id,  //  Not available when the photo has been deleted
                                    deleting: false,
                                    file_path: photo?.file_path  //  Not available when the photo has been deleted
                                };

                            }

                        });

                        return result;
                    });

                    this.setPaymentMethods();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching payment methods';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch payment methods:', error);
                } finally {
                    this.isLoadingAssociatedPaymentMethods = false;
                }
            },
            async showUnassociatedPaymentMethods() {

                try {

                    //  Start loader
                    this.isLoadingUnassociatedPaymentMethods = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            association: 'unassociated'
                        }
                    };

                    const response = await axios.get('/api/payment-methods', config);

                    this.pagination = response.data;
                    this.unassociatedPaymentMethods = this.pagination.data.map((paymentMethod) => {

                        let result = {
                            active: false,
                            id: paymentMethod.id,
                            name: paymentMethod.name,
                            type: paymentMethod.type,
                            logo: paymentMethod.image_url,
                            custom_name: paymentMethod.name,
                            currencies: paymentMethod.currencies,
                            config_schema: paymentMethod.config_schema,
                            configs: Object.fromEntries(
                                paymentMethod.config_schema
                                    .filter(config_schema => !['content'].includes(config_schema.type))
                                    .map(config_schema => [
                                        config_schema.attribute,
                                        config_schema.default ?? null
                                    ])
                            )
                        }

                        // Iterate through the config_schema and handle image type
                        paymentMethod.config_schema.forEach(config_schema_entity => {

                            if (config_schema_entity.type === 'image') {

                                result.configs[config_schema_entity.attribute] = {
                                    file_path:  null
                                };

                            }

                        });

                        return result;

                    });

                    this.setPaymentMethods();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching payment methods';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch payment methods:', error);
                } finally {
                    this.isLoadingUnassociatedPaymentMethods = false;
                }

            },
            setPaymentMethods() {
                this.paymentMethods = cloneDeep([...this.associatedPaymentMethods, ...this.unassociatedPaymentMethods]);
                this.setOriginalPaymentMethods();
            },
            setOriginalPaymentMethods() {
                this.originalPaymentMethods = cloneDeep(this.paymentMethods);
            },
            getPaymentMethodValidationErrors(configSchemaEntity, configs) {
                let errors = [];

                // Ensure validation rules exist
                if (!configSchemaEntity.hasOwnProperty('validation_rules')) {
                    return errors;
                }

                const value = configs[configSchemaEntity.attribute];

                // Check for required validation
                if (configSchemaEntity.validation_rules.hasOwnProperty('required')) {
                    const [isRequired, message] = configSchemaEntity.validation_rules.required;

                    if (isRequired) {
                        if (
                            value === null ||
                            value === undefined ||
                            (typeof value === "string" && value.trim() === "") ||
                            (typeof value === "object" && Object.keys(value).length === 0) ||
                            (Array.isArray(value) && value.length === 0)
                        ) {
                            errors.push(message);
                        }
                    }
                }

                // Check for regex pattern validation (only applicable for string values)
                if (configSchemaEntity.validation_rules.hasOwnProperty('regex_pattern')) {
                    const [pattern, message] = configSchemaEntity.validation_rules.regex_pattern;

                    if (typeof value === "string") {
                        try {
                            const regex = new RegExp(pattern);
                            if (!regex.test(value.trim())) {
                                errors.push(message);
                            }
                        } catch (error) {
                            console.error(`Invalid regex pattern: ${pattern}`);
                        }
                    }
                }

                // Check for QR Code validation
                if (configSchemaEntity.validation_rules.hasOwnProperty('qr_code')) {

                    const [message] = configSchemaEntity.validation_rules.qr_code;

                    if (value?.file_path?.startsWith('blob:') && value?.valid_qr === false) {
                        errors.push(message);
                    }
                }

                // Check for MIME Type validation
                if (configSchemaEntity.validation_rules.hasOwnProperty('mime_types')) {
                    const [allowedmime_types, message] = configSchemaEntity.validation_rules.mime_types;

                    if (value && value.file_ref) {
                        const uploadedFileType = value.file_ref.type;

                        if (!allowedmime_types.includes(uploadedFileType)) {
                            errors.push(message);
                        }
                    }
                }

                // Check for max_size validation
                if (configSchemaEntity.validation_rules.hasOwnProperty('max_size')) {

                    const [max_size, message] = configSchemaEntity.validation_rules.max_size;

                    if (value?.file_ref && value.file_ref.size > max_size) {
                        errors.push(message);
                    }
                }

                return errors;
            },
            getPaymentMethodFirstValidationError(configSchemaEntity, configs) {
                const errors = this.getPaymentMethodValidationErrors(configSchemaEntity, configs);
                return errors.length ? errors[0] : null;
            },
            checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, configs) {

                if (!configSchemaEntity.hasOwnProperty('condition')) {
                    // No condition means it's always valid
                    return true;
                }

                return configSchemaEntity.condition.every(condition => {

                    // Ensure condition follows 'attribute=value' or 'attribute!=value' format
                    const match = condition.match(/^([^!=]+)(!=|=)(.+)$/);

                    if (!match) {
                        console.log(`Invalid condition format: ${condition}`);
                        return false;
                    }

                    const [, attribute, operator, expectedValue] = match.map(str => str.trim());

                    // Check if attribute exists in the configs
                    if (!configs.hasOwnProperty(attribute)) {
                        return false;
                    }

                    // Handle equality and inequality conditions
                    if (operator === '=') {
                        return configs[attribute] === expectedValue;
                    } else if (operator === '!=') {
                        return configs[attribute] !== expectedValue;
                    }

                    return false;
                });
            },
            async submitPaymentMethods() {

                if(this.isSubmittingPaymentMethods || this.isUploading) return;

                // Indicate that payment method submittion is in progress
                this.isSubmittingPaymentMethods = true;

                // Create all store payment methods one by one
                let storePaymentMethodCreationPromises = this.changedPaymentMethods.map((paymentMethod) => {

                    if(paymentMethod.store_payment_method_id) {
                        return this.updatePaymentMethod(paymentMethod);
                    }else if(paymentMethod.active) {
                        return this.addPaymentMethod(paymentMethod);
                    }

                });

                // Process all store payment method submittion requests
                await Promise.allSettled(storePaymentMethodCreationPromises)
                    .then((results) => {

                        let successCount = 0;
                        let errors = [];

                        results.forEach((result, index) => {
                            if (result.status === 'fulfilled') {
                                successCount++;
                            } else {
                                errors.push(`Payment method ${index + 1}: ${result.reason?.message || 'An error occurred'}`);
                                this.formErrorMessagesIndex = index;
                                this.formState.setServerFormErrors(result.reason, index);
                            }
                        });

                        if (successCount) {
                            if(this.hasAssociatedPaymentMethods) {
                                this.notificationState.showSuccessNotification('Payment methods updated!');
                            }else{
                                this.notificationState.showSuccessNotification('Payment methods added!');
                            }
                        }

                        if (errors.length > 0) {
                            this.notificationState.showWarningNotification(errors.join('\n'));
                        }

                    })
                    .catch((error) => {
                        this.notificationState.showWarningNotification('An unexpected error occurred while submitting payment methods.');
                        console.error(error);
                    })
                    .finally(() => {
                        this.isSubmittingPaymentMethods = false; // ✅ Payment method creation done
                        this.setOriginalPaymentMethods();
                    });

                if (this.totalFailedUploads == 0 || this.uploadsFailedBefore) {

                    this.navigateToAddSocials();

                }else{

                    this.uploadsFailedBefore = true;

                }
            },
            async addPaymentMethod(paymentMethod) {

                try {

                    this.uploadMessage = `Creating ${paymentMethod.name}`;

                    let storePaymentMethodData = {
                        return: '1',
                        store_id: this.store.id,
                        active: paymentMethod.active,
                        configs: paymentMethod.configs,
                        payment_method_id: paymentMethod.id,
                        custom_name: paymentMethod.custom_name
                    };

                    const response = await axios.post('/api/store-payment-methods', storePaymentMethodData);

                    if(this.totalCompletedSteps < this.totalCompletionSteps) this.totalCompletedSteps++;
                    let createdStorePaymentMethod = response.data.store_payment_method;
                    paymentMethod.store_payment_method_id = createdStorePaymentMethod.id;

                    await this.uploadStorePaymentMethodImages(paymentMethod);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || `Something went wrong while creating ${paymentMethod.name}`;
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create store payment method:', error);
                }

            },
            async updatePaymentMethod(paymentMethod) {

                try {

                    let storePaymentMethodData = {
                        store_id: this.store.id,
                        active: paymentMethod.active,
                        configs: paymentMethod.configs,
                        custom_name: paymentMethod.custom_name
                    };

                    await axios.put(`/api/store-payment-methods/${paymentMethod.store_payment_method_id}`, storePaymentMethodData);

                    if(this.totalCompletedSteps < this.totalCompletionSteps) this.totalCompletedSteps++;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || `Something went wrong while updating ${paymentMethod.name}`;
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update store payment method:', error);
                }
            },
            async uploadStorePaymentMethodImages(paymentMethod) {

                let imageUploadPromises = paymentMethod.config_schema
                    .filter(config_schema => config_schema.type === 'image')
                    .map(config_schema => {
                        return this.uploadSinglePaymentMethodImage(paymentMethod, config_schema.attribute);
                    })
                    .filter(Boolean);

                if (imageUploadPromises.length === 0) return;

                this.isUploading = true;
                this.uploadMessage = `Uploading images...`;

                const results = await Promise.allSettled(imageUploadPromises);
                let failedUploads = results.filter(result => result.status === 'rejected').length;

                if (failedUploads > 0) {
                    this.notificationState.showWarningNotification(`⚠️ ${failedUploads} image(s) failed to upload. You can retry manually.`);
                }

                this.isUploading = false;
            },
            async uploadSinglePaymentMethodImage(paymentMethod, attribute, retryCount = 0, error = null) {

                try {

                    if (retryCount > 2) {
                        console.log(`❌ Image upload for '${paymentMethod.name}' failed after 3 attempts.`);
                        paymentMethod.configs[attribute].uploaded = false;
                        paymentMethod.configs[attribute].error_message = error?.response?.data?.message || error?.message || `Something went wrong while uploading ${attribute}`;
                        return Promise.reject(`Failed after 3 attempts`);
                    }

                    let formData = new FormData();
                    formData.append('return', '1');
                    formData.append('store_id', this.store.id);
                    formData.append('type', `store_payment_method_${attribute}`);
                    formData.append('file', paymentMethod.configs[attribute].file_ref);
                    formData.append('store_payment_method_id', paymentMethod.store_payment_method_id);

                    paymentMethod.configs[attribute].uploading = true;
                    paymentMethod.configs[attribute].error_message = null;

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    const response = await axios.post('/api/media-files', formData, config);

                    if(this.totalCompletedSteps < this.totalCompletionSteps) this.totalCompletedSteps++;
                    const createdMediaFile = response.data.media_file;
                    this.totalCompletedUploads++;

                    paymentMethod.configs[attribute] = {
                        uploaded: true,
                        deleting: false,
                        id: createdMediaFile.id,
                        file_path: createdMediaFile.file_path
                    };

                    console.log(`✅ Image for '${paymentMethod.name}' uploaded successfully.`);
                    this.uploadMessage = `Uploaded ${this.totalCompletedUploads}/${this.totalUploads} images`;

                    return response;

                } catch (error) {
                    console.error(`⚠️ Image upload for '${paymentMethod.name}' attempt ${retryCount + 1} failed.`, error);
                    await this.uploadSinglePaymentMethodImage(paymentMethod, attribute, retryCount + 1, error);
                } finally {
                    paymentMethod.configs[attribute].uploading = false;
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

                    paymentMethod.configs[attribute].file_path = null;
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
            navigateToAddSocials() {
                this.$router.push({
                    name: 'add-socials',
                    params: { store_id: this.store.id }
                });
            }
        },
        beforeRouteLeave(to, from, next) {

            if (this.isSubmittingPaymentMethods || this.isUploading || (this.hasChangedPaymentMethods && this.progressPercentage != 100 && !this.hasFailedUploads)) {

                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");

                if (!answer) {
                    return next(false);
                }

            }

            next();
        },
        created() {
            this.reset();
            this.showAssociatedPaymentMethods();
            this.showUnassociatedPaymentMethods();
        }
    };
  </script>
