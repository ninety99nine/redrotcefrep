<template>

    <Input
        type="text"
        v-model="localModelValue"
        :label="configSchemaEntity.label"
        :placeholder="configSchemaEntity.placeholder"
        :description="configSchemaEntity.description"
        :popoverContent="configSchemaEntity.description_info"
        :errorText="formState.getFormError(configSchemaEntity.attribute)"
        :secondaryLabel="configSchemaEntity.optional ? '(optional)' : null"
        :externalLinkUrl="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.href : null"
        :externalLinkName="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.label : null">

        <template #prefix>
            <span class="leading-4 text-gray-400 text-sm">
                {{ configSchemaEntity.prefix }}
            </span>
        </template>

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
