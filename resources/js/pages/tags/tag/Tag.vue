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

                        <div v-if="isLoadingStore || isLoadingTag || (isEditing && !hasTag)" class="flex items-center space-x-2">
                            <Skeleton width="w-40"></Skeleton>
                            <Skeleton width="w-4"></Skeleton>
                        </div>

                        <template v-else>

                            <!-- Heading -->
                            <div class="flex items-center space-x-1">

                                <h1 class="text-lg text-gray-700 font-semibold">
                                    {{ isCreating ? 'Add Tag' : tagForm.name || '...' }}
                                </h1>

                                <Popover
                                     placement="top"
                                    :content="isEditingProductTag || isCreatingProductTag ? 'Tags are labels you add to products to highlight features, making it easier for customers to find related items e.g. organic, new arrival, or best seller.' : 'Tags are labels you add to customers to group them by traits or behavior, making it easier to organize and personalize interactions e.g vip, frequent buyer or needs follow-up'">
                                </Popover>

                            </div>

                        </template>

                    </div>

                </div>

                <div class="relative mb-8">

                    <BackdropLoader v-if="isLoadingTag || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <!-- Name Input -->
                        <Input
                            type="text"
                            label="Name"
                            v-model="tagForm.name"
                            :errorText="formState.getFormError('name')"
                            @input="tagState.saveStateDebounced('Name changed')"
                            :placeholder="isEditingProductTag || isCreatingProductTag ? 'main dish' : 'vip'"
                            :disabled="isEditingProductTag || isCreatingProductTag ? !tagProductsReady : !tagCustomersReady"
                            :tooltipContent="`The name of your tag e.g ${isEditingProductTag || isCreatingProductTag ? 'main dish' : 'vip'}`">
                        </Input>

                    </div>

                </div>

                <div class="relative mb-8">

                    <BackdropLoader v-if="isLoadingTag || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <TagProducts
                        @tagProductsReady="tagProductsReady = true"
                        v-if="isEditingProductTag || isCreatingProductTag">
                    </TagProducts>

                    <TagCustomers
                        v-else
                        @tagCustomersReady="tagCustomersReady = true">
                    </TagCustomers>

                </div>

                <div
                    v-if="tag"
                    :class="['flex items-center justify-between space-x-4 overflow-hidden rounded-lg p-4 border mb-20', isLoadingTag ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

                    <div class="space-y-2">
                        <p>Delete <span class="font-bold text-black">{{ tagForm.name }}</span>?</p>
                        <p class="text-sm">Once this tag is deleted you will not be able to recover it.</p>
                    </div>

                    <div class="flex justify-end">

                        <Modal
                            triggerType="danger"
                            approveType="danger"
                            :approveLeftIcon="Trash2"
                            triggerText="Delete Tag"
                            approveText="Delete Tag"
                            :approveAction="deleteTag"
                            :triggerLoading="isDeletingTag"
                            :approveLoading="isDeletingTag">

                            <template #content>
                                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                                <p class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ tagForm.name }}</span>?</p>
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
    import Popover from '@Partials/Popover.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { Trash2, MoveLeft } from 'lucide-vue-next';
    import BackdropLoader from '@Partials/BackdropLoader.vue';
    import TagProducts from '@Pages/tags/tag/tag-products/TagProducts.vue';
    import TagCustomers from '@Pages/tags/tag/tag-customers/TagCustomers.vue';

    export default {
        inject: ['formState', 'storeState', 'tagState', 'changeHistoryState', 'notificationState'],
        components: {
            Input, Modal, Button, Loader, Popover, Skeleton, BackdropLoader, TagProducts, TagCustomers
        },
        data() {
            return {
                Trash2,
                MoveLeft,
                products: [],
                tagProductsReady: false,
                tagCustomersReady: false
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            },
            tagId(newValue) {
                if(newValue) {
                    this.setup();
                    this.setActionButtons();
                }
            },
            isReady(newValue) {
                if (newValue && this.changeHistoryState.data == null) {
                    this.tagState.saveOriginalState('Original tag');
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            tag() {
                return this.tagState.tag;
            },
            hasTag() {
                return this.tagState.hasTag;
            },
            tagId() {
                return this.$route.params.tag_id;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingTag() {
                return this.tagState.isLoadingTag;
            },
            isEditing() {
                return this.isEditingProductTag || this.isEditingCustomerTag;
            },
            isCreating() {
                return this.isCreatingProductTag || this.isCreatingCustomerTag;
            },
            isEditingProductTag() {
                return this.$route.name == 'edit-product-tag';
            },
            isCreatingProductTag() {
                return this.$route.name == 'create-product-tag';
            },
            isEditingCustomerTag() {
                return this.$route.name == 'edit-customer-tag';
            },
            isCreatingCustomerTag() {
                return this.$route.name == 'create-customer-tag';
            },
            tagForm() {
                return this.tagState.tagForm;
            },
            isReady() {
                return this.isEditingProductTag || this.isCreatingProductTag ? this.tagProductsReady : this.tagCustomersReady;
            },
            isSubmitting() {
                if(this.changeHistoryState.actionButtons.length == 0) return false;
                return this.changeHistoryState.actionButtons[1].loading;
            },
            isDeletingTag() {
                return this.tagState.isDeletingTag;
            },
        },
        methods: {
            goBack() {
                this.navigateToTags();
            },
            async setup() {
                if(this.tagForm == null) this.tagState.setTagForm(null);
                if(this.isEditing && this.store) {
                    await this.showTag();
                }
            },
            async navigateToTags() {
                await this.$router.replace({
                    name: this.isEditingProductTag || this.isCreatingProductTag ? 'show-product-tags' : 'show-customer-tags',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async onView(tag) {
                await this.$router.push({
                    name: this.isCreatingProductTag ? 'edit-product-tag' : 'edit-customer-tag',
                    params: {
                        tag_id: tag.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async showTag() {
                try {
                    this.tagState.isLoadingTag = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: [this.isEditingProductTag || this.isCreatingProductTag ? 'products.photo' : 'customers'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/tags/${this.tagId}`, config);

                    const tag = response.data;
                    this.tagState.setTag(tag);
                    this.tagState.setTagForm(tag);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching tag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch tag:', error);

                    if(error.status == 404) {
                        await this.$router.replace({
                            name: 'show-tags',
                            query: {
                                store_id: this.store.id,
                                searchTerm: this.$route.query.searchTerm,
                                filterExpressions: this.$route.query.filterExpressions,
                                sortingExpressions: this.$route.query.sortingExpressions
                            }
                        });
                    }

                } finally {
                    this.tagState.isLoadingTag = false;
                }
            },
            async createTag() {

                try {

                    if(this.tagState.isCreatingTag) return;

                    this.formState.hideFormErrors();

                    if(this.tagForm.name == null || this.tagForm.name.trim() === '') {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.tagState.isCreatingTag = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.getPayload(),
                        store_id: this.store.id
                    }

                    const response = await axios.post(`/api/tags`, data);
                    const createdTag = response.data.tag;

                    this.tagForm.id = createdTag.id;

                    //  Update store silently
                    this.storeState.silentUpdate();

                    this.notificationState.showSuccessNotification(`Tag created`);
                    this.tagState.saveOriginalState('Original tag');
                    await this.onView(createdTag);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating tag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create tag:', error);
                } finally {
                    this.tagState.isCreatingTag = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async updateTag() {

                try {

                    if(this.tagState.isUpdatingTag) return;

                    this.formState.hideFormErrors();

                    if(this.tagForm.name == null || this.tagForm.name.trim() === '') {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.tagState.isUpdatingTag = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.getPayload(),
                        store_id: this.store.id
                    }

                    const response = await axios.put(`/api/tags/${this.tagForm.id}`, data);
                    const tag = response.data.tag;

                    this.tagState.setTag(tag);
                    this.tagState.setTagForm(tag);

                    //  Update store silently
                    this.storeState.silentUpdate();

                    this.notificationState.showSuccessNotification(`Tag updated`);
                    this.tagState.saveOriginalState('Original tag');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating tag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update tag:', error);
                } finally {
                    this.tagState.isUpdatingTag = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async deleteTag(hideModal) {

                try {

                    if(this.tagState.isDeletingTag) return;

                    this.tagState.isDeletingTag = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/tags/${this.tag.id}`, config);

                    //  Update store silently
                    this.storeState.silentUpdate();

                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 1000));    //  Wait for modal to close

                    this.notificationState.showSuccessNotification('Tag deleted');

                    await this.navigateToTags();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting tag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete tag:', error);
                } finally {
                    this.tagState.isDeletingTag = false;
                    hideModal();
                }

            },
            getPayload() {
                let data = cloneDeep(this.tagForm);

                if(this.isCreatingProductTag) {
                    data.product_ids = data.products_to_add.map(product => product.id);
                }else if(this.isEditingProductTag) {
                    data.product_ids_to_add = data.products_to_add.map(product => product.id);
                    data.product_ids_to_remove = data.products_to_remove.map(product => product.id);
                }else if(this.isCreatingCustomerTag) {
                    data.customer_ids = data.customers_to_add.map(product => product.id);
                }else if(this.isEditingCustomerTag) {
                    data.customer_ids_to_add = data.customers_to_add.map(customer => customer.id);
                    data.customer_ids_to_remove = data.customers_to_remove.map(customer => customer.id);
                }

                delete data.products_to_add;
                delete data.products_to_remove;
                delete data.customers_to_add;
                delete data.customers_to_remove;

                return data;
            },
            setActionButtons() {
                if(this.isCreating || this.isEditing) {
                    this.changeHistoryState.removeButtons();
                    this.changeHistoryState.addDiscardButton(this.onDiscard);
                    this.changeHistoryState.addActionButton(
                        this.isEditing ? 'Save Changes' : 'Create Tag',
                        this.isEditing ? this.updateTag : this.createTag,
                        'primary',
                        null,
                    );
                }
            },
            setTagForm(tagForm) {
                this.tagState.tagForm = tagForm;
            }
        },
        beforeRouteLeave(to, from, next) {
            //  Triggered when navigating between routes not sharing same component e.g from "edit-tag" to "show-store-home"
            if (this.changeHistoryState.hasChangeHistory) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        unmounted() {
            this.tagState.reset();
            this.changeHistoryState.reset();
        },
        created() {

            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setTagForm;
            }

        }
    };

</script>
