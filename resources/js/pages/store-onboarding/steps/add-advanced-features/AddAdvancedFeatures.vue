<template>

    <div class="min-h-screen flex flex-col items-center pt-20 pb-40">

        <div class="w-full max-w-lg">

            <!-- Store Logo -->
            <StoreLogo :showButton="false" class="flex justify-center mb-4"></StoreLogo>

            <!-- Heading -->
            <h2 class="text-2xl font-semibold text-center mb-2">Unlock More Possibilities</h2>

            <!-- Instruction -->
            <p class="text-gray-500 text-center mb-6">Enhance your store’s functionality with optional upgrades</p>

        </div>

        <div class="w-full max-w-lg">

            <div class="mb-4">

                <!-- Additional Features (Loading Placeholder) -->
                <div v-if="isLoading" class="space-y-2">

                    <div v-for="(_, index) in [1,2,3]" :key="index" class="border shadow-sm rounded-lg p-4 bg-gray-50">
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

                <!-- Additional Features -->
                <div v-else class="space-y-3 mb-4">

                    <!-- Add Delivery option -->
                    <div class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                        <div class="flex justify-between space-x-4 items-center">

                            <div class="flex items-center space-x-2">

                                <!-- Icon -->
                                <Truck size="20" class="shrink-0"></Truck>

                                <!-- Name -->
                                <p class="text-sm">
                                    Add Delivery option
                                </p>

                            </div>

                            <!-- Add Delivery Option Toogle Switch -->
                            <Switch
                                size="md"
                                v-model="additionalFeatures.delivery.active">
                            </Switch>

                        </div>

                        <!-- Delivery Settings -->
                        <div
                            class="w-full space-y-4 mt-4"
                            v-if="additionalFeatures.delivery.active">

                            <Input
                                type="money"
                                label="Delivery fee"
                                :errorText="formState.getFormError('flat_fee_rate')"
                                v-model="additionalFeatures.delivery.flat_fee_rate"
                                description="You can set more rules in Settings » Delivery Methods">
                            </Input>

                        </div>

                    </div>

                    <!-- Add Self Pick-up option -->
                    <div class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                        <div class="flex justify-between space-x-4 items-center">

                            <div class="flex items-center space-x-2">

                                <!-- Icon -->
                                <MapPin size="20" class="shrink-0"></MapPin>

                                <!-- Name -->
                                <p class="text-sm">
                                    Add Self Pick-up option
                                </p>

                            </div>

                            <!-- Add Self Pick-up Option Toogle Switch -->
                            <Switch
                                size="md"
                                v-model="additionalFeatures.self_pick_up_delivery.active">
                            </Switch>

                        </div>

                    </div>

                    <!-- Loyalty & Rewards -->
                    <div class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                        <div class="flex justify-between space-x-4 items-center">

                            <div class="flex space-x-2">

                                <!-- Icon -->
                                <Gift size="20" class="shrink-0"></Gift>

                                <div class="space-y-1">

                                    <!-- Name -->
                                    <p class="text-sm">
                                        Loyalty & Rewards
                                    </p>

                                    <!-- Description -->
                                    <p class="text-xs text-gray-400">
                                        Reward your customers with store credits calculated as a percentage of their total purchase amount
                                    </p>

                                </div>

                            </div>

                            <!-- Loyalty & Rewards Toogle Switch -->
                            <Switch
                                size="md"
                                v-model="additionalFeatures.rewards.active">
                            </Switch>

                        </div>

                        <!-- Delivery Settings -->
                        <div
                            class="w-full space-y-4 mt-4"
                            v-if="additionalFeatures.rewards.active">

                            <Input
                                placeholder="10"
                                type="percentage"
                                label="Rewards (%)"
                                v-model="additionalFeatures.rewards.percentage_rate"
                                :errorText="formState.getFormError('reward_percentage_rate')">
                            </Input>

                        </div>

                    </div>

                    <!-- WhatsApp Bot notifications -->
                    <div class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                        <div class="flex justify-between space-x-4 items-center">

                            <div class="flex space-x-2">

                                <!-- Icon -->
                                <BellRing size="20" class="shrink-0"></BellRing>

                                <div class="space-y-1">

                                    <!-- Name -->
                                    <p class="text-sm">
                                        Automated WhatsApp notifications
                                    </p>

                                    <!-- Description -->
                                    <p class="text-xs text-gray-400">
                                        {{ appName }} will automatically send you Whatsapp notifications when you receive orders
                                    </p>

                                </div>

                            </div>

                            <!-- Automated WhatsApp Notifications Toogle Switch -->
                            <Switch
                                size="md"
                                v-model="additionalFeatures.automated_whatsapp_notifications.active">
                            </Switch>

                        </div>

                        <!-- Automated WhatsApp Notifications Settings -->
                        <div
                            class="w-full space-y-4 mt-4 border-t border-dashed border-gray-200 pt-4"
                            v-if="additionalFeatures.automated_whatsapp_notifications.active">

                            <!-- Bulk Mobile Number Input -->
                            <InputMobileNumbers
                                :isSubmitting="false"
                                :createMobileNumber="createMobileNumber"
                                :updateMobileNumber="updateMobileNumber"
                                :deleteMobileNumber="deleteMobileNumber"
                                v-model="additionalFeatures.automated_whatsapp_notifications.mobile_numbers">
                            </InputMobileNumbers>

                        </div>

                    </div>

                    <!-- Available on USSD -->
                    <div class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                        <div class="flex justify-between space-x-4 items-center">

                            <div class="flex space-x-2">

                                <!-- Icon -->
                                <Smartphone size="20" class="shrink-0"></Smartphone>

                                <div class="space-y-1">

                                    <!-- Name -->
                                    <p class="text-sm">
                                        Available on USSD
                                    </p>

                                    <!-- Description -->
                                    <p class="text-xs text-gray-400">
                                        Your store will be available on {{ appName }} supported Mobile Networks
                                    </p>

                                </div>

                            </div>

                            <!-- Available On USSD Toogle Switch -->
                            <Switch
                                size="md"
                                v-model="additionalFeatures.ussd.active">
                            </Switch>

                        </div>

                        <!-- USSD Settings -->
                        <div
                            class="w-full space-y-4 mt-4"
                            v-if="additionalFeatures.ussd.active">

                            <Alert :dismissable="false">
                                <template #description>
                                    <p class="font-semibold mb-4">Supported Networks</p>
                                    <img :src="'/images/mobile-network-icons/orange.png'" class="w-10 rounded-sm" />
                                </template>
                            </Alert>

                            <!-- Mobile Number Input -->
                            <Input
                                label="Mobile Number"
                                v-model="additionalFeatures.ussd.mobile_number"
                                :errorText="formState.getFormError('ussd_mobile_number')"
                                :description="ussdMobileNumberWithoutExtension ? `Customers dial *250*${ussdMobileNumberWithoutExtension}# to access your store` : 'Enter your mobile number to create your shortcode'">
                            </Input>

                            <!-- Call To Action Input -->
                            <Input
                                maxlength="20"
                                label="Call To Action"
                                placeholder="Order pizza"
                                v-model="additionalFeatures.ussd.call_to_action"
                                :errorText="formState.getFormError('call_to_action')"
                                description="The call to action to start the shopping experience on USSD">
                            </Input>

                        </div>

                    </div>

                    <div class="bg-white p-4 shadow-sm rounded-xl">

                        <p class="text-sm font-bold mb-4">What Else Can I Do?</p>

                        <div class="space-y-2">

                            <div
                                :key="index"
                                v-for="(option, index) in options"
                                class="flex items-center space-x-2">

                                <!-- Icon -->
                                <CircleCheck size="16" class="text-green-500"></CircleCheck>

                                <!-- Name -->
                                <p class="text-xs">
                                    {{ option.name }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="flex justify-end">

                <transition name="fade-1" mode="out-in">

                    <!-- Continue -->
                    <Button
                        size="md"
                        type="primary"
                        buttonClass="w-full"
                        :loading="isSubmitting"
                        :disabled="isSubmitting"
                        :action="addAdvancedFeatures"
                        v-if="additionalFeaturesHaveChanged">
                        <span>Continue</span>
                    </Button>

                    <!-- Skip -->
                    <Button
                        v-else
                        size="md"
                        type="light"
                        buttonClass="w-40"
                        rightIconSize="24"
                        :rightIcon="MoveRight"
                        :action="navigateToShare">
                        <span>Skip</span>
                    </Button>

                </transition>

            </div>

        </div>

    </div>

</template>

<script>

    import isEqual from 'lodash/isEqual';
    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import cloneDeep from 'lodash/cloneDeep';
    import Button from '@Partials/Button.vue';
    import Switch from '@Partials/Switch.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
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
                        call_to_action: '',
                        mobile_number: ''
                    },
                },
                workflow: null,
                delivery: null,
                selfPickUpDelivery: null,
                originalAdditionalFeatures: null,
                options: [
                    {
                        name: 'Accept credit cards'
                    },
                    {
                        name: 'Buy or connect existing domains'
                    },
                    {
                        name: 'Receive order notifications via SMS'
                    },
                ],
                appName: import.meta.env.VITE_APP_NAME
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            ussdMobileNumberWithoutExtension() {
                if(this.additionalFeatures.ussd.mobile_number) {
                    const phoneNumber = parsePhoneNumberFromString(this.additionalFeatures.ussd.mobile_number);
                    let nationalNumber =  phoneNumber.formatNational();
                    return nationalNumber.replace(/\s+/g, '');
                }
                return '';
            },
            additionalFeaturesHaveChanged() {
                // Clone the objects to avoid modifying the original data
                var a = cloneDeep(this.additionalFeatures);
                var b = cloneDeep(this.originalAdditionalFeatures);

                // Compare the modified arrays for equality
                return !isEqual(a, b);
            },
        },
        methods: {
            async setup() {

                try {

                    this.isLoading = true;

                    // Fire all async functions simultaneously
                    await Promise.all([/*   this.showWorkflows(), */ this.showDeliveryMethods()]);

                    // Set rewards
                    this.additionalFeatures.rewards.active = this.store.offer_rewards;

                    if (this.store.offer_rewards) {
                        this.additionalFeatures.rewards.percentage_rate = this.store.reward_percentage_rate.toString();
                    }

                    /*
                    // Set Automated Whatsapp Notifications
                    if (this.workflow) {
                        const workflowSteps = this.workflow._relationships.workflowSteps;

                        if(workflowSteps.length) {
                            const workflowStep = workflowSteps[0];
                            this.additionalFeatures.automated_whatsapp_notifications.mobile_numbers = workflowStep.settings.mobile_numbers.map((mobile_number) => mobile_number.international);
                        }
                    }else if(this.store.whatsappMobileNumber) {
                        this.additionalFeatures.automated_whatsapp_notifications.mobile_numbers.push(this.store.whatsappMobileNumber.international);
                    }
                    */

                    // Set USSD
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
            async addAdvancedFeatures() {

                try {

                    const promises = [];
                    this.isSubmitting = true;

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

                    /*
                    if (this.workflow) {
                        promises.push(this.updateAutomatedWhatsAppNotifications());
                    } else if (this.additionalFeatures.automated_whatsapp_notifications.active) {
                        promises.push(this.addAutomatedWhatsAppNotifications());
                    }
                    */

                    await Promise.all(promises);

                } catch (error) {

                    const message = error?.message || 'Something went wrong while setting up';
                    this.notificationState.showWarningNotification(message);
                    console.error('Failed to setup:', error);

                } finally {

                    this.isSubmitting = false;
                    this.originalAdditionalFeatures = cloneDeep(this.additionalFeatures);

                    this.navigateToShare();

                }
            },
            async updateStore() {

                try {

                    let data = {
                        offer_rewards: this.additionalFeatures.rewards.active,
                    };

                    if(this.additionalFeatures.ussd.call_to_action?.trim() != '') {
                        data.call_to_action = this.additionalFeatures.ussd.call_to_action;
                    }

                    if(this.additionalFeatures.ussd.mobile_number?.trim() != '') {
                        data.ussd_mobile_number = this.additionalFeatures.ussd.mobile_number;
                    }

                    if(this.additionalFeatures.rewards.percentage_rate?.trim() != '') {
                        data.reward_percentage_rate = this.additionalFeatures.rewards.percentage_rate;
                    }

                    await axios.put(`/api/stores/${this.store.id}`, data);

                    this.storeState.setStore({
                        ...this.store,
                        ...data
                    });

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update store changes:', error);
                }

            },
            async addDeliveryOption() {
                try {

                    let data = {
                        return: true,
                        active: true,
                        charge_fee: true,
                        fee_type: 'flat',
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
                }
            },
            async updateDeliveryOption() {
                try {

                    let data = {
                        return: true,
                        charge_fee: true,
                        fee_type: 'flat',
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
                }
            },
            async addAutomatedWhatsAppNotifications() {
                try {

                    let data = {
                        return: true,
                        active: true,
                        resource: 'order',
                        trigger: 'waiting',
                        store_id: this.store.id,
                        name: this.additionalFeatures.automated_whatsapp_notifications.name
                    };

                    let response = await axios.post(`/api/workflows`, data);

                    this.workflow = response.data.workflow;

                    data = {
                        return: true,
                        settings: {
                            recipient: 'team',
                            action: 'whatsapp',
                            mobile_numbers: this.additionalFeatures.automated_whatsapp_notifications.mobile_numbers
                        },
                        workflow_id: this.workflow.id
                    };

                    response = await axios.post(`/api/workflow-steps`, data);

                    this.workflowStep = response.data.workflow_step;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating workflow';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create workflow:', error);
                }
            },
            async updateAutomatedWhatsAppNotifications() {

                try {

                    let data = {
                        return: true,
                        resource: 'order',
                        trigger: 'waiting',
                        store_id: this.store.id,
                        name: this.additionalFeatures.automated_whatsapp_notifications.name,
                        active: this.additionalFeatures.automated_whatsapp_notifications.active
                    };

                    const response = await axios.put(`/api/workflows/${this.workflow.id}`, data);

                    this.workflow = response.data.workflow;

                } catch (errorException) {

                    this.formState.setServerFormErrors(errorException);

                }
            },
            async showWorkflows() {

                try {

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: 'workflowSteps'
                        }
                    };

                    const response = await axios.get('/api/workflows', config);

                    this.workflows = response.data.data;
                    this.workflow = this.workflows.find((workflow) => workflow.name == this.additionalFeatures.automated_whatsapp_notifications.name);

                    if(this.workflow) {
                        this.additionalFeatures.automated_whatsapp_notifications.active = this.workflow.active;
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching workflows';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch workflows:', error);
                }

            },
            async showDeliveryMethods() {

                try {

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get('/api/delivery-methods', config);

                    this.deliveryMethods = response.data.data;
                    this.delivery = this.deliveryMethods.find((deliveryMethod) => deliveryMethod.name == this.additionalFeatures.delivery.name);
                    this.selfPickUpDelivery = this.deliveryMethods.find((deliveryMethod) => deliveryMethod.name == this.additionalFeatures.self_pick_up_delivery.name);

                    if(this.delivery) {
                        this.additionalFeatures.delivery.active = this.delivery.active;
                        this.additionalFeatures.delivery.flat_fee_rate = this.delivery.flat_fee_rate.amount_without_currency;
                    }

                    if(this.selfPickUpDelivery) {
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
