<template>
    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <div class="bg-white p-8 shadow-sm rounded-xl">

            <h1 class="text-lg font-bold mb-4">Account Settings</h1>
            <p class="text-xs text-gray-500 mb-8">
                Update your personal information and password.
            </p>

            <div class="space-y-6">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                    <!-- First Name -->
                    <Input
                        type="text"
                        label="First Name"
                        v-model="userForm.first_name"
                        @change="authState.saveStateDebounced('First name changed')"
                        :errorText="formState.getFormError('first_name')"
                    />

                    <!-- Last Name -->
                    <Input
                        type="text"
                        label="Last Name"
                        v-model="userForm.last_name"
                        @change="authState.saveStateDebounced('Last name changed')"
                        :errorText="formState.getFormError('last_name')"
                    />

                </div>

                <!-- Email -->
                <Input
                    type="text"
                    label="Email Address"
                    v-model="userForm.email"
                    @change="authState.saveStateDebounced('Email changed')"
                    :errorText="formState.getFormError('email')"
                />

                <!-- Change Password Section -->
                <div class="pt-6 border-t border-gray-200">
                    <h2 class="text-md font-semibold mb-2">
                        {{ hasPassword ? 'Change Password' : 'Set a Password' }}
                    </h2>
                    <p class="text-xs text-gray-500 mb-4">
                        {{ hasPassword
                            ? 'Leave blank if you don\'t want to change your password.'
                            : 'Set a password to sign in with email in the future.'
                        }}
                    </p>

                    <div class="space-y-4">

                        <!-- Current Password — only show if user already has one -->
                        <div v-if="hasPassword">
                            <Input
                                type="password"
                                label="Current Password"
                                placeholder="Required to set a new password"
                                v-model="userForm.current_password"
                                :errorText="formState.getFormError('current_password')"
                                @change="authState.saveStateDebounced('Current password entered')"
                            />
                        </div>

                        <template v-if="!hasPassword || (hasPassword && (userForm.current_password ?? '').length != 0)">

                            <Input
                                type="password"
                                label="New Password"
                                v-model="userForm.password"
                                :errorText="formState.getFormError('password')"
                                @change="authState.saveStateDebounced('New password changed')"
                            />

                            <Input
                                type="password"
                                label="Confirm New Password"
                                v-model="userForm.password_confirmation"
                                :errorText="formState.getFormError('password_confirmation')"
                                @change="authState.saveStateDebounced('Password confirmation changed')"
                            />

                        </template>

                    </div>
                </div>

                <!-- Social Connections -->
                <div class="pt-6 border-t border-gray-200">
                    <h2 class="text-md font-semibold mb-2">Connected Accounts</h2>
                    <p class="text-xs text-gray-500 mb-6">
                        Link your social accounts to sign in faster.
                    </p>

                    <div class="space-y-4">

                        <!-- Google -->
                        <div class="flex items-center justify-between">

                            <div class="flex items-center space-x-4">
                                <img :src="'/images/social-login-icons/google.png'" alt="Google" class="w-8 h-8" />
                                <div>
                                    <p class="font-medium">Google</p>
                                    <p v-if="user.has_google" class="text-xs font-semibold text-green-600">Connected</p>
                                    <p v-else class="text-sm text-gray-500">Not connected</p>
                                </div>
                            </div>

                            <Button
                                size="xs"
                                leftIconSize="14"
                                :loading="connecting.google"
                                :leftIcon="user.has_google ? Minus : Plus"
                                :type="user.has_google ? 'danger' : 'success'"
                                :action="() => handleSocialAction('google', user.has_google)">
                                <span class="">{{ user.has_google ? 'Disconnect' : 'Connect' }}</span>
                            </Button>

                        </div>

                        <!-- Facebook -->
                        <div class="flex items-center justify-between">

                            <div class="flex items-center space-x-4">
                                <img :src="'/images/social-login-icons/facebook.png'" alt="Facebook" class="w-8 h-8" />
                                <div>
                                    <p class="font-medium">Facebook</p>
                                    <p v-if="user.has_facebook" class="text-xs font-semibold text-green-600">Connected</p>
                                    <p v-else class="text-sm text-gray-500">Not connected</p>
                                </div>
                            </div>

                            <Button
                                size="xs"
                                leftIconSize="14"
                                :loading="connecting.facebook"
                                :leftIcon="user.has_facebook ? Minus : Plus"
                                :type="user.has_facebook ? 'danger' : 'success'"
                                :action="() => handleSocialAction('facebook', user.has_facebook)">
                                <span class="">{{ user.has_facebook ? 'Disconnect' : 'Connect' }}</span>
                            </Button>

                        </div>

                        <!-- LinkedIn -->
                        <div class="flex items-center justify-between">

                            <div class="flex items-center space-x-4">
                                <img :src="'/images/social-login-icons/linkedin.png'" alt="LinkedIn" class="w-8 h-8" />
                                <div>
                                    <p class="font-medium">LinkedIn</p>
                                    <p v-if="user.has_linkedin" class="text-xs font-semibold text-green-600">Connected</p>
                                    <p v-else class="text-sm text-gray-500">Not connected</p>
                                </div>
                            </div>

                            <Button
                                size="xs"
                                leftIconSize="14"
                                :loading="connecting.linkedin"
                                :leftIcon="user.has_linkedin ? Minus : Plus"
                                :type="user.has_linkedin ? 'danger' : 'success'"
                                :action="() => handleSocialAction('linkedin', user.has_linkedin)">
                                <span class="">{{ user.has_linkedin ? 'Disconnect' : 'Connect' }}</span>
                            </Button>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>

    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import { isEmpty } from '@Utils/stringUtils.js';
    import { Plus, Minus } from 'lucide-vue-next';

    export default {
        inject: ['authState', 'storeState', 'formState', 'changeHistoryState', 'notificationState'],
        components: { Input, Button },

        data() {
            return {
                Plus,
                Minus,
                connectingGoogle: false,
                connectingFacebook: false,
                connectingLinkedin: false,
                connecting: {
                    google: false,
                    facebook: false,
                    linkedin: false
                }
            };
        },

        computed: {
            userForm() {
                return this.authState.userForm;
            },
            user() {
                return this.authState.user;
            },
            store() {
                return this.storeState.store;
            },
            socialLoginCount() {
                return this.user.social_login_count;
            },
            hasPassword() {
                return this.user.has_password === true;
            }
        },
        methods: {
            isEmpty,
            setup() {
                this.authState.setUserForm();
            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    'Save Changes',
                    this.updateUser,
                    'primary',
                    null,
                );
            },

            async updateUser() {
                try {
                    if (this.authState.isUpdatingUser) return;

                    this.authState.isUpdatingUser = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = { ...this.userForm };

                    // Only send password fields if filled
                    if (isEmpty(data.password)) {
                        delete data.password;
                        delete data.password_confirmation;
                        delete data.current_password;
                    }

                    await axios.put(`/api/auth/user`, data);
                    await this.authState.fetchUser();

                    this.userForm.password = null;
                    this.userForm.current_password = null;
                    this.userForm.password_confirmation = null;

                    this.notificationState.showSuccessNotification('Account updated successfully');
                    this.authState.saveOriginalState('Original account');

                } catch (error) {
                    const message = error?.response?.data?.message || 'Failed to update account';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                } finally {
                    this.authState.isUpdatingUser = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },

            async handleSocialAction(provider, isConnected) {

                this.connecting[provider] = true;

                try {

                    if (isConnected) {

                        // === DISCONNECT ===
                        const isLastLoginMethod = this.socialLoginCount === 1 && !this.hasPassword;

                        if (isLastLoginMethod) {
                            this.notificationState.showWarningNotification(
                                `You cannot disconnect ${provider}. This is your only login method. Please set a password first.`
                            );
                            return;
                        }

                        if (!confirm(`Are you sure you want to disconnect ${provider}?`)) {
                            return;
                        }

                        const providerField = `${provider}_id`;

                        await axios.put('/api/auth/user', {
                            [providerField]: null
                        });

                        await this.authState.fetchUser();
                        this.notificationState.showSuccessNotification(`${provider} disconnected successfully`);
                        this.connecting[provider] = false;

                    }
                    else {

                        // === CONNECT ===
                        const token = this.authState.token;
                        const storeId = this.store?.id;

                        // Build URL with token and store_id (if available)
                        const params = new URLSearchParams();
                        params.append('store_id', storeId);
                        params.append('token', token);

                        window.location.href = `/auth/${provider}${params.toString() ? '?' + params.toString() : ''}`;

                    }
                } catch (error) {
                    const msg = error?.response?.data?.message || `Failed to ${isConnected ? 'disconnect' : 'connect'} ${provider}`;
                    this.notificationState.showWarningNotification(msg);
                }
            }
        },
        created() {
            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];
            listeners.forEach(event => {
                this.changeHistoryState.listeners[event] = () => this.authState.setUserForm();
            });
        }
    };

</script>
