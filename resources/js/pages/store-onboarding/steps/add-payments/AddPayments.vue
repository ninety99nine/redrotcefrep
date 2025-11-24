<template>

    <div class="min-h-screen flex flex-col items-center pb-40">

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
                                    :src="paymentMethod.configs.logo?.[0]?.path ?? paymentMethod.image_url"
                                />
                            </div>

                            <!-- Name -->
                            <span class="text-sm">{{ paymentMethod.custom_name }}</span>

                        </div>

                        <!-- Active Toogle Switch -->
                        <Switch
                            size="md"
                            v-model="paymentMethod.active">
                        </Switch>

                    </div>

                    <!-- Configurations -->
                    <PaymentMethodConfigInputs
                        :uploadImages="uploadImages"
                        :paymentMethod="paymentMethod"
                        :paymentMethodIndex="paymentMethodIndex"
                        :deletePaymentMethodImage="deletePaymentMethodImage">
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

    import isEqual from 'lodash.isequal';
    import cloneDeep from 'lodash.clonedeep';
    import Button from '@Partials/Button.vue';
    import Switch from '@Partials/Switch.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
    import { MoveRight, Banknote, CloudUpload, ExternalLink } from 'lucide-vue-next';
    import PaymentMethodConfigInputs from '@Pages/store-onboarding/steps/add-payments/PaymentMethodConfigInputs/Index.vue';

    export default {
        inject: ['formState', 'storeState', 'storePaymentMethodState', 'notificationState'],
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
                totalFailedPaymentMethods: 0,
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
                            paymentMethod.configs[configSchemaEntity.attribute].path &&
                            paymentMethod.configs[configSchemaEntity.attribute].hasOwnProperty('uploaded');
                    }).length;

                }, 0);

            },
            totalFailedUploads() {
                return this.changedPaymentMethods.reduce((sum, paymentMethod) => {
                    return sum + paymentMethod.config_schema.filter(config_schema_entity => {
                        const config = paymentMethod.configs[config_schema_entity.attribute];

                        // Ensure config exists, has a path, and was NOT uploaded successfully
                        return config?.path?.startsWith("blob:") && config.uploaded === false;
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
            hasFailedPaymentMethods() {
                return this.totalFailedPaymentMethods > 0;
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
                this.totalFailedPaymentMethods = 0;
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

                        const paymentMethod = storePaymentMethod.payment_method;

                        let result = {
                            id: storePaymentMethod.id,
                            active: storePaymentMethod.active,
                            image_url: paymentMethod.image_url,
                            payment_method_id: paymentMethod.id,
                            config_schema: paymentMethod.config_schema,
                            custom_name: storePaymentMethod.custom_name,
                        };

                        // Iterate through the config_schema and handle image type
                        result.configs = paymentMethod.config_schema.reduce((configs, configSchema) => {

                            let value;

                            // Handle image types for logo and photo
                            if (configSchema.type === 'image' && configSchema.attribute === 'logo') {
                                value = storePaymentMethod?.logo ? [storePaymentMethod.logo] : [];
                            } else if (configSchema.type === 'image' && configSchema.attribute === 'photo') {
                                value = storePaymentMethod?.photo ? [storePaymentMethod.photo] : [];
                            } else {
                                // For other types, use existing config value or null
                                value = storePaymentMethod?.configs?.[configSchema.attribute] ?? configSchema.default ?? null;
                            }

                            return {
                                ...configs,
                                [configSchema.attribute]: value
                            };

                        }, {});

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
                            match_store_country: 1,
                            store_id: this.store.id,
                            association: 'unassociated'
                        }
                    };

                    const response = await axios.get('/api/payment-methods', config);

                    this.pagination = response.data;
                    this.unassociatedPaymentMethods = this.pagination.data.map((paymentMethod) => {

                        let result = {
                            id: null,
                            active: false,
                            custom_name: paymentMethod.name,
                            image_url: paymentMethod.image_url,
                            payment_method_id: paymentMethod.id,
                            config_schema: paymentMethod.config_schema,
                        };

                        // Iterate through the config_schema and handle image type
                        result.configs = paymentMethod.config_schema.reduce((configs, configSchema) => {

                            let value;

                            // Handle image types for logo and photo
                            if (configSchema.type === 'image' && configSchema.attribute === 'logo') {
                                value = [];
                            } else if (configSchema.type === 'image' && configSchema.attribute === 'photo') {
                                value = [];
                            } else {
                                // For other types, use existing config value or null
                                value = configSchema.default ?? null;
                            }

                            return {
                                ...configs,
                                [configSchema.attribute]: value
                            };

                        }, {});

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
            getPaymentMethodFirstValidationError(configSchemaEntity, configs) {
                return this.storePaymentMethodState.getPaymentMethodFirstValidationError(configSchemaEntity, configs);
            },
            checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, configs) {
                return this.storePaymentMethodState.checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, configs);
            },
            async submitPaymentMethods() {

                if(this.isSubmittingPaymentMethods || this.isUploading) return;

                // Indicate that payment method submittion is in progress
                this.isSubmittingPaymentMethods = true;

                this.totalFailedPaymentMethods = 0;

                // Create all store payment methods one by one
                let storePaymentMethodCreationPromises = this.changedPaymentMethods.map((paymentMethod) => {

                    if(paymentMethod.id) {
                        return this.updatePaymentMethod(paymentMethod);
                    }else if(paymentMethod.active) {
                        return this.addPaymentMethod(paymentMethod);
                    }

                });

                // Process all store payment method submittion requests
                await Promise.allSettled(storePaymentMethodCreationPromises)
                    .then((results) => {

                        let errors = [];
                        let successCount = 0;

                        results.forEach((result, index) => {
                            if (result.status === 'fulfilled') {
                                successCount++;
                            } else {
                                errors.push(`Payment method ${index + 1}: ${result.reason?.message || 'An error occurred'}`);
                                this.formErrorMessagesIndex = index;
                                this.formState.setServerFormErrors(result.reason, index);
                            }
                        });

                        if(!this.hasFailedPaymentMethods) {
                            if (successCount) {
                                if(this.hasAssociatedPaymentMethods) {
                                    this.notificationState.showSuccessNotification('Payment methods updated!');
                                }else{
                                    this.notificationState.showSuccessNotification('Payment methods added!');
                                }
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

                if ((!this.hasFailedPaymentMethods && !this.hasFailedUploads) || this.uploadsFailedBefore) {

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
                        custom_name: paymentMethod.custom_name,
                        payment_method_id: paymentMethod.payment_method_id,
                    };

                    const response = await axios.post('/api/store-payment-methods', storePaymentMethodData);

                    if(this.totalCompletedSteps < this.totalCompletionSteps) this.totalCompletedSteps++;
                    let createdStorePaymentMethod = response.data.store_payment_method;
                    paymentMethod.id = createdStorePaymentMethod.id;

                    await this.uploadImages(paymentMethod);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || `Something went wrong while creating ${paymentMethod.name}`;
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create store payment method:', error);

                    this.totalFailedPaymentMethods++;
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

                    await axios.put(`/api/store-payment-methods/${paymentMethod.id}`, storePaymentMethodData);

                    if(this.totalCompletedSteps < this.totalCompletionSteps) this.totalCompletedSteps++;

                    await this.uploadImages(paymentMethod);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || `Something went wrong while updating ${paymentMethod.name}`;
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update store payment method:', error);

                    this.totalFailedPaymentMethods++;
                }
            },
            async uploadImages(paymentMethod, photoIndex = null) {

                let photos = paymentMethod.config_schema
                        .filter(configSchema => configSchema.type === 'image')
                        .map(configSchema => {
                            if(paymentMethod.configs[configSchema.attribute].length) {
                                return [configSchema.attribute, paymentMethod.configs[configSchema.attribute][0]];
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
                                this.uploadSingleImage(paymentMethod, attribute, photo, index)
                            );
                        }
                    }
                }

                if (imageUploadPromises.length === 0) return;

                this.isUploading = true;
                this.uploadMessage = `Uploading images...`;

                return Promise.allSettled(imageUploadPromises).then((results) => {
                    let failedUploads = results.filter(result => result.status === 'rejected').length;

                    if (failedUploads > 0) {
                        this.notificationState.showWarningNotification(`⚠️ ${failedUploads} image(s) failed to upload. Try again or change the image.`);
                    }

                    this.isUploading = false;
                });
            },
            async uploadSingleImage(paymentMethod, attribute, photo, index, retryCount = 0, error = null) {

                try{

                    if (retryCount > 2) {
                        console.log(`❌ Image upload for '${paymentMethod.name}' failed after 3 attempts.`);
                        photo.uploaded = false;
                        photo.uploading = false;
                        photo.error_message = error?.response?.data?.message || error?.message || `Upload failed`;

                        return Promise.reject(`Failed after 3 attempts`);
                    }

                    let formData = new FormData();
                    formData.append('file', photo.file_ref);
                    formData.append('store_id', this.store.id);
                    formData.append('mediable_id', paymentMethod.id);
                    formData.append('mediable_type', 'store payment method');
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

                    console.log(`✅ Image for '${paymentMethod.name}' uploaded successfully.`);

                    return response;

                } catch (error) {
                    console.error(`⚠️ Image upload for '${paymentMethod.name}' attempt ${retryCount + 1} failed.`, error);

                    // Don't retry on 504 or timeout error
                    if (error.code === 'ECONNABORTED' || error.response?.status === 504) return;

                    return this.uploadSingleImage(paymentMethod, attribute, photo, index, retryCount + 1, error);
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
            navigateToAddSocials() {
                this.$router.push({
                    name: 'add-socials',
                    params: { store_id: this.store.id }
                });
            }
        },
        beforeRouteLeave(to, from, next) {

            if (this.isSubmittingPaymentMethods || this.isUploading || (this.hasChangedPaymentMethods && this.progressPercentage != 100 && !this.hasFailedPaymentMethods && !this.hasFailedUploads)) {

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
