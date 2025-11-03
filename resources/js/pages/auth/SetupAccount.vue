<template>

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">

        <div class="flex items-center justify-center p-6 bg-linear-to-b from-blue-100 to-white-100">

            <div class="w-full max-w-md">

                <Logo height="h-20 mx-auto mb-4"></Logo>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">

                    <h1 class="text-2xl font-semibold leading-none tracking-tight mb-4">
                        {{ tokenError ? (tokenError.includes('expired') ? 'Setup Link Expired' : 'Setup Link Invalid') : 'Set Up Your Account' }}
                    </h1>

                    <h1 class="text-sm text-gray-500 mb-4">
                        {{ tokenError ? (tokenError.includes('expired') ? 'The link to set up your account has expired. Contact your administrator to assist.' : 'The link to set up your account is invalid. Contact your administrator to assist.') : 'Please set your password to activate your account.' }}
                    </h1>

                    <template v-if="tokenError">

                        <Alert :title="tokenError" type="danger" :dismissable="false" class="mb-8"></Alert>

                        <Button type="primary" size="lg" :action="goHome" buttonClass="w-full">
                            <span>Go Home</span>
                        </Button>

                    </template>

                    <form v-else @submit.prevent="submit" class="space-y-4">

                        <Input
                            disabled
                            type="email"
                            label="Email"
                            v-model="form.email"
                            :errorText="formState.getFormError('email')"/>

                        <Input
                            type="password"
                            label="Password"
                            v-model="form.password"
                            :errorText="formState.getFormError('password')" />

                        <Input
                            type="password"
                            label="Confirm Password"
                            v-model="form.confirm_password"
                            :errorText="formState.getFormError('confirm_password')" />

                        <div v-if="formState.generalErrorText" class="text-red-500 text-sm mb-3">
                            {{ formState.generalErrorText }}
                        </div>

                        <Button
                            size="md"
                            type="primary"
                            class="w-full"
                            :action="submit"
                            :leftIcon="LogIn"
                            :disabled="loading">
                            <span class="ml-1">{{ loading ? 'Setting Password...' : 'Set Password' }}</span>
                        </Button>

                    </form>

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
    import { LogIn } from 'lucide-vue-next';
    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import { isEmpty } from '@Utils/stringUtils';

    export default {
        name: 'SetupAccount',
        components: { Logo, Alert, Input, Button },
        inject: ['authState', 'formState', 'notificationState'],
        data() {
            return {
                LogIn,
                form: {
                    email: '',
                    token: '',
                    password: '',
                    confirm_password: ''
                },
                loading: false,
                tokenError: null,
                currentYear: new Date().getFullYear(),
                appName: import.meta.env.VITE_APP_NAME
            };
        },
        methods: {
            isEmpty,
            goHome() {
                this.$router.push({ name: 'login' });
            },
            async validateToken() {
                try {
                    await axios.post('/api/auth/validate-token', {
                        email: this.form.email,
                        token: this.form.token
                    });
                    this.tokenError = null; // Token is valid
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Invalid or expired token.';
                    this.tokenError = message;
                    this.notificationState.showWarningNotification(message);
                }
            },
            async submit() {

                if (this.loading) return;

                this.formState.hideFormErrors();

                if (this.isEmpty(this.form.email)) {
                    this.formState.setFormError('email', 'Email is required');
                }
                if (this.isEmpty(this.form.password)) {
                    this.formState.setFormError('password', 'Password is required');
                }
                if (this.isEmpty(this.form.confirm_password)) {
                    this.formState.setFormError('confirm_password', 'Confirm password');
                }
                if (this.form.password !== this.form.confirm_password) {
                    this.formState.setFormError('confirm_password', 'Passwords do not match');
                }

                if (this.formState.hasErrors) return;

                this.loading = true;

                try {
                    const response = await axios.post('/api/auth/setup-password', this.form);

                    const user = response.data.user;
                    const token = response.data.token;

                    await this.authState.setUser(user);
                    this.authState.setTokenOnRequest(token);
                    this.authState.setTokenOnLocalStorage(token);

                    this.$router.push({ name: 'show-stores' });
                    this.notificationState.showSuccessNotification('Password set successfully!');
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while setting your password';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to set password:', error);
                } finally {
                    this.loading = false;
                }
            }
        },
        async mounted() {
            this.form.email = this.$route.query.email || '';
            this.form.token = this.$route.query.token || '';

            // Validate token on page load
            if (this.form.email && this.form.token) {
                await this.validateToken();
            } else {
                this.tokenError = 'Invalid or missing email/token.';
                this.notificationState.showWarningNotification('Invalid or missing email/token.');
            }
        }
    };

</script>
