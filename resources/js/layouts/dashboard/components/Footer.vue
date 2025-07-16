<template>

    <footer class="flex justify-center items-center p-8 m-4 border-t border-gray-100">
        <Logo height="w-8" class="mr-2" />
        <span class="block text-sm text-gray-500 sm:text-center dark:text-gray-400">© {{ currentYear }} Perfect Order. All Rights Reserved</span>
    </footer>

</template>

<script>

    import Logo from '@Partials/Logo.vue';

    export default {
        components: { Logo },
        data() {
            return {
                currentYear: new Date().getFullYear()   // 2025
            };
        }
    };

</script>
