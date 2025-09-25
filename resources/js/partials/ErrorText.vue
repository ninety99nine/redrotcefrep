<template>

    <span v-if="errorText" class="scroll-to-error font-medium text-red-500 text-xs">
        {{ errorText }}
    </span>

</template>

<script>

    export default {
        props: {
            errorText: {
                type: [String, null],
                default: null
            }
        }
    };

  </script>
