<template>

    <div>

        <!-- Input Label -->
        <div
            class="text-sm leading-6 font-medium text-gray-900">
            <span>{{ configSchemaEntity.label }}</span>
            <span v-if="configSchemaEntity.optional" class="font-normal text-gray-400 ml-1">(optional)</span>
        </div>

        <!-- Drag & Drop or Clickable Area -->
        <div
            @dragover.prevent
            @drop="handleDrop"
            @click="triggerFileInput"
            v-if="!localModelValue.file_path"
            class="mt-2 w-full h-40 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 text-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <p>Click or Drag & Drop Image</p>
            <input
                type="file"
                class="hidden"
                ref="fileInput"
                @change="handleFileUpload"
                :accept="acceptedFileTypes"
            />
        </div>

        <!-- Image Preview & Upload Indicators -->
        <div
            class="relative mt-2">

            <template v-if="hasLocalImage && !localModelValue.uploading">

                <!-- Success Tick -->
                <div v-if="localModelValue.uploaded === true" class="absolute z-10 top-1 right-1 bg-green-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                    <Check size="16"></Check>
                </div>

                <template v-else-if="localModelValue.uploaded === false">

                    <!-- Failure Cross -->
                    <div class="absolute z-10 top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                        <X size="16"></X>
                    </div>

                    <!-- Retry Button -->
                    <div
                        @click="uploadImage"
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 active:scale-95 absolute z-10 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 cursor-pointer">
                        <RefreshCw size="16"></RefreshCw>
                    </div>

                    <!-- Failed Indicator -->
                    <div class="absolute inset-0 bg-white/80 bg-opacity-80 border border-red-500 rounded-lg flex items-center justify-center"></div>

                </template>

                <!-- Remove Image Button -->
                <div
                    @click="removeImage"
                    v-if="(!localModelValue.uploaded && !localModelValue.uploading)"
                    class="absolute z-10 top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition cursor-pointer">
                    <X size="16"></X>
                </div>

            </template>

            <!-- Uploading Indicator -->
            <div v-if="hasLocalImage && localModelValue.uploading" :class="['absolute inset-0 bg-gray-900/80 bg-opacity-50 rounded-lg flex items-center justify-center']">
                <span class="text-white text-xs font-bold">Uploading...</span>
            </div>

            <!-- Deleting Indicator -->
            <div v-else-if="hasCloudImage && localModelValue.deleting" :class="['absolute inset-0 bg-red-900/80 bg-opacity-50 rounded-lg flex items-center justify-center']">
                <span class="text-white text-xs font-bold">Deleting...</span>
            </div>

            <!-- Remove Image Button -->
            <div
                @click="removeImage"
                v-if="(hasCloudImage || hasLocalImage) && !localModelValue.deleting && !localModelValue.uploading"
                class="absolute top-1 right-1 cursor-pointer bg-red-500  hover:bg-red-600 text-white rounded-full w-fit px-4 py-1 flex items-center justify-center text-xs active:scale-95 transition-all">
                Remove
            </div>

            <!-- Image -->
            <img
                :src="localModelValue.file_path"
                v-if="hasCloudImage || hasLocalImage"
                class="w-full max-h-40 p-4 object-contain rounded-lg border border-gray-300 dark:border-gray-700"
            />

            <p v-if="localModelValue.error_message" class="bg-red-500/20 py-2 px-4 relative rounded-b-lg text-xs">{{ localModelValue.error_message }}</p>

        </div>

    </div>

</template>

