<template>

    <div @click="navigateToCreateStore" class="flex flex-col justify-center items-center bg-white space-y-2 py-4 px-4 shadow-sm rounded-xl border border-gray-200 shadow-blue-200 hover:shadow-md hover:border-blue-200 hover:scale-105 transition-all duration-300 cursor-pointer">

        <div class="flex justify-center items-center w-10 h-10 bg-gray-100 rounded-full border border-dashed border-gray-300 mb-2">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </div>

        <span class="text-xs">{{ hasStores ? 'Add Store' : 'Create Your First Store' }}</span>

    </div>

</template>

<script>

    import Logo from '@Partials/Logo.vue';

    export default {
        components: { Logo },
        props: {
            hasStores: {
                type: Boolean
            }
        },
        methods: {
            navigateToCreateStore() {
                this.$router.push({
                    name: 'create-store'
                });
            }
        }
    };

</script>
