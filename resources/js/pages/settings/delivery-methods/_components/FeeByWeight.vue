<template>

    <div class="w-full space-y-4">

        <h1 class="text-md font-bold m-2">Weights</h1>

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
                class="relative bg-gray-50 p-4 border border-gray-300 rounded-lg"
                v-for="(weightCategory, index) in deliveryMethodForm.weight_categories">

                <div class="absolute top-2 right-2 flex items-center space-x-2">

                    <Minimize v-if="expandedStates[index]" size="16" class="text-gray-500 cursor-pointer hover:opacity-50" @click.stop="toggleExpanded(index)"></Minimize>
                    <SquarePen v-else size="16" class="text-gray-500 cursor-pointer hover:opacity-50" @click.stop="toggleExpanded(index)"></SquarePen>

                    <Button
                        size="xs"
                        type="bareDanger"
                        :leftIcon="Trash2"
                        :action="() => onRemoveWeightCategory(index)">
                    </Button>

                </div>

                <div v-if="expandedStates[index]" class="space-y-4">

                    <div class="flex items-center space-x-2 text-sm text-gray-500">

                        <Scale size="16" class="text-gray-500"></Scale>
                        <span>Category {{ index + 1 }}</span>

                    </div>

                    <div class="space-y-4">

                        <div class="flex space-x-4">

                            <Input
                                type="text"
                                label="Category Name"
                                placeholder="Light weight"
                                tooltipContent="Set the name for this weight category"
                                v-model="deliveryMethodForm.weight_categories[index].name"
                                :errorText="formState.getFormError('weight_categories'+index+'name')">
                            </Input>

                            <Input
                                type="money"
                                label="Category fee"
                                :currency="store?.currency"
                                v-model="deliveryMethodForm.weight_categories[index].fee"
                                :errorText="formState.getFormError('weight_categories'+index+'fee')"
                                tooltipContent="Specify the fee charged for deliveries within this weight category">
                            </Input>

                        </div>

                        <SelectTags
                            :showOptions="false"
                            placeholder="Add weight range"
                            :label="'Weight Range ('+store.weight_unit+')'"
                            v-model="deliveryMethodForm.weight_categories[index].weights"
                            @change="deliveryMethodState.saveStateDebounced('Weights changed')"
                            :errorText="formState.getFormError('weight_categories'+index+'weights')"
                            tooltipContent="Specify the weight range for this category, e.g 2.5 or 5-10" />

                    </div>

                </div>

                <div
                    v-else
                    @click="toggleExpanded(index)"
                    class="space-y-2 cursor-pointer">

                    <div class="flex items-center space-x-4 text-sm text-gray-500 mb-4">

                        <div class="flex items-center space-x-2">

                            <Scale size="16" class="text-gray-500"></Scale>
                            <span>Category {{ index + 1 }}</span>

                        </div>

                        <Pill size="xs" type="primary" :showDot="false" v-if="deliveryMethodForm.weight_categories[index].name">{{ deliveryMethodForm.weight_categories[index].name }} → {{ convertToMoneyWithSymbol(deliveryMethodForm.weight_categories[index].fee, this.store.currency) }}</Pill>
                        <ErrorText v-else errorText="No name" margin="mt-0"></ErrorText>

                    </div>

                    <div
                        class="flex space-x-2"
                        v-if="deliveryMethodForm.weight_categories[index].weights.length">

                        <span
                            :key="index"
                            class="py-0.5 px-2 bg-gray-100 border border-gray-300 text-gray-500 text-xs rounded-lg"
                            v-for="(weight, index) in deliveryMethodForm.weight_categories[index].weights">
                            {{ weight }} {{ store.weight_unit }}
                        </span>

                    </div>
                    <ErrorText v-else errorText="No weight range" margin="mt-0"></ErrorText>

                    <ErrorText v-if="formState.getFormError('weight_categories'+index+'name')" :errorText="formState.getFormError('weight_categories'+index+'name')"></ErrorText>
                    <ErrorText v-if="formState.getFormError('weight_categories'+index+'fee')" :errorText="formState.getFormError('weight_categories'+index+'fee')"></ErrorText>

                </div>

            </div>

            <div v-if="!hasWeightCategories">
                <div class="flex items-center space-x-4 px-4 py-4 border border-gray-300 rounded-lg bg-gray-50">
                    <Scale size="20" class="text-gray-500"></Scale>
                    <div class="text-sm space-y-2">
                        <p><Pill type="primary" size="xs" :showDot="false" :action="onAddWeightCategory">+ Add Category</Pill> to define your weight</p>
                    </div>
                </div>
            </div>

            <div class="w-full flex justify-end space-x-2">

                <Button
                    size="xs"
                    type="light"
                    :action="onResetWeightCategories"
                    v-if="weightCategoriesHaveChanged && hasOriginalWeightCategories">
                    <span>Undo</span>
                </Button>

                <Button
                    size="xs"
                    type="primary"
                    :leftIcon="Plus"
                    :action="onAddWeightCategory">
                    <span>{{ hasWeightCategories ? 'Add Another Category' : 'Add Category' }}</span>
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
    import SelectTags from '@Partials/SelectTags.vue';
    import { convertToMoneyWithSymbol } from '@Utils/numberUtils';
    import { Plus, Scale, Minimize, SquarePen, Trash2 } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'deliveryMethodState'],
        components: {
            Scale, Minimize, SquarePen, Pill, Input, Button, Skeleton, ErrorText, SelectTags
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
                originalWeightCategories: []
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
            hasWeightCategories() {
                return this.deliveryMethodForm.weight_categories.length > 0;
            },
            hasOriginalWeightCategories() {
                return this.originalWeightCategories.length > 0;
            },
            weightCategoriesHaveChanged() {
                const a = cloneDeep(this.deliveryMethodForm.weight_categories);
                const b = cloneDeep(this.originalWeightCategories);
                return !isEqual(a, b);
            }
        },
        methods: {
            convertToMoneyWithSymbol: convertToMoneyWithSymbol,
            onAddWeightCategory() {
                this.deliveryMethodForm.weight_categories.push({
                    fee: '',
                    name: '',
                    weights: []
                });
                this.expandedStates.push(true);
            },
            onRemoveWeightCategory(index) {
                this.deliveryMethodForm.weight_categories.splice(index, 1);
                this.expandedStates.splice(index, 1);
            },
            onResetWeightCategories() {
                this.deliveryMethodForm.weight_categories = cloneDeep(this.originalWeightCategories);
                this.expandedStates = new Array(this.deliveryMethodForm.weight_categories.length).fill(true);
            },
            toggleExpanded(index) {
                this.expandedStates[index] = !this.expandedStates[index];
            }
        }
    };
</script>
