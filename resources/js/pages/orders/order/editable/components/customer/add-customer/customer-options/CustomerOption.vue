<template>

    <div
        @click="() => onSelectCustomer(customer)"
        class="flex items-center space-x-2 cursor-pointer p-3 border rounded-lg bg-gray-50 hover:bg-gray-100">

        <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>

        <div>
            <p class="text-sm font-semibold">{{ customer._attributes.name }}</p>
            <p class="text-xs text-gray-500">{{ customer.email || customer.mobileNumber.national }}</p>
        </div>

    </div>

</template>

<script>

    export default {
        props: {
            customer: {
                type: Object
            },
            onSelectCustomer: {
                type: Function
            }
        }
    };

</script>