<script>

    import QrScanner from 'qr-scanner';
    import isEqual from 'lodash/isEqual';
    import cloneDeep from 'lodash/cloneDeep';
    import { X, Check, RefreshCw } from 'lucide-vue-next';

    export default {
        components: {
            X, Check, RefreshCw
        },
        props: {
            modelValue: {
                type: Object,
                default: () => null
            },
            configSchemaEntity: {
                type: Object
            },
            paymentMethod: {
                type: Object
            },
            deletePaymentMethodImage: {
                type: Function
            },
            uploadSinglePaymentMethodImage: {
                type: Function
            },
            getPaymentMethodValidationErrors: {
                type: Function
            },
        },
        data() {
            return {
                localModelValue: null,
            };
        },
        watch: {
            modelValue: {
                immediate: true,
                handler(newValue) {
                    this.localModelValue = cloneDeep(newValue);
                },
                deep: true
            },
            localModelValue: {
                handler(newValue) {
                    if(!isEqual(this.modelValue, newValue)) {
                        this.$emit('update:modelValue', newValue);
                    }
                },
                deep: true
            }
        },
        computed: {
            acceptedFileTypes() {

                // Check if `validation_rules` exist and contain `mime_types`
                if (this.configSchemaEntity?.validation_rules?.mime_types) {

                    const [allowedTypes] = this.configSchemaEntity.validation_rules.mime_types;

                    // Ensure it's an array and return a comma-separated list
                    if (Array.isArray(allowedTypes) && allowedTypes.length > 0) {
                        return allowedTypes.join(",");
                    }

                }

                // Default to "image/*" if no specific types are set
                return "image/*";

            },
            hasCloudImage() {
                return this.localModelValue?.file_path && !this.localModelValue?.file_path.startsWith("blob:");
            },
            hasLocalImage() {
                return this.localModelValue?.file_path && this.localModelValue?.file_path.startsWith("blob:");
            }
        },
        methods: {
            triggerFileInput() {
                this.$refs.fileInput.click();
            },
            handleFileUpload(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.processFile(file);
            },
            handleDrop(event) {
                event.preventDefault();
                const file = event.dataTransfer.files[0];
                if (!file) return;
                this.processFile(file);
            },
            processFile(file) {
                if (!file) return;

                // Process file and update state asynchronously
                new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = () => {
                        this.localModelValue.file_path = URL.createObjectURL(file);
                        this.localModelValue.uploading = false;
                        this.localModelValue.uploaded = null;
                        this.localModelValue.file_ref = file;

                        resolve(); // Ensure the file is completely processed
                    };
                    reader.readAsDataURL(file);
                }).then(async () => {

                    if (this.configSchemaEntity.validation_rules?.qr_code) {

                        // Wait for QR code validation to complete
                        await this.validateQRCode();

                    }

                    if(this.paymentMethod.store_payment_method_id) {

                        // Proceed with upload
                        await this.uploadImage();

                    }
                });
            },
            async validateQRCode() {

                try {

                    // Dynamically import QrScanner
                    const QrScanner = (await import('qr-scanner')).default;

                    // Scan QR code
                    const scanResult = await QrScanner.scanImage(this.localModelValue.file_path, { returnDetailedScanResult: true });

                    // Extract QR data (supporting both old & new API formats)
                    const qrData = scanResult?.data ?? scanResult;

                    if (qrData) {

                        console.log(`✅ QR Code detected: ${qrData}`);

                        // Update state with valid QR Code
                        this.localModelValue = {
                            ...this.localModelValue,
                            valid_qr: true,
                            qrData
                        };

                    } else {
                        throw new Error("Invalid QR Code");
                    }

                } catch (error) {

                    console.error("❌ Invalid QR Code:", error);

                    // Update state with invalid QR Code
                    this.localModelValue = {
                        ...this.localModelValue,
                        valid_qr: false
                    };

                }
            },
            async removeImage() {

                if (this.hasLocalImage) {

                    this.localModelValue.file_path = null;

                }else if(this.localModelValue.file_path != null) {

                    await this.deletePaymentMethodImage(this.paymentMethod, this.configSchemaEntity.attribute);

                }
            },
            async uploadImage() {

                const { attribute } = this.configSchemaEntity;

                // ✅ Correct: Dynamically set the key using [attribute]
                const configs = {
                    [attribute]: this.paymentMethod.configs[attribute]
                };

                if (!configs[attribute]) {
                    console.log(`⚠️ No valid config found for '${attribute}'`);
                    return;
                }

                const validationErrors = this.getPaymentMethodValidationErrors(this.configSchemaEntity, configs);

                if (!validationErrors.length) {
                    await this.uploadSinglePaymentMethodImage(this.paymentMethod, attribute);
                } else {
                    console.log(`❌ Upload prevented due to validation errors:`, validationErrors);
                }
            }
        }
    };
</script>
