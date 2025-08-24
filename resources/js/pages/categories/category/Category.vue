<template>

    <div class="pt-24 px-4">

        <div class="grid grid-cols-12 gap-4 mb-4">

            <div class="col-span-8 col-start-3">

                <div class="select-none bg-white rounded-lg p-4 mb-4">

                    <div class="flex items-center space-x-4">

                        <!-- Back Button -->
                        <Button
                            size="xs"
                            type="light"
                            :action="goBack"
                            :leftIcon="MoveLeft">
                            <span>Back</span>
                        </Button>

                        <div v-if="isLoadingStore || isLoadingCategory || (isEditing && !hasCategory)" class="flex items-center space-x-2">
                            <Skeleton width="w-40"></Skeleton>
                            <Skeleton width="w-4"></Skeleton>
                        </div>

                        <template v-else>

                            <!-- Heading -->
                            <div class="flex items-center space-x-1">

                                <h1 class="text-lg text-gray-700 font-semibold">
                                    {{ isCreating ? 'Add Category' : categoryForm.name || '...' }}
                                </h1>

                                <Popover content="Categories are groups that help organize your products so customers can easily browse and find what they’re looking for." placement="top"></Popover>

                            </div>

                        </template>

                    </div>

                </div>

                <div class="relative mb-8">

                    <BackdropLoader v-if="isLoadingCategory || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <!-- Name Input -->
                        <Input
                            type="text"
                            label="Name"
                            placeholder="Main Dish"
                            v-model="categoryForm.name"
                            :errorText="formState.getFormError('name')"
                            tooltipContent="The name of your category e.g Main Dish"
                            @input="categoryState.saveStateDebounced('Name changed')">
                        </Input>

                        <!-- Visibility Select -->
                        <Select
                            class="w-full"
                            :search="false"
                            label="Visibility"
                            :options="visibilityTypes"
                            v-model="categoryForm.visible"
                            :errorText="formState.getFormError('visible')"
                            @change="categoryState.saveStateDebounced('Visibility status changed')"
                            tooltipContent="Turn on if you want your category to be visible (Made available to customers)">
                        </Select>

                        <!-- Description Textarea -->
                        <Input
                            rows="2"
                            type="textarea"
                            label="Description"
                            v-model="categoryForm.description"
                            :errorText="formState.getFormError('description')"
                            description="Description must be less than 100 characters"
                            @input="categoryState.saveStateDebounced('Description changed')"
                            placeholder="Delicious, filling meals that take center stage on your plate"
                            tooltipContent="Sweet and short description of your category e.g Delicious, filling meals that take center stage on your plate">
                        </Input>

                        <!-- Image  -->
                        <Input
                            type="file"
                            :maxFiles="1"
                            v-model="categoryForm.photos"
                            @retryUploads="(files) => uploadImages(categoryForm.id)"
                            @change="categoryState.saveStateDebounced('Photos changed')"
                            @retryUpload="(file, fileIndex) => uploadImages(categoryForm.id, fileIndex)">
                        </Input>

                    </div>

                </div>

                <div class="relative mb-8">

                    <BackdropLoader v-if="isLoadingCategory || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <CategoryProducts></CategoryProducts>

                </div>

                <div
                    v-if="category"
                    :class="['flex items-center justify-between space-x-4 overflow-hidden rounded-lg p-4 border mb-20', isLoadingCategory ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

                    <div class="space-y-2">
                        <p>Delete <span class="font-bold text-black">{{ categoryForm.name }}</span>?</p>
                        <p class="text-sm">Once this category is deleted you will not be able to recover it.</p>
                    </div>

                    <div class="flex justify-end">

                        <Modal
                            triggerType="danger"
                            approveType="danger"
                            :approveLeftIcon="Trash2"
                            triggerText="Delete Category"
                            approveText="Delete Category"
                            :approveAction="deleteCategory"
                            :triggerLoading="isDeletingCategory"
                            :approveLoading="isDeletingCategory">

                            <template #content>
                                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                                <p class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ categoryForm.name }}</span>?</p>
                            </template>

                        </Modal>

                    </div>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import cloneDeep from 'lodash/cloneDeep';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { Trash2, MoveLeft } from 'lucide-vue-next';
    import { VueDraggableNext } from 'vue-draggable-next';
    import BackdropLoader from '@Partials/BackdropLoader.vue';
    import CategoryProducts from '@Pages/categories/category/category-products/CategoryProducts.vue';

    export default {
        inject: ['formState', 'storeState', 'categoryState', 'changeHistoryState', 'notificationState'],
        components: {
            Input, Modal, Button, Loader, Select, Popover,
            Skeleton, draggable: VueDraggableNext, BackdropLoader, CategoryProducts
        },
        data() {
            return {
                Trash2,
                MoveLeft,
                products: [],
                visibilityTypes: [
                    { label: 'Visible', value: true},
                    { label: 'Hidden', value: false},
                ]
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            },
            '$route.params.category_id'(newValue) {
                if(newValue) {
                    this.setup();
                    this.setActionButtons();
                }
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            category() {
                return this.categoryState.category;
            },
            hasCategory() {
                return this.categoryState.hasCategory;
            },
            categoryId() {
                return this.$route.params.category_id;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingCategory() {
                return this.categoryState.isLoadingCategory;
            },
            isEditing() {
                return this.$route.name === 'edit-category';
            },
            isCreating() {
                return this.$route.name === 'create-category';
            },
            categoryForm() {
                return this.categoryState.categoryForm;
            },
            isSubmitting() {
                if(this.changeHistoryState.actionButtons.length == 0) return false;
                return this.changeHistoryState.actionButtons[1].loading;
            },
            isDeletingCategory() {
                return this.categoryState.isDeletingCategory;
            }
        },
        methods: {
            goBack() {
                this.navigateToCategories();
            },
            async setup() {
                if(this.categoryForm == null) this.categoryState.setCategoryForm(null, this.isCreating);
                if(this.isEditing && this.store) await this.showCategory();
            },
            async navigateToCategories() {
                await this.$router.replace({
                    name: 'show-categories',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async onView(category) {
                await this.$router.push({
                    name: 'edit-category',
                    params: {
                        category_id: category.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async showCategory() {
                try {

                    this.categoryState.isLoadingCategory = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['photos', 'products.photo'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/categories/${this.categoryId}`, config);

                    const category = response.data;
                    this.categoryState.setCategory(category);
                    this.categoryState.setCategoryForm(category);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching category';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch category:', error);

                    if(error.status == 404) {
                        await this.$router.replace({
                            name: 'show-categories',
                            query: {
                                store_id: this.store.id,
                                searchTerm: this.$route.query.searchTerm,
                                filterExpressions: this.$route.query.filterExpressions,
                                sortingExpressions: this.$route.query.sortingExpressions
                            }
                        });
                    }

                } finally {
                    this.categoryState.isLoadingCategory = false;
                }
            },
            async createCategory() {

                try {

                    if(this.categoryState.isCreatingCategory || this.categoryState.isUploading) return;

                    this.formState.hideFormErrors();

                    if(this.categoryForm.name == null || this.categoryForm.name.trim() === '') {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.categoryState.isCreatingCategory = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.getPayload(),
                        store_id: this.store.id
                    }

                    const response = await axios.post(`/api/categories`, data);
                    const createdCategory = response.data.category;

                    this.categoryForm.id = createdCategory.id;

                    if(this.categoryForm.photos.length) {
                        await this.uploadImages(this.categoryForm.id);
                    }

                    //  Update store silently
                    this.storeState.silentUpdate();

                    this.notificationState.showSuccessNotification(`Category created`);
                    this.categoryState.saveOriginalState('Original category');
                    await this.onView(createdCategory);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating category';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create category:', error);
                } finally {
                    this.categoryState.isUploading = false;
                    this.categoryState.isCreatingCategory = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async updateCategory() {

                try {

                    if(this.categoryState.isUpdatingCategory || this.categoryState.isUploading) return;

                    this.formState.hideFormErrors();

                    if(this.categoryForm.name == null || this.categoryForm.name.trim() === '') {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.categoryState.isUpdatingCategory = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.getPayload(),
                        store_id: this.store.id
                    }

                    await axios.put(`/api/categories/${this.categoryForm.id}`, data);

                    if(this.categoryForm.photos.length) {
                        await this.uploadImages(this.categoryForm.id);
                    }

                    //  Update store silently
                    this.storeState.silentUpdate();

                    this.notificationState.showSuccessNotification(`Category updated`);
                    this.categoryState.saveOriginalState('Original category');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating category';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update category:', error);
                } finally {
                    this.categoryState.isUploading = false;
                    this.categoryState.isUpdatingCategory = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async deleteCategory(hideModal) {

                try {

                    if(this.categoryState.isDeletingCategory) return;

                    this.categoryState.isDeletingCategory = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/categories/${this.category.id}`, config);

                    //  Update store silently
                    this.storeState.silentUpdate();

                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 1000));    //  Wait for modal to close

                    this.notificationState.showSuccessNotification('Category deleted');

                    await this.navigateToCategories();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting category';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete category:', error);
                } finally {
                    this.categoryState.isDeletingCategory = false;
                    hideModal();
                }

            },
            async uploadImages(categoryId, photoIndex = null, variantIndex = null) {

                let photos = variantIndex !== null ? this.categoryForm.variants[variantIndex].photos : this.categoryForm.photos;

                let imageUploadPromises = [];

                for (let index = 0; index < photos.length; index++) {
                    let photo = photos[index];

                    if (photo.id == null && photo.uploading == false && (photo.uploaded === null || photo.uploaded === false)) {
                        if (photoIndex == null || photoIndex == index) {
                            imageUploadPromises.push(
                                this.uploadSingleImage(categoryId, photos[index], index)
                            );
                        }
                    }
                }

                if (imageUploadPromises.length === 0) {
                    this.categoryState.isUploading = false;
                    return;
                }

                this.categoryState.isUploading = true;

                return Promise.allSettled(imageUploadPromises).then((results) => {
                    let failedUploads = results.filter(result => result.status === 'rejected').length;

                    if (failedUploads > 0) {
                        this.notificationState.showWarningNotification(`⚠️ ${failedUploads} image(s) failed to upload. Try again or change the image.`);
                    }

                    this.categoryState.isUploading = false;
                });
            },
            async uploadSingleImage(categoryId, photo, index, retryCount = 0, error = null) {

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
                    formData.append('mediable_id', categoryId);
                    formData.append('store_id', this.store.id);
                    formData.append('mediable_type', 'category');
                    formData.append('upload_folder_name', 'category_photo');

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

                    return this.uploadSingleImage(categoryId, photo, index, retryCount + 1, error);
                }
            },
            getPayload() {
                let data = cloneDeep(this.categoryForm);
                data.product_ids = data.products.map(product => product.id);
                delete data.products;
                return data;
            },
            setActionButtons() {
                if(this.isCreating || this.isEditing) {
                    this.changeHistoryState.removeButtons();
                    this.changeHistoryState.addDiscardButton(this.onDiscard);
                    this.changeHistoryState.addActionButton(
                        this.isEditing ? 'Save Changes' : 'Create Category',
                        this.isEditing ? this.updateCategory : this.createCategory,
                        'primary',
                        null,
                    );
                }
            },
            setCategoryForm(categoryForm) {
                this.categoryState.categoryForm = categoryForm;
            }
        },
        beforeRouteLeave(to, from, next) {
            //  Triggered when navigating between routes not sharing same component e.g from "edit-category" to "show-store-home"
            if (this.changeHistoryState.hasChangeHistory) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        unmounted() {
            this.categoryState.reset();
            this.changeHistoryState.reset();
        },
        created() {

            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setCategoryForm;
            }

        }
    };

</script>
