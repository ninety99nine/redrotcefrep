<template>

    <div class="max-w-2xl mx-auto pt-32 pb-40">

        <div
            class="flex justify-end space-x-4"
            v-if="!isLoadingStore && !isLoadingDomains && hasDomains">

            <Button
                size="md"
                type="primary"
                buttonClass="px-4"
                :loading="isLoadingStore"
                :action="navigateToBuyDomain">
                <span>Buy Domain</span>
            </Button>

            <Button
                size="md"
                type="primary"
                buttonClass="px-4"
                :loading="isLoadingStore"
                :action="navigateToConnectExistingDomain">
                <span>Connect Existing Domain</span>
            </Button>

        </div>

        <!-- Loading Placeholder -->
        <div v-if="isLoadingStore || isLoadingDomains" class="space-y-3 mt-4">

            <div
                :key="index"
                v-for="(_, index) in [1, 2, 3]"
                class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2 font-bold">
                        <Skeleton width="w-8" height="h-8" rounded="rounded-full" :shine="true"></Skeleton>
                        <Skeleton width="w-40" :shine="true"></Skeleton>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Skeleton width="w-16" :shine="true"></Skeleton>
                        <Skeleton width="w-4" height="h-4" rounded="rounded-md" :shine="true"></Skeleton>
                    </div>
                </div>
            </div>

        </div>

        <!-- Domains -->
        <div
            class="space-y-3 mt-4"
            v-else-if="hasDomains">

            <div
                :key="domain.id"
                v-for="domain in domains"
                @click.stop="() => navigateToEditDomain(domain)"
                class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                <div class="flex justify-between items-center">

                    <div class="flex items-center space-x-2 font-bold">
                        <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center">
                            <Globe size="20" class="text-gray-500"></Globe>
                        </div>
                        <span class="text-sm">{{ domain.name }}</span>
                    </div>

                    <div class="flex items-center space-x-4">

                        <div class="flex items-center space-x-1">

                            <Tooltip
                                trigger="hover"
                                v-if="domain.last_verification_attempt_at"
                                :content="`Status last updated: ${formattedDatetime(domain.last_verification_attempt_at)}`">
                            </Tooltip>

                            <Pill
                                size="xs"
                                :type="getStatusPillType(domain.status)">
                                <div class="flex items-center space-x-2 mr-1">
                                    <RefreshCcw v-if="domain.status == 'processing'" size="12" class="text-blue-800 animate-spin"></RefreshCcw>
                                    <span>{{ domain.status }}</span>
                                </div>
                            </Pill>

                        </div>

                        <Button
                            size="xs"
                            type="bareDanger"
                            :leftIcon="Trash2"
                            :action="() => showDeleteDomainModal(domain)">
                        </Button>

                    </div>

                </div>

            </div>

        </div>

        <div
            v-else
            class="flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-purple-50 border border-gray-300 shadow-lg rounded-2xl py-16 px-8 space-y-6">

            <div class="relative">
                <div class="bg-gradient-to-br from-white-50 to-indigo-50 text-indigo-500 rounded-full p-2">
                    <Globe size="60"></Globe>
                </div>
                <div class="absolute inset-0 bg-indigo-300 opacity-20 rounded-full animate-ping"></div>
            </div>

            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-800">Connect Your Domains!</h3>
                <span class="text-sm text-gray-600 mt-2 block max-w-sm">
                    Add a domain to enhance your store's branding and accessibility.
                </span>
            </div>

            <div class="flex justify-end space-x-4">

                <button
                    @click.stop="navigateToBuyDomain"
                    class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium py-3 px-6 rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 cursor-pointer">
                    <span>Buy Domain</span>
                </button>

                <button
                    @click.stop="navigateToConnectExistingDomain"
                    class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium py-3 px-6 rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 cursor-pointer">
                    <span>Connect Existing Domain</span>
                </button>

            </div>

        </div>

        <Modal
            approveType="danger"
            :approveLeftIcon="Trash2"
            ref="deleteDomainModal"
            approveText="Delete Domain"
            :approveAction="deleteDomain"
            :triggerLoading="isDeletingDomain"
            :approveLoading="isDeletingDomain">

            <template #content v-if="deletableDomain">
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                <p class="mb-8">Are you sure you want to delete <span class="font-bold text-black">{{ deletableDomain.name }}</span>?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Tooltip from '@Partials/Tooltip.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { formattedDatetime } from '@Utils/dateUtils';
    import { Trash2, Globe, RefreshCcw } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: { Pill, Modal, Button, Tooltip, Skeleton, Trash2, Globe, RefreshCcw },
        data() {
            return {
                Trash2,
                domains: [],
                pagination: null,
                deletableDomain: null,
                isDeletingDomain: false,
                isLoadingDomains: false,
            }
        },
        watch: {
            store(newValue, oldValue) {
                if (!oldValue && newValue) {
                    this.setup();
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
            hasDomains() {
                return this.domains.length > 0;
            }
        },
        methods: {
            formattedDatetime,
            setup() {
                if (this.store) {
                    this.showDomains();
                }
            },
            getStatusPillType(status) {
                switch (status) {
                    case 'connected':
                        return 'success';
                    case 'processing':
                        return 'primary';
                    case 'pending':
                        return 'warning';
                    default:
                        return 'light';
                }
            },
            showDeleteDomainModal(domain) {
                this.deletableDomain = domain;
                this.$refs.deleteDomainModal.showModal();
            },
            async navigateToConnectExistingDomain() {
                await this.$router.push({
                    name: 'add-domain',
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            async navigateToBuyDomain() {
                await this.$router.push({
                    name: 'buy-domain',
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            async navigateToEditDomain(domain) {
                await this.$router.push({
                    name: 'edit-domain',
                    query: {
                        store_id: this.store.id
                    },
                    params: {
                        domain_id: domain.id
                    }
                });
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
                    this.pagination = response.data;
                    this.domains = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching domains';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch domains:', error);
                } finally {
                    this.isLoadingDomains = false;
                }
            },
            async deleteDomain() {
                try {

                    if (this.isDeletingDomain) return;

                    this.isDeletingDomain = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    };

                    await axios.delete(`/api/domains/${this.deletableDomain.id}`, config);
                    this.showDomains();
                    this.$refs.deleteDomainModal.hideModal();
                    this.notificationState.showSuccessNotification('Domain deleted');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting domain';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete domain:', error);
                    this.$refs.deleteDomainModal.hideModal();
                } finally {
                    this.isDeletingDomain = false;
                }
            }
        },
        created() {
            this.setup();
        }
    };
</script>
