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

        <!-- Search Domains -->
        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 mb-4">
            <Input
                type="search"
                class="w-full"
                :debounced="true"
                v-model="searchTerm"
                label="Search Domains"
                placeholder="example.com"
                :skeleton="isLoadingStore"
                @input="isSearchingDomains = true"
                :errorText="formState.getFormError('search')"
                tooltipContent="Enter a domain name (e.g., example.com) to check availability and pricing for it and related domains (e.g., .net, .org).">
            </Input>
        </div>

        <!-- Loading Placeholder -->
        <div v-if="isSearchingDomains" class="space-y-3 mt-4">
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
                        <Skeleton width="w-16" :shine="true"></Skeleton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Domain Results -->
        <div v-else-if="searchResults.length" class="space-y-3 mt-4">
            <div
                :key="index"
                v-for="(domain, index) in searchResults"
                class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold">{{ domain.name }}</span>
                    <div class="flex items-center space-x-4">
                        <Pill :type="domain.available ? 'success' : 'danger'" size="xs">
                            {{ domain.available ? 'Available' : 'Unavailable' }}
                        </Pill>
                        <Skeleton v-if="domain.isLoadingPrice" width="w-16" :shine="true"></Skeleton>
                        <template v-else-if="domain.available">
                            <Pill v-if="domain.is_premium" type="light" size="xs">Premium</Pill>
                            <div>
                                <p v-if="domain.price > 0" class="text-sm font-semibold">${{ domain.price.toFixed(2) }}/yr</p>
                                <span v-else class="text-xs font-semibold">Price unavailable</span>
                            </div>
                        </template>
                        <Button
                            size="xs"
                            type="primary"
                            :loading="selectedDomainIndex === index"
                            v-if="domain.available && domain.purchasable"
                            :action="() => purchaseDomain(domain, index)"
                            :disabled="isPurchasingDomain && selectedDomainIndex !== index">
                            <span>Buy Now</span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Results -->
        <div
            v-else-if="hasSearchTerm && !searchResults.length"
            class="flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-purple-50 border border-gray-300 shadow-lg rounded-2xl py-16 px-8 space-y-6">
            <div class="relative">
                <div class="bg-gradient-to-br from-white-50 to-indigo-50 text-indigo-500 rounded-full p-2">
                    <Globe size="60"></Globe>
                </div>
                <div class="absolute inset-0 bg-indigo-300 opacity-20 rounded-full animate-ping"></div>
            </div>
            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-800">No Results Found</h3>
                <span class="text-sm text-gray-600 mt-2 block max-w-sm">
                    The domain could not be checked. Please ensure the domain name is valid (e.g., example.com) and try again.
                </span>
            </div>
        </div>
    </div>
</template>

