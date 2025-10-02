<template>

    <!-- Currency Select -->
    <SelectCurrency
        :clearable="true"
        v-model="localModelValue"
        :label="configSchemaEntity.label"
        :allowedCurrencies="paymentMethod.currencies"
        :description="configSchemaEntity.description"
        :popoverContent="configSchemaEntity.description_info"
        :secondaryLabel="configSchemaEntity.optional ? '(optional)' : null"
        :externalLinkUrl="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.href : null"
        :externalLinkName="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.label : null">
    </SelectCurrency>

</template>

<script>

    import SelectCurrency from '@Partials/SelectCurrency.vue';

    export default {
        components: { SelectCurrency },
        props: {
            modelValue: {
                type: String
            },
            paymentMethod: {
                type: Object
            },
            configSchemaEntity: {
                type: Object
            },
        },
        data() {
            return {
                localModelValue: this.modelValue
            };
        },
        watch: {
            modelValue(newValue) {
                this.localModelValue = newValue;
            },
            localModelValue(newValue, oldValue) {
                this.$emit('update:modelValue', newValue);
            }
        }
    };
  </script>
