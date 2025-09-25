<template>

    <div class="w-full space-y-4 select-none">

        <!-- Show Distance In Invoice Checkbox -->
        <Input
            type="checkbox"
            inputLabel="Show distance on invoice"
            :skeleton="isLoadingStore || isLoadingDeliveryMethod"
            v-model="deliveryMethodForm.show_distance_on_invoice"
            :errorText="formState.getFormError('show_distance_on_invoice')"
            inputDescription="The calculated delivery distance will appear on the invoice"
            @change="deliveryMethodState.saveStateDebounced('Show distance on invoice changed')">
        </Input>

        <!-- Address Input -->
        <AddressInput
            :onlyValidate="true"
            title="Base location"
            :pinLocationOnMap="true"
            @onDeleted="unsetAddress"
            @onValidated="setAddress"
            :address="deliveryMethodForm.address"
            v-if="!isLoadingStore && !isLoadingDeliveryMethod"
            triggerClass="space-y-4 p-4 rounded-lg shadow-lg bg-white"
            subtitle="Starting point for calculating delivery distances">
        </AddressInput>

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
                v-for="(distanceZone, index) in deliveryMethodForm.distance_zones"
                class="relative bg-gray-50 border border-gray-300 rounded-lg">

                <div class="absolute top-2 right-2 flex items-center space-x-2">

                    <Minimize v-if="expandedStates[index]" size="16" class="text-gray-500 cursor-pointer hover:opacity-50" @click.stop="toggleExpanded(index)"></Minimize>
                    <SquarePen v-else size="16" class="text-gray-500 cursor-pointer hover:opacity-50" @click.stop="toggleExpanded(index)"></SquarePen>

                    <Button
                        size="xs"
                        type="bareDanger"
                        :leftIcon="Trash2"
                        :action="() => onRemoveDistanceZone(index)">
                    </Button>

                </div>

                <div v-if="expandedStates[index]" class="p-4 space-y-4">

                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <Scale size="16" class="text-gray-500"></Scale>
                        <span>Zone {{ index + 1 }}</span>
                    </div>

                    <div class="flex space-x-4">

                        <!-- Zone Distance Input -->
                        <Input
                            min="0"
                            type="number"
                            placeholder="10"
                            label="Delivery distance up to"
                            v-model="deliveryMethodForm.distance_zones[index].distance"
                            :errorText="formState.getFormError('distance_zones'+index+'distance')"
                            tooltipContent="Set the maximum distance eligible for this delivery zone">
                            <template #suffix>
                                <span class="text-xs ml-2">{{ store.distance_unit }}</span>
                            </template>
                        </Input>

                        <!-- Zone Fee Input -->
                        <Input
                            type="money"
                            label="Delivery fee"
                            :currency="store?.currency"
                            v-model="deliveryMethodForm.distance_zones[index].fee"
                            :errorText="formState.getFormError('distance_zones'+index+'fee')"
                            tooltipContent="Specify the fee charged for deliveries within the set distance range">
                        </Input>

                    </div>

                </div>

                <div
                    v-else
                    @click="toggleExpanded(index)"
                    class="rounded-lg p-4 space-y-2 cursor-pointer hover:bg-gray-100">

                    <div class="flex items-center space-x-4 text-sm text-gray-500">

                        <div class="flex items-center space-x-2">

                            <Scale size="16" class="text-gray-500"></Scale>
                            <span class="text-sm">Zone {{ index + 1 }}</span>

                        </div>

                        <Pill size="xs" type="primary" :showDot="false" v-if="deliveryMethodForm.distance_zones[index].distance">{{ deliveryMethodForm.distance_zones[index].distance }} {{ store.distance_unit }} → {{ convertToMoneyWithSymbol(deliveryMethodForm.distance_zones[index].fee, this.store.currency) }}</Pill>
                        <ErrorText v-else errorText="No distance" margin="mt-0"></ErrorText>

                    </div>

                    <ErrorText v-if="formState.getFormError('distance_zones'+index+'distance')" :errorText="formState.getFormError('distance_zones'+index+'distance')"></ErrorText>
                    <ErrorText v-if="formState.getFormError('distance_zones'+index+'fee')" :errorText="formState.getFormError('distance_zones'+index+'fee')"></ErrorText>

                </div>

            </div>

            <div v-if="!hasDistanceZones">
                <div class="flex items-center space-x-4 px-4 py-4 border border-gray-300 rounded-lg bg-gray-50">
                    <Scale size="20" class="text-gray-500"></Scale>
                    <div class="text-sm space-y-2">
                        <p><Pill type="primary" size="xs" :showDot="false" :action="onAddDistanceZone">+ Add Zone</Pill> to offer delivery within specific distances for a fee</p>
                    </div>
                </div>
            </div>

            <div class="w-full flex justify-end space-x-2">

                <Button
                    size="xs"
                    type="light"
                    :action="onResetDistanceZones"
                    v-if="distanceZonesHaveChanged && hasOriginalDistanceZones">
                    <span>Undo</span>
                </Button>

                <Button
                    size="xs"
                    type="primary"
                    :leftIcon="Plus"
                    :action="onAddDistanceZone">
                    <span>Add Zone</span>
                </Button>

            </div>

        </template>

    </div>

