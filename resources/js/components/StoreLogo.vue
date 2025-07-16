<template>

    <div :class="{ 'flex flex-col items-center space-y-2' : showButton }">

        <div
            @click="triggerFileUpload"
            :class="[size, 'bg-gray-200 rounded-full flex items-center justify-center cursor-pointer active:scale-95 transition-all relative group']">

            <!-- Uploading -->
            <div v-if="isUploadingAny" class="absolute inset-0 text-white bg-blue-500/80 bg-opacity-50 flex items-center justify-center rounded-full transition-opacity">
                <RefreshCw size="16" class="animate-spin"></RefreshCw>
            </div>

            <!-- Edit Icon on Hover -->
            <div class="absolute inset-0 text-white bg-black/80 bg-opacity-50 flex items-center justify-center rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                <RefreshCw v-if="filePath" size="16"></RefreshCw>
                <Plus v-else size="16"></Plus>
            </div>

            <!-- Store Logo -->
            <img :src="filePath ? filePath : '/images/logo-black-transparent.png'" alt="Store Logo" class="w-full h-full rounded-full" />

        </div>

        <input
            type="file"
            class="hidden"
            ref="fileInput"
            @change="handleFileUpload"
            accept="image/jpeg, image/jpg, image/png, image/gif" />

        <Button
            size="xs"
            type="light"
            v-if="showButton"
            buttonClass="w-40"
            :action="triggerFileUpload"
            :leftIcon="filePath ? RefreshCw : Plus">
            <span>{{ filePath ? 'Change' : 'Add' }} store logo</span>
        </Button>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { Plus, RefreshCw } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: { Plus, RefreshCw, Button },
        props: {
            size: {
                type: String,
                default: 'w-20 h-20'
            },
            store: {
                type: [Object, null],
                default: null
            },
            showButton: {
                type: Boolean,
                default: true
            },
            uploading: {
                type: Boolean,
                default: false
            }
        },
        emits: ['selectedFile'],
        data() {
            return {
                Plus,
                RefreshCw,
                filePath: null,
                localStore: null,
                isUploading: false,
                filePathBefore: null
            }
        },
        computed: {
            isUploadingAny() {
                return this.uploading ?? this.isUploading;
            }
        },
        methods: {
            triggerFileUpload() {
                console.log('stage 1');
                this.$refs.fileInput.click();
            },
            handleFileUpload(event) {
                console.log('stage 2');
                const file = event.target.files[0];
                if (file) {
                console.log('stage 3');
                    this.filePath = URL.createObjectURL(file);
                    this.$emit('selectedFile', this.$refs.fileInput.files[0]);
                    if(this.localStore) {
                console.log('stage 4');
                        this.uploadStoreLogo();
                    }
                }
            },
            async uploadStoreLogo() {

                try {

                    if(this.isUploadingAny || this.$refs.fileInput.files.length == 0) return;

                    let formData = new FormData();
                    formData.append('return', 1);
                    formData.append('type', 'store_logo');
                    formData.append('store_id', this.localStore.id);
                    formData.append('file', this.$refs.fileInput.files[0]);

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    this.isUploading = true;

                    const response = await axios.post('/api/media-files', formData, config);

                    this.notificationState.showSuccessNotification('Store logo updated!');

                    if(this.storeState.store && this.localStore.id == this.storeState.store.id) {
                        this.storeState.store.logo = response.data.media_file;
                        this.filePath = response.data.media_file.file_path;
                        this.filePathBefore = this.filePath;
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while uploading store logo';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to upload store logo:', error);
                    this.filePath = this.filePathBefore;
                } finally {
                    this.isUploading = false;
                    this.$refs.fileInput.value = '';
                }
            }
        },
        created() {
            this.localStore = this.store ? this.store : this.storeState.store;

            if(this.localStore?.logo) {
                this.filePath = this.localStore.logo.file_path;
                this.filePathBefore = this.filePath;
            }
        }
    };

</script>
