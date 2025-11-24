<template>

    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <div class="bg-white p-8 shadow-sm rounded-xl mb-2" v-if="store">

            <h1 class="text-lg font-bold mb-2">Google Search Preview</h1>

            <p class="text-xs text-gray-500 mb-4">This is an approximation of how your store might appear in Google search results.</p>

            <div class="border rounded-lg border-gray-300 p-4 flex">
                <!-- SEO Image -->
                <div v-if="previewImage" class="mr-4 shrink-0">
                    <img
                        :src="previewImage"
                        alt="SEO Image Preview"
                        class="w-16 h-16 object-cover rounded"
                        style="max-width: 64px; max-height: 64px;"
                    />
                </div>
                <!-- Text Content -->
                <div class="flex-1">
                    <div class="text-blue-600 font-medium mb-1 truncate" style="max-width: 536px;">
                        {{ previewTitle }}
                    </div>
                    <div class="text-green-700 text-sm mb-1">
                        {{ previewUrl }} ▾
                    </div>
                    <div class="text-gray-600 text-sm line-clamp-2" style="max-width: 536px;">
                        {{ previewDescription }}
                    </div>
                </div>
            </div>

        </div>

        <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

            <h1 class="text-lg font-bold mb-4">SEO Settings</h1>

            <p class="text-xs text-gray-500 mb-4">Improve how your business appears on Google searches and enable tracking.</p>

            <div class="space-y-4">

                <!-- SEO Title Input -->
                <Input
                    type="text"
                    maxlength="60"
                    label="SEO Title"
                    v-model="storeForm.seo_title"
                    :showCharacterLengthCounter="true"
                    :skeleton="isLoadingStore || !store"
                    :errorText="formState.getFormError('seo_title')"
                    @change="storeState.saveStateDebounced('SEO title changed')">
                </Input>

                <!-- SEO Description Input -->
                <Input
                    rows="2"
                    type="textarea"
                    maxlength="160"
                    label="SEO Description"
                    :showCharacterLengthCounter="true"
                    v-model="storeForm.seo_description"
                    :skeleton="isLoadingStore || !store"
                    :errorText="formState.getFormError('seo_description')"
                    @change="storeState.saveStateDebounced('SEO description changed')">
                </Input>

                <!-- SEO Image  -->
                <Input
                    type="file"
                    :maxFiles="1"
                    :imagePreviewGridCols="1"
                    v-model="storeForm.seo_image"
                    @retryUploads="(files) => uploadImages()"
                    @retryUpload="(file, fileIndex) => uploadImages(fileIndex)"
                    @change="storeState.saveStateDebounced('SEO image changed')">
                </Input>

                <!-- SEO Keywords Input -->
                <SelectTags
                    :showOptions="false"
                    label="SEO Keywords"
                    placeholder="Type keyword"
                    v-model="storeForm.seo_keywords"
                    :skeleton="isLoadingStore || !store"
                    :errorText="formState.getFormError('seo_keywords')"
                    @change="storeState.saveStateDebounced('SEO keywords changed')" />

                <!-- Google Analytics ID -->
                <Input
                    type="text"
                    label="Google Analytics ID"
                    secondaryLabel="(e.g., G-XXXXXXX)"
                    :skeleton="isLoadingStore || !store"
                    v-model="storeForm.google_analytics_id"
                    :errorText="formState.getFormError('google_analytics_id')"
                    @change="storeState.saveStateDebounced('Google Analytics ID changed')"
                />

                <!-- Meta Pixel ID -->
                <Input
                    type="text"
                    label="Meta Pixel ID"
                    v-model="storeForm.meta_pixel_id"
                    secondaryLabel="(e.g., 1234567890)"
                    :skeleton="isLoadingStore || !store"
                    :errorText="formState.getFormError('meta_pixel_id')"
                    @change="storeState.saveStateDebounced('Meta Pixel ID changed')"
                />

                <!-- TikTok Pixel ID -->
                <Input
                    type="text"
                    label="TikTok Pixel ID"
                    v-model="storeForm.tiktok_pixel_id"
                    :skeleton="isLoadingStore || !store"
                    secondaryLabel="(e.g., ABCDEF123456)"
                    :errorText="formState.getFormError('tiktok_pixel_id')"
                    @change="storeState.saveStateDebounced('TikTok Pixel ID changed')"
                />

            </div>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import SelectTags from '@Partials/SelectTags.vue';

    export default {
        inject: ['formState', 'storeState', 'changeHistoryState', 'notificationState'],
        components: {
            Input, Switch, Button, Skeleton, SelectTags
        },
        data() {
            return {

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
            previewTitle() {
                return this.storeForm.seo_title || this.store.name || 'Store Title';
            },
            previewDescription() {
                return this.storeForm.seo_description || this.store.description || 'Store description goes here.';
            },
            previewUrl() {
                return `${window.location.origin}/${this.store.alias}`;
            },
            previewImage() {
                return this.storeForm.seo_image?.[0]?.path || this.storeForm.logo?.[0]?.path || null || this.storeForm.logo?.[0]?.path || '/images/logo-black-transparent.png';
            },
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

                    if(this.storeForm.seo_image.length) {
                        await this.uploadImages();
                    }

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
            async uploadImages(photoIndex = null) {

                let photos = this.storeForm.seo_image;

                let imageUploadPromises = [];

                for (let index = 0; index < photos.length; index++) {
                    let photo = photos[index];

                    if (photo.id == null && photo.uploading == false && (photo.uploaded === null || photo.uploaded === false)) {
                        if (photoIndex == null || photoIndex == index) {
                            imageUploadPromises.push(
                                this.uploadSingleImage(photos[index], index)
                            );
                        }
                    }
                }

                if (imageUploadPromises.length === 0) {
                    this.storeState.isUploading = false;
                    return;
                }

                this.storeState.isUploading = true;

                return Promise.allSettled(imageUploadPromises).then((results) => {
                    let failedUploads = results.filter(result => result.status === 'rejected').length;

                    if (failedUploads > 0) {
                        this.notificationState.showWarningNotification(`⚠️ ${failedUploads} image(s) failed to upload. Try again or change the image.`);
                    }

                    this.storeState.isUploading = false;
                });
            },
            async uploadSingleImage(photo, index, retryCount = 0, error = null) {

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
                    formData.append('mediable_type', 'store');
                    formData.append('store_id', this.store.id);
                    formData.append('mediable_id', this.store.id);
                    formData.append('upload_folder_name', 'store_seo_image');

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

                    return this.uploadSingleImage(photo, index, retryCount + 1, error);
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
