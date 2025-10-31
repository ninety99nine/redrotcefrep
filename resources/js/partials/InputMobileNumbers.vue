<template>

    <Modal
        :onShow="onShow"
        :showFooter="false"
        :scrollOnContent="true"
        :header="mobileNumber ? 'Edit Mobile Number' : 'Add Mobile Number'">

        <template #content="triggerProps">

            <div>

                <!-- Mobile Number Input -->
                <Input
                    type="text"
                    :key="InputKey"
                    label="Mobile Number"
                    placeholder="+26772000001"
                    v-model="form.mobile_number"
                    :errorText="formState.getFormError('mobile_number')">

                </Input>

                <div class="flex space-x-2 mt-4">

                    <!-- Delete Button -->
                    <Button
                        size="sm"
                        type="danger"
                        v-if="mobileNumber"
                        :disabled="isSubmitting"
                        :action="() => _deleteMobileNumber(triggerProps.hideModal)">
                        <span>Delete</span>
                    </Button>

                    <!-- Save Changes / Add Mobile Number Button -->
                    <Button
                        size="sm"
                        type="success"
                        class="w-full"
                        buttonClass="w-full"
                        :disabled="!mustSaveChanges"
                        :action="() => mobileNumber ? _updateMobileNumber(triggerProps.hideModal) : _createMobileNumber(triggerProps.hideModal)">
                        <span>{{ mobileNumber ? 'Save Changes' : 'Add Mobile Number' }}</span>
                    </Button>

                </div>

            </div>

        </template>

        <template #trigger="triggerProps">

            <!-- Edit Mobile Number / Add Mobile Number Button - Triggers Modal -->
            <div :class="bodyClass">

                <!-- Content -->
                <slot name="content"></slot>

                <template v-if="hasMobileNumbers">

                    <div class="flex flex-wrap gap-2">

                        <!-- Prefix content -->
                        <slot name="prefix"></slot>

                        <Pill
                            size="xs"
                            :key="index"
                            type="primary"
                            :showDot="false"
                            v-for="(nationalMobileNumber, index) in nationalMobileNumbers"
                            :action="() => showMobileNumber(triggerProps.showModal, modelValue[index], index)">

                            <div class="flex items-center">
                                <span class="mx-2">{{ nationalMobileNumber }}</span>
                                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </div>

                        </Pill>

                        <Button
                            size="sm"
                            icon="add"
                            :leftIcon="Plus"
                            leftIconSize="16"
                            buttonClass="w-48"
                            rounded="rounded-full"
                            :action="() => showMobileNumber(triggerProps.showModal, null, null)">
                            <span class="text-xs">Add Mobile Number</span>
                        </Button>

                    </div>

                </template>

                <Button
                    v-else
                    size="xs"
                    type="light"
                    :leftIcon="Plus"
                    :action="() => showMobileNumber(triggerProps.showModal, null, null)" buttonClass="w-48">
                    <span class="whitespace-nowrap">Add Mobile Number</span>
                </Button>

            </div>

        </template>

    </Modal>

</template>


