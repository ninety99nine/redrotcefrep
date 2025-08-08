<template>

    <div class="pt-24 px-8 relative select-none">

        <!-- Clouds Image -->
        <img :src="'/images/clouds.png'" class="absolute bottom-0">

        <div class="relative bg-white/80 p-4 rounded-md">

            <h1 class="text-lg text-gray-700 font-semibold mb-4">Bulk Edit</h1>

            <!-- Products Table -->
            <Table
                @search="search"
                resource="products"
                :columns="columns"
                :perPage="perPage"
                @paginate="paginate"
                @refresh="showProducts"
                :searchTerm="searchTerm"
                :pagination="pagination"
                :isLoading="isLoadingProducts"
                @updatedColumns="updatedColumns"
                @updatedFilters="updatedFilters"
                @updatedSorting="updatedSorting"
                @updatedPerPage="updatedPerPage"
                :filterExpressions="filterExpressions"
                :sortingExpressions="sortingExpressions"
                searchPlaceholder="Search by product, variant names or SKU">

                <template #afterRefreshButton>

                    <div class="flex items-center space-x-2">

                        <!-- Add Product Button -->
                        <Button
                            size="xs"
                            type="primary"
                            :leftIcon="Plus"
                            :action="onAddProduct">
                            <span>Add Product</span>
                        </Button>

                    </div>

                </template>

                <!-- Select Action -->
                <template #belowToolbar>

                    <div :class="[{ 'hidden' : totalCheckedRows == 0 }, 'bg-gray-50 border border-gray-200 flex items-center mb-2 p-4 rounded-lg shadow space-x-2']">

                        <span class="text-sm">Actions: </span>

                        <!-- Action Trigger -->
                        <Dropdown
                            ref="actionDropdown"
                            dropdownClasses="w-72"
                            :options="bulkSelectionOptions">
                            <template #triggerText>
                                <span>Select Action ({{ `${totalCheckedRows} selected` }})</span>
                            </template>
                        </Dropdown>

                    </div>

                </template>

                <!-- Table Head -->
                <template #head>

                    <tr>

                        <!-- Checkbox -->
                        <th scope="col" class="whitespace-nowrap align-center px-4 py-4">
                            <Input
                                type="checkbox"
                                v-model="selectAll">
                            </Input>
                        </th>

                        <!-- Table Column Names -->
                        <template
                            :key="index"
                            v-for="(column, index) in columns">

                            <th
                                scope="col"
                                v-if="column.active"
                                class="whitespace-nowrap align-center pr-4 py-4">
                                {{ column.name }}
                            </th>

                        </template>

                        <!-- Actions -->
                        <th scope="col" class="whitespace-nowrap align-center pr-4 py-4"></th>

                    </tr>

                </template>

                <!-- Table Body -->
                <template #body>

                    <tr
                        :key="productForm.id"
                        v-for="(productForm, productIndex) in productForms"
                        :class="[checkedRows[productForm.id] ? 'bg-blue-50' : 'bg-white hover:bg-gray-50', 'group cursor-pointer border-b border-blue-100']">

                        <template
                            :key="columnIndex"
                            v-for="(column, columnIndex) in columns">

                            <!-- Checkbox -->
                            <td
                                @click.stop
                                v-if="columnIndex == 0"
                                class="whitespace-nowrap align-center px-4 py-4">

                                <Input
                                    type="checkbox"
                                    v-model="checkedRows[productForm.id]">
                                </Input>

                            </td>

                            <template v-if="column.active">

                                <!-- Name -->
                                <td v-if="column.name == 'Name'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <div
                                        v-if="!productForm.is_variant"
                                        class="flex space-x-2 items-center">
                                        <div
                                            v-if="productForm.photo"
                                            class="flex items-center justify-center w-10 h-10">

                                            <img class="w-full max-h-full object-contain rounded-lg flex-shrink-0" :src="productForm.photo.path">

                                        </div>
                                        <Input
                                            type="text"
                                            class="min-w-40"
                                            v-model="productForms[productIndex].name"
                                            @input="captureChange(productIndex, 'Name changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.name`)">
                                        </Input>
                                    </div>

                                </td>

                                <!-- Variant -->
                                <td v-else-if="column.name == 'Variant'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <div
                                        v-if="productForm.is_variant"
                                        class="flex space-x-2 items-center">
                                        <div
                                            v-if="productForm.photo"
                                            class="flex items-center justify-center w-10 h-10">

                                            <img class="w-full max-h-full object-contain rounded-lg flex-shrink-0" :src="productForm.photo.path">

                                        </div>
                                        <Input
                                            type="text"
                                            class="min-w-40"
                                            v-model="productForms[productIndex].name"
                                            @input="captureChange(productIndex, 'Name changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.name`)">
                                        </Input>
                                    </div>

                                    <div
                                        class="flex items-end space-x-1"
                                        v-else-if="productForm.has_variants">
                                        <Pill type="success" size="xs">{{ `${productForm.total_variants} ${productForm.total_variants == 1 ? 'variant' : 'variants'}` }}</Pill>
                                        <CornerRightDown size="16" class="text-gray-400"></CornerRightDown>
                                    </div>

                                </td>

                                <!-- Free -->
                                <td v-else-if="column.name == 'Free'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex justify-center">

                                        <Switch
                                            size="xs"
                                            v-model="productForms[productIndex].is_free"
                                            @change="captureChange(productIndex, 'Free status changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.is_free`)"
                                            v-if="!productForms[productIndex].has_variants || productForms[productIndex].is_variant">
                                        </Switch>

                                    </div>

                                </td>

                                <!-- Estimated Price -->
                                <td v-else-if="column.name == 'Estimated Price'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex justify-center">

                                        <Switch
                                            size="xs"
                                            v-model="productForms[productIndex].is_estimated_price"
                                            @change="captureChange(productIndex, 'Estimated price status changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.is_estimated_price`)"
                                            v-if="(!productForms[productIndex].has_variants || productForms[productIndex].is_variant) && !productForms[productIndex].is_free">
                                        </Switch>

                                    </div>

                                </td>

                                <!-- Regular Price -->
                                <td v-else-if="column.name == 'Regular Price'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="money"
                                        class="min-w-28"
                                        :currency="store.currency"
                                        v-model="productForms[productIndex].unit_regular_price"
                                        @input="captureChange(productIndex, 'Regular price changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.unit_regular_price`)"
                                        :tooltipContent="productForms[productIndex].is_free ? 'Disabled because pricing is set to Free' : null"
                                        v-if="(!productForms[productIndex].has_variants || productForms[productIndex].is_variant) && !productForms[productIndex].is_free">
                                    </Input>

                                </td>

                                <!-- Sale Price -->
                                <td v-else-if="column.name == 'Sale Price'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="money"
                                        class="min-w-28"
                                        :currency="store.currency"
                                        v-model="productForms[productIndex].unit_sale_price"
                                        @input="captureChange(productIndex, 'Sale price changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.unit_sale_price`)"
                                        v-if="(!productForms[productIndex].has_variants || productForms[productIndex].is_variant) && !productForms[productIndex].is_free">
                                    </Input>

                                </td>

                                <!-- Cost Price -->
                                <td v-else-if="column.name == 'Cost Price'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="money"
                                        class="min-w-28"
                                        :currency="store.currency"
                                        v-model="productForms[productIndex].unit_cost_price"
                                        @input="captureChange(productIndex, 'Cost price changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.unit_cost_price`)"
                                        v-if="(!productForms[productIndex].has_variants || productForms[productIndex].is_variant) && !productForms[productIndex].is_free">
                                    </Input>

                                </td>

                                <!-- Visible -->
                                <td v-else-if="column.name == 'Visible'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex justify-center">

                                        <Switch
                                            size="xs"
                                            v-model="productForms[productIndex].visible"
                                            @change="captureChange(productIndex, 'Visibility status changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.visible`)"
                                            v-if="!productForms[productIndex].has_variants || productForms[productIndex].visible">
                                        </Switch>

                                    </div>

                                </td>

                                <!-- Type -->
                                <td v-else-if="column.name == 'Type'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Select
                                        :search="false"
                                        class="min-w-40"
                                        :options="productTypes"
                                        v-model="productForms[productIndex].type"
                                        v-if="!productForms[productIndex].is_variant"
                                        @change="captureChange(productIndex, 'Type changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.type`)">
                                    </Select>
                                    <div
                                        v-else
                                        class="flex justify-center">
                                        <Pill type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                    </div>

                                </td>

                                <!-- Download Link -->
                                <td v-else-if="column.name == 'Download Link'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="text"
                                        class="min-w-60"
                                        v-model="productForms[productIndex].download_link"
                                        @input="captureChange(productIndex, 'Download link changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.download_link`)"
                                        v-if="!productForms[productIndex].is_variant && productForms[productIndex].type == 'digital'">
                                    </Input>
                                    <div
                                        class="flex justify-center"
                                        v-else>
                                        <Pill v-if="!productForms[productIndex].is_variant && productForms[productIndex].type != 'digital'" type="light" size="xs">not applicable</Pill>
                                        <Pill v-else type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                    </div>

                                </td>

                                <!-- Sku -->
                                <td v-else-if="column.name == 'Sku'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="text"
                                        class="min-w-40"
                                        v-model="productForms[productIndex].sku"
                                        @input="captureChange(productIndex, 'SKU changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.sku`)">
                                    </Input>

                                </td>

                                <!-- Barcode -->
                                <td v-else-if="column.name == 'Barcode'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="text"
                                        class="min-w-40"
                                        v-model="productForms[productIndex].barcode"
                                        @input="captureChange(productIndex, 'Barcode changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.barcode`)">
                                    </Input>

                                </td>

                                <!-- Show Description -->
                                <td v-else-if="column.name == 'Show Description'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex justify-center">

                                        <Switch
                                            size="xs"
                                            v-if="!productForms[productIndex].is_variant"
                                            v-model="productForms[productIndex].show_description"
                                            @change="captureChange(productIndex, 'Show Description status changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.show_description`)">
                                        </Switch>
                                        <div
                                            class="flex justify-center"
                                            v-else-if="productForms[productIndex].is_variant">
                                            <Pill type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                        </div>

                                    </div>

                                </td>

                                <!-- Description -->
                                <td v-else-if="column.name == 'Description'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="text"
                                        class="min-w-60"
                                        v-model="productForms[productIndex].description"
                                        @input="captureChange(productIndex, 'Description changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.description`)"
                                        v-if="!productForms[productIndex].is_variant && productForms[productIndex].show_description">
                                    </Input>
                                    <div
                                        v-else
                                        class="flex justify-center">
                                        <Pill v-if="productForms[productIndex].is_variant" type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                        <Pill v-else type="light" size="xs" :rightIcon="EyeOff" rightIconClass="ml-1">hidden</Pill>
                                    </div>

                                </td>

                                <!-- Weight -->
                                <td v-else-if="column.name == 'Weight'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="number"
                                        class="min-w-20"
                                        v-model="productForms[productIndex].unit_weight"
                                        @change="captureChange(productIndex, 'Weight changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.unit_weight`)"
                                        v-if="!productForms[productIndex].has_variants || productForms[productIndex].is_variant">
                                    </Input>
                                    <div
                                        v-else
                                        class="flex justify-center">
                                        <Pill type="light" size="xs" :rightIcon="CornerRightDown" rightIconClass="ml-1" class="opacity-50">see {{ productForm.total_variants == 1 ? 'variant' : 'variants' }}</Pill>
                                    </div>

                                </td>

                                <!-- Tax Override -->
                                <td v-else-if="column.name == 'Tax Override'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex justify-center">

                                        <Switch
                                            size="xs"
                                            v-if="!productForms[productIndex].is_variant"
                                            v-model="productForms[productIndex].tax_overide"
                                            @change="captureChange(productIndex, 'Tax override status changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.tax_overide`)">
                                        </Switch>
                                        <div
                                            class="flex justify-center"
                                            v-else-if="productForms[productIndex].is_variant">
                                            <Pill type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                        </div>

                                    </div>

                                </td>

                                <!-- Tax Override Amount -->
                                <td v-else-if="column.name == 'Tax Override Amount'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="money"
                                        class="min-w-28"
                                        :currency="store.currency"
                                        v-model="productForms[productIndex].tax_overide_amount"
                                        @change="captureChange(productIndex, 'Tax override amount changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.tax_overide_amount`)"
                                        v-if="!productForms[productIndex].is_variant && productForms[productIndex].tax_overide">
                                    </Input>
                                    <div
                                        class="flex justify-center"
                                        v-else>
                                        <Pill v-if="productForms[productIndex].is_variant" type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                        <Pill v-else type="light" size="xs" rightIconClass="ml-1">not applicable</Pill>
                                    </div>

                                </td>

                                <!-- Show Price Per Unit -->
                                <td v-else-if="column.name == 'Show Price Per Unit'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex justify-center">

                                        <Switch
                                            size="xs"
                                            v-model="productForms[productIndex].show_price_per_unit"
                                            @change="captureChange(productIndex, 'Price per unit status changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.show_price_per_unit`)"
                                            v-if="!productForms[productIndex].has_variants || productForms[productIndex].is_variant">
                                        </Switch>
                                        <div
                                            v-else
                                            class="flex justify-center">
                                            <Pill type="light" size="xs" :rightIcon="CornerRightDown" rightIconClass="ml-1" class="opacity-50">see {{ productForm.total_variants == 1 ? 'variant' : 'variants' }}</Pill>
                                        </div>

                                    </div>

                                </td>

                                <!-- Unit Value -->
                                <td v-else-if="column.name == 'Unit Value'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="number"
                                        class="min-w-20"
                                        v-model="productForms[productIndex].unit_value"
                                        @change="captureChange(productIndex, 'Unit value changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.unit_value`)"
                                        v-if="(!productForms[productIndex].has_variants || productForms[productIndex].is_variant) && productForms[productIndex].show_price_per_unit">
                                    </Input>
                                    <div
                                        class="flex justify-center"
                                        v-else>
                                        <Pill v-if="productForms[productIndex].has_variants" type="light" size="xs" :rightIcon="CornerRightDown" rightIconClass="ml-1" class="opacity-50">see {{ productForm.total_variants == 1 ? 'variant' : 'variants' }}</Pill>
                                        <Pill v-else type="light" size="xs">not applicable</Pill>
                                    </div>

                                </td>

                                <!-- Unit Type -->
                                <td v-else-if="column.name == 'Unit Type'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Select
                                        :search="false"
                                        class="min-w-40"
                                        :options="pricePerUnitTypes"
                                        v-model="productForms[productIndex].unit_type"
                                        @change="captureChange(productIndex, 'Unit type changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.unit_type`)"
                                        v-if="(!productForms[productIndex].has_variants || productForms[productIndex].is_variant) && productForms[productIndex].show_price_per_unit">
                                    </Select>
                                    <div
                                        class="flex justify-center"
                                        v-else>
                                        <Pill v-if="productForms[productIndex].has_variants" type="light" size="xs" :rightIcon="CornerRightDown" rightIconClass="ml-1" class="opacity-50">see {{ productForm.total_variants == 1 ? 'variant' : 'variants' }}</Pill>
                                        <Pill v-else type="light" size="xs">not applicable</Pill>
                                    </div>

                                </td>

                                <!-- Set Daily Capacity -->
                                <td v-else-if="column.name == 'Set Daily Capacity'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex justify-center">

                                        <Switch
                                            size="xs"
                                            v-if="!productForms[productIndex].is_variant"
                                            v-model="productForms[productIndex].set_daily_capacity"
                                            @change="captureChange(productIndex, 'Daily capacity status changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.set_daily_capacity`)">
                                        </Switch>
                                        <div
                                            v-else
                                            class="flex justify-center">
                                            <Pill v-if="productForms[productIndex].is_variant" type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                            <Pill v-else type="light" size="xs" rightIconClass="ml-1">none</Pill>
                                        </div>

                                    </div>

                                </td>

                                <!-- Daily Capacity -->
                                <td v-else-if="column.name == 'Daily Capacity'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="number"
                                        class="min-w-20"
                                        v-model="productForms[productIndex].daily_capacity"
                                        @change="captureChange(productIndex, 'Daily capacity changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.daily_capacity`)"
                                        v-if="!productForms[productIndex].is_variant && productForms[productIndex].set_daily_capacity">
                                    </Input>
                                    <div
                                        v-else
                                        class="flex justify-center">
                                        <Pill v-if="productForms[productIndex].is_variant" type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                        <Pill v-else type="light" size="xs" rightIconClass="ml-1">none</Pill>
                                    </div>

                                </td>

                                <!-- Stock Type -->
                                <td v-else-if="column.name == 'Stock Type'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Select
                                        :search="false"
                                        class="min-w-40"
                                        :options="stockQuantityTypes"
                                        v-if="!productForms[productIndex].is_variant"
                                        v-model="productForms[productIndex].stock_quantity_type"
                                        @change="captureChange(productIndex, 'Stock quantity type changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.stock_quantity_type`)">
                                    </Select>
                                    <div
                                        v-else
                                        class="flex justify-center">
                                        <Pill type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                    </div>

                                </td>

                                <!-- Stock Quantity -->
                                <td v-else-if="column.name == 'Stock Quantity'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="number"
                                        class="min-w-20"
                                        v-model="productForms[productIndex].stock_quantity"
                                        @change="captureChange(productIndex, 'Stock quantity changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.stock_quantity`)"
                                        v-if="(!productForms[productIndex].has_variants && productForms[productIndex].stock_quantity_type == 'limited') || (productForms[productIndex].is_variant && getParent(productForms[productIndex]).stock_quantity_type == 'limited')">
                                    </Input>
                                    <div
                                        v-else
                                        class="flex justify-center">
                                        <Pill v-if="productForms[productIndex].has_variants" type="light" size="xs" :rightIcon="CornerRightDown" rightIconClass="ml-1" class="opacity-50">see {{ productForm.total_variants == 1 ? 'variant' : 'variants' }}</Pill>
                                        <Pill v-else type="light" size="xs">not applicable</Pill>
                                    </div>

                                </td>

                                <!-- Set Min Order Quantity -->
                                <td v-else-if="column.name == 'Set Min Order Quantity'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex justify-center">

                                        <Switch
                                            size="xs"
                                            v-if="!productForms[productIndex].is_variant"
                                            v-model="productForms[productIndex].set_min_order_quantity"
                                            @change="captureChange(productIndex, 'Min order quantity status changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.set_min_order_quantity`)">
                                        </Switch>
                                        <div
                                            v-else
                                            class="flex justify-center">
                                            <Pill v-if="productForms[productIndex].is_variant" type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                            <Pill v-else type="light" size="xs" rightIconClass="ml-1">none</Pill>
                                        </div>
                                    </div>

                                </td>

                                <!-- Min Order Quantity -->
                                <td v-else-if="column.name == 'Min Order Quantity'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="number"
                                        class="min-w-20"
                                        v-model="productForms[productIndex].min_order_quantity"
                                        :disabled="!productForms[productIndex].set_min_order_quantity"
                                        @change="captureChange(productIndex, 'Min order quantity changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.min_order_quantity`)"
                                        v-if="!productForms[productIndex].is_variant && productForms[productIndex].set_min_order_quantity">
                                    </Input>
                                    <div
                                        v-else
                                        class="flex justify-center">
                                        <Pill v-if="productForms[productIndex].is_variant" type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                        <Pill v-else type="light" size="xs" rightIconClass="ml-1">none</Pill>
                                    </div>

                                </td>

                                <!-- Set Max Order Quantity -->
                                <td v-else-if="column.name == 'Set Max Order Quantity'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">

                                    <div class="flex justify-center">

                                        <Switch
                                            size="xs"
                                            v-if="!productForms[productIndex].is_variant"
                                            v-model="productForms[productIndex].set_max_order_quantity"
                                            @change="captureChange(productIndex, 'Max order quantity status changed')"
                                            :errorText="formState.getFormError(`product.${productIndex}.set_max_order_quantity`)">
                                        </Switch>
                                        <div
                                            v-else
                                            class="flex justify-center">
                                            <Pill v-if="productForms[productIndex].is_variant" type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                            <Pill v-else type="light" size="xs" rightIconClass="ml-1">none</Pill>
                                        </div>

                                    </div>

                                </td>

                                <!-- Max Order Quantity -->
                                <td v-else-if="column.name == 'Max Order Quantity'" class="whitespace-nowrap align-center pr-4 py-4">

                                    <Input
                                        type="number"
                                        class="min-w-20"
                                        v-model="productForms[productIndex].max_order_quantity"
                                        :disabled="!productForms[productIndex].set_max_order_quantity"
                                        @change="captureChange(productIndex, 'Max order quantity changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.max_order_quantity`)"
                                        v-if="!productForms[productIndex].is_variant && productForms[productIndex].set_max_order_quantity">
                                    </Input>
                                    <div
                                        v-else
                                        class="flex justify-center">
                                        <Pill v-if="productForms[productIndex].is_variant" type="light" size="xs" :rightIcon="CornerRightUp" rightIconClass="ml-1" class="opacity-50">see parent</Pill>
                                        <Pill v-else type="light" size="xs" rightIconClass="ml-1">none</Pill>
                                    </div>

                                </td>

                                <!-- Position -->
                                <td v-else-if="column.name == 'Position'" class="align-center pr-4 py-4 text-sm">
                                    <Input
                                        type="number"
                                        class="min-w-20"
                                        v-model="productForms[productIndex].position"
                                        @change="captureChange(productIndex, 'Position changed')"
                                        :errorText="formState.getFormError(`product.${productIndex}.position`)">
                                    </Input>
                                </td>

                            </template>

                            <!-- Actions -->
                            <td v-if="columnIndex == (columns.length - 1)" class="align-center pr-4 py-4">

                                <div class="flex items-center space-x-4">

                                    <!-- View Button -->
                                    <span v-if="!isDeletingProduct(productForms[productIndex])" @click.stop.prevent="onView(productForms[productIndex])" class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">View</span>

                                    <!-- Deleting Loader -->
                                    <Loader v-if="isDeletingProduct(productForms[productIndex])" type="danger">
                                        <span class="text-xs ml-2">Deleting...</span>
                                    </Loader>

                                    <!-- Delete Button -->
                                    <span v-else @click.stop.prevent="showDeleteConfirmationModal(productForms[productIndex])" class="text-sm font-medium text-red-600 dark:text-red-500 hover:underline">Delete</span>

                                </div>

                            </td>

                        </template>

                    </tr>

                </template>

                <!-- No Products -->
                <template #noResults>

                    <div class="flex justify-between items-end p-10 bg-blue-50 border-t border-blue-200">

                        <div>

                            <h1 class="text-2xl font-bold mb-4">
                                Ready For Your First Sale?
                            </h1>

                            <p class="text-sm text-gray-500">
                                Your products will appear here once customers start shopping.
                            </p>

                        </div>

                        <div>

                            <!-- Add Button -->
                            <Button
                                size="lg"
                                type="primary"
                                :leftIcon="Plus"
                                :action="onAddProduct">
                                <span>Add Product</span>
                            </Button>

                        </div>

                    </div>

                </template>

            </Table>

        </div>

        <!-- Delete Products -->
        <Modal
            approveType="danger"
            ref="deleteProductsModal"
            :leftApproveIcon="Trash2"
            :approveAction="deleteProducts"
            :approveLoading="isDeletingProducts"
            :approveText="totalCheckedRows == 1 ? 'Delete Product' : 'Delete Products'">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed pb-4 mb-4">Delete Products</p>

                <div class="flex space-x-2 items-center p-4 text-xs bg-red-50 border border-red-200 border-dashed rounded-lg mb-8">

                    <svg class="w-6 h-6 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>

                    <span class="text-red-500">Are you sure you want to delete {{ totalCheckedRows > 1 ? `these ${totalCheckedRows} products` : 'this product' }}?</span>

                </div>

            </template>

        </Modal>

        <!-- Confirm Delete Product -->
        <Modal
            approveType="danger"
            ref="deleteProductModal"
            :leftApproveIcon="Trash2"
            approveText="Delete Product"
            :approveAction="deleteProduct"
            :approveLoading="isDeletingProduct(deletableProduct)">

            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Delete</p>
                <p v-if="deletableProduct" class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ deletableProduct.name }}</span>?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import axios from 'axios';
    import isEqual from 'lodash/isEqual';
    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import Switch from '@Partials/Switch.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import Table from '@Partials/table/Table.vue';
    import { Plus, EyeOff, Trash2, CornerRightUp, CornerRightDown } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'productState', 'storeState', 'changeHistoryState', 'notificationState'],
        components: {
            CornerRightUp, CornerRightDown, Pill, Input, Modal, Loader, Button, Switch, Select, Popover, Dropdown, Table
        },
        data() {
            return {
                Plus,
                Trash2,
                EyeOff,
                CornerRightUp,
                CornerRightDown,

                perPage: '15',
                checkedRows: [],
                pagination: null,
                searchTerm: null,
                selectAll: false,
                latestRequestId: 0,
                filterExpressions: [],
                sortingExpressions: [],
                deletableProduct: null,
                cancelTokenSource: null,
                isLoadingProducts: false,
                isUpdatingProducts: false,
                isDeletingProducIds: [],
                includeProductFieldNames: true,
                columns: this.prepareColumns(),
                bulkSelectionOptions: [
                    {
                        label: 'Show',
                        action: () => this.updateProducts('show')
                    },
                    {
                        label: 'Hide',
                        action: () => this.updateProducts('hide')
                    },
                    {
                        label: 'Add to category',
                        action: null
                    },
                    {
                        label: 'Remove from category',
                        action: null
                    },
                    {
                        label: 'Delete',
                        action: this.showDeleteProductsModal,
                    }
                ],
                stockQuantityTypes: [
                    { label: 'Unlimited', value: 'unlimited'},
                    { label: 'Limited', value: 'limited'},
                ],
                productTypes: [
                    { label: 'Physical', value: 'physical'},
                    { label: 'Digital', value: 'digital'},
                    { label: 'Booking', value: 'booking'},
                    { label: 'Subscription', value: 'subscription'},
                    { label: 'Other', value: 'other'}
                ],
                pricePerUnitTypes: [
                    { label: 'G', value: 'g'},
                    { label: 'KG', value: 'kg'},
                    { label: 'L', value: 'l'},
                    { label: 'ML', value: 'ml'},
                    { label: 'PSC', value: 'psc'},
                    { label: 'PAX', value: 'pax'},
                    { label: 'PERSON', value: 'person'},
                    { label: 'ROOM', value: 'room'},
                    { label: 'PACK', value: 'pack'},
                    { label: 'QTY', value: 'qty'},
                    { label: 'LBS', value: 'lbs'},
                    { label: 'HOUR', value: 'hour'},
                    { label: 'BOX', value: 'box'},
                ],
            }
        },
        watch: {
            store(newValue) {
                if(newValue) {
                    this.showProducts();
                }
            },
            selectAll(newValue) {
                this.checkedRows = this.productForms.reduce((acc, productForm) => {
                    acc[productForm.id] = newValue;
                    return acc;
                }, {});
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasSearchTerm() {
                return this.searchTerm != null && this.searchTerm.trim() != '';
            },
            isDeletingProducts() {
                return this.isDeletingProducIds.length > 0;
            },
            totalCheckedRows() {
                return Object.values(this.checkedRows).filter(checked => checked).length;
            },
            hasFilterExpressions() {
                return this.filterExpressions.length > 0;
            },
            hasSortingExpressions() {
                return this.sortingExpressions.length > 0;
            },
            productForms() {
                return this.productState.productForms;
            },
        },
        methods: {
            prepareColumns() {
                const columnNames = ['Name', 'Variant', 'Free', 'Estimated Price', 'Regular Price', 'Sale Price', 'Cost Price', 'Visible', 'Type', 'Download Link', 'Sku', 'Barcode', 'Show Description', 'Description', 'Weight', 'Tax Override', 'Tax Override Amount', 'Show Price Per Unit', 'Unit Value', 'Unit Type', 'Set Daily Capacity', 'Daily Capacity', 'Stock Type', 'Stock Quantity', 'Set Min Order Quantity', 'Min Order Quantity', 'Set Max Order Quantity', 'Max Order Quantity', 'Position'];
                const defaultColumnNames  = ['Name', 'Variant', 'Free', 'Estimated Price', 'Regular Price', 'Sale Price', 'Cost Price', 'Visible', 'Type', 'Download Link', 'Sku', 'Barcode', 'Show Description', 'Description', 'Weight', 'Tax Override', 'Tax Override Amount', 'Show Price Per Unit', 'Unit Value', 'Unit Type', 'Set Daily Capacity', 'Daily Capacity', 'Stock Type', 'Stock Quantity', 'Set Min Order Quantity', 'Min Order Quantity', 'Set Max Order Quantity', 'Max Order Quantity', 'Position'];

                return columnNames.map(name => ({
                    name,
                    active: defaultColumnNames.includes(name),
                    priority: defaultColumnNames.includes(name)
                }));
            },
            showDeleteProductsModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.deleteProductsModal.showModal();
            },
            showDeleteConfirmationModal(product) {
                this.deletableProduct = product;
                this.$refs.deleteProductModal.showModal();
            },
            isDeletingProduct(product) {
                if(product == null) return false;
                return this.isDeletingProducIds.findIndex((id) => id == product.id) != -1;
            },
            onView(product) {
                this.$router.push({
                    name: 'edit-product',
                    params: {
                        product_id: product.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            onAddProduct() {
                this.$router.push({
                    name: 'create-product',
                    query: { store_id: this.store.id }
                });
            },
            getParent(productForm) {
                return this.productForms.find(currProductForm => currProductForm.id == productForm.parent_product_id);
            },
            captureChange(productIndex, actionName) {
                this.productForms[productIndex].modified = true;
                this.productState.saveStateDebounced(actionName);
            },
            paginate(page) {
                this.showProducts(page);
            },
            search(searchTerm) {
                this.searchTerm = searchTerm;
                this.showProducts();
            },
            updatedColumns(columns) {
                this.columns = columns;
            },
            updatedFilters(filters) {
                const newFilterExpressions = filters.map((filter) => filter.expression);
                if(!isEqual(this.filterExpressions, newFilterExpressions)) {
                    this.filterExpressions = newFilterExpressions;
                    this.showProducts();
                }
            },
            updatedSorting(sorting) {
                const newSortingExpressions = sorting.map((sort) => sort.expression);
                if(!isEqual(this.sortingExpressions, newSortingExpressions)) {
                    this.sortingExpressions = newSortingExpressions;
                    this.showProducts();
                }
            },
            updatedPerPage(perPage) {
                this.perPage = perPage;
                this.showProducts();
            },
            async showProducts(page = 1) {

                const currentRequestId = ++this.latestRequestId;

                try {

                    this.isLoadingProducts = true;

                    if (this.cancelTokenSource) {
                        this.cancelTokenSource.cancel('Request superseded by a newer one'); // Cancel previous request if it exists
                    }

                    this.cancelTokenSource = axios.CancelToken.source(); // Create a new cancel token source

                    const config = {
                        params: {
                            page: page,
                            per_page: this.perPage,
                            store_id: this.store.id,
                            association: 'team member',
                            _relationships: ['photo', 'variants.photo'].join(',')
                        },
                        cancelToken: this.cancelTokenSource.token // Attach cancel token
                    }

                    if(this.hasSearchTerm) config.params['search'] = this.searchTerm;

                    if(this.hasFilterExpressions) {
                        config.params['_filters'] = this.filterExpressions.join('|');
                    }

                    if(this.hasSortingExpressions) {
                        config.params['_sort'] = this.sortingExpressions.join('|');
                    }

                    const response = await axios.get(`/api/products`, config);

                    // Only process response if it matches the latest request
                    if (currentRequestId !== this.latestRequestId) return;

                    this.pagination = response.data;
                    const products = this.pagination.data;

                    this.productState.setProductForms(products);

                    this.checkedRows = this.productForms.reduce((acc, productForm) => {
                        acc[productForm.id] = false;
                        return acc;
                    }, {});

                } catch (error) {

                    if (axios.isCancel(error)) return; // Ignore canceled requests

                    if (currentRequestId !== this.latestRequestId) return;

                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch products:', error);
                } finally {
                    if (currentRequestId !== this.latestRequestId) return;
                    this.isLoadingProducts = false;
                    this.cancelTokenSource = null;
                }

            },
            async updateProducts(type = null) {

                try {

                    if(this.isUpdatingProducts) return;

                    const data = {
                        store_id: this.store.id
                    };

                    const isHiding = type == 'hide';
                    const isShowing = type == 'show';

                    if(isHiding || isShowing) {
                        data['visible'] = isShowing;
                        data['products'] = this.productForms.filter(productForm => this.checkedRows[productForm.id] && productForm.visible != data['visible']).map(productForm => { return { id: productForm.id } });

                        if(data['products'].length == 0) {
                            this.notificationState.showSuccessNotification(isShowing ? 'Products are visible' : 'Products are hidden');
                        }
                    }else{
                        data['products'] = this.productForms.filter(productForm => productForm.modified);
                    }

                    if(data['products'].length > 0) {

                        this.isUpdatingProducts = true;

                        await axios.put(`/api/products`, data);

                        this.showProducts();

                        this.notificationState.showSuccessNotification('Products updated');

                    }

                    // Uncheck only the related rows
                    data['products'].forEach(productForm => {
                        if (this.checkedRows[productForm.id] !== undefined) {
                            this.checkedRows[productForm.id] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update product:', error);
                } finally {
                    this.isUpdatingProducts = false;
                }

            },
            async deleteProducts() {

                try {

                    const productFormIds = this.productForms.filter(productForm => this.checkedRows[productForm.id]).map(productForm => productForm.id)
                                                .filter(productFormId => !this.isDeletingProducIds.includes(productFormId));

                    if (productFormIds.length === 0) return;

                    this.isDeletingProducIds.push(...productFormIds);

                    const config = {
                        data: {
                            store_id: this.store.id,
                            product_ids: productFormIds
                        }
                    }

                    await axios.delete(`/api/products`, config);

                    this.notificationState.showSuccessNotification(productFormIds == 1 ? 'Product deleted' : 'Products deleted');
                    this.productState.productForms = this.productForms.filter(productForm => !productFormIds.includes(productForm.id));
                    if(this.productForms.length == 0) this.showProducts();

                    this.isDeletingProducIds = this.isDeletingProducIds.filter(id => !productFormIds.includes(id));

                    // Uncheck only the related rows
                    productFormIds.forEach(productFormId => {
                        if (this.checkedRows[productFormId] !== undefined) {
                            this.checkedRows[productFormId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting products';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete products:', error);
                } finally {
                    this.$refs.deleteProductsModal.hideModal();
                }
            },
            async deleteProduct() {

                try {

                    if(this.isDeletingProducIds.includes(this.deletableProduct.id)) return;

                    this.isDeletingProducIds.push(this.deletableProduct.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/products/${this.deletableProduct.id}`, config);

                    this.notificationState.showSuccessNotification('Product deleted');
                    this.productState.productForms = this.productForms.filter(productForm => productForm.id != this.deletableProduct.id);
                    if(this.productForms.length == 0) this.showProducts();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete product:', error);
                } finally {
                    this.isDeletingProducIds.splice(this.isDeletingProducIds.findIndex((id) => id == this.deletableProduct.id), 1);
                    this.$refs.deleteProductModal.hideModal();
                }

            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton(this.onDiscard);
                this.changeHistoryState.addActionButton(
                    'Save Changes',
                    this.updateProducts,
                    'primary',
                    null
                );
            },
            setProductForms(productForms) {
                this.productState.productForms = productForms;
            }
        },
        created() {

            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setProductForms;
            }

            this.isLoadingProducts = true;
            this.searchTerm = this.$route.query.searchTerm;
            if(this.$route.query.filterExpressions) this.filterExpressions = this.$route.query.filterExpressions.split('|');
            if(this.$route.query.sortingExpressions) this.sortingExpressions = this.$route.query.sortingExpressions.split('|');
            if(this.store) this.showProducts();
        }
    };

</script>
