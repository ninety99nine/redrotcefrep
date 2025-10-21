<template>

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">

        <div class="flex items-center justify-center p-6 bg-gradient-to-b from-blue-100 to-white-100">

            <div class="w-full max-w-md">

                <Logo height="h-20 mx-auto mb-4"></Logo>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">

                    <h1 class="text-2xl font-semibold leading-none tracking-tight mb-4">Sign up</h1>

                    <h1 class="text-sm text-gray-500 mb-4">Lets create your account</h1>

                    <form @submit.prevent="submit" class="space-y-4">

                        <Input
                            type="text"
                            label="First Name"
                            placeholder="John"
                            v-model="form.first_name"
                            :errorText="formState.getFormError('first_name')" />

                        <Input
                            type="text"
                            label="Last Name"
                            placeholder="Doe"
                            v-model="form.last_name"
                            :errorText="formState.getFormError('last_name')" />

                        <vue-slide-up-down :active="expand" class="space-y-4">

                            <Input
                                type="email"
                                label="Email"
                                v-model="form.email"
                                placeholder="john@gmail.com"
                                :errorText="formState.getFormError('email')" />

                            <Input
                                type="password"
                                label="Password"
                                placeholder="******"
                                v-model="form.password"
                                :errorText="formState.getFormError('password')" />

                            <Input
                                type="password"
                                placeholder="******"
                                label="Confirm Password"
                                v-model="form.confirm_password"
                                :errorText="formState.getFormError('confirm_password')" />

                        </vue-slide-up-down>

                        <div v-if="formState.generalErrorText" class="text-red-500 text-sm mb-3">
                            {{ formState.generalErrorText }}
                        </div>

                        <div class="flex justify-end mb-6">
                            <router-link :to="{ name: 'login' }" class="text-sm text-blue-600 hover:underline">Already have an account?</router-link>
                        </div>

                        <Button
                            size="md"
                            type="primary"
                            :action="submit"
                            buttonClass="w-full">
                            <span class="ml-1">{{ loading ? 'Creating account...' : 'Create Account' }}</span>
                        </Button>

                    </form>

                </div>

                <SocialLinks class="mb-6"></SocialLinks>

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
import { isEmpty } from '@Utils/stringUtils';
import VueSlideUpDown from 'vue-slide-up-down';
import SocialLinks from '@Pages/auth/components/SocialLinks.vue';

export default {
    name: 'Register',
    components: { Logo, Input, Button, SocialLinks, VueSlideUpDown },
    inject: ['formState', 'notificationState'], // Removed authState
    data() {
        return {
            form: {
                email: '',
                password: '',
                last_name: '',
                first_name: '',
                confirm_password: ''
            },
            loading: false,
            currentYear: new Date().getFullYear()
        };
    },
    computed: {
        expand() {
            return this.isEmpty(this.form.first_name) && this.isEmpty(this.form.last_name);
        }
    },
    methods: {
        isEmpty,
        async submit() {
            if (this.loading) return;

            this.formState.hideFormErrors();

            if (this.isEmpty(this.form.first_name)) {
                this.formState.setFormError('first_name', 'Enter your first name');
            } else if (this.isEmpty(this.form.last_name)) {
                this.formState.setFormError('last_name', 'Enter your last name');
            } else if (this.isEmpty(this.form.email)) {
                this.formState.setFormError('email', 'Enter your email');
            } else if (this.isEmpty(this.form.password)) {
                this.formState.setFormError('password', 'Enter your password');
            } else if (this.isEmpty(this.form.confirm_password)) {
                this.formState.setFormError('confirm_password', 'Confirm your password');
            } else if (this.form.password != this.form.confirm_password) {
                this.formState.setFormError('password', 'Passwords do not match');
            }

            if (this.formState.hasErrors) {
                return;
            }

            this.loading = true;

            try {
                await axios.post('/api/auth/register', this.form);

                this.notificationState.showSuccessNotification('Registration successful! Please check your email to verify your account.');

                // Redirect to verify-email route with email
                this.$router.push({
                    name: 'verify-email',
                    query: { email: this.form.email, type: 'registration email' }
                });
            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Something went wrong while signing up';
                this.notificationState.showWarningNotification(message);
                this.formState.setServerFormErrors(error);
                console.error('Failed to sign up:', error);
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>
