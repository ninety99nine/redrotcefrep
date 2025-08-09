<template>

    <div class="pt-24 px-8 relative select-none">

        <!-- Clouds Image -->
        <img :src="'/images/clouds.png'" class="absolute bottom-0">

        <div class="relative bg-white/80 p-4 rounded-md">

            <div class="flex items-center space-x-4 mb-4">

                <!-- Back -->
                <Button
                    size="sm"
                    type="light"
                    :leftIcon="ArrowLeft"
                    :action="navigateToProducts">
                </Button>

                <h1 class="text-lg text-gray-700 font-semibold">Import Products</h1>

            </div>

            <div class="w-full flex items-center justify-center space-x-4 text-sm font-medium text-center text-gray-500 mb-8">

                <template v-for="(stepItem, index) in steps" :key="index">

                    <!-- Step Circle -->
                    <div class="flex items-center space-x-2">
                        <div v-if="step > index + 1" class="flex w-8 h-8 items-center justify-center rounded-full text-white bg-blue-500">
                            <Check size="16"></Check>
                        </div>
                        <div v-else :class="['flex w-8 h-8 items-center justify-center rounded-full', step === index + 1 ? 'bg-blue-100 border border-blue-300 text-blue-500 animate-pulse' : 'bg-white border border-gray-300']">
                            <span>{{ index + 1 }}</span>
                        </div>
                        <span>{{ stepItem.name }}</span>
                    </div>

                    <!-- Connecting Line (not shown for the last step) -->
                    <div v-if="index < steps.length - 1">
                        <div class="w-20 border-t"></div>
                    </div>

                </template>

            </div>

            <template v-if="step == 1">

                <div class="grid grid-cols-2 gap-8">

                    <div class="col-span-1 border border-gray-300 rounded-lg p-4">

                        <div class="flex flex-col justify-center h-full">

                            <p class="font-bold mb-4">Upload CSV</p>
                            <p class="text-sm mb-4">Start by downloading our <a href="\csvs\products.csv" target="_blank" class="cursor-pointer text-blue-500 hover:underline">sample CSV template</a></p>

                            <Input
                                type="file"
                                :maxFiles="1"
                                v-model="csvFile"
                                :mimeTypes="['text/csv']"
                                :imagePreviewGridCols="1"
                                @retryUploads="(files) => null"
                                @retryUpload="(file, fileIndex) => null"
                                placeholder="Click or Drag & Drop CSV template">
                            </Input>

                        </div>

                    </div>

                    <div class="col-span-1 border border-gray-300 rounded-lg">

                        <div class="grid grid-cols-2 gap-8 py-2 px-4 bg-gray-100 rounded-t-lg text-sm font-bold">

                            <div>
                                <span>Expected Column</span>
                            </div>

                            <div>
                                <span>Required</span>
                            </div>

                        </div>

                        <div class="max-h-80 overflow-auto rounded-b-lg">

                            <div
                                v-for="(column, index) in columns"
                                :class="['grid grid-cols-2 gap-8 py-1.5 px-4 hover:bg-gray-100', { 'bg-blue-50' : column.required}]">

                                <div>
                                    <span class="text-sm">{{ column.name }}</span>
                                </div>

                                <div class="flex items-center">
                                    <div v-if="column.required" class="flex w-4 h-4 items-center justify-center rounded-full text-white bg-blue-500">
                                        <Check size="10"></Check>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div v-if="csvFileReady" class="flex justify-end mt-4">

                    <!-- Continue -->
                    <Button
                        size="sm"
                        type="primary"
                        :rightIcon="ArrowRight"
                        :action="() => step = 2">
                        <span>Continue</span>
                    </Button>

                </div>

            </template>

            <template v-if="step == 2">

                <div class="border border-gray-300 rounded-lg">

                    <div class="grid grid-cols-4 gap-4 py-2 px-4 bg-gray-100 rounded-t-lg text-sm font-bold">
                        <div><span>Your CSV Column</span></div>
                        <div><span>Your Sample Data</span></div>
                        <div><span>Destination Column</span></div>
                        <div><span>Include</span></div>
                    </div>

                    <div class="max-h-96 overflow-auto pt-4 pb-8">

                        <div
                            :key="index"
                            v-for="(csvColumn, index) in csvColumns"
                            class="grid grid-cols-4 gap-4 py-1.5 px-4 hover:bg-gray-100 items-center">

                            <div class="text-sm">{{ csvColumn.name }}</div>

                            <div class="text-sm truncate">{{ csvColumn.sample }}</div>

                            <div>
                                <Select
                                    :search="true"
                                    :options="columnOptions"
                                    v-model="csvColumn.mappedTo"
                                ></Select>
                            </div>

                            <div>
                                <Input
                                    type="checkbox"
                                    v-model="csvColumn.include"
                                ></Input>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="flex justify-end space-x-2 mt-4 mb-20">

                    <!-- Back -->
                    <Button
                        size="sm"
                        type="light"
                        :leftIcon="ArrowLeft"
                        :action="() => step = 1">
                        <span>Back</span>
                    </Button>

                    <!-- Continue -->
                    <Button
                        size="sm"
                        type="primary"
                        :rightIcon="ArrowRight"
                        :action="() => step = 3">
                        <span>Continue</span>
                    </Button>

                </div>

            </template>

            <template v-if="step == 3 && !imported">

                <div class="w-full max-h-96 overflow-auto border border-blue-200 rounded-lg">

                    <table class="w-full text-left">

                        <thead class="text-sm bg-blue-50">

                            <tr>

                                <th
                                    scope="col"
                                    :key="column.name"
                                    v-for="column in includedColumns"
                                    class="text-sm whitespace-nowrap p-3 border border-gray-200">
                                    <div>{{ column.name }}</div>
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr
                                :key="rowIndex"
                                class="hover:bg-gray-100"
                                v-for="(row, rowIndex) in mappedData">

                                <td
                                    :key="column.name"
                                    v-for="column in includedColumns"
                                    class="text-sm whitespace-nowrap p-3 border border-gray-200">
                                    {{ row[column.name.toLowerCase()] || '' }}
                                </td>

                            </tr>

                        </tbody>

                    </table>


                </div>

                <div class="flex justify-end space-x-2 mt-4 mb-20">

                    <!-- Back -->
                    <Button
                        size="sm"
                        type="light"
                        :leftIcon="ArrowLeft"
                        :action="() => step = 2">
                        <span>Back</span>
                    </Button>

                    <!-- Submit -->
                    <Button
                        size="sm"
                        type="primary"
                        :leftIcon="Check"
                        :action="importProducts">
                        <span>Import Products</span>
                    </Button>

                </div>

            </template>

            <template v-if="step == 3 && imported">

                <div class="flex flex-col items-center bg-gray-50 border border-gray-300 rounded-lg py-20">

                    <div class="flex flex-col items-center mx-auto">

                        <div class="flex w-20 h-20 items-center justify-center rounded-full text-white bg-green-500 mb-4">
                            <Check size="40"></Check>
                        </div>

                        <span class="text-sm font-bold mb-16">Import Successful</span>

                        <div class="flex justify-end space-x-2">

                            <!-- Back -->
                            <Button
                                size="sm"
                                type="light"
                                :action="navigateToProducts">
                                <span>View Products</span>
                            </Button>

                            <!-- Reset -->
                            <Button
                                size="sm"
                                type="primary"
                                :action="reset"
                                :leftIcon="RotateCcw">
                                <span>Upload Another File</span>
                            </Button>

                        </div>

                    </div>

                </div>

            </template>

        </div>

    </div>

