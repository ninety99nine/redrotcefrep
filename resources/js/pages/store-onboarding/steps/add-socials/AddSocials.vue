<template>

    <div class="min-h-screen flex flex-col items-center pt-20 pb-40">

        <div class="w-full max-w-lg">

            <!-- Store Logo -->
            <StoreLogo :showButton="false" class="flex justify-center mb-4"></StoreLogo>

            <!-- Heading -->
            <h2 class="text-2xl font-semibold text-center mb-2">Connect Your Socials</h2>

            <!-- Instruction -->
            <p class="text-gray-500 text-center mb-6">Make it easy for customers to find and follow you</p>

        </div>

        <div class="w-full max-w-lg">

            <div class="mb-4">

                <!-- Social Links (Loading Placeholder) -->
                <div v-if="isLoadingSocialMediaIcons" class="space-y-2">

                    <div v-for="(_, index) in [1,2,3]" :key="index" class="border shadow-sm rounded-lg p-4 bg-gray-50">
                        <div class="w-full flex items-center space-x-2">
                            <Skeleton width="w-8" :shine="true"></Skeleton>
                            <Skeleton width="w-40" :shine="true"></Skeleton>
                        </div>
                    </div>

                </div>

                <!-- Social Links -->
                <div v-else class="space-y-3 mb-4">

                    <div
                        :key="index"
                        v-for="(socialMediaLink, index) in storeSocialMediaLinks"
                        class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                        <div class="flex justify-between items-center">

                            <div class="flex items-center space-x-2 font-bold">

                                <!-- Logo -->
                                <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center">

                                    <img
                                        alt="Social Media Logo"
                                        class="h-full object-contain"
                                        :src="getMatchingSocialMediaIcon(socialMediaLink.name).icon.toLowerCase()"
                                    />

                                </div>

                                <!-- Name -->
                                <span class="text-sm">{{ socialMediaLink.name }}</span>

                            </div>

                            <!-- Active Switch -->
                            <Switch
                                size="md"
                                v-model="socialMediaLink.active">
                            </Switch>

                        </div>

                        <!-- Social Link Input -->
                        <div
                            class="w-full mt-4"
                            v-if="socialMediaLink.active">

                            <Input
                                placeholder="https://"
                                v-model="socialMediaLink.url"
                                :label="`${socialMediaLink.name} link`"
                                :errorText="formState.getFormError('storeSocialMediaLinks'+index+'Link')">
                            </Input>

                            <!-- Validation Error Message -->
                            <p
                                v-if="hasMatchingSocialMediaLinkError(socialMediaLink.name)"
                                class="flex space-x-1 text-xs text-yellow-600 font-semibold bg-yellow-100 border border-yellow-300 p-3 rounded-lg shadow-md mt-2">
                                <Info size="14" class="shrink-0"></Info>
                                <span>{{ getMatchingSocialMediaLinkError(socialMediaLink.name) }}</span>
                            </p>


                        </div>

                    </div>

                </div>

            </div>

            <!-- Validation Error Messages -->
            <div
                v-if="hasStoreSocialMediaLinkValidationErrors"
                class="list-disc text-xs text-yellow-600 bg-yellow-100 border border-yellow-300 py-3 px-4 rounded-lg shadow-md mb-4">

                <!-- Heading -->
                <p class="text-lg font-semibold mb-2">Resolve these to continue</p>

                <!-- Errors -->
                <ul class="space-y-1">
                    <li
                        :key="index"
                        v-for="(paymentMethodValidationError, index) in storeSocialMediaLinkValidationErrors">
                        {{ paymentMethodValidationError.message }}
                    </li>
                </ul>

            </div>

            <!-- Total Supported Social Links -->
            <div
                v-else
                class="text-blue-600 bg-blue-100 border border-blue-300 p-3 rounded-lg shadow-md mb-4">

                <div class="flex items-center space-x-2">
                    <AtSign size="28" class="shrink-0"></AtSign>
                    <div>
                        <p v-if="hasActiveStoreSocialMediaLinks" class="text-sm font-semibold">{{ store.name }} supports <span class="font-bold">{{ totalActiveStoreSocialMediaLinks }} {{ totalActiveStoreSocialMediaLinks == 1 ? 'social platform' : 'social platforms' }}</span></p>
                        <p :class="[hasActiveStoreSocialMediaLinks ? 'text-xs' : 'text-sm font-semibold']">You can always {{ hasActiveStoreSocialMediaLinks ? 'add more' : 'add' }} social links later! 😊</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">

                <transition name="fade-1" mode="out-in">

                    <!-- Continue -->
                    <Button
                        size="md"
                        type="primary"
                        buttonClass="w-full"
                        :action="updateStore"
                        :loading="isSubmittingStore"
                        v-if="storeSocialMediaLinksHaveChanged || hasActiveStoreSocialMediaLinks"
                        :disabled="isSubmittingStore || hasStoreSocialMediaLinkValidationErrors">
                        <span>Continue</span>
                    </Button>

                    <!-- Skip -->
                    <Button
                        v-else
                        size="md"
                        type="light"
                        buttonClass="w-40"
                        rightIconSize="24"
                        :rightIcon="MoveRight"
                        :action="navigateToAddAdvancedFeatures">
                        <span>Skip</span>
                    </Button>

                </transition>

            </div>

        </div>

    </div>

