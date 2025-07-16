<template>

    <!-- Mobile Number Input -->
    <Input
        type="text"
        v-model="localModelValue"
        :label="configSchemaEntity.label"
        :description="configSchemaEntity.description"
        :popoverContent="configSchemaEntity.description_info"
        :secondaryLabel="configSchemaEntity.optional ? '(optional)' : null"
        :learn_moreLink="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.href : null"
        :learn_moreLabel="configSchemaEntity.learn_more ? configSchemaEntity.learn_more.label : null">
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

