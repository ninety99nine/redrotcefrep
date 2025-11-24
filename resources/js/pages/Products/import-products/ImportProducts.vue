<template>

    <ImportCsvFile
        :columns="columns"
        fileName="products"
        :formData="formData"
        heading="Import Products"
        :goBack="navigateToProducts"
        sampleFile="\csvs\products.csv"
        endpoint="/api/products/import"
        goBackButtonText="View Products"
        submitButtonText="Import Products"
        @imported="storeState.silentUpdate()">
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
                        name: 'Name',
                        required: true
                    },
                    {
                        name: 'Parent Name',
                        required: false
                    },
                    {
                        name: 'Free',
                        required: false
                    },
                    {
                        name: 'Estimated Price',
                        required: false
                    },
                    {
                        name: 'Regular Price',
                        required: false
                    },
                    {
                        name: 'Sale Price',
                        required: false
                    },
                    {
                        name: 'Cost Price',
                        required: false
                    },
                    {
                        name: 'Visible',
                        required: false
                    },
                    {
                        name: 'Type',
                        required: false
                    },
                    {
                        name: 'Download Link',
                        required: false
                    },
                    {
                        name: 'Sku',
                        required: false
                    },
                    {
                        name: 'Barcode',
                        required: false
                    },
                    {
                        name: 'Show Description',
                        required: false
                    },
                    {
                        name: 'Description',
                        required: false
                    },
                    {
                        name: 'Weight',
                        required: false
                    },
                    {
                        name: 'Tax Override',
                        required: false
                    },
                    {
                        name: 'Tax Override Amount',
                        required: false
                    },
                    {
                        name: 'Show Price Per Unit',
                        required: false
                    },
                    {
                        name: 'Unit Value',
                        required: false
                    },
                    {
                        name: 'Unit Type',
                        required: false
                    },
                    {
                        name: 'Set Daily Capacity',
                        required: false
                    },
                    {
                        name: 'Daily Capacity',
                        required: false
                    },
                    {
                        name: 'Stock Type',
                        required: false
                    },
                    {
                        name: 'Stock Quantity',
                        required: false
                    },
                    {
                        name: 'Set Min Order Quantity',
                        required: false
                    },
                    {
                        name: 'Min Order Quantity',
                        required: false
                    },
                    {
                        name: 'Set Max Order Quantity',
                        required: false
                    },
                    {
                        name: 'Max Order Quantity',
                        required: false
                    },
                    {
                        name: 'Categories',
                        required: false
                    },
                    {
                        name: 'Tags',
                        required: false
                    },
                    {
                        name: 'Images',
                        required: false
                    },
                    {
                        name: 'Position',
                        required: false
                    }
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
            async navigateToProducts() {
                await this.$router.replace({
                    name: 'show-products',
                    query: {
                        store_id: this.store.id
                    }
                });
            }
        }
    };

</script>
