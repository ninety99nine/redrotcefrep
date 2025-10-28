<template>

    <div class="flex flex-col items-center justify-center pb-40">

        <div class="w-full max-w-lg">

            <!-- Heading -->
            <h2 class="text-2xl font-semibold text-center">Start Your Store</h2>

            <!-- Instruction -->
            <p class="text-gray-500 text-center mb-6">Tell us a little about your store to get started</p>

        </div>

        <!-- Card -->
        <div class="w-full max-w-lg bg-white p-8 rounded-2xl shadow-lg">

            <!-- Store Logo -->
            <StoreLogo @selectedFile="handleSelectedFile" :uploading="logo && isCreatingStore" class="flex justify-center mb-4"></StoreLogo>

            <!-- Store Form -->
            <div class="space-y-4 mb-4">

                <!-- Name Input -->
                <Input
                    type="text"
                    label="Name"
                    v-model="form.name"
                    :showAsterisk="true"
                    @keyup="syncWithAlias"
                    placeholder="Baby Cakes 🧁"
                    autocomplete="organization"
                    :errorText="formState.getFormError('name')" />

                <!-- Whatsapp Number Input -->
                <Input
                    type="text"
                    label="WhatsApp Number"
                    placeholder="+26772000001"
                    v-model="form.whatsapp_mobile_number"
                    :errorText="formState.getFormError('whatsapp_mobile_number')"
                    description="Customers can send orders to this number after shopping">
                    <template #prefix>
                        <WhatsappIcon class="mx-2"></WhatsappIcon>
                    </template>
                </Input>

                <!-- Website Link Input -->
                <Input
                    type="text"
                    label="Store Link"
                    v-model="form.alias"
                    placeholder="baby-cakes"
                    @keyup="() => formatAlias(true)"
                    :errorText="formState.getFormError('alias')"
                    description="Your store’s shopping link. Custom domains can be set later.">
                    <template #prefix>
                        <div class="flex items-center space-x-1 pl-2 rounded-l-md bg-gray-50 text-gray-500 whitespace-nowrap">
                            <Earth size="18" class="mt-0.5"></Earth>
                            <span class="text-sm">perfectorder.shop</span>
                            <span class="text-gray-400 text-sm">/</span>
                        </div>
                    </template>
                </Input>

                <!-- Select Country Input -->
                <SelectCountry
                    class="w-full"
                    label="Country"
                    v-model="form.country"
                    labelPopoverTitle="What Is This?"
                    labelPopoverDescription="Your store’s country of operation">
                </SelectCountry>

                <!-- Select Currency Input -->
                <SelectCurrency
                    class="w-full"
                    label="Currency"
                    v-model="form.currency"
                    labelPopoverTitle="What Is This?"
                    labelPopoverDescription="Your store’s currency">
                </SelectCurrency>

            </div>

            <!-- Create Store Button -->
            <Button
                size="lg"
                type="primary"
                buttonClass="w-full"
                :action="createStore"
                :loading="isCreatingStore"
                :disabled="isCreatingStore">
                <span>Create Store</span>
            </Button>

        </div>

    </div>

  </template>

<script>

    import Input from '@Partials/Input.vue';
    import { Earth } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
    import WhatsappIcon from '@Partials/WhatsappIcon.vue';
    import SelectCountry from '@Partials/SelectCountry.vue';
    import { isEmpty, isNotEmpty } from '@Utils/stringUtils';
    import SelectCurrency from '@Partials/SelectCurrency.vue';

    export default {
        inject: ['authState', 'storeState', 'formState', 'notificationState'],
        components: {
            Earth, Input, Button, StoreLogo, WhatsappIcon, SelectCountry, SelectCurrency
        },
        data() {
            return {
                form: {
                    name: '',
                    alias: '',
                    country: 'BW',
                    currency: 'BWP',
                    whatsapp_mobile_number: ''
                },
                logo: null,
                aliasModified: false,
                isCreatingStore: false
            }
        },
        methods: {
            isEmpty,
            isNotEmpty,
            syncWithAlias() {
                if(this.aliasModified == false) {
                    this.form.alias = this.form.name;
                    this.formatAlias();
                }
            },
            formatAlias(aliasModified = false) {
                if(this.form.alias.length) {
                    this.form.alias = this.form.alias.replace(/[^a-zA-Z0-9\s-]/g, '').replace(/\s+/g, '-').trim().toLowerCase();
                    this.aliasModified = aliasModified;
                }else{
                    this.aliasModified = false;
                }
            },
            handleSelectedFile(file) {
                this.logo = file;
            },
            async createStore() {

                try {

                    if(this.isCreatingStore) return;

                    this.formState.hideFormErrors();

                    if(this.isEmpty(this.form.name)) {
                        this.formState.setFormError('name', 'Enter store name');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.isCreatingStore = true;

                    let formData = new FormData();
                    formData.append('return', 1);
                    formData.append('name', this.form.name);
                    formData.append('country', this.form.country);
                    formData.append('currency', this.form.currency);
                    if (this.isNotEmpty(this.form.alias)) formData.append('alias', this.form.alias.trim());
                    if (this.isNotEmpty(this.form.whatsapp_mobile_number)) formData.append('whatsapp_mobile_number', this.form.whatsapp_mobile_number.trim());

                    // Attach store logo if available
                    if (this.logo) {
                        formData.append('logo', this.logo);
                    }

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    const response = await axios.post('/api/stores', formData, config);

                    this.notificationState.showSuccessNotification('Store created!');

                    this.$router.push({
                        name: 'add-products',
                        params: { 'store_id': response.data.store.id }
                    });

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create store:', error);
                } finally {
                    this.isCreatingStore = false;
                }
            }
        },
        created() {
            this.storeState.setStore(null);
            if(this.authState.user.mobile_number) {
                this.form.whatsapp_mobile_number = this.authState.user.mobile_number.international;
            }
        }
    };

</script>
