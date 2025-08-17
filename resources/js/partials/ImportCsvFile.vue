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
                    :action="goBack"
                    :leftIcon="ArrowLeft">
                </Button>

                <h1 class="text-lg text-gray-700 font-semibold">{{ heading }}</h1>

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
                            <p class="text-sm mb-4">Start by downloading our <a :href="sampleFile" target="_blank" class="cursor-pointer text-blue-500 hover:underline">sample CSV template</a></p>

                            <slot name="instruction">
                                <span v-if="instruction" class="text-sm mb-4">{{ instruction }}</span>
                            </slot>

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
                                :key="index"
                                v-for="(column, index) in columns"
                                :class="['grid grid-cols-2 gap-8 py-1.5 px-4', column.required ? 'bg-blue-50' : 'hover:bg-gray-100']">

                                <div>
                                    <span class="text-sm">{{ column.name }}</span>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <div v-if="column.required" class="flex w-4 h-4 items-center justify-center rounded-full text-white bg-blue-500">
                                        <Check size="10"></Check>
                                    </div>
                                    <span v-if="column.instruction" class="text-xs italic">{{ column.instruction }}</span>
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
                                    class="text-sm text-center whitespace-nowrap p-3 border border-gray-200">
                                    <div>Row</div>
                                </th>

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
                                v-for="(row, rowIndex) in mappedRows">

                                <td
                                    class="text-sm text-center whitespace-nowrap p-3 border border-gray-200">
                                    {{ rowIndex + 1 }}
                                </td>

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
                        :action="importCsvFile">
                        <span>{{ submitButtonText }}</span>
                    </Button>

                </div>

            </template>

            <template v-if="step == 3 && imported">

                <div class="w-full bg-gray-50 border border-gray-300 rounded-lg py-20">

                        <div class="flex items-center justify-center mx-auto w-20 h-20 rounded-full text-white bg-green-500 mb-4">
                            <Check size="40"></Check>
                        </div>

                        <p class="w-fit mx-auto text-sm font-bold mb-10">Import Successful</p>

                        <div class="flex items-center justify-center mx-auto space-x-2">

                            <!-- Back -->
                            <Button
                                size="sm"
                                type="light"
                                v-if="goBack"
                                :action="goBack">
                                <span>{{ goBackButtonText }}</span>
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

                        <div v-if="failedImports.length" class="mx-4 mt-8">

                            <div class="flex items-center justify-center space-x-2 pt-4 mb-4 border-t border-gray-300 border-dashed">
                                <CircleAlert size="16"></CircleAlert>
                                <p class="text-sm font-bold">Failed Imports ({{ failedImports.length }} / {{ mappedRows.length }})</p>
                            </div>

                            <div class="w-full max-h-96 overflow-auto border border-blue-200 rounded-lg">

                                <table class="w-full text-left">

                                    <thead class="text-sm bg-blue-50">

                                        <tr>

                                            <th scope="col" class="text-sm text-center whitespace-nowrap p-3 border border-gray-200">
                                                <div>Row</div>
                                            </th>

                                            <th scope="col" class="text-sm whitespace-nowrap p-3 border border-gray-200">
                                                <div>Error Messages</div>
                                            </th>

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
                                            v-for="(failedRow, rowIndex) in failedMappedRows">

                                            <td class="text-sm text-center whitespace-nowrap p-3 border border-gray-200">
                                                {{ failedRow.row }}
                                            </td>

                                            <td class="text-sm text-red-500 p-3 border border-gray-200">
                                                <ul>
                                                    <li v-for="message in failedRow.messages" :key="message" class="whitespace-nowrap">{{ message }}</li>
                                                </ul>
                                            </td>

                                            <td
                                                :key="column.name"
                                                v-for="column in includedColumns"
                                                class="text-sm whitespace-nowrap p-3 border border-gray-200">
                                                {{ failedRow[column.name.toLowerCase()] || '' }}
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

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
    import { Check, CircleAlert, RotateCcw, ArrowLeft, ArrowRight } from 'lucide-vue-next';

    export default {
        inject: ['notificationState'],
        components: {
            Check, CircleAlert, Input, Button, Select,
        },
        props: {
            heading: {
                type: String,
                default: 'Import'
            },
            instruction: {
                type: String,
                default: null
            },
            submitButtonText: {
                type: String,
                default: 'Import'
            },
            sampleFile: {
                type: String,
                default: '\csvs\example.csv'
            },
            endpoint: {
                type: String,
                default: '/api/example/import'
            },
            formData: {
                type: Object,
                default: () => {}
            },
            columns: {
                type: Array,
                default: () => []
            },
            goBack: {
                type: Function,
                default: null
            },
            goBackButtonText: {
                type: String,
                default: 'Go Back'
            },
            fileName: {
                type: String,
                default: 'example'
            },
        },
        emits: ['imported'],
        data() {
            return {
                Check,
                step: 1,
                ArrowLeft,
                RotateCcw,
                ArrowRight,
                csvFile: [],
                csvColumns: [],
                imported: false,
                parsedCsvData: [],
                failedImports: [],
                csvFileReady: false,
                steps: [
                    { name: 'Upload' },
                    { name: 'Map Columns' },
                    { name: 'Review' }
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
            mappedRows() {
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
            },
            failedMappedRows() {
                return this.failedImports.map(failedImport => {
                    // Map the failed row data using the corresponding index from parsedCsvData (row + 1 to account for header)
                    const rowData = this.parsedCsvData[failedImport.row] || [];
                    const mappedRow = {};
                    this.csvColumns.forEach((csvColumn, index) => {
                        if (csvColumn.include && csvColumn.mappedTo) {
                            mappedRow[csvColumn.mappedTo] = rowData[index] || '';
                        }
                    });
                    return {
                        row: failedImport.row, // Store the 0-based row index
                        messages: failedImport.messages, // Include the error messages
                        ...mappedRow // Include the mapped row data
                    };
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
            escapeCsvValue(value) {
                if (value == null) return '';
                value = String(value);
                // Escape quotes by doubling them
                value = value.replace(/"/g, '""');
                // Wrap in quotes if contains comma, newline, or quote
                if (/[",\n]/.test(value)) {
                    value = `"${value}"`;
                }
                return value;
            },
            async importCsvFile() {

                try{

                    if(this.isImporting) return;

                    this.isImporting = true;

                    // Create CSV headers
                    const headers = this.includedColumns.map(col => col.name);

                    // Create CSV rows
                    const rows = this.mappedRows.map(row =>
                        headers.map(header => this.escapeCsvValue(row[header.toLowerCase()])).join(',')
                    );

                    const csvContent = [headers.join(','), ...rows].join('\n');

                    // Create FormData and Blob
                    const formData = new FormData();
                    const blob = new Blob([csvContent], { type: 'text/csv' });
                    formData.append('file', blob, `${this.fileName}.csv`);

                    Object.entries(this.formData).forEach(([key, value]) => {
                        formData.append(key, value);
                    });

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    const response = await axios.post(this.endpoint, formData, config);

                    this.imported = true;
                    this.failedImports = response.data.errors;
                    this.notificationState.showSuccessNotification(response.data.message);

                    this.$emit('imported');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while importing products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to import products:', error);
                    this.imported = false;
                } finally {
                    this.isImporting = false;
                }
            }
        }
    };

</script>
