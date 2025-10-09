<template>

    <div class="space-y-1">

        <a
            :key="index"
            v-for="(socialLoginLink, index) in socialLoginLinks"
            @click="handleSocialLoginClick(index, socialLoginLink.url)"
            :href="disableSocialLoginLinks ? null : getSocialLoginUrl(socialLoginLink.url)"
            :class="['flex items-center space-x-4 p-3 border bg-white border-gray-100 shadow-sm font-medium rounded-md transition-all duration-300', disableSocialLoginLinks ? 'cursor-not-allowed' : 'cursor-pointer hover:bg-gray-50 hover:border-gray-200 hover:scale-105 hover:shadow-md active:scale-100']">

            <div>
                <Loader v-if="socialLoginLink.isLoading" color="border-gray-400" class="mr-2"></Loader>
                <img
                    v-else
                    class="w-6 h-6 mr-1"
                    :alt="socialLoginLink.name"
                    :src="socialLoginLink.logo_url"
                />
            </div>

            <div>
                <span class="text-sm text-gray-600">{{ socialLoginLink.label }}</span>
            </div>

        </a>

    </div>

</template>

<script>

    import axios from 'axios';
    import Loader from '@Partials/Loader.vue';

    export default {
        components: { Loader },
        inject: ['storeState', 'notificationState'],
        props: {
            message: {
                type: String,
                default: 'Or sign in with'
            }
        },
        data() {
            return {
                socialLoginLinks: [],
                disableSocialLoginLinks: false,
                isLoadingSocialLoginLinks: false
            };
        },
        computed: {
            store() {
                return this.storeState.store;
            }
        },
        methods: {
            getSocialLoginUrl(url) {
                if (this.store?.id) {
                    const separator = url.includes('?') ? '&' : '?';
                    return `${url}${separator}store_id=${this.store.id}`;
                }
                return url;
            },
            handleSocialLoginClick(index, url) {
                if (this.disableSocialLoginLinks) {
                    return false;
                }
                this.socialLoginLinks[index].isLoading = true;
                this.disableSocialLoginLinks = true;
                window.location.href = this.getSocialLoginUrl(url);
            },
            async showSocialLoginLinks() {
                try {
                    this.isLoadingSocialLoginLinks = true;
                    const response = await axios.get('/api/auth/social-login-links');
                    this.socialLoginLinks = response.data.map(socialLoginLink => ({
                        ...socialLoginLink,
                        isLoading: false
                    }));
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load social login links';
                    this.notificationState.showWarningNotification(message);
                    console.error('Failed to fetch social login links:', error);
                } finally {
                    this.isLoadingSocialLoginLinks = false;
                }
            }
        },
        created() {
            this.showSocialLoginLinks();
        }
    };
</script>
