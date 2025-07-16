<template>

    <div class="space-y-1">

        <a
            :key="index"
            v-for="(socialLoginLink, index) in socialLoginLinks"
            :href="disableSocialLoginLinks ? null : socialLoginLink.url"
            @click="handleSocialLoginClick(index, socialLoginLink.url)"
            :class="['flex items-center space-x-4 p-4 border bg-white border-gray-300 shadow-sm font-medium rounded-md', disableSocialLoginLinks ? ' cursor-not-allowed' : 'cursor-pointer hover:bg-gray-50 hover:scale-105 hover:shadow-md active:scale-100 transition-all']">

            <div>

                <Loader v-if="socialLoginLink.isLoading" color="border-gray-400" class="mr-2"></Loader>

                <img
                    v-else
                    class="w-8 h-8 mr-1"
                    :alt="socialLoginLink.name"
                    :src="socialLoginLink.logo_url"/>

            </div>

            <div>
                <span class="text-sm text-gray-400">{{ socialLoginLink.label }}</span>
            </div>

        </a>

    </div>

</template>

<script>

    import axios from 'axios';
    import Loader from '@Partials/Loader.vue';

    export default {
        components: {
            Loader
        },
        inject: ['notificationState'],
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
                isLoadingSocialLoginLinks: false,
            };
        },
        methods: {
            handleSocialLoginClick(index, url) {

                if (this.disableSocialLoginLinks) {
                    return false;
                }

                this.socialLoginLinks[index].isLoading = true;
                this.disableSocialLoginLinks = true;

                window.location.href = url;

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
