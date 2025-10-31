<template>

    <div class="flex flex-col justify-center items-center w-full bg-linear-to-b from-blue-100 to-white-100 min-h-screen overflow-x-hidden">

        <div class="flex flex-col justify-center items-center">

            <!-- Loading effect -->
            <div class="relative inline-flex items-center justify-center w-28 h-28">

                <!-- Spinning Ring -->
                <div :class="['absolute inset-0 animate-spin rounded-full border-4 border-gray-300']" :style="'border-top-color:'+borderColor+';'"></div>

                <transition name="fade-1" mode="out-in">

                    <!-- Logo Placeholder -->
                    <Skeleton v-if="isLoadingLogo" width="w-20" height="h-20" color="bg-gray-200" class="border border-gray-300"></Skeleton>

                    <!-- Logo -->
                    <img v-else :src="logoUrl" alt="Social Logo" class="w-20 h-20 relative" />

                </transition>

            </div>

            <!-- Load Image -->
            <img :src="logoUrl" @load="onLogoLoaded" class="hidden"/>

            <div v-if="errorMessage" class="mt-8">

                <!-- Error Message -->
                <p class="font-medium text-red-500 text-sm mb-4">{{ errorMessage }}</p>

                <!-- Redirecting In Text -->
                <p class="text-gray-400 text-sm text-center ">{{ countdown == 0 ? 'Redirecting...' : `${'Redirecting in ' + countdown}` }}</p>

            </div>

            <!-- Logging In Text -->
            <p v-else class="text-gray-400 text-center mt-8">Logging In</p>

        </div>

    </div>

</template>

<script>

    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['authState'],
        components: { Skeleton },
        data() {
            return {
                countdown: 5,
                errorMessage: null,
                isLoadingLogo: true,
                countdownInterval: null,
                error: this.$route.query.error,
                token: this.$route.query.token,
                logoUrl: this.$route.query.logo_url,
                storeId: this.$route.query.store_id,
                provider: this.$route.query.provider,
                errorDescription: this.$route.query.error_description
            };
        },
        computed : {
            borderColor() {
                if(this.errorMessage) {

                    return '#e02523';

                }else{

                    const colorMapping = {
                        'google': '#df4b39',
                        'linkedin': '#117bb8',
                        'facebook': '#3f5c9b',
                    }

                    return colorMapping[this.provider];

                }
            }
        },
        methods: {
            onLogoLoaded() {
                this.isLoadingLogo = false;
            },
            startRedirectCountdown() {
                this.countdownInterval = setInterval(() => {
                    if (this.countdown > 0) {
                        this.countdown -= 1;
                    } else {
                        clearInterval(this.countdownInterval);
                        this.$router.replace({ name: 'login' });
                    }
                }, 1000);
            },
            completeSocialLogin() {
                this.authState.setTokenOnRequest(this.token);
                this.authState.setTokenOnLocalStorage(this.token);

                if(this.storeId) {
                    this.$router.push({
                        name: 'show-store-home',
                        params: { store_id: this.storeId }
                    });
                }else{
                    this.$router.push({ name: 'show-stores' });
                }
            }

        },
        created() {
            if(this.errorDescription || this.error) {
                this.errorMessage = this.errorDescription || this.error;
                this.startRedirectCountdown();
            }else{
                this.completeSocialLogin();
            }
        }
    };

</script>
