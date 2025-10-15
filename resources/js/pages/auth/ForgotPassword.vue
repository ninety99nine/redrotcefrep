<template>

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">

        <div class="flex items-center justify-center p-6 bg-gradient-to-b from-blue-100 to-white-100">

            <div class="w-full max-w-md">

                <Logo height="h-20 mx-auto mb-4"></Logo>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">

                    <h1 class="text-2xl font-semibold leading-none tracking-tight mb-4">Forgot Password</h1>

                    <h1 class="text-sm text-gray-500 mb-4">
                        <template v-if="type == 'email'">Enter your email address to receive a password reset link.</template>
                        <template v-else>Follow instructions to reset your password</template>
                    </h1>

                    <form @submit.prevent="submit" class="space-y-4">

                        <div class="flex space-x-2">

                            <Pill
                                :type="type === loginType.value ? 'primary' : 'light'"
                                :action="() => type = loginType.value"
                                v-for="loginType in loginTypes"
                                :key="loginType.value">
                                {{ loginType.label }}
                            </Pill>

                        </div>

                        <template v-if="type == 'email'">

                            <Input
                                type="email"
                                label="Email"
                                v-model="form.email"
                                :errorText="formState.getFormError('email')" />

                            <div v-if="formState.generalErrorText" class="text-red-500 text-sm mb-3">
                                {{ formState.generalErrorText }}
                            </div>

                            <Button
                                size="md"
                                type="primary"
                                :action="submit"
                                :disabled="loading"
                                buttonClass="w-full">
                                <span class="ml-1">{{ loading ? 'Sending...' : 'Send Reset Link' }}</span>
                            </Button>

                            <div class="flex justify-center mt-4">
                                <router-link :to="{ name: 'login' }" class="text-sm text-blue-600 hover:underline">Back to Login</router-link>
                            </div>

                        </template>

                        <template v-else>

                            <Alert :dismissable="false">
                                <template #description>
                                    <p class="font-semibold mb-4">Supported Networks</p>
                                    <img :src="'/images/mobile-network-icons/orange.png'" class="w-10 rounded-sm" />
                                </template>
                            </Alert>

                            <Alert :dismissable="false">
                                <template #description>
                                    <p class="mb-4">Dial <span class="font-bold">*250#</span> on your mobile phone. Select <span class="font-bold">Profile</span>, then select <span class="font-bold">Set Password</span> and enter your new password.</p>
                                    <p>Now click the login button below and login using your <span class="font-bold">mobile number</span> and <span class="font-bold">new password</span></p>
                                </template>
                            </Alert>

                            <Button
                                size="md"
                                type="primary"
                                :action="goToLogin"
                                :disabled="loading"
                                buttonClass="w-full">
                                <span class="ml-1">Login</span>
                            </Button>

                        </template>

                    </form>

                </div>

                <p class="text-sm text-center text-gray-500">© {{ currentYear }} Perfect Order. All rights reserved.</p>

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
    import Pill from '@Partials/Pill.vue';
    import Logo from '@Partials/Logo.vue';
    import Input from '@Partials/Input.vue';
    import Alert from '@Partials/Alert.vue';
    import Button from '@Partials/Button.vue';
    import { isEmpty } from '@Utils/stringUtils';

    export default {
        name: 'ForgotPassword',
        components: { Pill, Logo, Input, Alert, Button },
        inject: ['formState', 'notificationState'],
        data() {
            return {
                form: {
                    email: ''
                },
                type: 'email',
                loginTypes: [
                    {
                        label: 'Email',
                        value: 'email'
                    },
                    {
                        label: 'Mobile Number',
                        value: 'mobile_number'
                    }
                ],
                loading: false,
                currentYear: new Date().getFullYear()
            };
        },
        methods: {
            isEmpty,
            goToLogin() {
                this.$router.push({ name: 'login' });
            },
            async submit() {

                if (this.loading) return;

                this.formState.hideFormErrors();

                if (this.isEmpty(this.form.email)) {
                    this.formState.setFormError('email', 'Email is required');
                }

                if (this.formState.hasErrors) return;

                this.loading = true;

                try {
                    await axios.post('/api/auth/forgot-password', this.form);
                    this.notificationState.showSuccessNotification('A password reset link has been sent to your email.');
                    this.$router.push({ name: 'login' });
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while requesting a password reset link';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to request password reset link:', error);
                } finally {
                    this.loading = false;
                }
            }
        },
        mounted() {
            this.form.email = this.$route.query.email || '';
        }
    };

</script>
