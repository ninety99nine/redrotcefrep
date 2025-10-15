<template>

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">

        <div class="flex items-center justify-center p-6 bg-gradient-to-b from-blue-100 to-white-100">

            <div class="w-full max-w-md">

                <Logo height="h-20 mx-auto mb-4"></Logo>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">

                    <h1 class="text-2xl font-semibold leading-none tracking-tight mb-4">Sign in</h1>

                    <h1 class="text-sm text-gray-500 mb-4">Pick how you would like to sign in</h1>

                    <form @submit.prevent="submit" class="space-y-4 mb-4">

                        <div class="flex space-x-2">

                            <Pill
                                :type="form.type === loginType.value ? 'primary' : 'light'"
                                :action="() => form.type = loginType.value"
                                v-for="loginType in loginTypes"
                                :key="loginType.value">
                                {{ loginType.label }}
                            </Pill>

                        </div>

                        <Input
                            type="email"
                            v-model="form.email"
                            v-if="form.type == 'email'"
                            placeholder="john@gmail.com"
                            :errorText="formState.getFormError('email')" />

                        <Input
                            type="text"
                            placeholder="+26772000001"
                            v-model="form.mobile_number"
                            v-else-if="form.type == 'mobile_number'"
                            :errorText="formState.getFormError('mobile_number')" />

                        <Input
                            type="password"
                            v-model="form.password"
                            placeholder="******"
                            :errorText="formState.getFormError('password')" />

                        <div v-if="formState.generalErrorText" class="text-red-500 text-sm mb-3">
                            {{ formState.generalErrorText }}
                        </div>

                        <Button
                            size="md"
                            type="primary"
                            :action="submit"
                            buttonClass="w-full">
                            <span class="ml-1">{{ loading ? 'Signing in...' : 'Continue' }}</span>
                        </Button>

                        <div class="text-sm text-center mb-4">
                            <router-link :to="{ name: 'register' }" class="text-blue-600 hover:underline">Create account</router-link>
                            <span class="text-gray-300 mx-4">|</span>
                            <router-link :to="{ name: 'forgot-password' }" class="text-blue-600 hover:underline">Forgot password?</router-link>
                        </div>

                    </form>

                    <SocialLinks></SocialLinks>

                </div>

                <p class="text-sm text-center text-gray-500">© {{ currentYear }} Perfect Order. All rights reserved.</p>

            </div>

        </div>

        <!-- Right: Full Height Image (hidden on small screens) -->
        <div class="hidden lg:block">

            <img
                :src="'/images/lady-holding-boxes.jpg'"
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
    import Button from '@Partials/Button.vue';
    import { isEmpty } from '@Utils/stringUtils';
    import SocialLinks from '@Pages/auth/components/SocialLinks.vue';

    export default {
        name: 'Login',
        components: { Pill, Logo, Input, Button, SocialLinks },
        inject: ['authState', 'formState', 'notificationState'],
        data() {
            return {
                form: {
                    email: '',
                    password: '',
                    type: 'email',
                    mobile_number: ''
                },
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
            async submit() {

                if (this.loading) return;

                this.formState.hideFormErrors();

                if (this.form.type == 'email' && this.isEmpty(this.form.email)) {
                    this.formState.setFormError('email', 'Enter your email');
                }else if (this.form.type == 'mobile_number' && this.isEmpty(this.form.mobile_number)) {
                    this.formState.setFormError('mobile_number', 'Enter your mobile number');
                } else if (this.isEmpty(this.form.password)) {
                    this.formState.setFormError('password', 'Enter your password');
                }

                if (this.formState.hasErrors) {
                    return;
                }

                this.loading = true;

                try {

                    const response = await axios.post('/api/auth/login', this.form);

                    const token = response.data.token;
                    this.authState.setTokenOnRequest(token);
                    this.authState.setTokenOnLocalStorage(token);

                    const redirect = this.$route.query.redirect;

                    if (redirect) {
                        this.$router.push(redirect);
                    } else {
                        this.$router.push({ name: 'show-stores' });
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while signing in';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to sign in:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    };
</script>
