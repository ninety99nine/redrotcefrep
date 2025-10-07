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
                :disabled="domain?.type == 'purchased'"
                :errorText="formState.getFormError('name')"
                :skeleton="isLoadingStore || isLoadingDomain"
                @input="domainState.saveStateDebounced('Domain name changed')"
                tooltipContent="The domain name for your store (e.g., example.com)"
                :description="domain?.type == 'purchased' ? 'This domain cannot be changed because its a purchased domain' : null"
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

        <!-- Domain Contacts -->
        <div
            v-if="!isLoadingStore && !isLoadingDomain && (isLoadingDomainContacts || domainContacts)"
            class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4 mb-4">

            <div class="flex items-center space-x-2 mb-4">
                <User size="20"></User>
                <h1 class="font-bold">Domain Contacts</h1>
            </div>

            <Tabs
                :tabs="tabs"
                v-model="tab">
            </Tabs>

            <p class="bg-blue-50 border border-blue-100 rounded-lg text-sm p-2">{{ tabs[selectedTabIndex].description }}</p>

            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-semibold mb-1">First Name</p>
                        <Skeleton v-if="isLoadingDomainContacts" width="w-40" class="mt-2"></Skeleton>
                        <p v-else class="text-sm">{{ domainContacts[tab].first_name || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold mb-1">Last Name</p>
                        <Skeleton v-if="isLoadingDomainContacts" width="w-40" class="mt-2"></Skeleton>
                        <p v-else class="text-sm">{{ domainContacts[tab].last_name || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold mb-1">Email</p>
                        <Skeleton v-if="isLoadingDomainContacts" width="w-40" class="mt-2"></Skeleton>
                        <p v-else class="text-sm">{{ domainContacts[tab].email || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold mb-1">Phone</p>
                        <Skeleton v-if="isLoadingDomainContacts" width="w-40" class="mt-2"></Skeleton>
                        <p v-else class="text-sm">{{ domainContacts[tab].phone || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold mb-1">Address</p>
                        <Skeleton v-if="isLoadingDomainContacts" width="w-40" class="mt-2"></Skeleton>
                        <p v-else class="text-sm">{{ domainContacts[tab].address1 || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold mb-1">City</p>
                        <Skeleton v-if="isLoadingDomainContacts" width="w-40" class="mt-2"></Skeleton>
                        <p v-else class="text-sm">{{ domainContacts[tab].city || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold mb-1">State</p>
                        <Skeleton v-if="isLoadingDomainContacts" width="w-40" class="mt-2"></Skeleton>
                        <p v-else class="text-sm">{{ domainContacts[tab].state || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold mb-1">Postal Code</p>
                        <Skeleton v-if="isLoadingDomainContacts" width="w-40" class="mt-2"></Skeleton>
                        <p v-else class="text-sm">{{ domainContacts[tab].postal_code || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold mb-1">Country</p>
                        <Skeleton v-if="isLoadingDomainContacts" width="w-40" class="mt-2"></Skeleton>
                        <p v-else class="text-sm">{{ domainContacts[tab].country || 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <p class="text-sm">
                Your domain is provided by
                <a href="https://www.namecheap.com" target="_blank" class="underline inline-flex items-center">
                    <span>Namecheap.com</span>
                    <ExternalLink size="14" class="ml-1" />
                </a>
            </p>

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

    import Tabs from '@Partials/Tabs.vue';
    import Pill from '@Partials/Pill.vue';
    import Alert from '@Partials/Alert.vue';
    import Modal from '@Partials/Modal.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isEmpty, isNotEmpty } from '@Utils/stringUtils';
    import { User, Plus, Globe, RefreshCcw, MoveLeft, Trash2, CheckCircle, ExternalLink } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'domainState', 'changeHistoryState', 'notificationState'],
        components: { Tabs, Pill, Alert, Modal, Input, Button, Skeleton, User, Globe, MoveLeft, Trash2, CheckCircle, ExternalLink },
        data() {
            return {
                Plus,
                Globe,
                Trash2,
                MoveLeft,
                RefreshCcw,
                serverIp: null,
                connected: false,
                domainContacts: null,
                existingDomainNames: [],
                isLoadingDomains: false,
                isLoadingServerIp: false,
                originalDomainName: null,
                originalDomainStatus: null,
                tab: 'registrant',
                tabs: [
                    {
                        label: 'Domain Owner',
                        value: 'registrant',
                        description: 'The individual or entity that owns the domain. This information is used to register the domain and is publicly visible in WHOIS records unless privacy protection is enabled.'
                    },
                    {
                        label: 'Technical Contact',
                        value: 'tech',
                        description: 'The person or team responsible for managing the domain’s technical setup, such as DNS configurations. They handle technical issues related to the domain.'
                    },
                    {
                        label: 'Administrative Contact',
                        value: 'admin',
                        description: 'The contact responsible for administrative tasks, such as managing domain renewals and updates. They may also handle billing and policy-related matters.'
                    },
                    {
                        label: 'Billing Contact',
                        value: 'aux_billing',
                        description: 'The contact responsible for payment and billing details for the domain. This information is used for invoices and renewal notifications.'
                    }
                ]
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
            isLoadingDomainContacts() {
                return this.domainState.isLoadingDomainContacts;
            },
            domainStatus() {
                if (this.connected === true) {
                    return { label: 'connected', type: 'success' };
                }else{
                    return { label: 'pending', type: 'warning' };
                }
            },
            selectedTabIndex() {
                return this.tabs.findIndex(tab => tab.value == this.tab);
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

                    if(this.domain.type == 'purchased') {
                        this.showDomainContacts();
                    }

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
            async showDomainContacts() {
                try {

                    this.domainState.isLoadingDomainContacts = true;

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get(`/api/domains/${this.domainId}/contacts`, config);
                    this.domainContacts = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching domain contacts';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch domain contacts:', error);
                } finally {
                    this.domainState.isLoadingDomainContacts = false;
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
