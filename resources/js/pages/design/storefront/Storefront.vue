<template>

    <div>

        <Dropdown
            ref="dropdown"
            dropdownClasses="w-full">

            <template #trigger="props">

                <Button
                    size="md"
                    type="light"
                    class="w-full"
                    :leftIcon="Plus"
                    buttonClass="w-full"
                    :action="props.toggleDropdown">
                    <span>Add Design</span>
                </Button>

            </template>

            <template #content="props">

                <div class="z-20 bg-white rounded-lg shadow-md">
                    <div class="grid grid-cols-3 divide-x divide-y divide-gray-200">
                        <div
                            :key="index"
                            v-for="(designCardOption, index) in designCardOptions"
                            @click="(e) => addDesignCard(designCardOption.value, props.toggleDropdown, e)"
                            class="py-4 hover:bg-gray-100 cursor-pointer flex flex-col space-y-4 justify-between items-center">
                            <Map v-if="designCardOption.value == 'map'" size="20"></Map>
                            <Link v-if="designCardOption.value == 'link'" size="20"></Link>
                            <Type v-if="designCardOption.value == 'text'" size="20"></Type>
                            <Box v-if="designCardOption.value == 'products'" size="20"></Box>
                            <Image v-if="designCardOption.value == 'image'" size="20"></Image>
                            <Video v-if="designCardOption.value == 'video'" size="20"></Video>
                            <Clock v-if="designCardOption.value == 'countdown'" size="20"></Clock>
                            <Contact v-if="designCardOption.value == 'contact'" size="20"></Contact>
                            <span class="text-xs whitespace-nowrap">{{ designCardOption.label }}</span>
                        </div>
                    </div>
                </div>

            </template>

        </Dropdown>

        <template v-if="isLoadingStore || isLoadingDesignCards">

        </template>

        <template v-else-if="designCards.length">

            <!-- Draggable Fields -->
            <draggable
                class="mt-4 space-y-2"
                handle=".draggable-handle"
                ghost-class="bg-yellow-50"
                v-model="designState.designForm.design_cards"
                @change="designState.saveStateDebounced('Design card moved')">

                <template
                    :key="index"
                    v-for="(designCard, index) in designState.designForm.design_cards">

                    <div
                        v-if="!designCard.hasOwnProperty('delete')"
                        class="w-full bg-gray-50 p-4 border border-gray-300 rounded-lg">

                        <div class="flex items-center justify-between mb-4">

                            <div class="flex items-center space-x-2 text-gray-500">
                                <Map v-if="designCard.metadata.type == 'map'" size="16"></Map>
                                <Link v-if="designCard.metadata.type == 'link'" size="16"></Link>
                                <Type v-if="designCard.metadata.type == 'text'" size="16"></Type>
                                <Box v-if="designCard.metadata.type == 'products'" size="16"></Box>
                                <Image v-if="designCard.metadata.type == 'image'" size="16"></Image>
                                <Video v-if="designCard.metadata.type == 'video'" size="16"></Video>
                                <Clock v-if="designCard.metadata.type == 'countdown'" size="16"></Clock>
                                <Contact v-if="designCard.metadata.type == 'contact'" size="16"></Contact>
                                <span class="text-xs whitespace-nowrap">{{ getDesignCardLabel(designCard) }}</span>
                            </div>

                            <!-- Drag & Drop Handle -->
                            <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                        </div>

                        <template v-if="designCard.metadata.type == 'products'">

                            <div class="grid grid-cols-2 gap-4">

                                <!-- Category -->
                                <Select
                                    class="mb-2"
                                    width="w-full"
                                    :search="false"
                                    label="Category"
                                    :options="categories"
                                    v-model="designCard.metadata.category_id"
                                    @change="designState.saveStateDebounced('Category changed')"
                                    :errorText="formState.getFormError(`design_cards.${index}.metadata.category_id`)">
                                </Select>

                                <Select
                                    :search="false"
                                    label="Feature"
                                    class="w-full mb-4"
                                    :options="featureOptions"
                                    v-model="designCard.metadata.feature"
                                    @change="designState.saveStateDebounced('Feature number changed')"
                                    :errorText="formState.getFormError(`design_cards.${index}.metadata.feature`)">
                                </Select>

                            </div>

                            <Tabs
                                size="sm"
                                class="w-full"
                                :tabs="layoutTabs"
                                v-model="designCard.metadata.layout"
                                @change="designState.saveStateDebounced('Layout changed')"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.layout`)">
                            </Tabs>

                        </template>

                        <template v-if="designCard.metadata.type == 'link'">

                            <Input
                                type="text"
                                class="w-full mb-4"
                                placeholder="Title"
                                v-model="designCard.metadata.title"
                                @input="designState.saveStateDebounced('Title changed')"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
                            </Input>

                            <Input
                                type="text"
                                label="Link"
                                class="w-full"
                                placeholder="https://"
                                v-model="designCard.metadata.link"
                                @input="designState.saveStateDebounced('Link changed')"
                                tooltipContent="Include https:// at the begining of your link"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.link`)">
                            </Input>

                        </template>

                        <template v-if="designCard.metadata.type == 'text'">

                            <div class="flex items-center space-x-2 mb-4">

                                <Pill :type="designCard.metadata.mode == 'content' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'content'">Content</Pill>
                                <Pill :type="designCard.metadata.mode == 'design' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'design'">Design</Pill>

                            </div>

              <vue-easymde
                v-model="designCard.metadata.body"
                :options="editorOptions"
                class="text-xs"
                @input="designState.saveStateDebounced('Text content changed')"
              />

                        </template>

                        <template v-if="designCard.metadata.type == 'image'">

                            <Input
                                type="text"
                                class="w-full mb-4"
                                placeholder="Title"
                                v-model="designCard.metadata.title"
                                @input="designState.saveStateDebounced('Title changed')"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
                            </Input>

                            <Input
                                type="file"
                                class="mb-4"
                                :maxFiles="1"
                                :imagePreviewGridCols="1"
                                v-model="designCard.photo"
                                singleFileUploadMessage="Image attached"
                                @change="(photo) => designState.saveStateDebounced(photo.length ? 'Image added' : 'Image removed')">
                            </Input>

                            <Input
                                type="text"
                                label="Link"
                                class="w-full"
                                placeholder="https://"
                                secondaryLabel="(optional)"
                                v-model="designCard.metadata.link"
                                @input="designState.saveStateDebounced('Link changed')"
                                tooltipContent="Include https:// at the begining of your link"
                                description="We will redirect to this link when image is clicked"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.link`)">
                            </Input>

                        </template>

                        <template v-if="designCard.metadata.type == 'video'">

                            <Input
                                type="text"
                                class="w-full mb-4"
                                placeholder="Title"
                                v-model="designCard.metadata.title"
                                @input="designState.saveStateDebounced('Title changed')"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
                            </Input>

                            <Input
                                type="text"
                                class="w-full"
                                label="Video link"
                                v-model="designCard.metadata.link"
                                placeholder="Youtube or Tiktok video link"
                                @input="designState.saveStateDebounced('Video link changed')"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.link`)"
                                tooltipContent="Copy the link from the web browser (not from the app) to add a Tiktok video">
                            </Input>

                        </template>

                        <template v-if="designCard.metadata.type == 'contact'">

                            <Input
                                type="text"
                                class="w-full mb-4"
                                placeholder="Title"
                                v-model="designCard.metadata.title"
                                @input="designState.saveStateDebounced('Title changed')"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
                            </Input>

                        </template>

                        <template v-if="designCard.metadata.type == 'countdown'">

                            <Input
                                type="text"
                                class="w-full mb-4"
                                placeholder="Title"
                                v-model="designCard.metadata.title"
                                @input="designState.saveStateDebounced('Title changed')"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
                            </Input>

                            <Input
                                rows="2"
                                type="textarea"
                                class="w-full mb-4"
                                placeholder="Description"
                                v-model="designCard.metadata.description"
                                @input="designState.saveStateDebounced('Description changed')"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.description`)">
                            </Input>

                            <Datepicker
                                class="mb-4"
                                placeholder="Countdown to date"
                                v-model="designCard.metadata.date"
                                @change="designState.saveStateDebounced('Countdown date changed')"
                                :errorText="formState.getFormError(`design_cards.${index}.metadata.date`)">
                            </Datepicker>

                            <Input
                                type="file"
                                :maxFiles="1"
                                :imagePreviewGridCols="1"
                                v-model="designCard.photo"
                                singleFileUploadMessage="Image attached"
                                @change="(photo) => designState.saveStateDebounced(photo.length ? 'Image added' : 'Image removed')">
                            </Input>

                        </template>

                        <template v-if="designCard.metadata.type == 'map'">

                            <AddressInput
                                height="250px"
                                :onlyValidate="true"
                                :pinLocationOnMap="true"
                                triggerClass="space-y-4"
                                :address="designCard.metadata.address"
                                @onDeleted="() => unsetAddress(index)"
                                @onValidated="(address) => setAddress(index, address)">
                            </AddressInput>

                        </template>

                        <div class="flex justify-end space-x-4 mt-4">

                            <!-- Visible Toggle -->
                            <div class="cursor-pointer hover:text-blue-500 transition-all duration-500">
                                <Eye v-if="designCard.visible" size="16" @click.stop="() => toggleVisible(index)"></Eye>
                                <EyeOff v-else size="16" @click.stop="() => toggleVisible(index)"></EyeOff>
                            </div>

                            <Trash size="16" class="cursor-pointer" @click.stop="() => removeDesignCard(index)"></Trash>

                        </div>

                    </div>

                </template>

            </draggable>

        </template>

        <template v-else>

            <div class="flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg space-y-4 my-4">

                <Store size="32" class="text-gray-500"></Store>
                <p class="text-sm text-gray-500">Add designs for your storefront</p>

            </div>

        </template>

    </div>

</template>

<script>

    import isEqual from 'lodash/isEqual';
    import Pill from '@Partials/Pill.vue';
    import Tabs from '@Partials/Tabs.vue';
    import Input from '@Partials/Input.vue';
    import cloneDeep from 'lodash/cloneDeep';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import Switch from '@Partials/Switch.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import Datepicker from '@Partials/Datepicker.vue';
    import AddressInput from '@Partials/AddressInput.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import { Box, Eye, Map, Move, Plus, List, Link, Type, Store, Clock, Trash, Image, Video, EyeOff, LayoutGrid, Contact } from 'lucide-vue-next';


    export default {
        inject: ['formState', 'storeState', 'designState', 'notificationState', 'changeHistoryState'],
        components: {
            Box, Eye, Map, Move, List, Link, Type, Store, Clock, Trash, Image, Video, EyeOff, LayoutGrid, Contact,
            Pill, Tabs, Input, Button, Select, Switch, Dropdown, Datepicker, AddressInput, draggable: VueDraggableNext
        },
        emits: ['designCards'],
        data() {
            return {
                Plus,
                categories: [],
                isUploading: false,
                editorOptions: {
                    toolbar: [
                        'bold',          // Bold (**text**)
                        'italic',        // Italic (_text_)
                        'strikethrough', // Strikethrough (~text~)
                        '|',
                        'heading-1',     // Heading 1 (# Heading)
                        'heading-2',     // Heading 2 (## Heading)
                        'heading-3',     // Heading 3 (### Heading)
                        '|',
                        'unordered-list',// Bullet list (- Item)
                        'ordered-list',  // Numbered list (1. Item)
                        '|',
                        'link',          // Link [text](url)
                        'image',         // Image ![alt](url)
                        'quote',         // Blockquote (> Quote)
                        'code',          // Inline code (`code`)
                        'table',         // Table
                        '|',
                        'fullscreen',    // Fullscreen mode
                    ],
                    spellChecker: false, // Disable spell check for simplicity
                    status: false,       // Hide status bar
                    autofocus: true,     // Focus editor on load
                    minHeight: '200px',  // Comfortable editing area
                    placeholder: 'Type your content... Use the toolbar for formatting',
                    previewRender: (plainText) => {
                        // Custom preview rendering to match Storefront.vue
                        const rawHtml = marked(plainText, { breaks: true, gfm: true });
                        return DOMPurify.sanitize(rawHtml, { ADD_TAGS: ['img'], ADD_ATTR: ['src', 'alt'] });
                    },
                },
                layoutTabs: [
                    { label: 'List', value: 'list', leftIcon: List },
                    { label: 'Grid', value: 'grid', leftIcon: LayoutGrid },
                ],
                featureOptions: [
                    { label: '4 products', value: '4'},
                    { label: '6 products', value: '6'},
                    { label: '8 products', value: '8'},
                ],
                designCardOptions: [
                    { label: 'Products', value: 'products'},
                    { label: 'Link', value: 'link'},
                    { label: 'Text', value: 'text'},
                    { label: 'Image', value: 'image'},
                    { label: 'Video', value: 'video'},
                    { label: 'Contact', value: 'contact'},
                    { label: 'Countdown', value: 'countdown'},
                    { label: 'Map', value: 'map'},
                ],
                isLoadingDesignCards: false,
                hasLoadedInitialdesignCards: false,
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                    this.setActionButtons();
                }
            },
            designCards(newValue) {
                this.$emit('designCards', newValue);
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
        },
        methods: {
            async setup() {
                if(this.store) {

                    this.categories = this.store.categories.map((category) => {
                        return {
                            label: category.name,
                            value: category.id
                        }
                    });

                    if(!this.hasLoadedInitialdesignCards && !this.isLoadingDesignCards) {
                        this.showDesignCards();
                    }
                }
            },
            getDesignCardLabel(designCard) {
                return this.designCardOptions.find(designCardOption => designCardOption.value == designCard.metadata.type).label;
            },
            toggleVisible(index) {
                this.designCards[index].visible = !this.designCards[index].visible;
                this.designState.saveStateDebounced(this.designCards[index].visible ? 'Design card hidden' : 'Design card visible');
            },
            addDesignCard(type, toggleDropdown, e) {

                let metadata;

                if(type == 'products') {
                    metadata = {
                        category_id: this.categories.length ? this.categories[0].value : null,
                        layout: 'grid',
                        feature: '4'
                    };
                }else if(type == 'link') {
                    metadata = {
                        title: '',
                        link: ''
                    };
                }else if(type == 'text') {

                    let markdown = `# Heading 1\n## Heading 2\n### Heading 3\n**bold**\n~strike~\n_italic_\n**_Bold italic_**`;

                    metadata = {
                        body: markdown
                    };

                }else if(type == 'image') {
                    metadata = {
                        title: '',
                        link: ''
                    };
                }else if(type == 'video') {
                    metadata = {
                        title: '',
                        link: ''
                    };
                }else if(type == 'contact') {
                    metadata = {
                        title: ''
                    };
                }else if(type == 'countdown') {
                    metadata = {
                        date: '',
                        title: '',
                        description: ''
                    };
                }else if(type == 'map') {
                    metadata = {
                        address: null
                    };
                }

                metadata.type = type;
                metadata.mode = 'content';

                let designCard = {
                    visible: true,
                    type: 'storefront',
                    metadata: metadata,
                };

                if(['image', 'countdown'].includes(type)) {
                    designCard.photo = [];
                }

                this.designCards.unshift(designCard);
                this.designState.saveStateDebounced('Design card added');
                toggleDropdown(e);
            },
            removeDesignCard(index) {
                let designCard = this.designCards[index];

                if(designCard.id) {
                    this.designCards[index].delete = true;
                }else{
                    this.designCards.splice(index, 1);
                }
                this.designState.saveStateDebounced('Design card removed');
            },
            setAddress(index, address) {
                this.designCards[index].address = address;
                this.designState.saveStateDebounced('Address added');
            },
            unsetAddress(index) {
                this.designCards[index].address = null;
                this.designState.saveStateDebounced('Address removed');
            },
            async showDesignCards() {
                try {

                    this.isLoadingDesignCards = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get('/api/design-cards', config);
                    const designCards = response.data.data;

                    this.designState.setDesignForm({
                        design_cards: designCards
                    });

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching design cards';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch design cards:', error);
                } finally {
                    this.isLoadingDesignCards = false;
                    this.hasLoadedInitialdesignCards = true;
                }
            },
            async updateDesignCards() {
                try {

                    if (this.designState.isUpdatingDesign) return;
                    this.designState.isUpdatingDesign = true;

                    const originalState = this.changeHistoryState.getOriginalState();
                    let totalUpdated = 0;

                    const promises = this.designCards.map(async (designCard, index) => {
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
                                position: index + 1,
                                type: designCard.type,
                                store_id: this.store.id,
                                visible: designCard.visible,
                                metadata: designCard.metadata
                            };

                            let response;

                            if (designCard.id) {

                                const currDesignCard = cloneDeep(designCard);
                                const originalDesignCard = cloneDeep(originalState.design_cards.find(originalDesignCard => originalDesignCard.id == designCard.id));

                                if(!originalDesignCard || isEqual(currDesignCard, originalDesignCard)) return;

                                response = await axios.put(`/api/design-cards/${designCard.id}`, designCardData);
                                ++totalUpdated;

                                if (designCard.hasOwnProperty('photo')) {
                                    await this.uploadImages(designCard.id, null, index);
                                }

                            } else {

                                response = await axios.post('/api/design-cards', designCardData);
                                ++totalUpdated;

                                this.designCards[index].id = response.data.design_card.id;

                                if (designCard.hasOwnProperty('photo')) {
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

                    await Promise.allSettled(promises).then((results) => {
                        const successCount = results.filter(r => r.status === 'fulfilled').length;
                        if (successCount > 0) {
                            this.notificationState.showSuccessNotification(`${totalUpdated} design card${totalUpdated == 1 ? '' : 's'} updated successfully`);
                            this.designState.setDesignForm(this.designForm);
                        }
                    });
                } catch (error) {
                    this.notificationState.showWarningNotification('An unexpected error occurred while processing design cards.');
                    console.error(error);
                } finally {
                    this.designState.isUpdatingDesign = false;
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
                    formData.append('mediable_type', 'design_card');
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
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton(this.onDiscard);
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
        unmounted() {
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


<style scoped>
@reference 'tailwindcss';

:deep(.EasyMDEContainer.border-red-500) {
  @apply border-red-500 ring-1 ring-red-500;
}

/* Toolbar styles */
:deep(.editor-toolbar) {
  @apply bg-gray-100 border-b border-gray-300 rounded-t-md p-2 flex flex-wrap gap-1;
}

:deep(.editor-toolbar button) {
  @apply text-gray-700 hover:bg-gray-200 hover:text-gray-900 rounded p-1 transition-colors;
}

:deep(.editor-toolbar button.active) {
  @apply bg-blue-100 text-blue-700;
}

:deep(.editor-toolbar .separator) {
  @apply border-l border-gray-300 mx-1;
}

/* Editor content styles to match Storefront.vue */
:deep(.CodeMirror) {
  @apply border-l border-r border-b bg-white text-gray-700 font-sans text-base leading-6 p-3 rounded-b-md;
  min-height: 200px;
}

:deep(.CodeMirror .CodeMirror-placeholder) {
  @apply text-gray-400;
}

/* Match markdown-content styles for editing area */
:deep(.CodeMirror .cm-header-1) {
  @apply text-3xl font-bold text-red-900;
}

:deep(.CodeMirror .cm-header-2) {
  @apply text-2xl font-bold text-red-900;
}

:deep(.CodeMirror .cm-header-3) {
  @apply text-xl font-bold text-red-900;
}

:deep(.CodeMirror .cm-header-4) {
  @apply text-lg font-bold text-red-900;
}

:deep(.CodeMirror .cm-header-5) {
  @apply text-base font-bold text-red-900;
}

:deep(.CodeMirror .cm-header-6) {
  @apply text-sm font-bold text-red-900;
}

:deep(.CodeMirror .cm-string:not(.cm-url):not(.cm-comment)) {
  @apply text-base text-gray-700;
}

:deep(.CodeMirror .cm-strong) {
  @apply font-bold;
}

:deep(.CodeMirror .cm-em) {
  @apply italic;
}

:deep(.CodeMirror .cm-strikethrough) {
  @apply line-through;
}

:deep(.CodeMirror .cm-link) {
  @apply text-blue-600 underline;
}

:deep(.CodeMirror .cm-quote) {
  @apply text-gray-600 italic;
}

:deep(.CodeMirror .cm-code) {
  @apply font-mono text-sm bg-gray-100 rounded;
}

:deep(.CodeMirror .cm-variable-2) {
  /* Lists */
  @apply text-gray-700;
}

/* Preview styles to match Storefront.vue */
:deep(.editor-preview) {
  @apply bg-white p-4 rounded-b-md text-gray-700;
}

:deep(.editor-preview h1) {
  @apply text-3xl font-bold text-gray-900 mb-4;
}

:deep(.editor-preview h2) {
  @apply text-2xl font-bold text-gray-900 mb-3;
}

:deep(.editor-preview h3) {
  @apply text-xl font-bold text-gray-900 mb-2;
}

:deep(.editor-preview h4) {
  @apply text-lg font-bold text-gray-900 mb-2;
}

:deep(.editor-preview h5) {
  @apply text-base font-bold text-gray-900 mb-2;
}

:deep(.editor-preview h6) {
  @apply text-sm font-bold text-gray-900 mb-2;
}

:deep(.editor-preview p) {
  @apply text-base text-gray-700 mb-4;
}

:deep(.editor-preview strong) {
  @apply font-bold;
}

:deep(.editor-preview em) {
  @apply italic;
}

:deep(.editor-preview del) {
  @apply line-through;
}

:deep(.editor-preview ul) {
  @apply list-disc pl-6 mb-4;
}

:deep(.editor-preview ol) {
  @apply list-decimal pl-6 mb-4;
}

:deep(.editor-preview li) {
  @apply mb-2;
}

:deep(.editor-preview a) {
  @apply text-blue-600 underline hover:text-blue-800;
}

:deep(.editor-preview blockquote) {
  @apply border-l-4 border-gray-300 pl-4 text-gray-600 italic mb-4;
}

:deep(.editor-preview pre) {
  @apply bg-gray-100 p-4 rounded-md overflow-x-auto mb-4;
}

:deep(.editor-preview code) {
  @apply font-mono text-sm;
}

:deep(.editor-preview table) {
  @apply w-full border-collapse mb-4;
}

:deep(.editor-preview th, .editor-preview td) {
  @apply border border-gray-300 p-2;
}

:deep(.editor-preview th) {
  @apply bg-gray-50 font-semibold text-gray-900;
}

:deep(.editor-preview img) {
  @apply max-w-full h-auto mb-4;
}

/* Fullscreen mode */
:deep(.editor-toolbar.fullscreen) {
  @apply fixed top-0 left-0 right-0 z-50 bg-gray-100;
}

:deep(.CodeMirror-fullscreen) {
  @apply fixed top-12 left-0 right-0 bottom-0 z-50 bg-white;
}
</style>
