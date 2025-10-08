<template>

    <div class="min-h-screen flex flex-col items-center pt-20 pb-40">

        <div class="w-full max-w-lg">

            <!-- Store Logo -->
            <StoreLogo :showButton="false" class="flex justify-center mb-4"></StoreLogo>

            <!-- Heading -->
            <h2 class="text-2xl font-semibold text-center mb-2">Add Products</h2>

            <!-- Instruction -->
            <p class="text-gray-500 text-center mb-6">Keep it simple—just the essentials to get started! 🚀</p>

        </div>

        <div class="w-full max-w-lg">

            <!-- Form -->
            <div class="space-y-2 mb-4">

                <div
                    :key="index"
                    v-for="(product, index) in products"
                    :class="['space-y-3 bg-white p-4 shadow-lg rounded-xl']">

                    <div class="flex items-center space-x-1 font-bold mb-4">
                        <span class="text-sm">Product</span>
                        <div class="w-5 h-5 flex items-center justify-center bg-gray-100 rounded-full text-xs">{{ index + 1 }}</div>
                    </div>

                    <!-- Name Input -->
                    <Input
                        type="text"
                        v-model="product.name"
                        placeholder="Standard Ticket"
                        :errorText="formState.getFormError('name', index)">
                    </Input>

                    <!-- Unit Regular Price Money Input -->
                    <Input
                        type="money"
                        v-model="product.unit_regular_price"
                        :currencySymbol="store.currency.symbol"
                        :errorText="formState.getFormError('unit_regular_price', index)">
                    </Input>

                    <!-- Image Upload Area -->
                    <div>

                        <!-- Title -->
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Images
                            <span class="font-normal text-gray-500">(Optional)</span>
                        </label>

                        <!-- Drag & Drop or Clickable Area -->
                        <div
                            @dragover.prevent
                            @click="() => triggerFileInput(index)"
                            @drop="(event) => handleDrop(event, index)"
                            v-if="product.photos.length < maxPhotosPerProduct"
                            class="mt-2 w-full h-20 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 text-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <p v-if="!product.photos.length">Click or Drag & Drop Images</p>
                            <p v-else>Upload More Images</p>
                            <input
                                multiple
                                type="file"
                                class="hidden"
                                accept="image/*"
                                :ref="(el) => setFileInputRef(el, index)"
                                @change="(event) => handleFileUpload(event, index)"
                            />
                        </div>

                    </div>

                    <!-- Image Previews -->
                    <div v-if="product.photos.length" class="grid grid-cols-3 gap-2">

                        <div v-for="(photo, photoIndex) in product.photos" :key="photoIndex" class="relative group">

                            <template v-if="!photo.uploading">

                                <!-- Success Tick -->
                                <div v-if="photo.uploaded === true" class="absolute z-10 top-1 right-1 bg-green-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                    <Check size="16"></Check>
                                </div>

                                <template v-else-if="photo.uploaded === false">

                                    <!-- Failure Cross -->
                                    <div class="absolute z-10 top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                        <X size="16"></X>
                                    </div>

                                    <!-- Retry Button -->
                                    <div
                                        @click="() => uploadProductImages(product, photoIndex)"
                                        class="flex items-center justify-center w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 active:scale-95 absolute z-10 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 cursor-pointer">
                                        <RefreshCw size="16"></RefreshCw>
                                    </div>

                                    <!-- Failed Indicator -->
                                    <div class="absolute inset-0 bg-white/80 bg-opacity-80 border border-red-500 rounded-lg flex items-center justify-center"></div>

                                </template>

                                <!-- Remove Image Button -->
                                <div
                                    v-if="(!photo.uploaded && !photo.uploading)"
                                    @click.stop="(event) => removePhoto(event, index, photoIndex)"
                                    class="absolute z-10 top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition cursor-pointer">
                                    <X size="16"></X>
                                </div>

                            </template>

                            <!-- Uploading Indicator -->
                            <div v-if="photo.uploading" :class="['absolute inset-0 bg-gray-900/80 bg-opacity-50 rounded-lg flex items-center justify-center']">
                                <span class="text-white text-xs font-bold">Uploading...</span>
                            </div>

                            <!-- Image -->
                            <img
                                :src="photo.path"
                                class="w-full h-24 p-4 object-contain rounded-lg border border-gray-300 dark:border-gray-700"
                            />

                            <p v-if="photo.error_message" class="bg-red-500/20 py-2 px-4 relative rounded-b-lg text-xs">{{ photo.error_message }}</p>

                        </div>
                    </div>

                    <div v-if="productHasFailedUploads(index)" :class="['flex justify-end pt-2', { 'pb-4' : index == products.length - 1 }]">

                        <Button
                            size="xs"
                            type="warning"
                            icon="refresh"
                            :disabled="productIsUploading(index)"
                            :action="() => uploadProductImages(product)">
                            <span>Retry Uplaods</span>
                        </Button>

                    </div>

                    <div v-if="products.length > 1 && !hasCompletedSteps" :class="['flex justify-end pt-2', { 'pb-4' : index == products.length - 1 }]">

                        <Button
                            size="xs"
                            icon="delete"
                            type="danger"
                            v-if="!isCreatingProducts"
                            :action="() => removeProduct(index)">
                            <span>Remove Product</span>
                        </Button>

                    </div>

                    <div v-if="(index == products.length - 1) && !hasReachedProductLimit && !hasCompletedSteps" :class="['flex justify-center pt-4', { 'border-t-2 border-dashed border-gray-100' : products.length > 1 }]">

                        <Button
                            size="sm"
                            icon="add"
                            type="light"
                            :action="addProduct"
                            v-if="index == products.length - 1 && !isCreatingProducts">
                            <span>Add Product</span>
                        </Button>

                    </div>

                </div>

            </div>

            <div v-if="hasReachedProductLimit || hasFailedUploads || formState.hasErrors" class="space-y-2 mb-4">

                <p
                    v-if="hasReachedProductLimit"
                    class="text-sm text-blue-600 font-semibold bg-blue-100 border border-blue-300 p-3 rounded-lg shadow-md text-center">
                    Let's work with <span class="underline"><span class="font-bold">{{ maxProducts }}</span> products</span> for now! 🚀
                </p>

                <div
                    v-if="hasFailedUploads"
                    class="text-xs text-blue-600 font-semibold bg-blue-100 border border-blue-300 p-3 rounded-lg shadow-md">

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

            </div>

            <!-- Global Upload Progress Bar -->
            <div v-if="isCreatingProducts || isUploading || (progressPercentage === 100)" class="mb-4">
                <div class="w-full max-w-lg bg-gray-200 rounded-full h-2 mb-2">
                    <div
                        class="h-2 rounded-full transition-all duration-500"
                        :class="progressPercentage === 100 ? 'bg-green-500' : 'bg-blue-500'"
                        :style="{ width: progressPercentage + '%' }">
                    </div>
                </div>
                <p class="text-xs text-center mt-2 font-bold">
                    {{ progressPercentage === 100 ? 'Upload Complete 🎉' : `${uploadMessage} (${progressPercentage}%)` }}
                </p>
            </div>

            <!-- Continue -->
            <Button
                size="lg"
                type="primary"
                :action="submit"
                buttonClass="w-full"
                :loading="isCreatingProducts"
                :disabled="isCreatingProducts">
                <span>Continue</span>
            </Button>

        </div>

    </div>

  </template>

