<template>

    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <!-- Back Button -->
        <Button
            size="xs"
            type="light"
            class="mb-4"
            :leftIcon="MoveLeft"
            :action="navigateToShowDomains">
            <span>Back</span>
        </Button>

        <!-- Domain Form -->
        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 mb-4">

            <div
                class="flex justify-end"
                v-if="!isLoadingStore && !isLoadingDomain && isNotEmpty(domainForm.name)">
                <Pill v-if="domainStatus" :type="domainStatus.type" size="xs">{{ domainStatus.label }}</Pill>
            </div>

            <Input
                type="text"
                label="Domain Name"
                placeholder="example.com"
                v-model="domainForm.name"
                :skeleton="isLoadingStore || isLoadingDomain"
                :errorText="formState.getFormError('name')"
                @input="domainState.saveStateDebounced('Domain name changed')"
                tooltipContent="The domain name for your store (e.g., example.com)"
            />

        </div>

        <!-- DNS Instructions (shown after domain creation) -->
        <div
            class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4 mb-4"
            v-if="!isLoadingStore && !isLoadingDomain && isNotEmpty(domainForm.name) && !connected">

            <div class="flex items-center space-x-2 mb-4">
                <Globe size="20"></Globe>
                <h1 class="font-bold">Complete Connection</h1>
            </div>

            <p
                class="text-sm text-gray-600">
                To connect <span class="font-bold">{{ domainForm.name }}</span>, update the DNS settings at your domain provider (e.g., Namecheap, GoDaddy, etc.) with the following <span class="font-bold">A record</span> to prove ownership
            </p>

            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center space-x-4">
                    <div class="w-1/3">
                        <p class="text-sm font-semibold mb-2">Type</p>
                        <p class="text-sm">A</p>
                    </div>
                    <div class="w-1/3">
                        <p class="text-sm font-semibold mb-2">Host</p>
                        <p class="text-sm">@</p>
                    </div>
                    <div class="w-1/3">
                        <p class="text-sm font-semibold mb-2">Value</p>
                        <p class="text-sm">{{ serverIp }}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-center mt-4">

                <Button
                    size="md"
                    type="success"
                    class="w-full"
                    buttonClass="w-full"
                    :leftIcon="RefreshCcw"
                    :loading="isVerifyingDomain"
                    :action="verifyDomainConnection">
                    <span class="ml-1">Check Connection</span>
                </Button>

            </div>

        </div>

        <!-- Delete Domain (edit mode only) -->
        <div
            v-if="domain && !isLoadingStore && !isLoadingDomain && isEditing"
            :class="['flex items-center justify-between space-x-4 overflow-hidden rounded-lg p-4 border mt-4', isLoadingStore || isLoadingDomain ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

            <div class="space-y-2">
                <p>Delete <span class="font-bold text-black">{{ domain.name }}</span>?</p>
            </div>

            <div class="flex justify-end">

                <Modal
                    triggerType="danger"
                    approveType="danger"
                    :approveLeftIcon="Trash2"
                    triggerText="Delete Domain"
                    approveText="Delete Domain"
                    :approveAction="deleteDomain"
                    :triggerLoading="isDeletingDomain"
                    :approveLoading="isDeletingDomain">
                    <template #content>
                        <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                        <p class="mb-8">Are you sure you want to delete <span class="font-bold text-black">{{ domain.name }}</span>?</p>
                    </template>
                </Modal>

            </div>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Alert from '@Partials/Alert.vue';
    import Modal from '@Partials/Modal.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isEmpty, isNotEmpty } from '@Utils/stringUtils';
    import { Plus, Globe, RefreshCcw, MoveLeft, Trash2, CheckCircle } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'domainState', 'changeHistoryState', 'notificationState'],
        components: { Pill, Alert, Modal, Input, Button, Skeleton, Globe, MoveLeft, Trash2, CheckCircle },
        data() {
            return {
                Plus,
                Globe,
                Trash2,
                MoveLeft,
                RefreshCcw,
                serverIp: null,
                connected: false,
                existingDomainNames: [],
                isLoadingDomains: false,
                isLoadingServerIp: false,
                originalDomainName: null,
                originalDomainStatus: null
            }
        },
        watch: {
            store(newValue, oldValue) {
                if (!oldValue && newValue) {
                    this.setup();
                }
            },
            domainForm: {
                handler(newVal) {
                    if(newVal.name == this.originalDomainName && this.originalDomainStatus == 'connected') {
                        this.connected = true;
                    }else{
                        this.connected = false;
                    }
                },
                deep: true
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isCreating() {
                return this.domainId == null;
            },
            isEditing() {
                return this.domainId != null;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            domainId() {
                return this.$route.params.domain_id;
            },
            domain() {
                return this.domainState.domain;
            },
            domainForm() {
                return this.domainState.domainForm;
            },
            isLoadingDomain() {
                return this.domainState.isLoadingDomain;
            },
            isCreatingDomain() {
                return this.domainState.isCreatingDomain;
            },
            isUpdatingDomain() {
                return this.domainState.isUpdatingDomain;
            },
            isDeletingDomain() {
                return this.domainState.isDeletingDomain;
            },
            isVerifyingDomain() {
                return this.domainState.isVerifyingDomain;
            },
            domainStatus() {
                if (this.connected === true) {
                    return { label: 'connected', type: 'success' };
                }else{
                    return { label: 'pending', type: 'warning' };
                }
            }
        },
        methods: {
            isEmpty: isEmpty,
            isNotEmpty: isNotEmpty,
            setup() {
                this.domainState.setDomainForm(null, true);

                if(this.store) this.showDomains();
                if(this.store && this.domainId) this.showDomain();
                if(this.store && !this.serverIp) this.showServerIp();
            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    this.isEditing ? 'Save Changes' : 'Add Domain',
                    this.isEditing ? this.updateDomain : this.createDomain,
                    'primary',
                    null
                );
            },
            async navigateToShowDomains() {
                await this.$router.push({
                    name: 'show-domains',
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            async navigateToEditDomain() {
                await this.$router.push({
                    name: 'edit-domain',
                    query: {
                        store_id: this.store.id
                    },
                    params: {
                        domain_id: this.domain.id
                    }
                });
            },
            async showServerIp() {
                try {

                    this.isLoadingServerIp = true;

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get(`/api/domains/server-ip`, config);
                    this.serverIp = response.data.server_ip;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching server IP';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch server IP:', error);
                } finally {
                    this.isLoadingServerIp = false;
                }
            },
            async showDomains() {
                try {

                    this.isLoadingDomains = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get('/api/domains', config);
                    const pagination = response.data;
                    this.existingDomainNames = pagination.data.map(domain => domain.name);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching domains';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch domains:', error);
                } finally {
                    this.isLoadingDomains = false;
                }
            },
            async showDomain() {
                try {

                    this.domainState.isLoadingDomain = true;

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get(`/api/domains/${this.domainId}`, config);
                    const domain = response.data;

                    this.domainState.setDomain(domain);
                    this.domainState.setDomainForm(domain, true);

                    this.$nextTick(() => {
                        this.connected = domain.status === 'connected' ? true : false;
                        this.originalDomainStatus = this.domain.status;
                        this.originalDomainName = this.domain.name;
                    });

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching domain';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch domain:', error);
                } finally {
                    this.domainState.isLoadingDomain = false;
                }
            },
            async createDomain() {
                try {

                    if (this.domainState.isCreatingDomain) return;

                    this.formState.hideFormErrors();

                    if (this.isEmpty(this.domainForm.name)) {
                        this.formState.setFormError('name', 'The domain name is required');
                    }else if(this.existingDomainNames.includes(this.domainForm.name)) {
                        this.formState.setFormError('name', 'The domain name already exists');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.domainState.isCreatingDomain = true;
                    this.changeHistoryState.actionButtons[1].loading = true;
                    const data = {
                        name: this.domainForm.name,
                        active: this.domainForm.active,
                        store_id: this.store.id
                    };

                    const response = await axios.post('/api/domains', data);
                    const domain = response.data.domain;

                    this.domainState.setDomain(domain);
                    this.domainState.setDomainForm(domain, true);
                    this.connected = domain.status === 'connected';

                    this.originalDomainName = domain.name;
                    this.originalDomainStatus = domain.status;

                    this.notificationState.showSuccessNotification('Domain created');
                    this.domainState.saveOriginalState('Original domain');
                    this.navigateToEditDomain();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating domain';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create domain:', error);
                } finally {
                    this.domainState.isCreatingDomain = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },
            async updateDomain() {
                try {

                    if (this.domainState.isUpdatingDomain) return;

                    this.formState.hideFormErrors();

                    if (this.isEmpty(this.domainForm.name)) {
                        this.formState.setFormError('name', 'The domain name is required');
                    }else if(this.domain.name != this.domainForm.name && this.existingDomainNames.includes(this.domainForm.name)) {
                        this.formState.setFormError('name', 'The domain name already exists');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.domainState.isUpdatingDomain = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        name: this.domainForm.name,
                        active: this.domainForm.active,
                        store_id: this.store.id
                    };

                    const response = await axios.put(`/api/domains/${this.domainId}`, data);
                    const domain = response.data.domain;

                    this.domainState.setDomain(domain);
                    this.domainState.setDomainForm(domain, true);
                    this.connected = domain.status === 'connected';

                    this.originalDomainName = domain.name;
                    this.originalDomainStatus = domain.status;

                    this.notificationState.showSuccessNotification('Domain updated');
                    this.domainState.saveOriginalState('Original domain');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating domain';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update domain:', error);
                } finally {
                    this.domainState.isUpdatingDomain = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },
            async deleteDomain(hideModal) {
                try {

                    if (this.domainState.isDeletingDomain) return;
                    this.domainState.isDeletingDomain = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    };

                    await axios.delete(`/api/domains/${this.domainId}`, config);

                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500));
                    this.notificationState.showSuccessNotification('Domain deleted');
                    await this.navigateToShowDomains();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting domain';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete domain:', error);
                    hideModal();
                } finally {
                    this.domainState.isDeletingDomain = false;
                }
            },
            async verifyDomainConnection() {
                try {

                    if (this.domainState.isVerifyingDomain) return;

                    this.formState.hideFormErrors();

                    if((this.isCreating || (this.isEditing && this.domain.name != this.domainForm.name)) && this.existingDomainNames.includes(this.domainForm.name)) {
                        this.formState.setFormError('name', 'The domain name already exists');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.domainState.isVerifyingDomain = true;

                    const data = {
                        store_id: this.store.id,
                        name: this.domainForm.name
                    };

                    const response = await axios.post(`/api/domains/verify-connection`, data);
                    this.connected = response.data.connected;

                    if(this.connected) {
                        if(this.isCreating) {
                            this.createDomain();
                        }else{
                            this.updateDomain();
                        }
                    }else{
                        this.notificationState.showWarningNotification(`${this.domainForm.name} is not connected`);
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while verifying domain connection';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to verify domain connection:', error);
                } finally {
                    this.domainState.isVerifyingDomain = false;
                }
            },
            setDomainForm(domainForm) {
                this.domainState.domainForm = domainForm;
            }
        },
        beforeUnmount() {
            this.domainState.reset();
        },
        created() {
            this.setup();
            this.setActionButtons();
            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];
            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setDomainForm;
            }
        }
    };
</script>
