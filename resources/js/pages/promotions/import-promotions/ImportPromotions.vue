<template>

    <ImportCsvFile
        :columns="columns"
        :formData="formData"
        fileName="promotions"
        heading="Import Promotions"
        :goBack="navigateToPromotions"
        sampleFile="\csvs\promotions.csv"
        endpoint="/api/promotions/import"
        goBackButtonText="View Promotions"
        submitButtonText="Import Promotions"
        @imported="storeState.silentUpdate()">

        <template #instruction>
            <span class="text-sm mb-4">
                Each promotion must have a <span class="font-semibold">name</span> — and at least one offer: discount (percentage or flat amount) or free delivery.
            </span>
        </template>

    </ImportCsvFile>

</template>

<script>

    import ImportCsvFile from '@Partials/ImportCsvFile.vue';

    export default {
        inject: ['storeState'],
        components: {
            ImportCsvFile
        },
        data() {
            return {
                columns: [
                    {
                        name: 'Name',
                        required: true,
                        instruction: 'Always'
                    },
                    {
                        name: 'Description',
                        required: false
                    },
                    {
                        name: 'Active',
                        required: false,
                        instruction: 'true/false (default: true)'
                    },
                    {
                        name: 'Code',
                        required: false,
                        instruction: 'If provided, promotion activated using code'
                    },
                    {
                        name: 'Discount Percentage',
                        required: false,
                        instruction: 'Percentage discount, e.g. 10 for 10% (do not provide with Discount Flat Amount)'
                    },
                    {
                        name: 'Discount Flat Amount',
                        required: false,
                        instruction: 'Flat discount amount, e.g. 5 for P5 (do not provide with Discount Percentage)'
                    },
                    {
                        name: 'Offer Free Delivery',
                        required: false,
                        instruction: 'true/false'
                    },
                    {
                        name: 'Minimum Spend',
                        required: false,
                        instruction: 'Minimum grand total for activation, e.g. 100'
                    },
                    {
                        name: 'Minimum Total Products',
                        required: false,
                        instruction: 'Minimum number of different products, e.g. 2'
                    },
                    {
                        name: 'Minimum Total Product Quantities',
                        required: false,
                        instruction: 'Minimum total quantity of products, e.g. 5'
                    },
                    {
                        name: 'Start Date',
                        required: false,
                        instruction: 'Start datetime, format YYYY-MM-DD HH:MM:SS'
                    },
                    {
                        name: 'End Date',
                        required: false,
                        instruction: 'End datetime, format YYYY-MM-DD HH:MM:SS'
                    },
                    {
                        name: 'Hours of Day',
                        required: false,
                        instruction: 'Comma-separated hours, e.g. 9,10,11'
                    },
                    {
                        name: 'Days of the Week',
                        required: false,
                        instruction: 'Comma-separated days, e.g. monday,tuesday'
                    },
                    {
                        name: 'Days of the Month',
                        required: false,
                        instruction: 'Comma-separated days, e.g. 1,15,30'
                    },
                    {
                        name: 'Months of the Year',
                        required: false,
                        instruction: 'Comma-separated months, e.g. january,february'
                    },
                    {
                        name: 'New Customers',
                        required: false,
                        instruction: 'true/false - Activate for new customers (default: false)'
                    },
                    {
                        name: 'Existing Customers',
                        required: false,
                        instruction: 'true/false - Activate for existing customers (default: false)'
                    },
                    {
                        name: 'Usage Limit',
                        required: false,
                        instruction: 'Remaining quantity for usage limit, e.g. 100'
                    },
                    {
                        name: 'User ID',
                        required: false,
                        instruction: 'UUID of the user creating the promotion'
                    },
                ]
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            formData() {
                return {
                    store_id: this.store?.id
                }
            }
        },
        methods: {
            async navigateToPromotions() {
                await this.$router.replace({
                    name: 'show-promotions',
                    query: {
                        store_id: this.store.id
                    }
                });
            }
        }
    };

</script>
