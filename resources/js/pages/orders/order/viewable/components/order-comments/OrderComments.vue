<template>

    <div class="bg-white rounded-lg space-y-4 p-4 mb-4">

        <h1 class="text-gray-700 font-semibold mb-4">Comments</h1>

        <div class="flex justify-between items-center space-x-4">

            <template v-if="!isLoadingStore && !isLoadingOrder && hasOrder">

                <div class="w-full flex space-x-2">

                    <div
                        @click.stop="$refs.comment.focusInput()"
                        class="w-full border border-gray-300 p-2 rounded-lg">

                        <Input
                            type="file"
                            :maxFiles="5"
                            class="w-full"
                            :wrapperClass="{}"
                            v-model="form.photos"
                            @retryUploads="(photos) => uploadImages()"
                            @retryUpload="(photo, photoIndex) => uploadImages(photoIndex)">

                            <template #fileTrigger="props">

                                <div
                                    class="w-full flex space-x-2"
                                    @click.stop="$refs.comment.focusInput()">

                                    <!-- Comment Input -->
                                    <Input
                                        rows="1"
                                        @click.stop
                                        ref="comment"
                                        class="w-full"
                                        type="textarea"
                                        :wrapperClass="{}"
                                        v-model="form.comment"
                                        placeholder="Say something"
                                        @onEnter="onEnterCreateOrderComment"
                                        :errorText="formState.getFormError('comment')">
                                    </Input>

                                    <!-- File Upload Trigger -->
                                    <div
                                        @click.stop="props.handleClick"
                                        class="z-10 w-10 h-10 flex shrink-0 items-center justify-center bg-gray-50 border border-gray-300 rounded-lg hover:bg-blue-50 cursor-pointer">
                                        <Image size="20" :class="[{ 'text-gray-400' : !props.filesLeftToUpload }]"></Image>
                                    </div>

                                </div>

                            </template>

                        </Input>

                    </div>

                    <Button
                        size="sm"
                        type="primary"
                        buttonClass="shrink-0"
                        :action="createOrderComment"
                        :disabled="isCreatingOrderComment || isUploading">
                        <span>Post</span>
                    </Button>

                </div>

            </template>

        </div>

        <!-- Order Comments (Loading Placeholder) -->
        <div v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="space-y-2">

            <div
                :key="index"
                v-for="(_, index) in [1, 2]" class="rounded-lg overflow-hidden border border-gray-300">

                <div class="space-y-2 p-2 px-4 bg-gray-50">

                    <div class="flex justify-between items-center space-x-8">

                        <Skeleton width="w-1/3" :shine="true"></Skeleton>

                        <div class="flex items-center space-x-2 whitespace-nowrap">

                            <Skeleton width="w-20" :shine="true"></Skeleton>
                            <Skeleton width="w-10" :shine="true"></Skeleton>
                            <Skeleton width="w-10" :shine="true"></Skeleton>

                        </div>

                    </div>

                    <div class="flex justify-end items-end space-x-2">

                        <Skeleton width="w-10" :shine="true"></Skeleton>

                        <div class="flex items-center justify-center w-10 h-10 rounded-lg border border-dashed border-gray-200">

                            <Image size="16" class="text-gray-400 shrink-0"></Image>

                        </div>
                    </div>

                </div>

            </div>

        </div>

        <template v-else>

            <div v-if="hasOrderComments" class="max-h-96 overflow-y-auto space-y-2 pr-2">

                <OrderComment
                    :index="index"
                    :key="orderComment.id"
                    :onViewPhoto="onViewPhoto"
                    :orderComment="orderComment"
                    :deleteOrderComment="deleteOrderComment"
                    v-for="(orderComment, index) in orderComments"
                    :isDeletingOrderCommentIds="isDeletingOrderCommentIds">
                </OrderComment>

            </div>

        </template>

    </div>

    <!-- View Order Comment Image -->
    <Modal
        :showFooter="false"
        ref="showPhotoModal"
        :scrollOnContent="false">

        <template
            #content
            v-if="orderCommentPhoto">

            <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Image Preview</p>

            <div class="flex justify-center">
                <img :src="orderCommentPhoto.path" />
            </div>

            <div v-if="!orderCommentPhoto.hasOwnProperty('temporary')" class="flex justify-end space-x-2 mt-4">

                <Button
                    size="sm"
                    type="primary"
                    :leftIcon="ArrowDownToLine"
                    :action="downloadOrderCommentPhoto"
                    :loading="this.isDownloadingOrderCommentPhotoIds.findIndex((id) => id == this.orderCommentPhoto.id) != -1">
                    <span>Download</span>
                </Button>

                <Button
                    size="sm"
                    type="danger"
                    :leftIcon="Trash2"
                    :action="deleteOrderCommentPhoto"
                    :loading="this.isDeletingOrderCommentPhotoIds.findIndex((id) => id == this.orderCommentPhoto.id) != -1">
                    <span>Delete</span>
                </Button>

            </div>

        </template>

    </Modal>

