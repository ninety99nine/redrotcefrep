<template>

    <div class="animated-border-blue bg-white space-y-4 py-4 px-4 shadow-sm rounded-xl flex flex-col items-center">

        <div class="w-full flex items-center justify-between">

            <h1 class="flex items-center space-x-2 text-sm text-gray-700 font-bold">
                <Video size="20"></Video>
                <span>Tutorials</span>
            </h1>

            <Button
                size="xs"
                type="primary">
                <span>Explore</span>
            </Button>

        </div>

        <p class="text-xs text-gray-600">
            Learn step by step with easy-to-follow tutorials to guide you through the essentials 🎯
        </p>

    </div>

</template>

<script>

    import { Video } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        components: {
            Video, Button
        }
    };

</script>
