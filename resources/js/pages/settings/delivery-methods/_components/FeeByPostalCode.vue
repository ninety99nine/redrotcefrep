<template>

    <div class="w-full space-y-4 select-none">

        <h1 class="text-md font-bold m-2">Zones</h1>

        <template v-if="isLoadingStore || isLoadingDeliveryMethod">

            <div class="bg-gray-50 p-4 border border-gray-300 rounded-lg">

                <div class="flex justify-between items-center">

                    <div class="flex items-center space-x-4">

                        <Skeleton width="w-4" height="h-4" rounded="rounded-md" :shine="true"></Skeleton>

                        <Skeleton width="w-32" :shine="true"></Skeleton>

                    </div>

                    <div class="flex items-center space-x-2">

                        <Skeleton width="w-4" height="h-4" rounded="rounded-md" :shine="true"></Skeleton>

                        <Skeleton width="w-4" height="h-4" rounded="rounded-md" :shine="true"></Skeleton>

                    </div>

                </div>

            </div>

        </template>

        <template v-else>

            <div
                :key="index"
                class="relative bg-gray-50 border border-gray-300 rounded-lg"
                v-for="(postalCodeZone, index) in deliveryMethodForm.postal_code_zones">

                <div class="absolute top-2 right-2 flex items-center space-x-2">

                    <Minimize v-if="expandedStates[index]" size="16" class="text-gray-500 cursor-pointer hover:opacity-50" @click.stop="toggleExpanded(index)"></Minimize>
                    <SquarePen v-else size="16" class="text-gray-500 cursor-pointer hover:opacity-50" @click.stop="toggleExpanded(index)"></SquarePen>

                    <Button
                        size="xs"
                        type="bareDanger"
                        :leftIcon="Trash2"
                        :action="() => onRemovePostalCodeZone(index)">
                    </Button>

                </div>

                <div v-if="expandedStates[index]" class="p-4 space-y-4">

                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <LocateFixed size="16" class="text-gray-500"></LocateFixed>
                        <span>Zone {{ index + 1 }}</span>
                    </div>

                    <div class="space-y-4">

                        <div class="flex space-x-4">

                            <!-- Area Name Input -->
                            <Input
                                type="text"
                                label="Area Name"
                                placeholder="Nairobi"
                                tooltipContent="Set the area name for this delivery zone"
                                v-model="deliveryMethodForm.postal_code_zones[index].name"
                                :errorText="formState.getFormError('postal_code_zones'+index+'name')">
                            </Input>

                            <!-- Zone Fee Input -->
                            <Input
                                type="money"
                                label="Delivery fee"
                                :currency="store?.currency"
                                v-model="deliveryMethodForm.postal_code_zones[index].fee"
                                :errorText="formState.getFormError('postal_code_zones'+index+'fee')"
                                tooltipContent="Specify the fee charged for deliveries within the area">
                            </Input>

                        </div>

                        <SelectTags
                            :showOptions="false"
                            label="Postal Codes"
                            placeholder="Add postal codes"
                            v-model="deliveryMethodForm.postal_code_zones[index].value"
                            :errorText="formState.getFormError('postal_code_zones'+index+'value')"
                            @change="deliveryMethodState.saveStateDebounced('Postal codes changed')"
                            tooltipContent="The postal codes supported by this area e.g 10001, 00100 or SW1A 1AA"
                            description="We support prefix based zone. For example, if you want to create a zone starting with 10, simply add 10. All postal code starting with 10 will be included in the zone." />

                    </div>

                </div>

                <div
                    v-else
                    @click="toggleExpanded(index)"
                    class="rounded-lg p-4 space-y-2 cursor-pointer hover:bg-gray-100">

                    <div class="flex items-center space-x-4 text-sm text-gray-500 mb-4">

                        <div class="flex items-center space-x-2">

                            <LocateFixed size="16" class="text-gray-500"></LocateFixed>
                            <span class="text-sm">Zone {{ index + 1 }}</span>

                        </div>

                        <Pill size="xs" type="primary" :showDot="false" v-if="deliveryMethodForm.postal_code_zones[index].name">{{ deliveryMethodForm.postal_code_zones[index].name }} → {{ convertToMoneyWithSymbol(deliveryMethodForm.postal_code_zones[index].fee, this.store.currency) }}</Pill>
                        <ErrorText v-else errorText="No name" margin="mt-0"></ErrorText>

                    </div>

                    <div
                        class="flex space-x-2"
                        v-if="deliveryMethodForm.postal_code_zones[index].value.length">

                        <span
                            :key="index"
                            class="py-0.5 px-2 bg-gray-100 border border-gray-300 text-gray-500 text-xs rounded-lg"
                            v-for="(postalCode, index) in deliveryMethodForm.postal_code_zones[index].value">
                            {{ postalCode }}
                        </span>

                    </div>
                    <ErrorText v-else errorText="No weight range" margin="mt-0"></ErrorText>

                    <ErrorText v-if="formState.getFormError('postal_code_zones'+index+'name')" :errorText="formState.getFormError('postal_code_zones'+index+'name')"></ErrorText>
                    <ErrorText v-if="formState.getFormError('postal_code_zones'+index+'fee')" :errorText="formState.getFormError('postal_code_zones'+index+'fee')"></ErrorText>

                </div>

            </div>

            <div v-if="!hasPostalCodeZones">
                <div class="flex items-center space-x-4 px-4 py-4 border border-gray-300 rounded-lg bg-gray-50">
                    <LocateFixed size="20" class="text-gray-500"></LocateFixed>
                    <div class="text-sm space-y-2">
                        <p><Pill type="primary" size="xs" :showDot="false" :action="onAddPostalCodeZone">+ Add Zone</Pill> to offer delivery to specific postal codes for a fee</p>
                    </div>
                </div>
            </div>

            <div class="w-full flex justify-end space-x-2">

                <Button
                    size="xs"
                    type="light"
                    :action="onResetPostalCodeZones"
                    v-if="postalCodeZonesHaveChanged && hasOriginalPostalCodeZones">
                    <span>Undo</span>
                </Button>

                <Button
                    size="xs"
                    type="primary"
                    :leftIcon="Plus"
                    :action="onAddPostalCodeZone">
                    <span>{{ hasPostalCodeZones ? 'Add Another Zone' : 'Add Zone' }}</span>
                </Button>

            </div>

        </template>

    </div>