</template>

<script>

    import Papa from 'papaparse';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import { Check, RotateCcw, ArrowLeft, ArrowRight } from 'lucide-vue-next';

    export default {
        inject: ['storeState', 'notificationState'],
        components: {
            Check, Input, Button, Select
        },
        data() {
            return {
                Check,
                step: 1,
                ArrowLeft,
                RotateCcw,
                ArrowRight,
                imported: false,
                steps: [
                    { name: 'Upload' },
                    { name: 'Map Columns' },
                    { name: 'Review' }
                ],
                csvFile: [],
                parsedCsvData: [],
                csvFileReady: false,
                csvColumns: [],
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
                        name: 'Position',
                        required: false
                    }
                ],
            }
        },
        watch: {
            csvFile(newValue) {
                if (newValue.length > 0) {
                    const file = newValue[0].file_ref;
                    const isCsv = file.type === 'text/csv' || file.name.toLowerCase().endsWith('.csv');
                    if (isCsv) {
                        this.step = 2;
                        this.parseCsvFile(file);
                        this.csvFileReady = true;
                    } else {
                        this.notificationState.showWarningNotification('Please upload a valid CSV file.');
                        this.csvFileReady = false;
                        this.csvFile = [];
                    }
                }else{
                    this.csvFileReady = false;
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            columnOptions() {
                return this.columns.map(column => ({
                    label: column.name,
                    value: column.name.toLowerCase()
                }));
            },
            includedColumns() {
                const includedMappedValues = this.csvColumns
                    .filter(csvColumn => csvColumn.include && csvColumn.mappedTo)
                    .map(csvColumn => csvColumn.mappedTo);
                return this.columns.filter(column =>
                    includedMappedValues.includes(column.name.toLowerCase())
                );
            },
            mappedData() {
                // Skip the header row and map each data row
                return this.parsedCsvData.slice(1).map(row => {
                    const mappedRow = {};
                    this.csvColumns.forEach((csvColumn, index) => {
                        if (csvColumn.include && csvColumn.mappedTo) {
                            mappedRow[csvColumn.mappedTo] = row[index] || '';
                        }
                    });
                    return mappedRow;
                });
            }
        },
        methods: {
            reset() {
                this.step = 1;
                this.csvFile = [];
                this.csvColumns = [];
                this.imported = false;
                this.parsedCsvData = [];
                this.csvFileReady = false;
            },
            async navigateToProducts() {
                await this.$router.replace({
                    name: 'show-products',
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            parseCsvFile(file) {
                Papa.parse(file, {
                    complete: (result) => {
                        if (result.data.length > 0) {
                            const headers = result.data[0];
                            const sampleData = result.data[1] || [];
                            this.csvColumns = headers.map((name, index) => ({
                                name,
                                sample: sampleData[index] || '',
                                mappedTo: this.autoMapColumn(name),
                                include: true
                            }));
                            this.parsedCsvData = result.data;
                            this.step = 2;
                        } else {
                            this.notificationState.showWarningNotification('The CSV file is empty or invalid.');
                            this.csvFile = [];
                            this.csvFileReady = false;
                        }
                    },
                    header: false,
                    skipEmptyLines: true,
                    error: () => {
                        this.notificationState.showWarningNotification('Error parsing CSV file.');
                        this.csvFile = [];
                        this.csvFileReady = false;
                    }
                });
            },
            autoMapColumn(csvColumnName) {
                const normalizedCsvName = csvColumnName.toLowerCase();
                const matchedColumn = this.columnOptions.find(option =>
                    option.value === normalizedCsvName ||
                    option.label.toLowerCase() === csvColumnName.toLowerCase()
                );
                return matchedColumn ? matchedColumn.value : '';
            },
            async importProducts() {

                try{

                    if(this.isImportingProducts) return;

                    this.isImportingProducts = true;

                    // Create CSV headers
                    const headers = this.includedColumns.map(col => col.name);

                    // Create CSV rows
                    const rows = this.mappedData.map(row =>
                        headers.map(header => row[header.toLowerCase()] || '').join(',')
                    );
                    const csvContent = [headers.join(','), ...rows].join('\n');

                    // Create FormData and Blob
                    const formData = new FormData();
                    const blob = new Blob([csvContent], { type: 'text/csv' });
                    formData.append('file', blob, 'products.csv');
                    formData.append('store_id', this.store.id);

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    await axios.post('/api/products/import', formData, config);

                    this.imported = true;
                    this.notificationState.showSuccessNotification('Products imported successfully!');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while importing products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to import products:', error);
                    this.imported = false;
                } finally {
                    this.isImportingProducts = false;
                }
            }
        }
    };

</script>
