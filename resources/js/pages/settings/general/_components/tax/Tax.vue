<template>

    <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

        <h1 class="flex items-center font-bold mb-4">
            <CircleDollarSign size="20" class="mr-2 shrink-0"></CircleDollarSign>
            <span>Tax</span>
        </h1>

        <p class="text-sm text-gray-500 mb-4">Set your store's tax rate and method (Inclusive/Exclusive) to ensure accurate pricing and compliance with local tax regulations.</p>

        <div class="grid grid-cols-2 gap-4">

            <div class="col-span-1">

                <!-- Select Tax Method Unit -->
                <Select
                    class="w-full"
                    :search="false"
                    label="Tax Method"
                    :options="taxOptions"
                    v-model="storeForm.tax_method"
                    :errorText="formState.getFormError('tax_method')"
                    @change="storeState.saveStateDebounced('Tax method changed')"
                    tooltipContent="Choose whether taxes are included in your prices (inclusive) or added on top of prices (exclusive).">
                </Select>

            </div>

                <div class="col-span-1">

                    <!-- Tax ID Input -->
                    <Input
                        type="text"
                        label="Tax ID"
                        placeholder="12-3456789"
                        v-model="storeForm.tax_id"
                        :skeleton="isLoadingStore || !store"
                        :errorText="formState.getFormError('tax_id')"
                        @change="storeState.saveStateDebounced('Tax ID changed')"
                        tooltipContent="Provide your tax identification number (e.g. EIN for US, VAT for UK).">
                    </Input>

                </div>

                <div class="col-span-2">

                    <!-- Percentage Rate Input -->
                    <Input
                        placeholder="10"
                        type="percentage"
                        label="Tax Rate (%)"
                        :skeleton="isLoadingStore || !store"
                        v-model="storeForm.tax_percentage_rate"
                        :errorText="formState.getFormError('tax_percentage_rate')"
                        @change="storeState.saveStateDebounced('Tax percentage rate changed')"
                        tooltipContent="Provide your tax identification number (e.g. EIN for US, VAT for UK).">
                    </Input>

                </div>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Select from '@Partials/Select.vue';
    import { CircleDollarSign } from 'lucide-vue-next';
    import SelectCountry from '@Partials/SelectCountry.vue';
    import SelectCurrency from '@Partials/SelectCurrency.vue';
    import SelectLanguage from '@Partials/SelectLanguage.vue';

    export default {
        inject: ['formState', 'storeState'],
        components: {
            CircleDollarSign, Input, Select, SelectCountry, SelectCurrency, SelectLanguage
        },
        data() {
            return {
                taxOptions: [
                    {
                        label: 'Inclusive (Prices include tax)',
                        value: 'inclusive'
                    },
                    {
                        label: 'Exclusive (Prices exclude tax)',
                        value: 'exclusive'
                    }
                ]
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            storeForm() {
                return this.storeState.storeForm;
            }
        }
    };

</script>