</template>

<script>

    import isEqual from 'lodash.isequal';
    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import cloneDeep from 'lodash.clonedeep';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import ErrorText from '@Partials/ErrorText.vue';
    import SelectTags from '@Partials/SelectTags.vue';
    import { convertToMoneyWithSymbol } from '@Utils/numberUtils';
    import { Plus, LocateFixed, Minimize, SquarePen, Trash2 } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'deliveryMethodState'],
        components: {
            LocateFixed, Minimize, SquarePen, Pill, Input, Button, Skeleton, ErrorText, SelectTags
        },
        data() {
            return {
                Plus,
                Trash2,
                expandedStates: [],
                originalPostalCodeZones: []
            };
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingDeliveryMethod() {
                return this.deliveryMethodState.isLoadingDeliveryMethod;
            },
            deliveryMethodForm() {
                return this.deliveryMethodState.deliveryMethodForm;
            },
            hasPostalCodeZones() {
                return this.deliveryMethodForm.postal_code_zones.length > 0;
            },
            hasOriginalPostalCodeZones() {
                return this.originalPostalCodeZones.length > 0;
            },
            postalCodeZonesHaveChanged() {
                const a = cloneDeep(this.deliveryMethodForm.postal_code_zones);
                const b = cloneDeep(this.originalPostalCodeZones);
                return !isEqual(a, b);
            }
        },
        methods: {
            convertToMoneyWithSymbol: convertToMoneyWithSymbol,
            onAddPostalCodeZone() {
                var totalZones = this.deliveryMethodForm.postal_code_zones.length;
                var fee = totalZones > 0 ? (this.deliveryMethodForm.postal_code_zones[totalZones - 1].fee * 2).toString() : '50.00';
                var distance = totalZones > 0 ? (this.deliveryMethodForm.postal_code_zones[totalZones - 1].distance * 2).toString() : '10';

                this.deliveryMethodForm.postal_code_zones.push({
                    'fee': fee,
                    'distance': distance,
                });

                this.expandedStates.push(true);
            },
            onRemovePostalCodeZone(index) {
                this.deliveryMethodForm.postal_code_zones.splice(index, 1);
                this.expandedStates.splice(index, 1);
            },
            onResetPostalCodeZones() {
                this.deliveryMethodForm.postal_code_zones = cloneDeep(this.originalPostalCodeZones);
                this.expandedStates = new Array(this.deliveryMethodForm.postal_code_zones.length).fill(true);
            },
            toggleExpanded(index) {
                this.expandedStates[index] = !this.expandedStates[index];
            }
        }
    };
</script>