</template>

<script>

    import isEqual from 'lodash/isEqual';
    import Input from '@Partials/Input.vue';
    import cloneDeep from 'lodash/cloneDeep';
    import Button from '@Partials/Button.vue';
    import Switch from '@Partials/Switch.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
    import { Info, AtSign, MoveRight } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Info, Input, AtSign, Button, Switch, Skeleton, StoreLogo
        },
        data() {
            return {
                MoveRight,
                socialMediaLinks: [],
                isSubmittingStore: false,
                storeSocialMediaLinks: [],
                isLoadingSocialMediaIcons: false,
                originalStoreSocialMediaLinks: [],
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasActiveStoreSocialMediaLinks() {
                return this.storeSocialMediaLinks.some(storeSocialMediaLink => storeSocialMediaLink.active);
            },
            totalActiveStoreSocialMediaLinks() {
                return this.storeSocialMediaLinks.filter(socialMediaLink => socialMediaLink.active).length;
            },
            storeSocialMediaLinksHaveChanged() {
                // Clone the objects to avoid modifying the original data
                var a = cloneDeep(this.storeSocialMediaLinks);
                var b = cloneDeep(this.originalStoreSocialMediaLinks);

                // Compare the modified arrays for equality
                return !isEqual(a, b);
            },
            storeSocialMediaLinkValidationErrors() {
                return this.storeSocialMediaLinks
                    .filter(storeSocialMediaLink => storeSocialMediaLink.active)
                    .map(storeSocialMediaLink => {
                        const regex = new RegExp('^(https?:\\/\\/)?([a-zA-Z0-9-]+\\.)?[a-zA-Z0-9-]+\\.[a-zA-Z]{2,6}(:[0-9]{1,5})?(\\/[^\\s]*)?$');

                        let message = '';

                        if (storeSocialMediaLink.url.trim() === '') {
                            message = `The ${storeSocialMediaLink.name} link is required`;
                        } else if (!regex.test(storeSocialMediaLink.url.trim())) {
                            message = `The ${storeSocialMediaLink.name} link is incorrect`;
                        }

                        return message ? { name: storeSocialMediaLink.name, message } : null;
                    })
                    .filter(error => error !== null); // Remove null values
            },
            hasStoreSocialMediaLinkValidationErrors() {
                return this.storeSocialMediaLinkValidationErrors.length > 0;
            },
        },
        methods: {
            hasMatchingSocialMediaLinkError(name) {
                return this.storeSocialMediaLinkValidationErrors.some(socialLinkValidationError => socialLinkValidationError.name == name);
            },
            getMatchingSocialMediaLinkError(name) {
                const socialLinkValidationError = this.storeSocialMediaLinkValidationErrors.find(socialLinkValidationError => socialLinkValidationError.name == name);
                return socialLinkValidationError ? socialLinkValidationError.message : null;
            },
            getMatchingSocialMediaIcon(name) {
                return this.socialMediaLinks.find(socialMediaLink => socialMediaLink.name.toLowerCase() === name.toLowerCase());
            },
            async showSocialMediaLinks() {

                try {

                    this.isLoadingSocialMediaIcons = true;

                    const response = await axios.get('/api/social-media-links');

                    this.socialMediaLinks = response.data;

                    this.storeSocialMediaLinks = cloneDeep(this.socialMediaLinks.map((socialMediaLink) => {

                        const storeSocialMediaLink = this.store.social_media_links.find((storeSocialMediaLink) => storeSocialMediaLink.name.toLowerCase() == socialMediaLink.name.toLowerCase());

                        return storeSocialMediaLink ? storeSocialMediaLink :  {
                            url: '',
                            active: false,
                            name: socialMediaLink.name
                        }

                    }));

                    this.originalStoreSocialMediaLinks = cloneDeep(this.storeSocialMediaLinks);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching social media links';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch social media links:', error);
                } finally {
                    this.isLoadingSocialMediaIcons = false;
                }

            },
            async updateStore() {

                try {

                    this.isSubmittingStore = true;

                    const socialMediaLinks = this.storeSocialMediaLinks.filter((socialMediaLink) => socialMediaLink.url?.trim() != '');

                    let data = {
                        social_media_links: socialMediaLinks
                    };

                    await axios.put(`/api/stores/${this.store.id}`, data);

                    this.storeState.setStore({
                        ...this.store,
                        ...{
                            social_media_links: socialMediaLinks
                        }
                    });

                    this.notificationState.showSuccessNotification('Social media links updated');
                    this.originalStoreSocialMediaLinks = cloneDeep(this.storeSocialMediaLinks);
                    this.navigateToAddAdvancedFeatures();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating social media links';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update social media links:', error);
                } finally {
                    this.isSubmittingStore = false;
                }

            },
            navigateToAddAdvancedFeatures() {
                this.$router.push({
                    name: 'advanced-features',
                    params: { store_id: this.store.id }
                });
            }
        },
        beforeRouteLeave(to, from, next) {

            if (this.isSubmittingStore || this.storeSocialMediaLinksHaveChanged) {

                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");

                if (!answer) {
                    return next(false);
                }

            }

            next();
        },
        created() {
            this.showSocialMediaLinks();
        }
    };

</script>
