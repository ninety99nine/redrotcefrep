<template>

    <div class="min-h-screen flex flex-col items-center pb-40">

        <div class="w-full max-w-lg">
            <StoreLogo :showButton="false" class="flex justify-center mb-4"></StoreLogo>
            <h2 class="text-2xl font-semibold text-center mb-2">Connect Your Socials</h2>
            <p class="text-gray-500 text-center mb-6">Make it easy for customers to find and follow you</p>
        </div>

        <div class="w-full max-w-lg">
            <div class="mb-4">
                <div v-if="isLoadingDesignCardConfigurations" class="space-y-2">
                    <div v-for="(_, index) in [1,2,3]" :key="index" class="border border-gray-300 shadow-sm rounded-lg p-4 bg-gray-50">
                        <div class="w-full flex items-center space-x-2">
                            <Skeleton width="w-8" :shine="true"></Skeleton>
                            <Skeleton width="w-40" :shine="true"></Skeleton>
                        </div>
                    </div>
                </div>
                <div v-else class="space-y-3 mb-4">
                    <div
                        :key="index"
                        v-for="(platform, index) in designCardPlatforms"
                        class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-2 font-bold">
                                <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center">
                                    <img
                                        alt="Social Media Logo"
                                        class="h-full object-contain"
                                        :src="getMatchingPlatformIcon(platform.name).icon.toLowerCase()"
                                    />
                                </div>
                                <span class="text-sm">{{ platform.name }}</span>
                            </div>
                            <Switch
                                size="md"
                                v-model="platform.active">
                            </Switch>
                        </div>
                        <div
                            class="w-full mt-4"
                            v-if="platform.active">
                            <Input
                                placeholder="https://"
                                v-model="platform.link"
                                :label="`${platform.name} link`"
                                :errorText="formState.getFormError('designCardPlatforms'+index+'Link')">
                            </Input>
                            <p
                                v-if="hasMatchingPlatformValidationError(platform.name)"
                                class="flex space-x-1 text-xs text-yellow-600 font-semibold bg-yellow-100 border border-yellow-300 p-3 rounded-lg shadow-md mt-2">
                                <Info size="14" class="shrink-0"></Info>
                                <span>{{ getMatchingPlatformValidationError(platform.name) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-if="hasDesignCardPlatformValidationErrors"
                class="list-disc text-xs text-yellow-600 bg-yellow-100 border border-yellow-300 py-3 px-4 rounded-lg shadow-md mb-4">
                <p class="text-lg font-semibold mb-2">Resolve these to continue</p>
                <ul class="space-y-1">
                    <li
                        :key="index"
                        v-for="(platformValidationError, index) in designCardPlatformValidationErrors">
                        {{ platformValidationError.message }}
                    </li>
                </ul>
            </div>
            <div
                v-else
                class="text-blue-600 bg-blue-100 border border-blue-300 p-3 rounded-lg shadow-md mb-4">
                <div class="flex items-center space-x-2">
                    <AtSign size="28" class="shrink-0"></AtSign>
                    <div>
                        <p v-if="hasActiveDesignCardPlatforms" class="text-sm font-semibold">{{ store.name }} supports <span class="font-bold">{{ totalActiveDesignCardPlatforms }} {{ totalActiveDesignCardPlatforms == 1 ? 'social platform' : 'social platforms' }}</span></p>
                        <p :class="[hasActiveDesignCardPlatforms ? 'text-xs' : 'text-sm font-semibold']">You can always {{ hasActiveDesignCardPlatforms ? 'add more' : 'add' }} social links later! 😊</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <transition name="fade-1" mode="out-in">
                    <Button
                        size="md"
                        type="primary"
                        buttonClass="w-full"
                        :action="createOrUpdateDesignCard"
                        :loading="isUpdatingDesignCardPlatforms"
                        v-if="designCardPlatformsHaveChanged || hasActiveDesignCardPlatforms"
                        :disabled="isUpdatingDesignCardPlatforms || hasDesignCardPlatformValidationErrors">
                        <span>Continue</span>
                    </Button>
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
    import { isEmpty } from '@Utils/stringUtils';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isNotEmpty } from '@Utils/stringUtils';
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
                designCards: [],
                designCardPlatforms: [],
                isLoadingDesignCards: false,
                designCardConfigurations: [],
                originalDesignCardPlatforms: [],
                isUpdatingDesignCardPlatforms: false,
                isLoadingDesignCardConfigurations: false,
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasActiveDesignCardPlatforms() {
                return this.designCardPlatforms.some(platform => platform.active);
            },
            totalActiveDesignCardPlatforms() {
                return this.designCardPlatforms.filter(platform => platform.active).length;
            },
            designCardPlatformsHaveChanged() {
                var a = cloneDeep(this.designCardPlatforms);
                var b = cloneDeep(this.originalDesignCardPlatforms);
                return !isEqual(a, b);
            },
            designCardPlatformValidationErrors() {
                return this.designCardPlatforms
                    .filter(platform => platform.active)
                    .map(platform => {
                        const regex = new RegExp('^(https?:\\/\\/)?([a-zA-Z0-9-]+\\.)?[a-zA-Z0-9-]+\\.[a-zA-Z]{2,6}(:[0-9]{1,5})?(\\/[^\\s]*)?$');
                        let message = '';
                        if (this.isEmpty(platform.link)) {
                            message = `The ${platform.name} link is required`;
                        } else if (!regex.test(platform.link.trim())) {
                            message = `The ${platform.name} link is incorrect`;
                        }
                        return message ? { name: platform.name, message } : null;
                    })
                    .filter(error => error !== null);
            },
            hasDesignCardPlatformValidationErrors() {
                return this.designCardPlatformValidationErrors.length > 0;
            },
            socialsConfiguration() {
                return this.designCardConfigurations.find(config => config.type === 'socials') || null;
            },
            socialsDesignCard() {
                return this.designCards.find(designCard => designCard.placement === 'menu' && designCard.type === 'socials') || null;
            }
        },
        methods: {
            isEmpty,
            isNotEmpty,
            hasMatchingPlatformValidationError(name) {
                return this.designCardPlatformValidationErrors.some(validationError => validationError.name === name);
            },
            getMatchingPlatformValidationError(name) {
                const validationError = this.designCardPlatformValidationErrors.find(validationError => validationError.name === name);
                return validationError ? validationError.message : null;
            },
            getMatchingPlatformIcon(name) {
                // Placeholder: Replace with actual icon fetching logic or API call
                return { name, icon: `/images/social-media-icons/${name.toLowerCase()}.png` };
            },
            async setup() {
                await this.showDesignCardConfigurations();
                await this.showDesignCards();
                this.initializeDesignCardPlatforms();
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
                    this.designCards = response.data.data;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching design cards';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch design cards:', error);
                } finally {
                    this.isLoadingDesignCards = false;
                }
            },
            async showDesignCardConfigurations() {
                try {
                    this.isLoadingDesignCardConfigurations = true;
                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };
                    const response = await axios.get('/api/design-cards/configurations', config);
                    this.designCardConfigurations = response.data;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching design card options';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch design card options:', error);
                } finally {
                    this.isLoadingDesignCardConfigurations = false;
                }
            },
            initializeDesignCardPlatforms() {
                if (!this.socialsConfiguration) {
                    this.designCardPlatforms = [];
                    return;
                }
                this.designCardPlatforms = cloneDeep(this.socialsConfiguration.metadata.platforms.map((platform) => {

                    const link = this.socialsDesignCard?.metadata.platforms.find(p => p.name.toLowerCase() === platform.name.toLowerCase())?.link || '';

                    return {
                        link: link,
                        name: platform.name,
                        active: this.isNotEmpty(link)
                    }

                }));

                this.originalDesignCardPlatforms = cloneDeep(this.designCardPlatforms);
            },
            async createOrUpdateDesignCard() {
                try {

                    if (!this.socialsConfiguration) {
                        throw new Error('Socials configuration not found');
                    }

                    this.isUpdatingDesignCardPlatforms = true;

                    const platforms = this.designCardPlatforms
                        .filter(platform => this.isNotEmpty(platform.link))
                        .map(platform => ({
                            name: platform.name,
                            link: platform.link
                        }));

                    const maxPosition = this.designCards.length > 0
                        ? Math.max(...this.designCards.map(card => card.position || 0)) + 1
                        : 1;

                    let data = {
                        visible: true,
                        placement: 'menu',
                        store_id: this.store.id,
                        type: this.socialsConfiguration.type,
                        metadata: {
                            ...this.socialsConfiguration.metadata,
                            platforms: platforms
                        }
                    };

                    if (this.socialsDesignCard) {
                        await axios.put(`/api/design-cards/${this.socialsDesignCard.id}`, data);
                    } else {
                        data['arrangement'] = 'last';   //  Places the design card as the last item on the stack
                        await axios.post('/api/design-cards', data);
                    }

                    this.notificationState.showSuccessNotification('Social media links updated');
                    this.originalDesignCardPlatforms = cloneDeep(this.designCardPlatforms);
                    this.navigateToAddAdvancedFeatures();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating social media links';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update social media links:', error);
                } finally {
                    this.isUpdatingDesignCardPlatforms = false;
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
            if (this.isUpdatingDesignCardPlatforms || this.designCardPlatformsHaveChanged) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        created() {
            this.setup();
        }
    };
</script>
