<template>

    <div class="flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg space-y-4 my-4">

        <Store size="32" class="text-gray-500"></Store>
        <p class="text-sm text-gray-500">Add designs for your {{ placement }} page</p>

    </div>

</template>

<script>

    import { Store } from 'lucide-vue-next';

    export default {
        inject: ['designState'],
        components: { Store },
        computed: {
            placement() {
                return this.designState.placement;
            }
        },
    }

</script>
