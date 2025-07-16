<template>

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">

        <div class="flex items-center justify-center p-6 bg-gradient-to-b from-blue-100 to-white-100">

            <div class="w-full max-w-md">

                <Logo height="h-20 mx-auto mb-4"></Logo>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">

                    <h1 class="text-2xl font-semibold leading-none tracking-tight mb-4">Reset Password</h1>
                    <h1 class="text-sm text-gray-500 mb-4">Enter a new password for your account.</h1>

                    <form @submit.prevent="submit" class="space-y-4">

                        <Input
                            disabled
                            type="email"
                            label="Email"
                            v-model="form.email"
                            :errorText="formState.getFormError('email')"/>

                        <Input
                            type="password"
                            label="New Password"
                            v-model="form.password"
                            :errorText="formState.getFormError('password')" />

                        <Input
                            type="password"
                            label="Confirm New Password"
                            v-model="form.confirmPassword"
                            :errorText="formState.getFormError('confirmPassword')" />

                        <div v-if="formState.generalErrorText" class="text-red-500 text-sm mb-3">
                            {{ formState.generalErrorText }}
                        </div>

                        <Button
                            size="md"
                            type="primary"
                            :action="submit"
                            :disabled="loading"
                            buttonClass="w-full">
                            <span class="ml-1">{{ loading ? 'Resetting...' : 'Reset Password' }}</span>
                        </Button>

                        <div class="flex justify-center mt-4">
                            <router-link :to="{ name: 'login' }" class="text-sm text-blue-600 hover:underline">Back to Login</router-link>
                        </div>

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
    import Logo from '@Partials/Logo.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';

    export default {
        name: 'ResetPassword',
        components: { Logo, Input, Button },
        inject: ['formState', 'notificationState'],
        data() {
            return {
                form: {
                    email: '',
                    token: '',
                    password: '',
                    confirmPassword: ''
                },
                loading: false,
                currentYear: new Date().getFullYear()
            };
        },
        methods: {
            async submit() {

                if (this.loading) return;

                this.formState.hideFormErrors();

                if (this.form.email.trim() === '') {
                    this.formState.setFormError('email', 'Email is required');
                }

                if (this.form.password.trim() === '') {
                    this.formState.setFormError('password', 'New Password is required');
                }

                if (this.form.confirmPassword.trim() === '') {
                    this.formState.setFormError('confirmPassword', 'Confirm New Password is required');
                }

                if (this.form.password !== this.form.confirmPassword) {
                    this.formState.setFormError('confirmPassword', 'Passwords do not match');
                }

                if (this.formState.hasErrors) return;

                this.loading = true;

                try {
                    await axios.post('/api/auth/reset-password', this.form);
                    this.notificationState.showSuccessNotification('Password reset successfully. Please log in with your new password.');
                    this.$router.push({ name: 'login' });
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while resetting your password';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to reset password:', error);
                } finally {
                    this.loading = false;
                }
            }
        },
        mounted() {
            this.form.email = this.$route.query.email || '';
            this.form.token = this.$route.query.token || '';

            if (!this.form.email) {
                this.formState.setFormError('general', 'Missing email.');
                this.notificationState.showWarningNotification('Missing email.');
            }else if (!this.form.token) {
                this.formState.setFormError('general', 'Missing token.');
                this.notificationState.showWarningNotification('Missing token.');
            }
        }
    };

</script>
