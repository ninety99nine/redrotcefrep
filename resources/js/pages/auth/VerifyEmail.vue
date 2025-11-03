<template>
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
        <div class="flex items-center justify-center p-6 bg-linear-to-b from-blue-100 to-white-100">
            <div class="w-full max-w-md">
                <Logo height="h-20 mx-auto mb-4"></Logo>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">

                    <!-- Loading State -->
                    <div class="flex items-center justify-center h-40"
                         v-if="loading && !verified && !verificationError">
                        <Loader>
                            <span class="ml-2">Verifying your email...</span>
                        </Loader>
                    </div>

                    <!-- Initial Verification State -->
                    <div v-else-if="!verified && !verificationError">
                        <h1 class="text-2xl font-semibold leading-none tracking-tight mb-4">
                            {{ verificationType === 'registration email' ? 'Verify Your Email' : 'Verify Your New Email' }}
                        </h1>
                        <p class="text-sm text-gray-500 mb-6">
                            We've sent a verification link to <strong>{{ form.email }}</strong>.
                            Please check your email and click on the link to
                            {{ verificationType === 'registration email' ? 'activate your account' : 'verify your new email address' }}.
                        </p>

                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600">Check your email for verification link</p>
                        </div>

                        <Button
                            size="md"
                            type="light"
                            :action="resendVerification"
                            :disabled="loading || isResendDisabled"
                            buttonClass="w-full mb-4">
                            <template #default>
                                <span v-if="loading" class="ml-1">Sending...</span>
                                <span v-else-if="isResendDisabled" class="ml-1">
                                    Resend in {{ resendCountdown }}s
                                </span>
                                <span v-else class="ml-1">Resend Verification Email</span>
                            </template>
                        </Button>

                        <div class="flex justify-center mt-4">
                            <router-link :to="{ name: 'login' }" class="text-sm text-blue-600 hover:underline">Back to Login</router-link>
                        </div>
                    </div>

                    <!-- Success State -->
                    <div v-else-if="verified" class="text-center py-8">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>

                        <h1 class="text-2xl font-semibold text-green-800 mb-2">Email Verified!</h1>
                        <p class="text-sm text-gray-600 mb-6">
                            {{ verificationType === 'registration email' ? 'Your account has been activated.' : 'Your new email address has been verified.' }}
                        </p>

                        <Button
                            size="md"
                            type="primary"
                            :action="goToDashboard"
                            buttonClass="w-full">
                            <span class="ml-1">Continue to Dashboard</span>
                        </Button>

                        <div class="flex justify-center mt-4">
                            <router-link :to="{ name: 'login' }" class="text-sm text-blue-600 hover:underline">Back to Login</router-link>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div v-else-if="verificationError" class="text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>

                        <h1 class="text-xl font-semibold text-red-800 mb-4">Verification Failed</h1>
                        <p class="text-sm text-gray-600 mb-6">{{ formState.generalErrorText || formState.getFormError('token') || 'There was an error verifying your email.' }}</p>

                        <Button
                            size="md"
                            type="light"
                            :action="resendVerification"
                            :disabled="loading || isResendDisabled"
                            buttonClass="w-full mb-4">
                            <template #default>
                                <span v-if="loading" class="ml-1">Sending...</span>
                                <span v-else-if="isResendDisabled" class="ml-1">
                                    Resend in {{ resendCountdown }}s
                                </span>
                                <span v-else class="ml-1">Resend Verification Email</span>
                            </template>
                        </Button>

                        <div class="flex justify-center">
                            <router-link :to="{ name: 'login' }" class="text-sm text-blue-600 hover:underline">Back to Login</router-link>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-center text-gray-500">© {{ currentYear }} {{ appName }}. All rights reserved.</p>
            </div>
        </div>

        <!-- Right: Full Height Image (hidden on small screens) -->
        <div class="hidden lg:block">
            <img
                :src="'/images/lady-with-laptop.jpg'"
                class="w-full h-full object-cover"
                alt="Lady holding boxes" />
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import Logo from '@Partials/Logo.vue';
import Loader from '@Partials/Loader.vue';
import Button from '@Partials/Button.vue';
import { isEmpty } from '@Utils/stringUtils';

