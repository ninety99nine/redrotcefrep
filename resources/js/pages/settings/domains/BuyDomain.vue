<template>
    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <!-- Back Button -->
        <Button
            size="xs"
            type="light"
            class="mb-4"
            :action="goBack"
            :leftIcon="MoveLeft">
            <span>Back</span>
        </Button>

        <!-- Search Domains (Search View) -->
        <div v-if="viewMode === 'search'" class="bg-gray-50 border border-gray-300 rounded-lg p-4 mb-4">
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

        <!-- Domain Results (Search View) -->
        <div v-if="viewMode === 'search' && searchResults.length" class="space-y-3 mt-4">
            <div
                v-for="(domain, index) in searchResults"
                :key="index"
                :class="[index === 0 ? 'bg-orange-50' : 'bg-white', 'p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg']">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold">{{ domain.name }}</span>
                    <div class="flex items-center space-x-4">
                        <Skeleton v-if="domain.is_loading_availability" width="w-16" :shine="true"></Skeleton>
                        <Pill v-else :type="domain.available ? 'success' : 'danger'" size="xs">
                            <div class="flex items-center space-x-1">
                                <Check v-if="domain.available" size="12"></Check>
                                <span>{{ domain.available ? 'Available' : 'Unavailable' }}</span>
                            </div>
                        </Pill>
                        <Pill
                            size="xs"
                            type="success"
                            v-if="domain.available && !domain.is_premium && domain.discount_percentage > 0">
                            {{ domain.discount_percentage }}% OFF
                        </Pill>
                        <Skeleton v-if="domain.is_loading_price" width="w-16" :shine="true"></Skeleton>
                        <template v-else-if="domain.available">
                            <Pill v-if="domain.is_premium" type="light" size="xs">Premium</Pill>
                            <div class="text-sm font-semibold text-right">
                                <p v-if="domain.registration_price > 0" class="text-sm">
                                    ${{ domain.registration_price.toFixed(2) }}
                                    <span v-if="domain.min_duration > 1">/{{ domain.min_duration }}yr</span>
                                </p>
                                <span v-else class="text-xs">Price unavailable</span>
                                <span v-if="domain.renewal_price > 0" class="text-xs text-gray-500 block">
                                    Renews at ${{ domain.renewal_price.toFixed(2) }}/yr
                                </span>
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

        <!-- No Results (Search View) -->
        <div
            v-else-if="viewMode === 'search' && hasSearchTerm && !searchResults.length && !isSearchingDomains"
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

        <!-- User Details Form (Details View) -->
        <div v-if="viewMode === 'details'">

            <div class="border border-blue-300 bg-blue-50 p-4 rounded-xl shadow mb-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold">{{ selectedDomain.name }}</span>
                    <div class="flex justify-between items-end space-x-4">
                        <Pill v-if="selectedDomain.is_premium" type="primary" size="xs" class="mt-1">Premium</Pill>
                        <Pill
                            size="xs"
                            type="success"
                            v-if="selectedDomain.available && !selectedDomain.is_premium && selectedDomain.discount_percentage > 0">
                            {{ selectedDomain.discount_percentage }}% OFF
                        </Pill>
                        <div class="text-sm font-semibold text-right">
                            <p v-if="selectedDomain.registration_price > 0" class="text-2xl">
                                ${{ selectedDomain.registration_price.toFixed(2) }}
                                <span v-if="selectedDomain.min_duration > 1">/{{ selectedDomain.min_duration }}yr</span>
                            </p>
                            <span v-if="selectedDomain.renewal_price > 0" class="text-xs text-gray-700 block">
                                Renews at ${{ selectedDomain.renewal_price.toFixed(2) }}/yr
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- User Details Form -->
            <div class="bg-gray-50 border border-gray-300 rounded-lg p-6 space-y-4 mb-4">

                <div class="border-b border-gray-300 border-dashed pb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Domain Owner</h3>
                    <h3 class="text-sm text-gray-500">Provide details that make you the legal owner for this domain</h3>
                </div>

                <div class="grid grid-cols-2 gap-4">

                    <Input
                        required
                        type="text"
                        label="First Name"
                        placeholder="John"
                        v-model="formData.first_name"
                        :errorText="formState.getFormError('first_name')" />

                    <Input
                        required
                        type="text"
                        label="Last Name"
                        placeholder="Doe"
                        v-model="formData.last_name"
                        :errorText="formState.getFormError('last_name')" />

                    <Input
                        required
                        type="email"
                        label="Email"
                        v-model="formData.email"
                        :errorText="formState.getFormError('email')"
                        placeholder="johndoe@example.com"/>

                    <Input
                        required
                        type="text"
                        label="Phone"
                        v-model="formData.phone"
                        placeholder="+26772000001"
                        :errorText="formState.getFormError('phone')"/>

                    <Input
                        required
                        type="text"
                        label="Address"
                        placeholder="123 Main St"
                        v-model="formData.address1"
                        :errorText="formState.getFormError('address1')" />

                    <Input
                        required
                        type="text"
                        label="City"
                        v-model="formData.city"
                        placeholder="Los Angeles"
                        :errorText="formState.getFormError('city')" />

                    <Input
                        required
                        type="text"
                        placeholder="CA"
                        label="State/Province"
                        v-model="formData.state"
                        :errorText="formState.getFormError('state')" />

                    <Input
                        required
                        type="text"
                        label="Postal Code"
                        placeholder="90210"
                        v-model="formData.postal_code"
                        :errorText="formState.getFormError('postal_code')" />

                    <SelectCountry
                        required
                        label="Country"
                        v-model="formData.country"
                        :errorText="formState.getFormError('country')">
                    </SelectCountry>

                </div>

            </div>

            <Button
                size="lg"
                type="primary"
                buttonClass="w-full"
                :action="submitPurchase"
                :loading="isPurchasingDomain"
                :disabled="isPurchasingDomain">
                <span>Buy Domain</span>
            </Button>
        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import SelectCountry from '@Partials/SelectCountry.vue';
    import { Check, Globe, MoveLeft } from 'lucide-vue-next';
    import { isEmpty, isNotEmpty } from '@Utils/stringUtils';
    import { parsePhoneNumberFromString } from 'libphonenumber-js';

    export default {
        inject: ['authState', 'formState', 'storeState', 'notificationState'],
        components: { Input, Button, Skeleton, SelectCountry, Pill, Check, Globe, MoveLeft },
        data() {
            return {
                Check,
                Globe,
                MoveLeft,
                searchTerm: null,
                searchResults: [],
                lastSearchTerm: null,
                selectedDomainIndex: null,
                isSearchingDomains: false,
                isPurchasingDomain: false,
                suggestionTlds: ['com', 'net', 'org', 'ai'],
                viewMode: 'search', // 'search' or 'details'
                selectedDomain: null,
                formData: {
                    city: '',
                    state: '',
                    email: '',
                    phone: '',
                    country: '',
                    address1: '',
                    last_name: '',
                    first_name: '',
                    postal_code: '',
                }
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
                return this.isNotEmpty(this.searchTerm);
            }
        },
        methods: {
            isEmpty: isEmpty,
            isNotEmpty: isNotEmpty,
            async goBack() {
                if(this.viewMode === 'search') {
                    this.navigateToShowDomains();
                }else{
                    this.viewMode = 'search';
                    this.selectedDomain = null;
                    this.selectedDomainIndex = null;
                }
                await this.$router.push({
                    name: 'show-domains',
                    query: { store_id: this.store.id }
                });
            },
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
                        name: domain,
                        min_duration: 1,
                        renewal_price: 0,
                        regular_price: 0,
                        available: false,
                        is_premium: false,
                        purchasable: false,
                        registration_price: 0,
                        discount_percentage: 0,
                        is_loading_price: true,
                        is_loading_availability: true
                    }));

                    // Fire all availability and pricing requests concurrently
                    const availabilityPromises = domainsToCheck.map(domain => {
                        const [, , tld] = domain.match(/^([a-zA-Z0-9.-]+)\.([a-zA-Z]{2,})$/) || [];
                        return {
                            tld,
                            promise: axios.post('/api/domains/search', {
                                store_id: this.store.id,
                                search: domain
                            }).catch(e => ({ error: e }))
                        };
                    });

                    const tldsToFetch = Array.from(new Set([tld, ...this.suggestionTlds]));
                    const pricePromises = tldsToFetch.map(tld => ({
                        tld,
                        promise: axios.get('/api/domains/pricing', {
                            params: {
                                tld,
                                store_id: this.store.id
                            }
                        }).catch(e => ({ error: e }))
                    }));

                    // Handle availability responses
                    availabilityPromises.forEach(({ tld, promise }) => {
                        promise.then(response => {
                            if (response.error || !response.data.successful) {
                                console.error(`Failed to search domain with TLD ${tld}:`, response.error || response.data.message);
                                this.updateDomain(tld, { available: false }, 'availability');
                            } else {
                                this.updateDomain(tld, response.data.domains[0] || {}, 'availability');
                            }
                        });
                    });

                    // Handle pricing responses
                    pricePromises.forEach(({ tld, promise }) => {
                        promise.then(response => {
                            if (response.error) {
                                console.error(`Failed to fetch price for TLD ${tld}:`, response.error);
                                this.updateDomain(tld, {}, 'pricing');
                            } else {
                                this.updateDomain(tld, response.data, 'pricing');
                            }
                        });
                    });

                    // Wait for all promises to settle to update isSearchingDomains
                    Promise.allSettled([
                        ...availabilityPromises.map(p => p.promise),
                        ...pricePromises.map(p => p.promise)
                    ]).then(() => {
                        this.isSearchingDomains = false;
                        if (this.searchResults.some(domain => domain.available && !domain.purchasable)) {
                            this.notificationState.showWarningNotification('Price unavailable for one or more domains. Please try again later or contact support.');
                        }
                        this.lastSearchTerm = this.searchTerm;
                    });

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while searching domains';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to search domains:', error);
                    this.isSearchingDomains = false;
                }
            },
            updateDomain(tld, data, type) {
                this.searchResults = this.searchResults.map(domain => {
                    if (!domain.name.endsWith(`.${tld}`)) return domain;

                    const updatedDomain = { ...domain };

                    if (type === 'availability') {
                        updatedDomain.is_loading_availability = false;
                        updatedDomain.available = data.available || false;
                        updatedDomain.is_premium = data.is_premium || false;
                        if (data.is_premium && data.premium_price) {
                            updatedDomain.registration_price = data.premium_price;
                            updatedDomain.discount_percentage = 0;
                            updatedDomain.regular_price = 0;
                        }
                    }

                    if (type === 'pricing') {
                        updatedDomain.is_loading_price = false;
                        updatedDomain.min_duration = data.min_duration || updatedDomain.min_duration;
                        updatedDomain.renewal_price = data.renewal_price || updatedDomain.renewal_price;
                        updatedDomain.regular_price = data.regular_price || updatedDomain.regular_price;
                        if (!updatedDomain.is_premium) {
                            updatedDomain.registration_price = data.price || updatedDomain.registration_price;
                            updatedDomain.discount_percentage = data.discount_percentage || updatedDomain.discount_percentage;
                        }
                    }

                    updatedDomain.purchasable = updatedDomain.available && updatedDomain.registration_price > 0;

                    return updatedDomain;
                });
            },
            purchaseDomain(domain, index) {
                this.selectedDomain = domain;
                this.selectedDomainIndex = index;

                this.formState.hideFormErrors();

                // Pre-fill form with user data if available
                this.formData = {
                    email: this.user.email || 'brandontabona@gmail.com',
                    last_name: this.user.last_name || 'Tabona',
                    first_name: this.user.first_name || 'Julian',
                    phone: this.user.mobile_number?.international || '+26772882239',

                    city: this.store.address?.city || 'Gaborone',
                    state: this.store.address?.state || 'South-East',
                    country: this.store.address?.country || 'BW',
                    address1: this.store.address?.address_line || '',
                    postal_code: this.store.address?.postal_code || '00000',
                };

                this.viewMode = 'details';
            },
            async submitPurchase() {
                try {

                    this.formState.hideFormErrors();

                    // Validate required fields
                    const requiredFields = ['first_name', 'last_name', 'email', 'phone', 'address1', 'city', 'state', 'postal_code', 'country'];
                    const missingFields = requiredFields.filter(field => !this.formData[field] || this.isEmpty(this.formData[field]));

                    if (missingFields.length > 0) {
                        missingFields.forEach(field => {
                            this.formState.setFormError(field, `${field.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())} is required`);
                        });
                        this.notificationState.showWarningNotification('Please fill in all required fields.');
                        return;
                    }

                    // Validate email format
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(this.formData.email)) {
                        this.formState.setFormError('email', 'Please enter a valid email address');
                        this.notificationState.showWarningNotification('Please enter a valid email address.');
                        return;
                    }

                    // Validate phone format (e.g., +26772000001)
                    const phoneNumber = parsePhoneNumberFromString(this.formData.phone);

                    if (!phoneNumber || !phoneNumber.isValid()) {
                        this.formState.setFormError('phone', 'Please enter a valid phone number (e.g., +26772000001)');
                        this.notificationState.showWarningNotification('Please enter a valid phone number.');
                        return;
                    }

                    this.isPurchasingDomain = true;

                    const data = {
                        store_id: this.store.id,
                        city: this.formData.city,
                        state: this.formData.state,
                        email: this.formData.email,
                        phone: this.formData.phone,
                        country: this.formData.country,
                        address1: this.formData.address1,
                        last_name: this.formData.last_name,
                        first_name: this.formData.first_name,
                        domain_name: this.selectedDomain.name,
                        postal_code: this.formData.postal_code,
                        domain_price: this.selectedDomain.registration_price,
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
                }
            }
        }
    };
</script>
