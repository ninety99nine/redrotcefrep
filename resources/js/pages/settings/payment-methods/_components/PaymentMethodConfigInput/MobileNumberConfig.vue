<template>

    <Input
        type="text"
        v-model="localModelValue"
        placeholder="+26772000001"
        :label="configSchemaEntity.label"
        :description="configSchemaEntity.description"
        :popoverContent="configSchemaEntity.description_info"
        :errorText="formState.getFormError(configSchemaEntity.attribute)"
        :secondaryLabel="configSchemaEntity.optional ? '(optional)' : null"
        :learn_moreLink="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.href : null"
        :learn_moreLabel="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.label : null">
    </Input>

</template>

<script>

    import Input from '@Partials/Input.vue';

    export default {
        inject: ['formState'],
        components: { Input },
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