<script>

    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
    import { isEmpty, isNotEmpty } from '@Utils/stringUtils.js';
    import { X, Check, RefreshCw, CloudUpload, ExternalLink } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            X, Check, RefreshCw, CloudUpload, ExternalLink, Input, Button, StoreLogo
        },
        data() {
            return {
                products: [],
                maxProducts: 5,
                fileInputs: [],
                uploadMessage: '',
                isUploading: false,
                totalCompletedSteps: 0,
                maxPhotosPerProduct: 5,
                totalCompletedUploads: 0,
                isCreatingProducts: false,
                formErrorMessagesIndex: null
            };
        },
        computed: {
            logo() {
                return this.store.logo;
            },
            store() {
                return this.storeState.store;
            },
            progressPercentage() {
                return this.totalCompletionSteps === 0 ? 0 : Math.round((this.totalCompletedSteps / this.totalCompletionSteps) * 100);
            },
            totalUploads() {
                return this.products.reduce((sum, product) => {
                    return sum + product.photos.length;
                }, 0);
            },
            totalFailedUploads() {
                return this.products.reduce((sum, product) => {
                    return sum + product.photos.filter(photo => photo.uploaded === false).length;
                }, 0);
            },
            totalCompletionSteps() {
                return this.products.length + this.totalUploads;
            },
            hasCompletedSteps() {
                return this.totalCompletedSteps > 0;
            },
            hasFailedUploads() {
                return this.totalFailedUploads > 0;
            },
            hasReachedProductLimit() {
                return this.maxProducts == this.products.length;
            }
        },
        methods: {
            isEmpty: isEmpty,
            isNotEmpty: isNotEmpty,
            addProduct() {
                this.products.push({
                    id: null,
                    name: '',
                    photos: [],
                    unit_regular_price: '0.00',
                });
            },
            removeProduct(index) {
                this.products.splice(index, 1);
            },
            productIsUploading(index) {
                return this.products[index].photos.filter(photo => photo.uploading === true).length > 0;
            },
            productHasFailedUploads(index) {
                return this.products[index].photos.filter(photo => photo.uploaded === false).length
            },
            setFileInputRef(el, index) {
                if (el) {
                    this.fileInputs[index] = el;
                }
            },
            triggerFileInput(index) {
                if(this.isCreatingProducts || this.isUploading) {
                    this.notificationState.showWarningNotification(`Still creating products`);
                    return;
                }

                if (typeof index !== "number") {
                    console.log(`⚠️ Invalid index: ${index}`, index);
                    return;
                }

                if (this.fileInputs[index]) {
                    this.fileInputs[index].click();
                } else {
                    console.log(`⚠️ File input not found for index: ${index}`);
                }
            },
            handleFileUpload(event, index) {
                const files = event.target.files;

                if (!files.length) return;

                this.processFiles(files, index);

                this.$nextTick(() => {
                    this.fileInputs[index].value = '';
                });
            },
            handleDrop(event, index) {
                event.preventDefault();
                const files = event.dataTransfer.files;
                this.processFiles(files, index);
            },
            processFiles(files, index) {
                if (!files.length) return;

                const product = this.products[index];
                const currentPhotosCount = product.photos.length;
                const remainingSlots = this.maxPhotosPerProduct - currentPhotosCount;

                if (remainingSlots <= 0) {
                    this.notificationState.showWarningNotification(`You can only upload up to ${this.maxPhotosPerProduct} photos per product.`);
                    return;
                }

                // Take only the allowed number of new photos
                const filesToUpload = Array.from(files).slice(0, remainingSlots);

                // Convert new files into objects and add them to the product's photo array (use Promises)
                const filePromises = filesToUpload.map((file) => {
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            product.photos.push({
                                path: URL.createObjectURL(file),
                                error_message: null,
                                uploading: false,
                                uploaded: null,
                                file_ref: file
                            });
                            resolve(); // Mark file as processed
                        };
                        reader.readAsDataURL(file);
                    });
                });

                // Wait for all FileReaders to finish processing
                Promise.all(filePromises).then(() => {
                    this.uploadProductImages(product);
                });
            },
            removePhoto(event, productIndex, imgIndex) {
                if (event) {
                    event.preventDefault(); // Prevent form validation
                    event.stopPropagation(); // Stop the click event from bubbling up
                }

                this.products[productIndex].photos.splice(imgIndex, 1);
            },
            submit() {
                if(this.isCreatingProducts || this.isUploading) return;

                if(this.totalCompletedSteps == 0) {
                    this.submitProducts();
                }else{
                    this.navigateToAddPayments();
                }
            },
            async submitProducts() {

                // Check if any product is missing required fields
                for (const [index, product] of this.products.entries()) {
                    if (this.isEmpty(product.name)) {
                        this.formState.setFormError('name', 'The product name is required', index);
                        this.notificationState.showWarningNotification(`The product name is required`);
                        this.formErrorMessagesIndex = index;
                        return;
                    }

                    if (this.isEmpty(product.unit_regular_price)) {
                        this.formState.setFormError('unit_regular_price', 'The product price is required', index);
                        this.notificationState.showWarningNotification(`The product price is required`);
                        this.formErrorMessagesIndex = index;
                        return;
                    }
                }

                // Indicate that product creation is in progress
                this.isCreatingProducts = true;

                // Create all products one by one
                let productCreationPromises = this.products.map(async (localProduct) => {

                    try {

                        let productData = {
                            return: '1',
                            store_id: this.store.id,
                            name: localProduct.name,
                            unit_regular_price: localProduct.unit_regular_price
                        };

                        this.uploadMessage = `Creating ${localProduct.name}`;

                        const response = await axios.post('/api/products', productData);

                        this.totalCompletedSteps++;
                        let createdProduct = response.data.product;

                        localProduct.id = createdProduct.id;
                        return this.uploadProductImages(localProduct);

                    } catch (error) {
                        const message = error?.response?.data?.message || error?.message || `Something went wrong while creating ${localProduct.name}`;
                        this.notificationState.showWarningNotification(message);
                        this.formState.setServerFormErrors(error);
                        console.error('Failed to create product:', error);
                    }

                });

                // Process all product creation requests
                await Promise.allSettled(productCreationPromises)
                    .then((results) => {
                        let successCount = 0;
                        let errors = [];

                        results.forEach((result, index) => {
                            if (result.status === 'fulfilled') {
                                successCount++;
                                this.notificationState.showSuccessNotification(`${this.products[index].name} created successfully`);
                            } else {
                                errors.push(`Product ${index + 1}: ${result.reason?.message || 'An error occurred'}`);
                                this.formErrorMessagesIndex = index;
                                this.formState.setServerFormErrors(result.reason, index);
                            }
                        });

                        if (successCount) {
                            this.notificationState.showSuccessNotification('Products added!');
                        }

                        if (errors.length > 0) {
                            this.notificationState.showWarningNotification(errors.join('\n'));
                        }
                    })
                    .catch((error) => {
                        this.notificationState.showWarningNotification('An unexpected error occurred while submitting products.');
                        console.error(error);
                    })
                    .finally(() => {
                        this.isCreatingProducts = false; // ✅ Product creation done
                    });

                if (this.totalCompletedSteps > 0 && (!this.isUploading && this.totalFailedUploads == 0)) {
                    this.navigateToAddPayments();
                }
            },
            async uploadProductImages(localProduct, photoIndex = null) {

                if (!localProduct.id) return Promise.resolve();

                let imageUploadPromises = [];

                for (let index = 0; index < localProduct.photos.length; index++) {
                    const photo = localProduct.photos[index];

                    if(photo.uploaded === null || photo.uploaded === false) {
                        if(photoIndex == null || photoIndex == index) {
                            imageUploadPromises.push(
                                this.uploadSingleImage(localProduct, photo, index)
                            );
                        }
                    }
                }

                if (!imageUploadPromises.length) return Promise.resolve();

                this.isUploading = true;
                this.uploadMessage = `Uploading photos`;

                return Promise.allSettled(imageUploadPromises).then((results) => {

                    let failedUploads = results.filter(result => result.status === 'rejected').length;
                    if (failedUploads > 0) {
                        this.notificationState.showWarningNotification(`⚠️ ${failedUploads} image(s) failed to upload. Try again or change the image.`);
                    }

                    this.isUploading = false; // ✅ All images uploaded (successful or failed)
                });
            },
            async uploadSingleImage(localProduct, photo, index, retryCount = 0, error = null) {

                try{

                    if (retryCount > 2) {
                        console.log(`❌ Image ${index + 1} permanently failed after 3 attempts.`);
                        photo.uploaded = false;
                        photo.uploading = false;
                        photo.error_message = error?.response?.data?.message || error?.message || `Something went wrong while uploading photo`;

                        return Promise.reject(`Failed after 3 attempts`);
                    }

                    let formData = new FormData();
                    formData.append('file', photo.file_ref);
                    formData.append('store_id', this.store.id);
                    formData.append('mediable_type', 'product');
                    formData.append('mediable_id', localProduct.id);
                    formData.append('upload_folder_name', 'product_photo');

                    photo.uploading = true;
                    photo.error_message = null;

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    const response = await axios.post('/api/media-files', formData, config);

                    photo.uploaded = true;
                    photo.uploading = false;
                    this.totalCompletedSteps++;
                    this.totalCompletedUploads++;

                    this.uploadMessage = `Uploaded ${this.totalCompletedUploads}/${this.totalUploads} photos`;
                    console.log(`✅ Image ${index + 1} uploaded successfully.`);

                    return response;

                } catch (error) {
                    console.error(`⚠️ Image ${index + 1} upload attempt ${retryCount + 1} failed.`, error);

                    // Don't retry on 504 or timeout error
                    if (error.code === 'ECONNABORTED' || error.response?.status === 504) return;

                    return this.uploadSingleImage(localProduct, photo, index, retryCount + 1, error);
                }
            },
            navigateToAddPayments() {
                this.$router.push({
                    name: 'add-payments',
                    params: { store_id: this.store.id }
                });
            }
        },
        beforeRouteLeave(to, from, next) {

            if (this.isCreatingProducts || this.isUploading) {

                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }

            } else if (this.totalCompletedSteps == 0 && this.products.some(product => this.isNotEmpty(product.name) || product.photos.length > 0)) {

                const answer = window.confirm("Are you sure you want to leave before adding products?");
                if (!answer) {
                    return next(false);
                }

            }

            next();
        },
        created() {
            this.addProduct();
        }
    };
  </script>
