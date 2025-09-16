<template>
    <color-picker v-model:pureColor="localModelValue" v-bind="$props"/>
</template>

<script>
import "vue3-colorpicker/style.css";
import { ColorPicker } from "vue3-colorpicker";

export default {
    components: { ColorPicker },
    props: {
        modelValue: {
            type: [String, null],
            default: null, // Ensure it can accept null
        },
        useType: {
            type: String,
            default: 'pure',
            options: ['pure', 'gradient', 'both']
        },
        pickerType: {
            type: String,
            default: 'fk',
            options: ['fk', 'chrome']
        },
        shape: {
            type: String,
            default: 'circle',
            options: ['circle', 'square']
        },
        format: {
            type: String,
            default: 'hex8',
            options: ['rgb', 'prgb', 'hex', 'hex6', 'hex3', 'hex4', 'hex8', 'name', 'hsl', 'hsv']
        },
        disableHistory: {
            type: Boolean,
            default: false
        },
        roundHistory: {
            type: Boolean,
            default: false
        },
        disableAlpha: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            localModelValue: this.modelValue || '#00000000', // Default to a transparent color string if null
        };
    },
    watch: {
        modelValue(newValue) {
            this.updateValue(newValue);
        },
        localModelValue(newValue) {
            const nullableValue = newValue == '#00000000' ? null : newValue;
            this.$emit('update:modelValue', nullableValue);
            this.$emit('change', nullableValue);
        },
    },
    methods: {
        updateValue(newValue) {
            this.localModelValue = newValue || '#00000000'; // Reset to a transparent color string if null
        }
    },
};
</script>
