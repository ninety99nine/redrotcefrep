<template>

    <Modal
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false"
        :targetClass="targetClass">

        <template #content>

            <div class="grid grid-cols-2 gap-2">

                <h1 class="font-lg font-bold my-2">{{ localAddress ? 'Edit Address' : 'Add Address' }}</h1>

                <template v-if="isLoadingCountryAddressOptions">

                </template>

                <template v-else-if="step == 1">

                    <div class="col-span-2 space-y-2">

                        <!-- Address Line Input -->
                        <Input
                            type="text"
                            v-model="form.address_line"
                            placeholder="Street address"
                            :errorText="formState.getFormError('address_line')">
                        </Input>

                        <!-- Address Line 2 Input -->
                        <Input
                            type="text"
                            v-model="form.address_line2"
                            :errorText="formState.getFormError('address_line2')"
                            placeholder="Apartment, unit number, suite, etc. (optional)">
                        </Input>

                    </div>

                    <!-- City Input -->
                    <div v-if="countryAddressOption.city.required" :class="cityWrapperClass">
                        <Input
                            type="text"
                            v-model="form.city"
                            :errorText="formState.getFormError('city')"
                            :placeholder="countryAddressOption.city.label">
                        </Input>
                    </div>

                    <!-- State Input -->
                    <div v-if="countryAddressOption.state.required" class="col-span-1">
                        <Input
                            type="text"
                            v-model="form.state"
                            :errorText="formState.getFormError('state')"
                            :placeholder="countryAddressOption.state.label">
                        </Input>
                    </div>

                    <!-- Postal Code Input -->
                    <div v-if="countryAddressOption.postal_code.required" class="col-span-1">
                        <Input
                            type="text"
                            v-model="form.postal_code"
                            :errorText="formState.getFormError('postal_code')"
                            :placeholder="countryAddressOption.postal_code.label">
                        </Input>
                    </div>

                    <!-- Country Select -->
                    <div :class="countryWrapperClass">
                        <SelectCountry
                            class="w-full"
                            v-model="form.country">
                        </SelectCountry>
                    </div>

                </template>

                <template v-else-if="step == 2">

                    <div class="col-span-2">

                        <!-- Google Maps -->
                        <GoogleMaps
                            height="350px"
                            @markerMoved="markerMoved"
                            @addedMarker="addedMarker"
                            :address="googleMapsAddress"
                            :placeId="mustSaveChanges ? null : form.place_id"
                            :latitude="mustSaveChanges ? null : form.latitude"
                            :longitude="mustSaveChanges ? null : form.longitude">
                        </GoogleMaps>

                    </div>

                </template>

                <div class="col-span-2 flex space-x-2 mt-4">

                    <Button
                        size="sm"
                        type="danger"
                        :action="deleteAddress"
                        :disabled="isSubmitting"
                        v-if="step == 1 && localAddress">
                        <span>Delete</span>
                    </Button>

                    <template v-if="step == 2">

                        <Button
                            size="sm"
                            type="light"
                            class="w-24"
                            :leftIcon="MoveLeft"
                            :action="() => step = 1">
                            <span>Back</span>
                        </Button>

                    </template>

                    <Button
                        size="sm"
                        type="primary"
                        :action="submit"
                        buttonClass="w-full"
                        wrapperClass="w-full"
                        :disabled="!pinLocationOnMap && !mustSaveChanges">
                        <span>{{ submitText }}</span>
                    </Button>

                </div>

            </div>

        </template>

        <template #trigger>

            <!-- Change Address / Add Address Button - Triggers Modal -->
            <div :class="triggerClass">

                <h1 v-if="title" class="flex items-center font-lg font-bold">
                    <svg class="w-6 h-6 mr-2 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    <span>{{ title }}</span>
                </h1>

                <p v-if="subtitle" class="text-sm text-gray-500 mb-2 pb-4 border-b border-dashed border-gray-300">{{ subtitle }}</p>

                <template v-if="editable">

                    <div v-if="localAddress" class="space-y-2 mb-4">

                        <p class="text-sm">{{ localAddress.complete_address }}</p>

                        <div class="flex justify-end items-center space-x-2">

                            <Button
                                size="xs"
                                type="light"
                                :action="showModal"
                                :leftIcon="RefreshCcw">
                                <span class="whitespace-nowrap ml-1">Change</span>
                            </Button>

                            <Button
                                size="xs"
                                :leftIcon="Trash2"
                                type="outlineDanger"
                                :action="deleteAddress"
                                :disabled="isSubmitting">
                            </Button>

                        </div>

                    </div>

                    <Button
                        v-else
                        size="xs"
                        type="light"
                        :leftIcon="Plus"
                        :action="showModal"
                        buttonClass="w-full">
                        <span>{{ triggerText }}</span>
                    </Button>

                </template>

                <!-- Google Maps -->
                <GoogleMaps
                    :height="height"
                    :gmpDraggable="false"
                    :latitude="previewLatitude"
                    :longitude="previewLongitude"
                    v-if="previewLatitude && previewLongitude">
                </GoogleMaps>

            </div>

        </template>

    </Modal>

