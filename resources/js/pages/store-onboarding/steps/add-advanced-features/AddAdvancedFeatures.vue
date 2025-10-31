<template>
    <div class="min-h-screen flex flex-col items-center pb-40">
        <div class="w-full max-w-lg">
            <StoreLogo :showButton="false" class="flex justify-center mb-4"></StoreLogo>
            <h2 class="text-2xl font-semibold text-center mb-2">Unlock More Possibilities</h2>
            <p class="text-gray-500 text-center mb-6">Enhance your store’s functionality with optional upgrades</p>
        </div>

        <div class="w-full max-w-lg">
            <div class="mb-4">
                <div v-if="isLoading" class="space-y-2">
                    <div v-for="(_, index) in [1,2,3]" :key="index" class="border border-gray-300 shadow-sm rounded-lg p-4 bg-gray-50">
                        <div class="flex justify-between">
                            <div>
                                <div class="w-full flex items-center space-x-2">
                                    <Skeleton width="w-4" :shine="true"></Skeleton>
                                    <Skeleton width="w-40" :shine="true"></Skeleton>
                                </div>
                            </div>
                            <div>
                                <Skeleton width="w-8" :shine="true"></Skeleton>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="space-y-3 mb-4">
                    <div class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">
                        <div class="flex justify-between space-x-4 items-center">
                            <div class="flex items-center space-x-2">
                                <Truck size="20" class="shrink-0"></Truck>
                                <p class="text-sm">Add Delivery option</p>
                            </div>
                            <Switch size="md" v-model="additionalFeatures.delivery.active"></Switch>
                        </div>
                        <div class="w-full space-y-4 mt-4" v-if="additionalFeatures.delivery.active">
                            <Input type="money" label="Delivery fee" :errorText="formState.getFormError('flat_fee_rate')" v-model="additionalFeatures.delivery.flat_fee_rate" description="You can set more rules in Settings » Delivery Methods"></Input>
                        </div>
                    </div>

                    <div class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">
                        <div class="flex justify-between space-x-4 items-center">
                            <div class="flex items-center space-x-2">
                                <MapPin size="20" class="shrink-0"></MapPin>
                                <p class="text-sm">Add Self Pick-up option</p>
                            </div>
                            <Switch size="md" v-model="additionalFeatures.self_pick_up_delivery.active"></Switch>
                        </div>
                    </div>

                    <div class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">
                        <div class="flex justify-between space-x-4 items-center">
                            <div class="flex space-x-2">
                                <Gift size="20" class="shrink-0"></Gift>
                                <div class="space-y-1">
                                    <p class="text-sm">Loyalty & Rewards</p>
                                    <p class="text-xs text-gray-400">Reward your customers with store credits calculated as a percentage of their total purchase amount</p>
                                </div>
                            </div>
                            <Switch size="md" v-model="additionalFeatures.rewards.active"></Switch>
                        </div>
                        <div class="w-full space-y-4 mt-4" v-if="additionalFeatures.rewards.active">
                            <Input placeholder="10" type="percentage" label="Rewards (%)" v-model="additionalFeatures.rewards.percentage_rate" :errorText="formState.getFormError('reward_percentage_rate')"></Input>
                        </div>
                    </div>

                    <div class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">
                        <div class="flex justify-between space-x-4 items-center">
                            <div class="flex space-x-2">
                                <BellRing size="20" class="shrink-0"></BellRing>
                                <div class="space-y-1">
                                    <p class="text-sm">Automated WhatsApp Notifications</p>
                                    <p class="text-xs text-gray-400">{{ appName }} will automatically send you WhatsApp notifications when you receive orders</p>
                                </div>
                            </div>
                            <Switch size="md" v-model="additionalFeatures.automated_whatsapp_notifications.active"></Switch>
                        </div>
                        <div class="w-full space-y-4 mt-4 border-t border-dashed border-gray-200 pt-4" v-if="additionalFeatures.automated_whatsapp_notifications.active">
                            <InputMobileNumbers
                                :isSubmitting="false"
                                :createMobileNumber="createMobileNumber"
                                :updateMobileNumber="updateMobileNumber"
                                :deleteMobileNumber="deleteMobileNumber"
                                v-model="additionalFeatures.automated_whatsapp_notifications.mobile_numbers">
                            </InputMobileNumbers>
                        </div>
                    </div>

                    <div class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">
                        <div class="flex justify-between space-x-4 items-center">
                            <div class="flex space-x-2">
                                <Smartphone size="20" class="shrink-0"></Smartphone>
                                <div class="space-y-1">
                                    <p class="text-sm">Available on USSD</p>
                                    <p class="text-xs text-gray-400">Your store will be available on {{ appName }} supported countries and mobile networks</p>
                                </div>
                            </div>
                            <Switch size="md" v-model="additionalFeatures.ussd.active"></Switch>
                        </div>
                        <div class="w-full space-y-4 mt-4" v-if="additionalFeatures.ussd.active">
                            <Input
                                label="Mobile Number"
                                placeholder="+26772000001"
                                v-model="additionalFeatures.ussd.mobile_number"
                                :errorText="formState.getFormError('ussd_mobile_number') || (isNotEmpty(additionalFeatures.ussd.mobile_number) && isEmpty(ussdMobileNumberWithoutExtension) ? 'The mobile number is not valid' : null)"
                                :description="ussdMobileNumberWithoutExtension ? `Customers dial *250*${ussdMobileNumberWithoutExtension}# to access your store` : 'Enter your mobile number to create your shortcode'">
                            </Input>
                            <Input maxlength="20" label="Call To Action" placeholder="Order pizza" v-model="additionalFeatures.ussd.call_to_action" :errorText="formState.getFormError('call_to_action')" description="The call to action to start the shopping experience on USSD"></Input>
                            <div class="border border-gray-100 rounded-lg">
                                <table class="w-full bg-white border-collapse shadow-sm rounded-lg overflow-hidden">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Country</th>
                                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Supported Networks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="border-t border-gray-200">
                                            <td>
                                                <div class="px-6 py-4 flex items-center text-sm text-gray-900 space-x-2">
                                                    <img :src="'/svgs/country-flags/bw.svg'" class="w-6 h-4 rounded-sm" alt="Botswana Flag" />
                                                    <span>Botswana</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="px-6 py-4 flex items-center">
                                                    <img :src="'/images/mobile-network-icons/orange.png'" class="w-8 rounded-sm" alt="Orange Network Logo" />
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 shadow-sm rounded-xl">
                        <p class="text-sm font-bold mb-4">What Else Can I Do?</p>
                        <div class="space-y-2">
                            <div :key="index" v-for="(option, index) in options" class="flex items-center space-x-2">
                                <CircleCheck size="16" class="text-green-500"></CircleCheck>
                                <p class="text-xs">{{ option.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <transition name="fade-1" mode="out-in">
                    <Button size="md" type="primary" buttonClass="w-full" :loading="isSubmitting" :disabled="isSubmitting" :action="addAdvancedFeatures" v-if="additionalFeaturesHaveChanged">
                        <span>Continue</span>
                    </Button>
                    <Button v-else size="md" type="light" buttonClass="w-40" rightIconSize="24" :rightIcon="MoveRight" :action="navigateToShare">
                        <span>Skip</span>
                    </Button>
                </transition>
            </div>
        </div>
    </div>
</template>

<script>
    import isEqual from 'lodash.isEqual';
    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import cloneDeep from 'lodash.cloneDeep';
    import Button from '@Partials/Button.vue';
    import Switch from '@Partials/Switch.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
    import { isEmpty, isNotEmpty } from '@Utils/stringUtils';
    import { parsePhoneNumberFromString } from 'libphonenumber-js';
    import InputMobileNumbers from '@Partials/InputMobileNumbers.vue';
    import { Gift, Truck, MapPin, BellRing, MoveRight, Smartphone, CircleCheck } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Alert, Input, Button, StoreLogo, Skeleton, Switch, InputMobileNumbers,
            Gift, Truck, MapPin, BellRing, Smartphone, CircleCheck
        },
        data() {
            return {
                MoveRight,
                workflows: [],
                isLoading: false,
                deliveryMethods: [],
                isSubmitting: false,
                workflowConfigurations: [],
                isLoadingWorkflowConfigurations: false,
                additionalFeatures: {
                    delivery: {
                        active: false,
                        name: 'Delivery',
                        flat_fee_rate: '0.00',
                    },
                    self_pick_up_delivery: {
                        active: false,
                        name: 'Self-Pickup',
                    },
                    rewards: {
                        active: false,
                        percentage_rate: '0'
                    },
                    automated_whatsapp_notifications: {
                        active: false,
                        mobile_numbers: [],
                        name: 'Whatsapp a waiting order to the team',
                    },
                    ussd: {
                        active: false,
                        mobile_number: '',
                        call_to_action: '',
                    },
                },
                workflow: null,
                delivery: null,
                selfPickUpDelivery: null,
                originalAdditionalFeatures: null,
                options: [
                    { name: 'Accept credit cards' },
                    { name: 'Buy or connect existing domains' },
                    { name: 'Receive order notifications via SMS' },
                ],
                appName: import.meta.env.VITE_APP_NAME
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            ussdMobileNumberWithoutExtension() {
                if (this.additionalFeatures.ussd.mobile_number) {
                    const phoneNumber = parsePhoneNumberFromString(this.additionalFeatures.ussd.mobile_number) || null;
                    if (phoneNumber && phoneNumber.isValid()) {
                        let nationalNumber = phoneNumber.formatNational();
                        return nationalNumber.replace(/\s+/g, '');
                    }
                }
                return '';
            },
            additionalFeaturesHaveChanged() {
                const a = cloneDeep(this.additionalFeatures);
                const b = cloneDeep(this.originalAdditionalFeatures);
                return !isEqual(a, b);
            },
        },
        methods: {
            isEmpty,
            isNotEmpty,
            async setup() {
                try {
                    this.isLoading = true;
                    await Promise.all([this.showWorkflowConfigurations(), this.showWorkflows(), this.showDeliveryMethods()]);

                    this.additionalFeatures.rewards.active = this.store.offer_rewards;
                    if (this.store.offer_rewards) {
                        this.additionalFeatures.rewards.percentage_rate = this.store.reward_percentage_rate.toString();
                    }

                    if (this.store.ussd_mobile_number) {
                        this.additionalFeatures.ussd.active = true;
                        this.additionalFeatures.ussd.call_to_action = this.store.call_to_action;
                        this.additionalFeatures.ussd.mobile_number = this.store.ussd_mobile_number.international;
                    }

                    this.originalAdditionalFeatures = cloneDeep(this.additionalFeatures);
                } catch (error) {
                    console.error("Error in setup():", error);
                } finally {
                    this.isLoading = false;
                }
            },
            createMobileNumber(typedMobileNumber, resetForm, hideModal) {
                this.additionalFeatures.automated_whatsapp_notifications.mobile_numbers.push(typedMobileNumber);
                hideModal();
                resetForm();
            },
            updateMobileNumber(typedMobileNumber, mobileNumberIndex, resetForm, hideModal) {
                this.additionalFeatures.automated_whatsapp_notifications.mobile_numbers[mobileNumberIndex] = typedMobileNumber;
                hideModal();
                resetForm();
            },
            deleteMobileNumber(mobileNumberIndex, resetForm, hideModal) {
                this.additionalFeatures.automated_whatsapp_notifications.mobile_numbers.splice(mobileNumberIndex, 1);
                hideModal();
                resetForm();
            },
            getWorkflowActionFields() {
                const config = this.workflowConfigurations.find(c => c.target === 'order' && c.trigger === 'waiting');
                if (!config) return {};
                const actionConfig = config.actions.find(a => a.name === 'whatsapp team');
                if (!actionConfig) return {};
                const templateConfig = actionConfig.templates.find(t => t.name === 'order details');
                if (!templateConfig) return {};
                return templateConfig.fields;
            },
            async addAdvancedFeatures() {
                try {
                    this.isSubmitting = true;
                    const promises = [];

                    promises.push(this.updateStore());

                    if (this.delivery) {
                        promises.push(this.updateDeliveryOption());
                    } else if (this.additionalFeatures.delivery.active) {
                        promises.push(this.addDeliveryOption());
                    }

                    if (this.selfPickUpDelivery) {
                        promises.push(this.updateSelfPickUpOption());
                    } else if (this.additionalFeatures.self_pick_up_delivery.active) {
                        promises.push(this.addSelfPickUpOption());
                    }

                    if (this.additionalFeatures.automated_whatsapp_notifications.active) {
                        promises.push(this.addOrUpdateAutomatedWhatsAppNotifications());
                    } else if (this.workflow) {
                        promises.push(this.deleteAutomatedWhatsAppNotifications());
                    }

                    await Promise.all(promises);
                    this.originalAdditionalFeatures = cloneDeep(this.additionalFeatures);
                    this.notificationState.showSuccessNotification('Additional features updated');
                    this.navigateToShare();
                } catch (error) {
                    const message = error?.message || 'Something went wrong while setting up';
                    this.notificationState.showWarningNotification(message);
                    console.error('Failed to setup:', error);
                } finally {
                    this.isSubmitting = false;
                }
            },
            async updateStore() {
                try {
                    let data = {
                        offer_rewards: this.additionalFeatures.rewards.active,
                    };

                    if (this.isNotEmpty(this.additionalFeatures.ussd.call_to_action)) {
                        data.call_to_action = this.additionalFeatures.ussd.call_to_action;
                    }

                    if (this.isNotEmpty(this.additionalFeatures.ussd.mobile_number)) {
                        const phoneNumber = parsePhoneNumberFromString(this.additionalFeatures.ussd.mobile_number) || null;
                        if (phoneNumber && phoneNumber.isValid()) {
                            data.ussd_mobile_number = this.additionalFeatures.ussd.mobile_number;
                        }
                    }else{
                        data.ussd_mobile_number = null;
                    }

                    if (this.isNotEmpty(this.additionalFeatures.rewards.percentage_rate)) {
                        data.reward_percentage_rate = this.additionalFeatures.rewards.percentage_rate;
                    }else{
                        data.reward_percentage_rate = '0';
                    }

                    await axios.put(`/api/stores/${this.store.id}`, data);
                    this.storeState.setStore({ ...this.store, ...data });
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update store changes:', error);
                    throw error;
                }
            },
            async addDeliveryOption() {
                try {
                    let data = {
                        return: true,
                        active: true,
                        charge_fee: true,
                        fee_type: 'flat fee',
                        store_id: this.store.id,
                        description: 'We deliver to you',
                        name: this.additionalFeatures.delivery.name,
                        flat_fee_rate: this.additionalFeatures.delivery.flat_fee_rate
                    };
                    const response = await axios.post(`/api/delivery-methods`, data);
                    this.delivery = response.data.delivery_method;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating delivery method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create delivery method:', error);
                    throw error;
                }
            },
            async updateDeliveryOption() {
                try {
                    let data = {
                        return: true,
                        charge_fee: true,
                        fee_type: 'flat fee',
                        store_id: this.store.id,
                        description: 'We deliver to you',
                        name: this.additionalFeatures.delivery.name,
                        active: this.additionalFeatures.delivery.active,
                        flat_fee_rate: this.additionalFeatures.delivery.flat_fee_rate,
                    };
                    const response = await axios.put(`/api/delivery-methods/${this.delivery.id}`, data);
                    this.delivery = response.data.delivery_method;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating delivery method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update delivery method:', error);
                    throw error;
                }
            },
            async addSelfPickUpOption() {
                try {
                    let data = {
                        return: true,
                        active: true,
                        store_id: this.store.id,
                        description: 'You collect the order yourself',
                        name: this.additionalFeatures.self_pick_up_delivery.name
                    };
                    const response = await axios.post(`/api/delivery-methods`, data);
                    this.selfPickUpDelivery = response.data.delivery_method;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating self pickup delivery method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create self pickup delivery method:', error);
                    throw error;
                }
            },
            async updateSelfPickUpOption() {
                try {
                    let data = {
                        return: true,
                        store_id: this.store.id,
                        description: 'You collect the order yourself',
                        name: this.additionalFeatures.self_pick_up_delivery.name,
                        active: this.additionalFeatures.self_pick_up_delivery.active
                    };
                    const response = await axios.put(`/api/delivery-methods/${this.selfPickUpDelivery.id}`, data);
                    this.selfPickUpDelivery = response.data.delivery_method;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating self pickup delivery method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update self pickup delivery method:', error);
                    throw error;
                }
            },
            async showWorkflowConfigurations() {
                try {

                    this.isLoadingWorkflowConfigurations = true;

                    const config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get('/api/workflows/configurations', config);
                    this.workflowConfigurations = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to fetch workflow configurations';
                    this.notificationState.showWarningNotification(message);
                    console.error('Failed to fetch workflow configurations:', error);
                } finally {
                    this.isLoadingWorkflowConfigurations = false;
                }
            },
            async showWorkflows() {
                try {

                    const config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get('/api/workflows', config);
                    this.workflows = response.data.data;

                    this.workflow = this.workflows.find(workflow =>
                        workflow.name === this.additionalFeatures.automated_whatsapp_notifications.name &&
                        workflow.target === 'order' &&
                        workflow.trigger === 'waiting'
                    );

                    if (this.workflow) {
                        this.additionalFeatures.automated_whatsapp_notifications.active = this.workflow.active;
                        this.additionalFeatures.automated_whatsapp_notifications.mobile_numbers = this.workflow.actions[0].mobile_numbers || [];
                    }else if(!isEmpty(this.store.whatsapp_mobile_number)) {
                        this.additionalFeatures.automated_whatsapp_notifications.mobile_numbers = [this.store.whatsapp_mobile_number.international];
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching workflows';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch workflows:', error);
                }
            },
            async addOrUpdateAutomatedWhatsAppNotifications() {
                try {

                    const fields = this.getWorkflowActionFields();
                    const data = {
                        active: this.additionalFeatures.automated_whatsapp_notifications.active,
                        target: 'order',
                        trigger: 'waiting',
                        store_id: this.store.id,
                        name: this.additionalFeatures.automated_whatsapp_notifications.name,
                        actions: [
                            {
                                action: 'whatsapp team',
                                template: 'order details',
                                ...fields,
                                mobile_numbers: this.additionalFeatures.automated_whatsapp_notifications.mobile_numbers
                            }
                        ]
                    };

                    let response;

                    if (this.workflow) {
                        response = await axios.put(`/api/workflows/${this.workflow.id}`, data);
                    } else {
                        response = await axios.post(`/api/workflows`, data);
                    }

                    this.workflow = response.data;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while setting up WhatsApp notifications';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to set up WhatsApp notifications:', error);
                    throw error;
                }
            },
            async deleteAutomatedWhatsAppNotifications() {
                try {
                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    };
                    await axios.delete(`/api/workflows/${this.workflow.id}`, config);
                    this.workflow = null;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting WhatsApp notifications';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete WhatsApp notifications:', error);
                    throw error;
                }
            },
            async showDeliveryMethods() {
                try {
                    const config = {
                        params: {
                            store_id: this.store.id,
                            association: 'team member'
                        }
                    };
                    const response = await axios.get('/api/delivery-methods', config);
                    this.deliveryMethods = response.data.data;

                    this.delivery = this.deliveryMethods.find(deliveryMethod => deliveryMethod.name === this.additionalFeatures.delivery.name);
                    this.selfPickUpDelivery = this.deliveryMethods.find(deliveryMethod => deliveryMethod.name === this.additionalFeatures.self_pick_up_delivery.name);

                    if (this.delivery) {
                        this.additionalFeatures.delivery.active = this.delivery.active;
                        this.additionalFeatures.delivery.flat_fee_rate = this.delivery.flat_fee_rate.amount_without_currency;
                    }

                    if (this.selfPickUpDelivery) {
                        this.additionalFeatures.self_pick_up_delivery.active = this.selfPickUpDelivery.active;
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching delivery methods';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch delivery methods:', error);
                }
            },
            navigateToShare() {
                this.$router.push({
                    name: 'share',
                    params: { store_id: this.store.id }
                });
            }
        },
        beforeRouteLeave(to, from, next) {
            if (this.isSubmitting || this.additionalFeaturesHaveChanged) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        created() {
            this.setup();
        }
    };
</script>
