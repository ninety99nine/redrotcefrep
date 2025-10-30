<template>

    <SelectCurrency
        :clearable="true"
        v-model="localModelValue"
        :label="configSchemaEntity.label"
        :allowedCurrencies="paymentMethod.currencies"
        :description="configSchemaEntity.description"
        :popoverContent="configSchemaEntity.description_info"
        :errorText="formState.getFormError(configSchemaEntity.attribute)"
        :secondaryLabel="configSchemaEntity.optional ? '(optional)' : null"
        :externalLinkUrl="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.href : null"
        :externalLinkName="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.label : null">
    </SelectCurrency>

</template>

<script>

    import SelectCurrency from '@Partials/SelectCurrency.vue';

    export default {
        inject: ['formState'],
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
        emits: ['change', 'update:modelValue'],
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
                if(newValue != this.modelValue) {
                    this.$emit('change', newValue);
                    this.$emit('update:modelValue', newValue);
                }
            }
        }
    };
  </script>
