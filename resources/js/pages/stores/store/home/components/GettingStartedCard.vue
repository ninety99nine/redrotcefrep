<template>

    <div class="animated-border-blue bg-white space-y-4 py-4 px-4 shadow-sm rounded-xl flex flex-col items-center">

        <div class="w-full flex items-center justify-between">

            <h1 class="flex items-center space-x-2 text-sm text-gray-700 font-bold">
                <Flag size="20"></Flag>
                <span>Getting Started</span>
            </h1>

            <Button
                size="xs"
                type="primary">
                <span>Let’s Go</span>
            </Button>

        </div>

        <p class="text-xs text-gray-600">
            Get the basics out of the way by following these simple steps 👌
        </p>

    </div>

</template>

<script>

    import { Flag } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        components: {
            Flag, Button
        }
    };

</script>