export default {
    name: 'VerifyEmail',
    components: { Logo, Loader, Button },
    inject: ['authState', 'formState', 'notificationState'],
    data() {
        return {
            form: {
                email: '',
                token: ''
            },
            loading: false,
            verified: false,
            verificationError: false,
            currentYear: new Date().getFullYear(),
            resendStorageKey: null,
            resendAttempts: 0,
            resendTimer: null,
            resendCountdown: 0,
            resendDelays: [30, 120, 300, 600],
            verificationType: 'registration email', // Default to registration
            appName: import.meta.env.VITE_APP_NAME
        };
    },
    computed: {
        isResendDisabled() {
            return this.resendCountdown > 0 || this.resendAttempts >= this.resendDelays.length;
        },
        currentDelay() {
            return this.resendDelays[this.resendAttempts] || 600;
        }
    },
    methods: {
        isEmpty,

        getStorageKey() {
            if (!this.form.email) return null;
            return `verify_email_resend_${btoa(this.form.email).replace(/[^a-zA-Z0-9]/g, '')}`;
        },

        loadResendState() {
            const key = this.getStorageKey();
            if (!key) return false;

            try {
                const stored = localStorage.getItem(key);
                if (stored) {
                    const data = JSON.parse(stored);
                    const now = Date.now();

                    if (data.expiresAt && now < data.expiresAt) {
                        this.resendAttempts = data.attempts;
                        this.resendCountdown = Math.ceil((data.expiresAt - now) / 1000);
                        this.startResendTimer();
                        return true;
                    } else {
                        localStorage.removeItem(key);
                    }
                }
            } catch (e) {
                console.warn('Failed to load resend state:', e);
                if (key) localStorage.removeItem(key);
            }
            return false;
        },

        saveResendState() {
            const key = this.getStorageKey();
            if (!key || this.resendCountdown <= 0) return;

            try {
                const expiresAt = Date.now() + (this.resendCountdown * 1000);
                const data = {
                    attempts: this.resendAttempts,
                    expiresAt: expiresAt,
                    email: this.form.email,
                    timestamp: Date.now()
                };
                localStorage.setItem(key, JSON.stringify(data));
            } catch (e) {
                console.warn('Failed to save resend state:', e);
            }
        },

        startResendTimer() {
            if (this.resendTimer) {
                clearInterval(this.resendTimer);
            }

            this.resendTimer = setInterval(() => {
                this.resendCountdown--;

                if (this.resendCountdown % 5 === 0 && this.resendCountdown > 0) {
                    this.saveResendState();
                }

                if (this.resendCountdown <= 0) {
                    clearInterval(this.resendTimer);
                    this.resendTimer = null;
                    const key = this.getStorageKey();
                    if (key) localStorage.removeItem(key);
                } else {
                    this.saveResendState();
                }
            }, 1000);
        },

        resetResendTimer() {
            if (this.resendTimer) {
                clearInterval(this.resendTimer);
                this.resendTimer = null;
            }
            this.resendCountdown = 0;
            this.resendAttempts = 0;
            const key = this.getStorageKey();
            if (key) localStorage.removeItem(key);
        },

        initTimerSystem() {
            this.resendStorageKey = this.getStorageKey();
            this.loadResendState();
        },

        async verifyEmail() {
            if (this.loading) return;

            this.formState.hideFormErrors();

            if (this.isEmpty(this.form.email)) {
                this.formState.setFormError('email', 'Email is required');
                this.verificationError = true;
                return;
            }

            if (this.isEmpty(this.form.token)) {
                this.formState.setFormError('token', 'Verification token is required');
                this.verificationError = true;
                return;
            }

            if (this.formState.hasErrors) {
                this.verificationError = true;
                return;
            }

            this.loading = true;
            this.verificationError = false;

            try {
                const response = await axios.post('/api/auth/verify-email', {
                    email: this.form.email,
                    token: this.form.token
                });
                this.verified = true;
                this.notificationState.showSuccessNotification('Email verified successfully!');

                // Handle automatic login
                const user = response.data.user;
                const token = response.data.token;

                await this.authState.setUser(user);
                this.authState.setTokenOnRequest(token);
                this.authState.setTokenOnLocalStorage(token);

                this.resetResendTimer();

                // Redirect to dashboard
                this.goToDashboard();
            } catch (error) {
                this.verificationError = true;
                const message = error?.response?.data?.message ||
                               error?.response?.data?.errors?.email?.[0] ||
                               error?.response?.data?.errors?.token?.[0] ||
                               error?.message ||
                               'Email verification failed. Please try again.';

                this.formState.setFormError('general', message);
                this.notificationState.showWarningNotification(message);
            } finally {
                this.loading = false;
            }
        },

        async resendVerification() {
            if (this.loading || this.isResendDisabled) return;

            this.loading = true;
            this.verificationError = false;
            this.formState.hideFormErrors();

            try {
                await axios.post('/api/auth/resend-email-verification', {
                    email: this.form.email,
                    type: this.verificationType
                });

                const nextDelay = this.resendDelays[this.resendAttempts] || 20;

                this.notificationState.showSuccessNotification(
                    `Verification email sent! Please check your inbox. Next resend available in ${nextDelay}s`
                );

                this.verified = false;
                this.resendAttempts++;
                this.resendCountdown = nextDelay;
                this.startResendTimer();
            } catch (error) {
                const message = error?.response?.data?.message ||
                               error?.response?.data?.errors?.email?.[0] ||
                               error?.message ||
                               'Failed to resend verification email';

                this.formState.setFormError('general', message);
                this.notificationState.showWarningNotification(message);
            } finally {
                this.loading = false;
            }
        },

        goToDashboard() {
            this.$router.push({ name: 'show-stores' });
        },

        cleanupOldStorage() {
            try {
                const keys = Object.keys(localStorage);
                const now = Date.now();
                const TWENTY_FOUR_HOURS = 24 * 60 * 60 * 1000;

                keys.forEach(key => {
                    if (key.startsWith('verify_email_resend_')) {
                        try {
                            const data = JSON.parse(localStorage.getItem(key));
                            if (data.timestamp && (now - data.timestamp) > TWENTY_FOUR_HOURS) {
                                localStorage.removeItem(key);
                            }
                        } catch (e) {
                            localStorage.removeItem(key);
                        }
                    }
                });
            } catch (e) {
                console.warn('Cleanup failed:', e);
            }
        }
    },

    beforeUnmount() {
        this.saveResendState();
        if (this.resendTimer) {
            clearInterval(this.resendTimer);
        }
    },

    mounted() {
        this.cleanupOldStorage();
        this.form.email = decodeURIComponent(this.$route.query.email || '');
        this.form.token = this.$route.query.token || '';
        this.verificationType = this.$route.query.type || 'registration email';
        this.initTimerSystem();

        if (!this.form.email) {
            this.formState.setFormError('general', 'Missing email parameter.');
            this.notificationState.showWarningNotification('Missing email parameter.');
            this.verificationError = true;
            return;
        }

        if (!this.form.token) {
            this.formState.setFormError('general', 'Missing verification token.');
            this.notificationState.showWarningNotification('Missing verification token.');
            this.verificationError = true;
            return;
        }

        this.verifyEmail();
    }
};
</script>
