<template>

    <div
        @click="navigateToLogin"
        class="select-none flex justify-between items-center bg-blue-500 text-white py-2 px-4 mb-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg active:scale-95 active:shadow-md cursor-pointer group">

        <div
            class="flex items-center space-x-2">

            <div
                class="w-16 h-16 bg-gray-200 flex items-center justify-center rounded-full border-2 border-blue-400">
                <img :src="'/images/logo-black-transparent.png'" alt="Store Logo" class="w-full h-full" />
            </div>

            <div class="space-y-1">
                <h1 class="text-sm font-bold">Powered By {{ appName }}</h1>
                <p class="text-xs">Create your own shop today!</p>
            </div>

        </div>

        <div>

            <div class="flex bg-blue-700 rounded-full px-2 py-1 -mt-2 mb-2">
                <span class="text-xs">AD</span>
            </div>

            <svg class="w-6 h-6 group-hover:scale-150 transition-all duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>

        </div>

    </div>

</template>

<script>

    export default {
        data() {
            return {
                appName: import.meta.env.VITE_APP_NAME
            }
        },
        methods: {
            navigateToLogin() {
                console.log('asd');
                this.$router.push({
                    name: 'login'
                })
            }
        }
    };

</script>
