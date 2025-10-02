<template>

    <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

        <h1 class="flex items-center font-bold mb-4">
            <Earth size="20" class="mr-2 flex-shrink-0"></Earth>
            <span>Localization</span>
        </h1>

        <p class="text-sm text-gray-500 mb-4">Set your store's country, currency, language, and preferred distance unit (Km/Mile) to align with your local market and ensure a seamless shopping experience for your customers.</p>


        <div class="grid grid-cols-2 gap-4">

            <!-- Select Country -->
            <SelectCountry
                class="w-full"
                label="Country"
                v-model="storeForm.country"
                :skeleton="isLoadingStore || !store"
                :errorText="formState.getFormError('country')"
                tooltipContent="Your store’s country of operation"
                @change="storeState.saveStateDebounced('Store country changed')">
            </SelectCountry>

            <!-- Select Currency -->
            <SelectCurrency
                class="w-full"
                label="Currency"
                v-model="storeForm.currency"
                :skeleton="isLoadingStore || !store"
                :errorText="formState.getFormError('currency')"
                tooltipContent="The currency accepted by your store"
                @change="storeState.saveStateDebounced('Store currency changed')">
            </SelectCurrency>

            <!-- Select Language -->
            <SelectLanguage
                class="w-full"
                label="Language"
                v-model="storeForm.language"
                :skeleton="isLoadingStore || !store"
                :errorText="formState.getFormError('language')"
                tooltipContent="The language accepted by your store"
                @change="storeState.saveStateDebounced('Store language changed')">
            </SelectLanguage>

            <!-- Select Distance Unit -->
            <Select
                class="w-full"
                :search="false"
                label="Distance Unit"
                :options="distanceOptions"
                v-model="storeForm.distance_unit"
                :errorText="formState.getFormError('distance_unit')"
                @change="storeState.saveStateDebounced('Distance unit changed')"
                tooltipContent="The unit of measurement for distances used in your store. For example, kilometers (km) for metric systems or miles for imperial systems. This setting affects shipping calculations and location-based features.">
            </Select>

            <!-- Select Weight Unit -->
            <Select
                class="w-full"
                :search="false"
                label="Weight Unit"
                :options="weightOptions"
                v-model="storeForm.weight_unit"
                :errorText="formState.getFormError('weight_unit')"
                @change="storeState.saveStateDebounced('Weight unit changed')"
                tooltipContent="The unit of measurement for weights used in your store. This setting is used for product weights and shipping calculations. Common units include kilograms (kg) for metric systems and pounds (lb) for imperial systems.">
            </Select>

        </div>

    </div>

</template>

<script>

    import { Earth } from 'lucide-vue-next';
    import Input from '@Partials/Input.vue';
    import Select from '@Partials/Select.vue';
    import SelectCountry from '@Partials/SelectCountry.vue';
    import SelectCurrency from '@Partials/SelectCurrency.vue';
    import SelectLanguage from '@Partials/SelectLanguage.vue';

    export default {
        inject: ['formState', 'storeState'],
        components: {
            Earth, Input, Select, SelectCountry, SelectCurrency, SelectLanguage
        },
        data() {
            return {
                distanceOptions: [
                    {
                        label: 'Kilometer (km)',
                        value: 'km'
                    },
                    {
                        label: 'Mile',
                        value: 'mile'
                    }
                ],
                weightOptions: [
                    {
                        label: 'Kilogram (kg)',
                        value: 'kg'
                    },
                    {
                        label: 'Gram',
                        value: 'g'
                    },
                    {
                        label: 'Pound (lb)',
                        value: 'lb'
                    },
                    {
                        label: 'Ounce (oz)',
                        value: 'oz'
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
