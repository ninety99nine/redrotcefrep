<template>

    <Select
        width="w-full"
        v-model="localModelValue"
        :label="configSchemaEntity.label"
        :options="configSchemaEntity.options"
        :placeholder="configSchemaEntity.placeholder"
        :description="configSchemaEntity.description"
        :popoverContent="configSchemaEntity.description_info"
        :secondaryLabel="configSchemaEntity.optional ? '(optional)' : null"
        :externalLinkUrl="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.href : null"
        :externalLinkName="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.label : null">
    </Select>

</template>

<script>

    import Select from '@Partials/Select.vue';

    export default {
        components: { Select },
        props: {
            modelValue: {
                type: String
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