</template>

<script>

    import isEqual from 'lodash/isEqual';
    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import cloneDeep from 'lodash/cloneDeep';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import ErrorText from '@Partials/ErrorText.vue';
    import AddressInput from '@Partials/AddressInput.vue';
    import { convertToMoneyWithSymbol } from '@Utils/numberUtils';
    import { Plus, Scale, Minimize, SquarePen, Trash2 } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'deliveryMethodState'],
        components: {
            Scale, Minimize, SquarePen, Pill, Input, Button, Skeleton, ErrorText, AddressInput
        },
        props: {
            form: {
                type: Object
            }
        },
        data() {
            return {
                Plus,
                Trash2,
                expandedStates: [],
                originalDistanceZones: []
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
            hasDistanceZones() {
                return this.deliveryMethodForm.distance_zones.length > 0;
            },
            hasOriginalDistanceZones() {
                return this.originalDistanceZones.length > 0;
            },
            distanceZonesHaveChanged() {
                const a = cloneDeep(this.deliveryMethodForm.distance_zones);
                const b = cloneDeep(this.originalDistanceZones);
                return !isEqual(a, b);
            }
        },
        methods: {
            convertToMoneyWithSymbol: convertToMoneyWithSymbol,
            onAddDistanceZone() {
                var totalZones = this.deliveryMethodForm.distance_zones.length;
                var fee = totalZones > 0 ? (this.deliveryMethodForm.distance_zones[totalZones - 1].fee * 2).toString() : '50.00';
                var distance = totalZones > 0 ? (this.deliveryMethodForm.distance_zones[totalZones - 1].distance * 2).toString() : '10';

                this.deliveryMethodForm.distance_zones.push({
                    'fee': fee,
                    'distance': distance,
                });

                this.expandedStates.push(true);
            },
            onRemoveDistanceZone(index) {
                this.deliveryMethodForm.distance_zones.splice(index, 1);
                this.expandedStates.splice(index, 1);
            },
            onResetDistanceZones() {
                this.deliveryMethodForm.distance_zones = cloneDeep(this.originalDistanceZones);
                this.expandedStates = new Array(this.deliveryMethodForm.distance_zones.length).fill(true);
            },
            toggleExpanded(index) {
                this.expandedStates[index] = !this.expandedStates[index];
            },
            setAddress(address) {
                this.deliveryMethodForm.address = address;
                this.deliveryMethodState.saveStateDebounced('Address changed');
            },
            unsetAddress() {
                this.deliveryMethodForm.address = null;
                this.deliveryMethodState.saveStateDebounced('Address removed');
            },
        }
    };
</script>
