<template>

    <!-- Text Input -->
    <Input
        type="text"
        v-model="localModelValue"
        :label="configSchemaEntity.label"
        :placeholder="configSchemaEntity.placeholder"
        :description="configSchemaEntity.description"
        :popoverContent="configSchemaEntity.description_info"
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
        components: { Input },
        props: {
            modelValue: {
                type: String
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