</template>

<script>

    import { v4 as uuidv4 } from 'uuid';
    import { Image } from 'lucide-vue-next';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import { isEmpty } from '@Utils/stringUtils';
    import Skeleton from '@Partials/Skeleton.vue';
    import { Trash2, ArrowDownToLine } from 'lucide-vue-next';
    import OrderComment from '@Pages/orders/order/viewable/components/order-comments/OrderComment.vue';

    export default {
        inject: ['authState' ,'formState', 'storeState', 'orderState', 'notificationState'],
        components: { Image, Modal, Input, Button, Skeleton, OrderComment },
        data() {
            return {
                Trash2,
                ArrowDownToLine,
                form: {
                    photos: [],
                    comment: ''
                },
                pagination: null,
                orderComments: [],
                isUploading: false,
                orderCommentPhoto: null,
                createdOrderComment: null,
                isCreatingOrderComment: false,
                isLoadingOrderComments: false,
                isDeletingOrderCommentIds: [],
                isDeletingOrderCommentPhotoIds: [],
                isDownloadingOrderCommentPhotoIds: [],
                isDownloadingOrderCommentPhoto: false,
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.showOrderComments();
                }
            },
            order(newValue, oldValue) {
                console.log('stage 1');
                if(newValue && oldValue) {
                console.log('stage 2');
                    const paidTotalChanged = newValue.paid_total != oldValue.paid_total;
                    const outstandingTotalChanged = newValue.outstanding_total != oldValue.outstanding_total;

                    if(paidTotalChanged || outstandingTotalChanged) {
                console.log('stage 3');
                        this.showOrderComments();
                    }
                }
            },
            isUploading(newValue) {
                if(newValue == false) {
                    this.orderCommentCreationCompleted();
                }
            },
        },
        computed: {
            order() {
                return this.orderState.store;
            },
            store() {
                return this.storeState.store;
            },
            orderId() {
                return this.$route.params.order_id;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            hasOrderComments() {
                return this.orderComments.length > 0;
            }
        },
        methods: {
            isEmpty,
            reset() {
                this.form.photos = [];
                this.form.comment = '';
            },
            async showOrderComments() {
                try {

                    this.isLoadingOrderComments = true;

                    let config = {
                        params: {
                            order_id: this.orderId,
                            store_id: this.store.id,
                            _relationships: ['photos', 'user'].join(',')
                        }
                    };

                    const response = await axios.get('/api/order-comments', config);

                    this.pagination = response.data;
                    this.orderComments = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching order comments';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch order comments:', error);
                } finally {
                    this.isLoadingOrderComments = false;
                }
            },
            onEnterCreateOrderComment(e) {
                e.preventDefault();
                this.createOrderComment();
            },
            async createOrderComment() {

                try {

                    if(this.isCreatingOrderComment || this.isUploading) return;

                    this.formState.hideFormErrors();

                    if(this.isEmpty(this.form.comment)) {
                        this.formState.setFormError('comment', 'The comment is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.isCreatingOrderComment = true;

                    const data = {
                        order_id: this.orderId,
                        store_id: this.store.id,
                        comment: this.form.comment
                    };

                    const response = await axios.post(`/api/order-comments`, data);
                    this.createdOrderComment = response.data.order_comment;

                    if(this.form.photos.length) {
                        this.uploadImages();
                    }else{
                        this.orderCommentCreationCompleted();
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating order comment';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create order comment:', error);
                    this.isCreatingOrderComment = false;
                }
            },
            orderCommentCreationCompleted() {
                this.orderComments.push({
                    user: this.authState.user,
                    ...this.createdOrderComment,
                    photos: this.form.photos.map(function(photo) { return { id: uuidv4(), temporary: true, path: photo.path } }),
                });
                this.notificationState.showSuccessNotification('Comment created!');
                this.isCreatingOrderComment = false;
                this.showOrderComments();
                this.reset();
            },
            async uploadImages(photoIndex = null) {

                let imageUploadPromises = [];

                for (let index = 0; index < this.form.photos.length; index++) {

                    const photo = this.form.photos[index];

                    if((photo.uploaded === null || photo.uploaded === false) && photo.uploading == false) {

                        if(photoIndex == null || photoIndex == index) {

                            imageUploadPromises.push(
                                this.uploadSingleImage(photo, index)
                            );
                        }
                    }
                }

                this.isUploading = true;

                return Promise.allSettled(imageUploadPromises).then((results) => {

                    let failedUploads = results.filter(result => result.status === 'rejected').length;

                    if (failedUploads > 0) {
                        this.notificationState.showWarningNotification(`⚠️ ${failedUploads} image(s) failed to upload. Try again or change the image.`);
                    }

                    this.isUploading = false; // ✅ All images uploaded (successful or failed)

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
                    formData.append('store_id', this.store.id);
                    formData.append('mediable_type', 'order comment');
                    formData.append('mediable_id', this.createdOrderComment.id);
                    formData.append('upload_folder_name', 'order_comment_photo');

                    photo.uploading = true;
                    photo.error_message = null;

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    const response = await axios.post('/api/media-files', formData, config);

                    photo.uploaded = true;
                    photo.uploading = false;

                    console.log(`✅ Image ${index + 1} uploaded successfully.`);

                    return response;

                } catch (error) {
                    console.error(`⚠️ Image ${index + 1} upload attempt ${retryCount + 1} failed.`, error);

                    // Don't retry on 504 or timeout error
                    if (error.code === 'ECONNABORTED' || error.response?.status === 504) return;

                    return this.uploadSingleImage(photo, index, retryCount + 1, error);
                }
            },
            onViewPhoto(photo) {
                this.orderCommentPhoto = photo;
                this.$refs.showPhotoModal.showModal();
            },
            async downloadOrderCommentPhoto() {
                try {

                    if(this.isDownloadingOrderCommentPhotoIds.includes(this.orderCommentPhoto.id)) return;
                    this.isDownloadingOrderCommentPhotoIds.push(this.orderCommentPhoto.id);

                    let config = {
                        responseType: 'blob',
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get(`/api/media-files/${this.orderCommentPhoto.id}/download`, config);
                    const blob = response.data;
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = this.orderCommentPhoto.name;
                    link.click();
                    URL.revokeObjectURL(url);
                } catch (error) {
                    console.error('Error downloading the store QR code image:', error);
                } finally {
                    this.isDownloadingOrderCommentPhotoIds.splice(this.isDownloadingOrderCommentPhotoIds.findIndex((id) => id == this.orderCommentPhoto.id), 1);
                }
            },
            async deleteOrderCommentPhoto() {

                try {

                    if(this.isDeletingOrderCommentPhotoIds.includes(this.orderCommentPhoto.id)) return;
                    this.isDeletingOrderCommentPhotoIds.push(this.orderCommentPhoto.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/media-files/${this.orderCommentPhoto.id}`, config);

                    this.orderComments = this.orderComments.map((orderComment) => ({
                        ...orderComment,
                        photos: orderComment.photos.filter((photo) => photo.id !== this.orderCommentPhoto.id)
                    }));

                    this.notificationState.showSuccessNotification('Photo deleted');
                    this.$refs.showPhotoModal.hideModal();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting comment photo';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete comment photo:', error);
                } finally {
                    this.isDeletingOrderCommentPhotoIds.splice(this.isDeletingOrderCommentPhotoIds.findIndex((id) => id == this.orderCommentPhoto.id), 1);
                }

            },
            async deleteOrderComment(orderComment) {

                try {

                    if(this.isDeletingOrderCommentIds.includes(orderComment.id)) return;
                    this.isDeletingOrderCommentIds.push(orderComment.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/order-comments/${orderComment.id}`, config);

                    this.notificationState.showSuccessNotification('Comment deleted');
                    this.orderComments = this.orderComments.filter(currOrderComment => currOrderComment.id != orderComment.id);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting order comment';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete order comment:', error);
                } finally {
                    this.isDeletingOrderCommentIds.splice(this.isDeletingOrderCommentIds.findIndex((id) => id == orderComment.id), 1);
                }

            }
        },
        created() {
            if(this.store) this.showOrderComments();
        }
    };

</script>