<script>

    import isEqual from 'lodash.isequal';
    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import cloneDeep from 'lodash.cloneDeep';
    import Button from '@Partials/Button.vue';
    import { Plus } from 'lucide-vue-next';
    import { parsePhoneNumberFromString } from 'libphonenumber-js';

    export default {
        inject: ['formState', 'notificationState'],
        components: { Pill, Input, Modal, Button  },
        props: {
            modelValue: {
                type: [String, Array]
            },
            isSubmitting: {
                type: Boolean,
                default: false
            },
            createMobileNumber: {
                type: Function,
                default: null
            },
            updateMobileNumber: {
                type: Function,
                default: null
            },
            deleteMobileNumber: {
                type: Function,
                default: null
            },
            bodyClass: {
                type: String,
                default: null
            },
        },
        data() {
            return {
                Plus,
                form: {
                    mobile_number: ''
                },
                InputKey: 0,
                originalForm: null,
                mobileNumber: null,
                mobileNumberIndex: null,
            };
        },
        computed: {
            hasMobileNumbers() {
                return this.modelValue.length > 0;
            },
            nationalMobileNumbers() {
                return this.modelValue.map((mobileNumber) => {

                    const phoneNumber = parsePhoneNumberFromString(mobileNumber) || null;
                    if(phoneNumber && phoneNumber.isValid()) {
                        let nationalNumber = phoneNumber.formatNational();
                        return nationalNumber.replace(/\s+/g, '');
                    }

                    return null;

                }).filter(mobileNumber => mobileNumber != null);
            },
            formHasChanged() {
                return !isEqual(this.form, this.originalForm);
            },
            mustSaveChanges() {
                return this.formHasChanged && !this.isSubmitting;
            },
        },
        methods: {
            onShow() {
                ++this.InputKey;
            },
            showMobileNumber(showModal, mobileNumber, mobileNumberIndex) {
                this.mobileNumberIndex = mobileNumberIndex;
                this.mobileNumber = mobileNumber;

                if(this.mobileNumber) {
                    this.setForm();
                }else{
                    this.resetForm();
                }

                showModal();

            },
            setForm() {
                this.form.mobile_number = this.mobileNumber;
                this.originalForm = cloneDeep(this.form);
            },
            resetForm() {
                this.form.mobile_number = '';
                this.originalForm = cloneDeep(this.form);
            },
            _createMobileNumber(hideModal) {

                const phoneNumber = parsePhoneNumberFromString(this.form.mobile_number) || null;
                if(!phoneNumber || !phoneNumber.isValid()) {
                    this.formState.setFormError('mobile_number', 'This is not a valid mobile number');
                    return;
                }

                // Check if the mobile number already exists
                const mobileNumberExists = this.modelValue.some(
                    (number) => number === this.form.mobile_number
                );

                // If it exists, prevent adding and possibly show a warning
                if(mobileNumberExists) {
                    this.notificationState.showWarningNotification('Mobile number already exists');
                    hideModal();
                    return;
                }

                if(this.createMobileNumber) {
                    this.createMobileNumber(
                        this.form.mobile_number, this.resetForm, hideModal
                    );
                }else{
                    this.modelValue.push(this.form.mobile_number);
                    this.$emit('change', this.modelValue);
                    this.resetForm();
                    hideModal();
                }

            },
            _updateMobileNumber(hideModal) {

                const phoneNumber = parsePhoneNumberFromString(this.form.mobile_number) || null;
                if(!phoneNumber || !phoneNumber.isValid()) {
                    this.formState.setFormError('mobile_number', 'This is not a valid mobile number');
                    return;
                }

                // Check if the mobile number already exists
                const mobileNumberExists = this.modelValue.some(
                    (number) => number === this.form.mobile_number
                );

                // If it exists, prevent adding and possibly show a warning
                if(mobileNumberExists) {
                    this.notificationState.showWarningNotification('Mobile number already exists');
                    hideModal();
                    return;
                }

                if(this.updateMobileNumber) {
                    this.updateMobileNumber(
                        this.form.mobile_number, this.mobileNumberIndex, this.resetForm, hideModal
                    );
                }else{
                    this.modelValue.splice(this.mobileNumberIndex, 1, this.form.mobile_number);
                    this.$emit('change', this.modelValue);
                    this.resetForm();
                    hideModal();
                }

            },
            _deleteMobileNumber(hideModal) {

                if(this.deleteMobileNumber) {
                    this.deleteMobileNumber(
                        this.mobileNumberIndex, this.resetForm, hideModal
                    );
                }else{
                    this.modelValue.splice(this.mobileNumberIndex, 1);
                    this.$emit('change', this.modelValue);
                    this.resetForm();
                    hideModal();
                }

            },
        }
    };

</script>
