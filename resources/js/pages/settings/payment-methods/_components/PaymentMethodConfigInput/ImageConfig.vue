<template>

    <Input
        type="file"
        :maxFiles="1"
        :imagePreviewGridCols="1"
        v-model="localModelValue"
        :label="configSchemaEntity.label"
        @retryUploads="(files) => uploadImages"
        @retryUpload="(file, fileIndex) => uploadImages(fileIndex)"
        :errorText="formState.getFormError(configSchemaEntity.attribute)"
        :secondaryLabel="configSchemaEntity.optional ? '(optional)' : null"
        :mimeTypes="configSchemaEntity.validation_rules.mime_types[0] ?? ['image/*']">
    </Input>

</template>

<script>

    import Input from '@Partials/Input.vue';

    export default {
        inject: ['formState'],
        components: { Input },
        props: {
            modelValue: {
                type: [Object, null]
            },
            configSchemaEntity: {
                type: Object
            },
            uploadImages: {
                type: Function
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
