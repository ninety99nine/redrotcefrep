<template>

    <div>

        <LoadingDesignCards v-if="isLoadingStore || isLoadingDesignCards"></LoadingDesignCards>

        <template v-else>

            <AddDesignCardButton :placement="placement"></AddDesignCardButton>

            <DesignCards v-if="hasDesignCards"></DesignCards>

            <NoDesignCards v-else :placement="placement"></NoDesignCards>

        </template>

    </div>

</template>

<script>

    import isEqual from 'lodash.isequal';
    import cloneDeep from 'lodash.clonedeep';
    import NoDesignCards from '@Pages/design/_components/_components/NoDesignCards.vue';
    import DesignCards from '@Pages/design/_components/_components/design-cards/DesignCards.vue';
    import LoadingDesignCards from '@Pages/design/_components/_components/LoadingDesignCards.vue';
    import AddDesignCardButton from '@Pages/design/_components/_components/AddDesignCardButton.vue';

    export default {
        inject: ['formState', 'storeState', 'designState', 'notificationState', 'changeHistoryState'],
        props: {
            placement: {
                type: String
            }
        },
        components: {
            NoDesignCards, DesignCards, LoadingDesignCards, AddDesignCardButton
        },
        data() {
            return {
                isUploading: false,
                isChangingDesignCardArrangement: false,
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                    this.setActionButtons();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            designForm() {
                return this.designState.designForm;
            },
            designCards() {
                return this.designForm?.design_cards ?? [];
            },
            isLoadingDesignCards() {
                return this.designState.isLoadingDesignCards;
            },
            hasDesignCards() {
                return this.designCards.filter(designCard => !designCard.hasOwnProperty('delete')).length > 0;
            }
        },
        methods: {
            async setup() {
                if(this.store) {

                    this.designState.categories = this.store.categories.map((category) => {
                        return {
                            label: category.name,
                            value: category.id
                        }
                    });

                    this.showDesignCards();

                }
            },
            async showDesignCards() {
                try {

                    this.designState.isLoadingDesignCards = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id,
                            placement: this.placement,
                            _relationships: ['address', 'photos'].join(',')
                        }
                    };

                    const response = await axios.get('/api/design-cards', config);
                    const designCards = response.data.data;

                    const design = {
                        design_cards: designCards
                    };

                    this.designState.setDesignForm(design, this.store);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching design cards';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch design cards:', error);
                } finally {
                    this.designState.isLoadingDesignCards = false;
                }
            },
            async updateDesignCards() {
                try {

                    if (this.designState.isUpdatingDesignCards) return;
                    this.designState.isUpdatingDesignCards = true;

                    const originalState = this.changeHistoryState.getOriginalState();
                    let totalUpdated = 0;

                    const storePromise = this.updateStore();
                    const designCardPromises = this.designCards.map(async (designCard, index) => {
                        try {

                            if (designCard.delete) {

                                await axios.delete(`/api/design-cards/${designCard.id}`, {
                                    data: { store_id: this.store.id }
                                });

                                this.designCards.splice(index, 1);
                                ++totalUpdated;

                                return;
                            }

                            let designCardData = {
                                ...designCard,
                                position: index + 1,
                                store_id: this.store.id,
                                placement: this.placement,
                                visible: designCard.visible
                            };

                            let response;

                            if (designCard.id) {

                                const currDesignCard = cloneDeep(designCard);
                                const originalDesignCard = cloneDeep(originalState.design_cards.find(originalDesignCard => originalDesignCard.id == designCard.id));

                                delete currDesignCard.expanded;
                                delete originalDesignCard.expanded;

                                if(!originalDesignCard || isEqual(currDesignCard, originalDesignCard)) return;

                                response = await axios.put(`/api/design-cards/${designCard.id}`, designCardData);
                                ++totalUpdated;

                                if (designCard.hasOwnProperty('photos')) {
                                    await this.uploadImages(designCard.id, null, index);
                                }

                            } else {

                                response = await axios.post('/api/design-cards', designCardData);
                                ++totalUpdated;

                                this.designCards[index].id = response.data.design_card.id;

                                if (designCard.hasOwnProperty('photos')) {
                                    await this.uploadImages(this.designCards[index].id, null, index);
                                }
                            }

                            return response;

                        } catch (error) {
                            const message = error?.response?.data?.message || error?.message || `Something went wrong ${designCard.id ? 'updating' : 'creating'} ${this.getDesignCardLabel(designCard)} card`;

                            this.notificationState.showWarningNotification(message);
                            this.formState.setServerFormErrors(error, index);
                            console.error(`Failed to process design card ${index + 1}:`, error);
                            throw error;
                        }
                    });

                    const [storeResult, ...designCardResults] = await Promise.allSettled([storePromise, ...designCardPromises]);

                    if (storeResult.status === 'rejected') {
                        this.notificationState.showWarningNotification('Failed to update store settings');
                        console.error('Store update failed:', storeResult.reason);
                    }

                    const successCount = designCardResults.filter(r => r.status === 'fulfilled').length;
                    if (successCount > 0 || storeResult.status === 'fulfilled') {
                        if (totalUpdated == 0 && storeResult.status === 'fulfilled') {
                            this.notificationState.showSuccessNotification('Store settings updated successfully');
                        } else if (totalUpdated > 0) {
                            this.notificationState.showSuccessNotification(`${totalUpdated} design card${totalUpdated == 1 ? '' : 's'} updated successfully`);
                        }
                    }

                    this.changeDesignCardArrangement();
                    this.designState.saveOriginalState('Original design');

                } catch (error) {
                    this.notificationState.showWarningNotification('An unexpected error occurred while processing design cards.');
                    console.error(error);
                } finally {
                    this.designState.isUpdatingDesignCards = false;
                }
            },
            async updateStore() {

                try {

                    if(this.storeState.isUpdatingStore) return;

                    this.storeState.isUpdatingStore = true;

                    const data = {
                        ...this.designForm['store_settings'],
                        store_id: this.store.id
                    }

                    await axios.put(`/api/stores/${this.store.id}`, data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update store:', error);
                } finally {
                    this.storeState.isUpdatingStore = false;
                }

            },
            async uploadImages(designCardId, photoIndex = null, cardIndex = null) {

                if (!designCardId) return Promise.resolve();

                let photos = this.designCards[cardIndex].photos;
                let imageUploadPromises = [];

                for (let index = 0; index < photos.length; index++) {
                    let photo = photos[index];

                    if (photo.id == null && photo.uploading == false && (photo.uploaded === null || photo.uploaded === false)) {
                        if (photoIndex == null || photoIndex == index) {
                            imageUploadPromises.push(this.uploadSingleImage(designCardId, photo, index, cardIndex));
                        }
                    }
                }

                if (imageUploadPromises.length === 0) {
                    this.isUploading = false;
                    return Promise.resolve();
                }

                this.isUploading = true;

                return Promise.allSettled(imageUploadPromises).then((results) => {

                    let failedUploads = results.filter(result => result.status === 'rejected').length;

                    if (failedUploads > 0) {
                        this.notificationState.showWarningNotification(`⚠️ ${failedUploads} image(s) failed to upload. Try again or change the image.`);
                    }

                    this.isUploading = false;

                });

            },
            async uploadSingleImage(designCardId, photo, index, cardIndex, retryCount = 0, error = null) {
                try {

                    if (retryCount > 2) {
                        console.log(`❌ Image ${index + 1} permanently failed after 3 attempts.`);
                        photo.uploaded = false;
                        photo.uploading = false;
                        photo.error_message = error?.response?.data?.message || error?.message || 'Upload failed';
                        return Promise.reject('Failed after 3 attempts');
                    }

                    let formData = new FormData();
                    formData.append('file', photo.file_ref);
                    formData.append('store_id', this.store.id);
                    formData.append('mediable_id', designCardId);
                    formData.append('mediable_type', 'design card');
                    formData.append('upload_folder_name', 'design_card_photo');

                    photo.uploading = true;
                    photo.error_message = null;

                    const config = { headers: { 'Content-Type': 'multipart/form-data' } };
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
                    if (error.code === 'ECONNABORTED' || error.response?.status === 504) return;
                    return this.uploadSingleImage(designCardId, photo, index, cardIndex, retryCount + 1, error);
                }
            },
            async changeDesignCardArrangement() {

                try {

                    console.log('changeDesignCardArrangement');

                    if(this.isChangingDesignCardArrangement) return;

                    const designCardIds = this.designCards.map((designCard) => designCard.id);

                    if(designCardIds.length == 0) return;

                    this.isChangingDesignCardArrangement = true;

                    const data = {
                        store_id: this.store.id,
                        design_card_ids: designCardIds
                    };

                    await axios.post(`/api/design-cards/arrangement`, data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating design card arrangement';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update design card arrangement:', error);
                } finally {
                    this.isChangingDesignCardArrangement = false;
                }

            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    'Save Changes',
                    this.updateDesignCards,
                    'primary',
                    null
                );
            },
            setDesignForm(designForm) {
                this.designState.designForm = designForm;
            },
        },
        beforeUnmount() {
            this.designState.reset();
        },
        created() {

            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setDesignForm;
            }

        }
    }
</script>
