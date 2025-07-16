<template>

    <div class="space-y-2 mb-4">

        <template v-if="isLoadingProductVariations">

            <div
                :key="index"
                v-for="(_, index) in [1, 2, 3]"
                class="border-b shadow-sm rounded-lg p-2 bg-gray-50 w-full">

                <!-- Skeleton Loading -->
                <Skeleton width="w-1/3" :shine="true"></Skeleton>

            </div>

        </template>

        <template v-else>

            <div class="space-y-3 mb-4">

                <template
                    :key="index"
                    v-for="(variantAttribute, index) in selectedProduct.variant_attributes">

                    <Select
                        width="w-full"
                        :label="variantAttribute.name"
                        :description="variantAttribute.instruction"
                        v-model="variantAttributeValues[variantAttribute.name]">
                        <option v-for="(value, i) in variantAttribute.values" :value="value" :key="i">
                            {{ value }}
                        </option>
                    </Select>

                </template>

            </div>

            <!-- Matching Variation -->
            <ProductOption
                :product="matchingVariations[0]"
                :onSelectProduct="onSelectProduct"
                v-if="matchingVariations.length === 1">
            </ProductOption>

        </template>

    </div>

</template>

<script>

    import axios from 'axios';
    import Select from '@Partials/Select.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import ProductOption from '@Pages/orders/order/editable/components/products/add-product/product-options/ProductOption.vue';

    export default {
        inject: ['formState', 'notificationState'],
        components: { Select, Skeleton, ProductOption },
        props: {
            selectedProduct: {
                type: Object
            },
            onSelectProduct: {
                type: Function
            }
        },
        data() {
            return {
                variations: [],
                variantAttributeValues: {},
                isLoadingProductVariations: false,
            }
        },
        computed: {
            matchingVariations() {
                // Filter variations based on selected attributes
                return this.variations.filter((variation) => {
                    return Object.entries(this.variantAttributeValues).every(([attrName, selectedValue]) => {
                        // Check if the variation contains this attribute and the selected value matches
                        return variation.variables.some(variable =>
                            variable.value === selectedValue
                        );
                    });
                });
            }
        },
        methods: {
            initializeVariantDefaults() {
                this.variantAttributeValues = {};

                this.selectedProduct.variant_attributes.forEach(attr => {
                    if (attr.values.length > 0) {
                        this.variantAttributeValues[attr.name] = attr.values[0]; // Select first option by default
                    }
                });
            },
            async showProductVariations() {
                try {

                    this.isLoadingProductVariations = true;

                    let config = {
                        params: {
                            per_page: 100,
                            _relationships: ['photo'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/products/${this.selectedProduct.id}/variations`, config);

                    const pagination = response.data;
                    this.variations = pagination.data;
                    this.initializeVariantDefaults();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching product variations';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch product variations:', error);
                } finally {
                    this.isLoadingProductVariations = false;
                this.hasLoadedInitialProducts = true;
                }
            }
        },
        created() {
            this.showProductVariations();
        }
    };

</script>