</template>


<script>

    import isEqual from 'lodash/isEqual';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import cloneDeep from 'lodash/cloneDeep';
    import Button from '@Partials/Button.vue';
    import { isNotEmpty } from '@Utils/stringUtils';
    import GoogleMaps from '@Partials/GoogleMaps.vue';
    import SelectCountry from '@Partials/SelectCountry.vue';
    import { Plus, Trash2, MoveLeft, RefreshCcw } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'notificationState'],
        components: {
            Input, Modal, Button, GoogleMaps, SelectCountry
        },
        props: {
            address: {
                type: [Object, null],
                default: null
            },
            ownerId: {
                type: [String, null],
                default: null
            },
            ownerType: {
                type: [String, null],
                default: null
            },
            title: {
                type: String,
                default: null
            },
            subtitle: {
                type: String,
                default: null
            },
            pinLocationOnMap: {
                type: Boolean,
                default: true
            },
            onlyValidate: {
                type: Boolean,
                default: false
            },
            editable: {
                type: Boolean,
                default: true
            },
            height: {
                type: String,
                default: '350px'
            },
            triggerClass: {
                type: String,
                default: 'space-y-4 p-4 border border-gray-300 rounded-lg shadow-lg bg-white'
            },
            triggerText: {
                type: String,
                default: 'Add Address'
            },
            targetClass: {
                type: String,
                default: 'body'
            },
        },
        emits: ['change', 'onValidated', 'onCreated', 'onUpdated', 'onDeleted'],
        data() {
            return {
                Plus,
                Trash2,
                step: 1,
                MoveLeft,
                RefreshCcw,
                form: {
                    city: null,
                    state: null,
                    country: 'BW',
                    place_id: null,
                    latitude: null,
                    longitude: null,
                    postal_code: null,
                    address_line: null,
                    address_line2: null,
                    owner_id: this.ownerId,
                    owner_type: this.ownerType
                },
                originalForm: null,
                localAddress: null,
                isSubmitting: false,
                previewLatitude: null,
                previewLongitude: null,
                countryAddressOptions: [],
                isLoadingCountryAddressOptions: false
            };
        },
        watch: {
            'address'(newValue) {
                this.setFields(newValue);
                this.localAddress = newValue;
            }
        },
        computed: {
            submitText() {
                if(this.pinLocationOnMap && this.step == 1) {
                    return 'Next';
                }else if(this.localAddress) {
                    return 'Save Address';
                }else{
                    return 'Add Address';
                }
            },
            cityWrapperClass() {
                const stateRequired = this.countryAddressOption.state.required;
                const postalCodeRequired = this.countryAddressOption.postal_code.required;

                return (stateRequired && postalCodeRequired) || (!stateRequired && !postalCodeRequired) ? 'col-span-1' : 'col-span-2';
            },
            countryWrapperClass() {
                const cityRequired = this.countryAddressOption.city.required;
                const stateRequired = this.countryAddressOption.state.required;
                const postalCodeRequired = this.countryAddressOption.postal_code.required;

                return cityRequired || stateRequired || postalCodeRequired ? 'col-span-1' : 'col-span-2';
            },
            hasCountryAddressOption() {
                return Object.keys(this.countryAddressOptions).length > 0;
            },
            countryAddressOption() {
                return this.hasCountryAddressOption ? this.countryAddressOptions[this.form.country] : null;
            },
            googleMapsAddress() {
                var googleMapsAddress = this.form.address_line;

                if(this.isNotEmpty(this.form.address_line2)) googleMapsAddress += (', '+this.form.address_line2);
                if(this.isNotEmpty(this.form.city)) googleMapsAddress += (', '+this.form.city);
                if(this.isNotEmpty(this.form.state)) googleMapsAddress += (', '+this.form.state);
                if(this.isNotEmpty(this.form.postal_code)) googleMapsAddress += (', '+this.form.postal_code);
                if(this.isNotEmpty(this.form.country)) googleMapsAddress += (', '+this.form.country);

                return googleMapsAddress;
            },
            hasAddress() {
                return this.address?.id != null;
            },
            formHasChanged() {
                // Clone the objects to avoid modifying the original data
                var a = cloneDeep(this.form);
                var b = cloneDeep(this.originalForm);

                // Compare the modified arrays for equality
                return !isEqual(a, b);
            },
            mustSaveChanges() {
                return this.formHasChanged && !this.isSubmitting;
            },
        },
        methods: {
            isNotEmpty,
            showModal() {
                this.step = 1;
                this.copyOriginalForm();
                this.$refs.modal.showModal();
            },
            hideModal() {
                this.$refs.modal.hideModal();
            },
            markerMoved(location) {
                this.form.place_id = null;
                this.form.latitude = location.latitude;
                this.form.longitude = location.longitude;
            },
            addedMarker(location) {
                this.form.place_id = location.place_id;
                this.form.latitude = location.latitude;
                this.form.longitude = location.longitude;
            },
            setFields(address) {
                if(address) {
                    this.form.city = address.city;
                    this.form.state = address.state;
                    this.form.country = address.country;
                    this.form.place_id = address.place_id;
                    this.form.owner_id = address.owner_id;
                    this.form.owner_type = address.owner_type;
                    this.form.postal_code = address.postal_code;
                    this.form.address_line = address.address_line;
                    this.form.address_line2 = address.address_line2;
                    this.form.latitude = address.latitude ? parseFloat(address.latitude) : null;
                    this.form.longitude = address.longitude ? parseFloat(address.longitude) : null;

                    this.previewLatitude = this.form.latitude;
                    this.previewLongitude = this.form.longitude;
                }else{
                    this.form.city = null;
                    this.form.state = null;
                    this.form.country = 'BW';
                    this.form.place_id = null;
                    this.form.latitude = null;
                    this.form.longitude = null;
                    this.form.postal_code = null;
                    this.form.address_line = null;
                    this.form.address_line2 = null;

                    this.previewLatitude = null;
                    this.previewLongitude = null;
                }

                //  Capture the original form before editting.
                this.originalForm = cloneDeep(this.form);

            },
            copyOriginalForm() {
                this.form = cloneDeep(this.originalForm);
            },
            submit() {
                if(this.pinLocationOnMap && this.step == 1) {
                    this.step = 2;
                }else if(this.onlyValidate) {
                    this.validateAddress();
                }else if(this.hasAddress) {
                    this.updateAddress();
                }else{
                    this.createAddress();
                }
            },
            async showCountryAddressOptions() {
                try {

                    this.isLoadingCountryAddressOptions = true;

                    const response = await axios.get(`/api/addresses/country/options`);
                    this.countryAddressOptions = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching country address options';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch country address options:', error);
                } finally {
                    this.isLoadingCountryAddressOptions = false;
                }
            },
            async validateAddress() {

                try {

                    this.isSubmitting = true;

                    const data = {
                        ...this.form
                    };

                    const response = await axios.post(`/api/addresses/validate`, data);

                    this.localAddress = response.data;
                    this.setFields(this.localAddress);

                    let emittableAddress = cloneDeep(this.localAddress);
                    this.$emit('onValidated', emittableAddress);
                    this.$emit('change', emittableAddress);

                    this.hideModal();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while validating address';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to validate address:', error);
                } finally {
                    this.isSubmitting = false;
                }

            },
            async createAddress() {

                try {

                    this.isSubmitting = true;

                    const data = {
                        return: '1',
                        ...this.form
                    };

                    const response = await axios.post(`/api/addresses`, data);

                    this.localAddress = response.data;
                    this.setFields(this.localAddress);

                    let emittableAddress = cloneDeep(this.localAddress);
                    this.$emit('onCreated', emittableAddress);
                    this.$emit('change', emittableAddress);

                    this.notificationState.showSuccessNotification('Address created');
                    this.hideModal();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating address';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create address:', error);
                } finally {
                    this.isSubmitting = false;
                }

            },
            async updateAddress() {

                try {

                    this.isSubmitting = true;

                    const data = {
                        return: '1',
                        ...this.form
                    };

                    const response = await axios.put(`/api/addresses/${this.localAddress.id}`, data);

                    this.localAddress = response.data;
                    this.setFields(this.localAddress);

                    let emittableAddress = cloneDeep(this.localAddress);
                    this.$emit('onUpdated', emittableAddress);
                    this.$emit('change', emittableAddress);

                    this.notificationState.showSuccessNotification('Address updated');
                    this.hideModal();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating address';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update address:', error);
                } finally {
                    this.isSubmitting = false;
                }

            },
            async deleteAddress() {

                try {

                    if(!this.hasAddress) {
                        this.$emit('onDeleted');
                        this.$emit('change');

                        this.localAddress = null;
                        this.setFields(null);
                        this.hideModal();
                        return;
                    }

                    this.isSubmitting = true;

                    const response = await axios.delete(`/api/addresses/${this.localAddress.id}`);

                    this.setFields(null);
                    this.localAddress = null;

                    this.$emit('onDeleted');
                    this.$emit('change');

                    this.notificationState.showSuccessNotification('Address deleted');
                    this.hideModal();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting address';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete address:', error);
                } finally {
                    this.isSubmitting = false;
                }

            },
        },
        created() {
            this.setFields(this.address);
            this.showCountryAddressOptions();
            this.localAddress = this.address;
        }
    };
</script>