<script>
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import Pill from '@Partials/Pill.vue';
    import { Globe, MoveLeft } from 'lucide-vue-next';

    export default {
        inject: ['authState', 'formState', 'storeState', 'notificationState'],
        components: { Input, Button, Skeleton, Pill, Globe, MoveLeft },
        data() {
            return {
                Globe,
                MoveLeft,
                searchTerm: null,
                searchResults: [],
                lastSearchTerm: null,
                selectedDomainIndex: null,
                isSearchingDomains: false,
                isPurchasingDomain: false,
                suggestionTlds: ['com', 'net', /** 'biz', 'store', 'tech', 'live', 'online' */]
            };
        },
        watch: {
            searchTerm(newVal) {
                if (newVal && newVal !== this.lastSearchTerm) {
                    this.searchDomains();
                }
            }
        },
        computed: {
            user() {
                return this.authState.user;
            },
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            hasSearchTerm() {
                return this.searchTerm != null && this.searchTerm.trim() != '';
            },
        },
        methods: {
            async navigateToShowDomains() {
                await this.$router.push({
                    name: 'show-domains',
                    query: { store_id: this.store.id }
                });
            },
            async searchDomains() {
                try {

                    this.formState.hideFormErrors();
                    this.isSearchingDomains = true;
                    this.searchResults = [];

                    const domainRegex = /^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                    if (!this.searchTerm || !domainRegex.test(this.searchTerm)) {
                        this.formState.setFormError('search', 'Please enter a valid domain name (e.g., example.com).');
                        this.isSearchingDomains = false;
                        return;
                    }

                    // Extract base name and TLD
                    const [, baseName, tld] = this.searchTerm.match(/^([a-zA-Z0-9.-]+)\.([a-zA-Z]{2,})$/) || [];
                    if (!baseName || !tld) {
                        this.formState.setFormError('search', 'Invalid domain format.');
                        this.isSearchingDomains = false;
                        return;
                    }

                    // Initialize results with loading state
                    const domainsToCheck = [this.searchTerm];
                    this.suggestionTlds.forEach(suggestionTld => {
                        if (suggestionTld !== tld) {
                            domainsToCheck.push(`${baseName}.${suggestionTld}`);
                        }
                    });

                    this.searchResults = domainsToCheck.map(domain => ({
                        price: 0,
                        name: domain,
                        available: false,
                        is_premium: false,
                        purchasable: false,
                        isLoadingPrice: true,
                    }));

                    // Fetch availability for suggestion TLDs in parallel
                    const domainPromises = domainsToCheck
                        .map(domain => axios.post('/api/domains/search', {
                            store_id: this.store.id,
                            search: domain
                        }));

                    const domainResponses = await Promise.all(domainPromises.map(p => p.catch(e => ({ error: e }))));

                    domainResponses.forEach((response, index) => {
                        const domain = domainsToCheck[index];
                        if (response.error) {
                            console.error(`Failed to search domain ${domain}:`, response.error);
                            this.updateSearchResult(domain, { available: false });
                        } else if (response.data.successful) {
                            this.updateSearchResult(domain, response.data.domains[0] || {});
                        }
                    });

                    this.isSearchingDomains = false;

                    // Fetch prices for each TLD in parallel
                    const tldsToFetch = Array.from(new Set([tld, ...this.suggestionTlds]));
                    const pricePromises = tldsToFetch.map(tld => axios.get('/api/domains/pricing', {
                        params: {
                            tld,
                            store_id: this.store.id
                        }
                    }).catch(e => ({ error: e, tld })));

                    const priceResponses = await Promise.all(pricePromises);

                    priceResponses.forEach(response => {
                        if (response.error) {
                            console.error(`Failed to fetch price for TLD ${response.tld}:`, response.error);
                            this.updatePriceForTld(response.tld, 0);
                        } else {
                            const pricing = response.data;
                            this.updatePriceForTld(pricing);
                        }
                    });

                    this.lastSearchTerm = this.searchTerm;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while searching domains';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to search domains:', error);
                } finally {
                    this.isSearchingDomains = false;
                }
            },
            updateSearchResult(domainName, data) {
                const index = this.searchResults.findIndex(d => d.name === domainName);
                if (index !== -1) {
                    this.searchResults[index] = {
                        ...this.searchResults[index],
                        price: data.premium_price || 0,
                        available: data.available || false,
                        is_premium: data.is_premium || false,
                        purchasable: data.available && (data.premium_price > 0 || this.searchResults[index].price > 0)
                    };
                }
            },
            updatePriceForTld(pricing) {
                this.searchResults = this.searchResults.map(domain => {
                    if (domain.name.endsWith(`.${pricing.tld}`) && !domain.is_premium) {
                        return {
                            ...domain,
                            price: pricing.price,
                            isLoadingPrice: false,
                            regular_price: pricing.regular_price,
                            purchasable: domain.available && pricing.price > 0
                        };
                    }
                    return { ...domain, isLoadingPrice: false };
                });
            },
            async purchaseDomain(domain, index) {
                try {
                    if (this.isPurchasingDomain) return;

                    this.selectedDomainIndex = index;
                    this.isPurchasingDomain = true;

                    const data = {
                        store_id: this.store.id,
                        domain_name: domain.name,
                        domain_price: domain.price,
                        first_name: this.user.first_name,
                        last_name: this.user.last_name,
                        email: this.user.email,
                        phone: this.user.mobile_number?.formatE164() ?? '',
                        address1: this.user.address1 ?? '',
                        city: this.user.city ?? '',
                        state: this.user.state ?? '',
                        postal_code: this.user.postal_code ?? '',
                        country: this.user.country ?? ''
                    };

                    const response = await axios.post('/api/domains/purchase', data);
                    const dpoPaymentUrl = response.data.metadata.dpo_payment_url;
                    window.location.href = dpoPaymentUrl;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while purchasing domain';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to purchase domain:', error);
                } finally {
                    this.isPurchasingDomain = false;
                    this.selectedDomainIndex = null;
                }
            }
        }
    };
</script>
