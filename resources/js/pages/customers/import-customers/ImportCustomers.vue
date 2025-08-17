<template>

    <ImportCsvFile
        :columns="columns"
        :formData="formData"
        fileName="customers"
        heading="Import Customers"
        :goBack="navigateToCustomers"
        sampleFile="\csvs\customers.csv"
        endpoint="/api/customers/import"
        goBackButtonText="View Customers"
        submitButtonText="Import Customers"
        @imported="storeState.silentUpdate()">

        <template #instruction>
            <span class="text-sm mb-4">
                Each customer must have atleast a <span class="font-semibold">first name</span> — and either an <span class="font-semibold">email</span>, <span class="font-semibold">mobile number</span> or both.
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
                        name: 'ID',
                        required: false
                    },
                    {
                        name: 'First Name',
                        required: true,
                        instruction: 'Always'
                    },
                    {
                        name: 'Last Name',
                        required: false
                    },
                    {
                        name: 'Email',
                        required: true,
                        instruction: 'Required if mobile is not provided'
                    },
                    {
                        name: 'Mobile',
                        required: true,
                        instruction: 'Required if email is not provided'
                    },
                    {
                        name: 'Birthday',
                        required: false
                    },
                    {
                        name: 'Referral Code',
                        required: false
                    },
                    {
                        name: 'Notes',
                        required: false
                    },
                    {
                        name: 'Tags',
                        required: false
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
            async navigateToCustomers() {
                await this.$router.replace({
                    name: 'show-customers',
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            async navigateToCustomers() {
                await this.$router.replace({
                    name: 'show-customers',
                    query: {
                        store_id: this.store.id
                    }
                });
            }
        }
    };

</script>
