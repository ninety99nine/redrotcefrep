<template>

    <div class="pt-24 px-4">

        <div class="grid grid-cols-12 gap-4 mb-4">

            <div class="col-span-8">

                <div class="select-none bg-white rounded-lg p-4 mb-4">

                    <div class="flex items-center space-x-4">

                        <!-- Back Button -->
                        <Button
                            size="xs"
                            type="light"
                            :action="goBack"
                            :leftIcon="MoveLeft">
                            <span>Back</span>
                        </Button>

                        <div v-if="isLoadingStore || isLoadingProduct || (isEditing && !hasProduct)" class="flex items-center space-x-2">
                            <Skeleton width="w-40"></Skeleton>
                            <Skeleton width="w-4"></Skeleton>
                        </div>

                        <template v-else>

                            <!-- Heading -->
                            <div class="flex items-center space-x-1">

                                <h1 class="text-lg text-gray-700 font-semibold">
                                    {{ isCreating ? 'Add Product' : productForm.name || '...' }}
                                </h1>

                                <Popover content="Products are physical or non physical items that customers can place orders and pay for using your preferred payment methods" placement="top"></Popover>

                            </div>

                        </template>

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <!-- Name Input -->
                        <Input
                            type="text"
                            label="Name"
                            v-model="productForm.name"
                            placeholder="Standard Ticket"
                            :errorText="formState.getFormError('name')"
                            @input="productState.saveStateDebounced('Name changed')"
                            tooltipContent="The name of your product e.g Standard Ticket">
                        </Input>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                            <!-- Visibility Select -->
                            <Select
                                class="w-full"
                                :search="false"
                                label="Visibility"
                                :options="visibilityTypes"
                                v-model="productForm.visible"
                                :errorText="formState.getFormError('visible')"
                                @change="productState.saveStateDebounced('Visibility status changed')"
                                tooltipContent="Turn on if you want your product to be visible (Made available to customers)">
                            </Select>

                            <!-- Type Select -->
                            <Select
                                label="Type"
                                class="w-full"
                                :search="false"
                                :options="productTypes"
                                v-model="productForm.type"
                                :errorText="formState.getFormError('type')"
                                @change="productState.saveStateDebounced('Type changed')">
                            </Select>

                        </div>

                        <!-- Download Link Input -->
                        <Input
                            type="text"
                            label="Download Link"
                            externalLinkName="Learn more"
                            v-model="productForm.download_link"
                            v-if="productForm.type == 'digital'"
                            placeholder="https://example.com/download"
                            externalLinkUrl="https://example.com/download"
                            :errorText="formState.getFormError('download_link')"
                            tooltipContent="This is the link to download this resource"
                            @input="productState.saveStateDebounced('Download link changed')"
                            description="Confirm your order to show download link on invoice">
                        </Input>

                        <!-- Show Description Checkbox -->
                        <Input
                            type="checkbox"
                            inputLabel="Show Description"
                            v-model="productForm.show_description"
                            @change="productState.saveStateDebounced('Show description status changed')">
                        </Input>

                        <!-- Description Textarea -->
                        <Input
                            rows="2"
                            type="textarea"
                            label="Description"
                            v-model="productForm.description"
                            v-if="productForm.show_description"
                            placeholder="1 day show with popular artists"
                            :errorText="formState.getFormError('description')"
                            @input="productState.saveStateDebounced('Description changed')"
                            tooltipContent="Sweet and short description of your product e.g 1 day show with popular artists">
                        </Input>

                        <!-- Weight Input -->
                        <Input
                            type="text"
                            label="Weight"
                            v-if="!hasVariants"
                            v-model="productForm.unit_weight"
                            :errorText="formState.getFormError('unit_weight')"
                            @input="productState.saveStateDebounced('Weight changed')"
                            tooltipContent="The weight of the product. Useful for delivery calculations">
                            <template #suffix>
                                <span class="text-sm text-gray-400">{{ store?.weight_unit }}</span>
                            </template>
                        </Input>

                        <div
                            v-if="!hasVariants"
                            class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                            <!-- Sku Input -->
                            <Input
                                type="text"
                                label="SKU"
                                placeholder="std-ticket"
                                v-model="productForm.sku"
                                :errorText="formState.getFormError('sku')"
                                @input="productState.saveStateDebounced('SKU changed')"
                                tooltipContent="The stock keeping unit for this product. Useful for stock management">
                            </Input>

                            <!-- Barcode Input -->
                            <Input
                                type="text"
                                label="Barcode"
                                placeholder="123456789"
                                v-model="productForm.barcode"
                                :errorText="formState.getFormError('barcode')"
                                @input="productState.saveStateDebounced('Barcode changed')"
                                tooltipContent="The barcode for this product. Useful for stock management">
                            </Input>

                        </div>

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Pricing</p>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                            <!-- Is Free Switch -->
                            <Switch
                                size="xs"
                                suffixText="Is Free"
                                v-model="productForm.is_free"
                                :errorText="formState.getFormError('is_free')"
                                @change="productState.saveStateDebounced('Free status changed')"
                                tooltipContent="Turn on if you want your product to be made Free for customers">
                            </Switch>

                            <!-- Estimated Price Switch -->
                            <Switch
                                size="xs"
                                v-if="!productForm.is_free"
                                suffixText="Display as estimated price"
                                v-model="productForm.is_estimated_price"
                                :errorText="formState.getFormError('is_estimated_price')"
                                @change="productState.saveStateDebounced('Estimated price status changed')"
                                tooltipContent="The checkout page will display the price as an estimated price. Customers will confirm with you before payment">
                            </Switch>

                        </div>

                        <!-- Info Alert -->
                        <Alert
                            type="success"
                            v-if="productForm.is_free">
                            <template #description>
                                This product is <span class="font-bold">Free</span>
                            </template>
                        </Alert>

                        <template v-else>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                                <!-- Unit Regular Price Input -->
                                <Input
                                    type="money"
                                    label="Regular Price"
                                    :currency="store?.currency"
                                    v-model="productForm.unit_regular_price"
                                    :errorText="formState.getFormError('unit_regular_price')"
                                    @input="productState.saveStateDebounced('Regular price changed')"
                                    tooltipContent="Set the regular price for this product (How much the product is sold when its not on sale)">
                                </Input>

                                <!-- Unit Sale Price Input -->
                                <Input
                                    type="money"
                                    label="Sale Price"
                                    :currency="store?.currency"
                                    v-model="productForm.unit_sale_price"
                                    :errorText="formState.getFormError('unit_sale_price')"
                                    @input="productState.saveStateDebounced('Sale price changed')"
                                    tooltipContent="Set the sale price for this product (if the product is on sale)">
                                </Input>

                            </div>

                            <!-- Unit Cost Price Input -->
                            <Input
                                type="money"
                                label="Cost Price"
                                :currency="store?.currency"
                                description="Invisible to customers"
                                v-model="productForm.unit_cost_price"
                                :errorText="formState.getFormError('unit_cost_price')"
                                @input="productState.saveStateDebounced('Cost price changed')"
                                tooltipContent="Set the cost price for this product (if the product is on sale)">
                            </Input>

                            <!-- Price Per Unit Checkbox -->
                            <Input
                                type="checkbox"
                                inputLabel="Price per unit"
                                v-model="productForm.show_price_per_unit"
                                @change="productState.saveStateDebounced('Price per unit status changed')">
                            </Input>

                            <div v-if="productForm.show_price_per_unit" class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                                <!-- Unit Value -->
                                <Input
                                    type="number"
                                    label="Unit Value"
                                    v-model="productForm.unit_value"
                                    :errorText="formState.getFormError('unit_value')"
                                    @input="productState.saveStateDebounced('Unit value changed')">
                                </Input>

                                <!-- Unit Type -->
                                <Select
                                    class="w-full"
                                    :search="false"
                                    label="Unit Type"
                                    :options="pricePerUnitTypes"
                                    v-model="productForm.unit_type"
                                    :errorText="formState.getFormError('unit_type')"
                                    @change="productState.saveStateDebounced('Unit type changed')">
                                </Select>

                            </div>

                            <!-- Tax Overide Checkbox -->
                            <Input
                                type="checkbox"
                                inputLabel="Tax Overide"
                                v-model="productForm.tax_overide"
                                @change="productState.saveStateDebounced('Tax overide status changed')">
                            </Input>

                            <!-- Tax Overide Input -->
                            <Input
                                type="money"
                                :currency="store?.currency"
                                v-if="productForm.tax_overide"
                                v-model="productForm.tax_overide_amount"
                                :errorText="formState.getFormError('tax_overide_amount')"
                                @input="productState.saveStateDebounced('Tax overide amount changed')">
                            </Input>

                        </template>

                    </div>

                </div>

                <div :class="[{ 'bg-white rounded-lg p-4 space-y-4' : !hasVariants }, 'mb-4']">

                    <p :class="['text-lg text-gray-700 font-semibold', productForm.variants.length >= 2 ? 'mb-0' : 'mb-4', { 'pl-4' : hasVariants }]">Variants</p>

                    <div v-if="productForm.variants.length >= 2 && variantOptions.length" class="flex items-end space-x-4 mb-2">

                        <!-- Variant Options -->
                        <SelectTags
                            class="w-full"
                            :allowCustom="false"
                            :options="variantOptions"
                            v-model="selectedVariants"
                            placeholder="Add variant filter" />

                        <!-- Clear Filter -->
                        <Button
                            size="xs"
                            type="light"
                            :leftIcon="X"
                            :action="clearSelectedVariants">
                            <span>Clear Filter</span>
                        </Button>

                    </div>

                    <template
                        :key="variant.temporary_id"
                        v-for="(variant, index) in productForm.variants">

                        <div
                            class="relative mb-4"
                            v-if="isFilteredVariants(variant)">

                            <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                            <div
                                class="bg-white border border-gray-200 rounded-lg p-4">

                                <div class="flex items-center space-x-4 justify-end">

                                    <div class="flex items-center space-x-1 text-gray-400">
                                        <Split size="16"></Split>
                                        <span class="text-sm">variant {{ `${index + 1}` }}</span>
                                    </div>

                                    <!-- Remove Variant -->
                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeVariant(index)">
                                    </Button>

                                </div>

                                <div class="space-y-4 mb-4">

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                                        <div class="flex items-center space-x-4">

                                            <!-- Image -->
                                            <div
                                                v-if="productForm.variants[index].photos.length"
                                                class="flex items-center justify-center w-16 h-16">

                                                <img class="w-full max-h-full object-contain rounded-lg flex-shrink-0" :src="productForm.variants[index].photos[0].path">

                                            </div>

                                            <!-- Name Input -->
                                            <Input
                                                type="text"
                                                label="Name"
                                                class="w-full"
                                                placeholder="Standard Ticket"
                                                v-model="productForm.variants[index].name"
                                                @input="productState.saveStateDebounced('Name changed')"
                                                tooltipContent="The name of your product e.g Standard Ticket"
                                                :errorText="formState.getFormError(`variants.${index}.name`)">
                                            </Input>

                                        </div>

                                        <!-- Visibility Select -->
                                        <Select
                                            class="w-full"
                                            :search="false"
                                            label="Visibility"
                                            :options="visibilityTypes"
                                            v-model="productForm.variants[index].visible"
                                            :errorText="formState.getFormError(`variants.${index}.visible`)"
                                            @change="productState.saveStateDebounced('Visibility status changed')"
                                            tooltipContent="Turn on if you want your product to be visible (Made available to customers)">
                                        </Select>

                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                                        <!-- Unit Regular Price Input -->
                                        <Input
                                            type="money"
                                            label="Regular Price"
                                            :currency="store?.currency"
                                            v-model="productForm.variants[index].unit_regular_price"
                                            @input="productState.saveStateDebounced('Regular price changed')"
                                            :errorText="formState.getFormError(`variants.${index}.unit_regular_price`)"
                                            tooltipContent="Set the regular price for this product (How much the product is sold when its not on sale)">
                                        </Input>

                                        <!-- Unit Sale Price Input -->
                                        <Input
                                            type="money"
                                            label="Sale Price"
                                            :currency="store?.currency"
                                            v-model="productForm.variants[index].unit_sale_price"
                                            @input="productState.saveStateDebounced('Sale price changed')"
                                            :errorText="formState.getFormError(`variants.${index}.unit_sale_price`)"
                                            tooltipContent="Set the sale price for this product (if the product is on sale)">
                                        </Input>

                                        <template v-if="productForm.variants[index].show_more">

                                            <div class="col-span-2">

                                                <!-- Weight Input -->
                                                <Input
                                                    type="text"
                                                    label="Weight"
                                                    class="w-full"
                                                    v-model="productForm.variants[index].unit_weight"
                                                    @input="productState.saveStateDebounced('Weight changed')"
                                                    :errorText="formState.getFormError(`variants.${index}.unit_weight`)"
                                                    tooltipContent="The weight of the product. Useful for delivery calculations">
                                                    <template #suffix>
                                                        <span class="text-sm text-gray-400">{{ store?.weight_unit }}</span>
                                                    </template>
                                                </Input>

                                            </div>

                                            <!-- Sku Input -->
                                            <Input
                                                type="text"
                                                label="SKU"
                                                placeholder="std-ticket"
                                                v-model="productForm.variants[index].sku"
                                                @input="productState.saveStateDebounced('SKU changed')"
                                                :errorText="formState.getFormError(`variants.${index}.sku`)"
                                                tooltipContent="The stock keeping unit for this product. Useful for stock management">
                                            </Input>

                                            <!-- Barcode Input -->
                                            <Input
                                                type="text"
                                                label="Barcode"
                                                placeholder="123456789"
                                                v-model="productForm.variants[index].barcode"
                                                @input="productState.saveStateDebounced('Barcode changed')"
                                                :errorText="formState.getFormError(`variants.${index}.barcode`)"
                                                tooltipContent="The barcode for this product. Useful for stock management">
                                            </Input>

                                        </template>

                                    </div>

                                    <!-- Image Input -->
                                    <Input
                                        type="file"
                                        :maxFiles="1"
                                        :imagePreviewGridCols="1"
                                        singleFileUploadMessage="Photo attached"
                                        v-if="productForm.variants[index].show_more"
                                        v-model="productForm.variants[index].photos"
                                        @change="productState.saveStateDebounced('Photos changed')"
                                        @retryUploads="(files) => uploadImages(productForm.variants[index].id, null, index)"
                                        @retryUpload="(file, fileIndex) => uploadImages(productForm.variants[index].id, fileIndex, index)">
                                    </Input>

                                </div>

                                <div class="flex justify-center">

                                    <!-- Show More Or Less Button -->
                                    <Button
                                        size="sm"
                                        type="bare"
                                        :action="() => toggleAdditionalVariantOptions(index)"
                                        :rightIcon="productForm.variants[index].show_more ? ChevronUp : ChevronDown">
                                        <span>{{ productForm.variants[index].show_more ? 'show less' : 'show more' }}</span>
                                    </Button>

                                </div>

                            </div>

                        </div>

                    </template>

                    <div :class="[{'flex space-x-2 justify-between' : !hasVariants}]">

                        <p v-if="!hasVariants" class="text-sm text-gray-700">Add variants if your product supports different versions of itself e.g different sizes, materials, colors, etc</p>

                        <div class="flex justify-end space-x-2">

                            <!-- Change Arrangement -->
                            <Button
                                size="xs"
                                type="light"
                                :leftIcon="ArrowDownUp"
                                :action="showChangeArrangementModal"
                                v-if="productForm.variants.length >= 2">
                                <span>Change Arrangement</span>
                            </Button>

                            <!-- Add Variants -->
                            <Button
                                size="xs"
                                type="light"
                                :leftIcon="Plus"
                                :action="addVariant">
                                <span>Add Variants</span>
                            </Button>

                        </div>

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg p-4">

                        <p class="text-lg text-gray-700 font-semibold">Options</p>

                        <DataCollectionFields></DataCollectionFields>

                    </div>

                </div>

            </div>

            <div class="col-span-4">

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Images</p>

                        <Input
                            type="file"
                            :maxFiles="5"
                            v-model="productForm.photos"
                            @retryUploads="(files) => uploadImages(productForm.id)"
                            @change="productState.saveStateDebounced('Photos changed')"
                            @retryUpload="(file, fileIndex) => uploadImages(productForm.id, fileIndex)">
                        </Input>

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Inventory</p>

                        <!-- Stock Quantity Type Select -->
                        <Select
                            label="Stock"
                            class="w-full"
                            :search="false"
                            :options="stockQuantityTypes"
                            v-model="productForm.stock_quantity_type"
                            :errorText="formState.getFormError('stock_quantity_type')"
                            @change="productState.saveStateDebounced('Stock quantity type changed')"
                            tooltipContent="Select the available stock e.g Unlimited means that stock is always available while limited means that stock is limited to the quatities specified">
                        </Select>

                        <!-- Stock Quantity Input -->
                        <Input
                            min="1"
                            type="number"
                            placeholder="100"
                            label="Stock Quantity"
                            v-model="productForm.stock_quantity"
                            v-if="productForm.stock_quantity_type == 'limited'"
                            :errorText="formState.getFormError('stock_quantity')"
                            @input="productState.saveStateDebounced('Stock quantity changed')"
                            tooltipContent="Set the stock quantity e.g 100 means that you only have 100 items for this product">
                        </Input>

                        <!-- Daily Capacity Switch -->
                        <Switch
                            size="xs"
                            suffixText="Daily Capacity"
                            v-model="productForm.set_daily_capacity"
                            :errorText="formState.getFormError('set_daily_capacity')"
                            tooltipContent="The maximum number number of items you can sell per day"
                            @change="productState.saveStateDebounced('Daily capacity status changed')">
                        </Switch>

                        <!-- Daily Capacity Input -->
                        <Input
                            min="1"
                            type="number"
                            placeholder="10"
                            v-model="productForm.daily_capacity"
                            v-if="productForm.set_daily_capacity"
                            :errorText="formState.getFormError('daily_capacity')"
                            @input="productState.saveStateDebounced('Daily capacity changed')">
                        </Input>

                        <!-- Minimum Quantities Per Order Switch -->
                        <Switch
                            size="xs"
                            suffixText="Minimum Order Quantity"
                            v-model="productForm.set_min_order_quantity"
                            :errorText="formState.getFormError('set_min_order_quantity')"
                            @change="productState.saveStateDebounced('Minimum order quantity status changed')"
                            tooltipContent="Set the minimum order quantity for this product (Used to manage supply and demand)">
                        </Switch>

                        <!-- Minimum Allowed Quantity Per Order Input -->
                        <Input
                            min="1"
                            type="number"
                            placeholder="10"
                            v-model="productForm.min_order_quantity"
                            v-if="productForm.set_min_order_quantity"
                            :errorText="formState.getFormError('min_order_quantity')"
                            @input="productState.saveStateDebounced('Minimum order quantity changed')">
                        </Input>

                        <!-- Maximum Quantities Per Order Switch -->
                        <Switch
                            size="xs"
                            suffixText="Maximum Order Quantity"
                            v-model="productForm.set_max_order_quantity"
                            :errorText="formState.getFormError('set_max_order_quantity')"
                            @change="productState.saveStateDebounced('Maximum order quantity status changed')"
                            tooltipContent="Set the maximum order quantity for this product (Used to manage supply and demand)">
                        </Switch>

                        <!-- Maximum Allowed Quantity Per Order Input -->
                        <Input
                            min="1"
                            type="number"
                            placeholder="10"
                            v-model="productForm.max_order_quantity"
                            v-if="productForm.set_max_order_quantity"
                            :errorText="formState.getFormError('max_order_quantity')"
                            @input="productState.saveStateDebounced('Maximum order quantity changed')">
                        </Input>

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Categories</p>

                        <!-- Category Tags -->
                        <SelectTags
                            :options="categories"
                            placeholder="Add category"
                            v-model="productForm.categories"
                            :errorText="formState.getFormError('categories')"
                            @change="productState.saveStateDebounced('Categories changed')" />

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Tags</p>

                        <!-- Tags -->
                        <SelectTags
                            :options="tags"
                            placeholder="Add Tag"
                            v-model="productForm.tags"
                            :errorText="formState.getFormError('tags')"
                            @change="productState.saveStateDebounced('Tags changed')" />

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Delivery Methods</p>

                        <!-- Delivery Methods -->
                        <SelectTags
                            :options="deliveryMethodOptions"
                            placeholder="Add delivery method"
                            v-model="productForm.delivery_method_ids"
                            :errorText="formState.getFormError('delivery_method_ids')"
                            @change="productState.saveStateDebounced('Delivery methods changed')" />

                    </div>

                </div>

            </div>

        </div>

        <!-- Change Variant Arrangement Modal -->
        <Modal
            :scrollOnContent="false"
            ref="changeArrangementModal"
            approveText="Change Arrangement"
            :approveAction="changeVariantArrangement"
            :approveLoading="isChangingVariantArrangement">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-200 pb-4 mb-4">Change Arrangement</p>

                <div class="overflow-y-auto rounded-lg h-60">

                    <!-- Draggable Variants -->
                    <draggable
                        class="space-y-2"
                        handle=".draggable-handle"
                        ghost-class="bg-yellow-50"
                        v-model="variantsForArrangement">

                        <template
                            :key="index"
                            v-for="(variant, index) in variantsForArrangement">

                            <div class="flex justify-between items-center p-2 space-x-4 rounded-lg bg-gray-50">

                                <div :class="['flex items-center justify-center w-10 h-10 rounded-lg', { 'border border-dashed border-gray-200' : !variant.photo }]">

                                    <img v-if="variant.photo" class="w-full max-h-full object-contain rounded-lg flex-shrink-0" :src="variant.photo.path">

                                    <Image v-else size="20" class="text-gray-400 flex-shrink-0"></Image>

                                </div>

                                <div class="w-full flex items-center justify-between">

                                    <!-- Name -->
                                    <span class="text-sm">{{ variant.name ?? 'no name' }}</span>

                                     <!-- Drag & Drop Handle -->
                                    <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                                </div>

                            </div>

                        </template>

                    </draggable>

                </div>

            </template>

        </Modal>

        <Modal
            approveType="danger"
            approveText="Delete Variant"
            :approveAction="deleteVariant"
            ref="confirmDeleteVariantModal"
            :approveLoading="isDeletingVariantIds.some(id => id == deletableVariant.id)">

            <template #content v-if="deletableVariant">
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                <p class="mb-4">Are you sure you want to permanently delete <span class="font-bold text-black">{{ deletableVariant.name }}</span>?</p>
                <div
                    v-if="deletableVariant.photos.length"
                    class="flex justify-center space-x-2 mb-8">
                    <img class="w-20 max-h-20 object-contain rounded-lg flex-shrink-0" :src="deletableVariant.photos[0].path">
                </div>
            </template>
        </Modal>

        <div
            v-if="product"
            :class="['overflow-hidden rounded-lg space-y-4 p-4 border', isLoadingProduct ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

            <!-- Delete Product Info -->
            <p>Do you want to permanently delete <span class="font-bold text-black">{{ productForm.name }}</span>? Once this product is deleted you will not be able to recover it.</p>

            <div class="flex justify-end">

                <Modal
                    triggerType="danger"
                    approveType="danger"
                    :approveLeftIcon="Trash2"
                    triggerText="Delete Product"
                    approveText="Delete Product"
                    :approveAction="deleteProduct"
                    :triggerLoading="isDeletingProduct"
                    :approveLoading="isDeletingProduct">

                    <template #content>
                        <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                        <p class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ productForm.name }}</span>?</p>
                    </template>

                </Modal>

            </div>

        </div>

    </div>

</template>

<script>

    import isEqual from 'lodash/isEqual';
    import Pill from '@Partials/Pill.vue';
    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import cloneDeep from 'lodash/cloneDeep';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import Switch from '@Partials/Switch.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import { isEmpty } from '@Utils/stringUtils';
    import Skeleton from '@Partials/Skeleton.vue';
    import SelectTags from '@Partials/SelectTags.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import BackdropLoader from '@Partials/BackdropLoader.vue';
    import DataCollectionFields from '@Pages/Products/product/data-collection-fields/DataCollectionFields.vue';
    import { Move, X, Plus, Image, Split, Trash2, MoveLeft, ArrowDownUp, ChevronUp, ChevronDown } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'productState', 'changeHistoryState', 'notificationState'],
        components: {
            Move, Image, Split, Pill, Alert, Input, Modal, Button, Loader, Switch, Select, Popover,
            Skeleton, SelectTags, draggable: VueDraggableNext, BackdropLoader, DataCollectionFields
        },
        data() {
            return {
                X,
                Plus,
                Trash2,
                MoveLeft,
                tags: [],
                ChevronUp,
                ArrowDownUp,
                ChevronDown,
                categories: [],
                deliveryMethods: [],
                selectedVariants: [],
                deletableVariant: null,
                isDeletingVariantIds: [],
                variantsForArrangement: [],
                isLoadingDeliveryMethods: false,
                hasLoadedInitialDeliveryMethods: false,
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
                productTypes: [
                    { label: 'Physical', value: 'physical'},
                    { label: 'Digital', value: 'digital'},
                    { label: 'Booking', value: 'booking'},
                    { label: 'Subscription', value: 'subscription'},
                    { label: 'Other', value: 'other'}
                ],
                visibilityTypes: [
                    { label: 'Visible', value: true},
                    { label: 'Hidden', value: false},
                ],
                stockQuantityTypes: [
                    { label: 'Unlimited', value: 'unlimited'},
                    { label: 'Limited', value: 'limited'},
                ],
                allowedQuantityPerOrder: [
                    { label: 'Unlimited', value: 'unlimited'},
                    { label: 'Limited', value: 'limited'},
                ]
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            },
            productId(newValue) {
                if(newValue) {
                    this.setup();
                    this.setActionButtons();
                }
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            product() {
                return this.productState.product;
            },
            hasProduct() {
                return this.productState.hasProduct;
            },
            productId() {
                return this.$route.params.product_id;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingProduct() {
                return this.productState.isLoadingProduct;
            },
            isEditing() {
                return this.$route.name === 'edit-product';
            },
            isCreating() {
                return this.$route.name === 'create-product';
            },
            productForm() {
                return this.productState.productForm;
            },
            isSubmitting() {
                if(this.changeHistoryState.actionButtons.length == 0) return false;
                return this.changeHistoryState.actionButtons[1].loading;
            },
            hasVariants() {
                return this.productState.hasVariants;
            },
            isDeletingProduct() {
                return this.productState.isDeletingProduct;
            },
            isCreatingVariants() {
                return this.productState.isCreatingVariants;
            },
            isChangingVariantArrangement() {
                return this.productState.isChangingVariantArrangement;
            },
            variantOptions() {
                return this.productForm.variants.filter((variant) => variant.name).map((variant) => {
                    return {
                        label: variant.name,
                        value: variant.temporary_id
                    }
                });
            },
            deliveryMethodOptions() {
                return this.deliveryMethods.map((deliveryMethod) => {
                    return {
                        label: deliveryMethod.name,
                        value: deliveryMethod.id
                    }
                });
            }
        },
        methods: {
            isEmpty,
            goBack() {
                this.navigateToProducts();
            },
            async setup() {
                if(this.productForm == null) this.productState.setProductForm(null, this.isCreating);
                if(this.isEditing && this.store) await this.showProduct();

                if(this.store) {

                    this.tags = this.store.product_tags.map((tag) => {
                        return {
                            label: tag.name,
                            value: tag.id
                        }
                    });

                    this.categories = this.store.categories.map((category) => {
                        return {
                            label: category.name,
                            value: category.id
                        }
                    });

                    if(!this.hasLoadedInitialDeliveryMethods && !this.isLoadingDeliveryMethods) {
                        this.showDeliveryMethods();
                    }

                }
            },
            async navigateToProducts() {
                await this.$router.replace({
                    name: 'show-products',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async onView(product) {
                await this.$router.push({
                    name: 'edit-product',
                    params: {
                        product_id: product.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            addVariant() {
                this.selectedVariants = [];
                this.productState.addVariant();
            },
            async removeVariant(index) {
                this.selectedVariants = [];
                const variant = this.productForm.variants[index];

                if(variant.id) {
                    this.deletableVariant = variant;
                    this.$refs.confirmDeleteVariantModal.showModal();
                }else{
                    this.productState.removeVariant(index);
                }
            },
            clearSelectedVariants() {
                this.selectedVariants = [];
            },
            isFilteredVariants(variant) {
                return this.selectedVariants.length == 0 || this.selectedVariants.includes(variant.temporary_id);
            },
            toggleAdditionalVariantOptions(index) {
                this.productForm.variants[index].show_more = !this.productForm.variants[index].show_more;
            },
            showChangeArrangementModal() {
                this.setVariantsForArrangement();
                this.$refs.changeArrangementModal.showModal();
            },
            setVariantsForArrangement() {
                this.variantsForArrangement = cloneDeep(this.productForm.variants.map((variant) => {
                    return {
                        name: variant.name,
                        temporary_id: variant.temporary_id,
                        photo: variant.photos.length ? variant.photos[0] : null
                    }
                }));
                this.$refs.changeArrangementModal.showModal();
            },
            async changeVariantArrangement() {

                try {

                    if(this.productState.isChangingVariantArrangement) return;

                    // Create a map of original variants by temporary_id for quick lookup
                    const variantMap = new Map(
                        this.productForm.variants.map(variant => [variant.temporary_id, variant])
                    );

                    // Reorder productForm.variants based on variantsForArrangement order
                    this.productForm.variants = this.variantsForArrangement.map(arrangedVariant =>
                        variantMap.get(arrangedVariant.temporary_id)
                    ).filter(variant => variant !== undefined); // Filter out any undefined entries

                    const productIds = this.productForm.variants.filter((variant) => variant.id).map((variant) => variant.id);

                    if(productIds.length == 0) return;

                    this.productState.isChangingVariantArrangement = true;

                    const data = {
                        store_id: this.store.id,
                        product_ids: productIds,
                        parent_product_id: this.product.id
                    };

                    await axios.post(`/api/products/arrangement`, data);

                    this.notificationState.showSuccessNotification(`Variant arrangement updated`);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating variant arrangement';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update variant arrangement:', error);
                } finally {
                    this.productState.isChangingVariantArrangement = false;
                    this.$refs.changeArrangementModal.hideModal();
                }

            },
            async showProduct() {
                try {

                    this.productState.isLoadingProduct = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['photos', 'tags', 'categories', 'variants.photos', 'deliveryMethods'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/products/${this.productId}`, config);

                    const product = response.data;
                    this.productState.setProduct(product);
                    this.productState.setProductForm(product);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch product:', error);

                    if (error.response?.status === 404) {
                        await this.$router.replace({
                            name: 'show-products',
                            query: {
                                store_id: this.store.id,
                                searchTerm: this.$route.query.searchTerm,
                                filterExpressions: this.$route.query.filterExpressions,
                                sortingExpressions: this.$route.query.sortingExpressions
                            }
                        });
                    }

                } finally {
                    this.productState.isLoadingProduct = false;
                }
            },
            async createProduct(parentProduct = null, variantIndex = null) {

                try {

                    if(parentProduct == null) {

                        if(this.productState.isCreatingProduct || this.productState.isUploading) return;

                        this.formState.hideFormErrors();

                        if(this.isEmpty(this.productForm.name)) {
                            this.formState.setFormError('name', 'The name is required');
                        }

                        for (let index = 0; index < this.productForm.variants.length; index++) {
                            const variant = this.productForm.variants[index];

                            if(this.isEmpty(variant.name)) {
                                this.formState.setFormError(`variants.${index}.name`, 'The name is required');
                            }
                        }

                        if(this.formState.hasErrors) {
                            return;
                        }

                        this.productState.isCreatingProduct = true;
                        this.changeHistoryState.actionButtons[1].loading = true;

                    }

                    let data;

                    if(parentProduct) {

                        data = {
                            store_id: this.store.id,
                            parent_product_id: parentProduct.id,
                            ...this.productForm.variants[variantIndex]
                        }
                    }else{
                        data = {
                            ...this.productForm,
                            store_id: this.store.id
                        }
                    }

                    const response = await axios.post(`/api/products`, data);
                    const createdProduct = response.data.product;

                    if(parentProduct) {

                        this.productForm.variants[variantIndex].id = createdProduct.id;

                        if(this.productForm.variants[variantIndex].photos.length) {
                            await this.uploadImages(this.productForm.variants[variantIndex].id, null, variantIndex);
                        }

                    }else{

                        this.productForm.id = createdProduct.id;

                        if(this.productForm.photos.length) {
                            await this.uploadImages(this.productForm.id);
                        }

                        for (let index = 0; index < this.productForm.variants.length; index++) {
                            await this.createProduct(createdProduct, index);
                        }

                        if(this.productForm.tags.length || this.productForm.categories.length) {

                            //  Update store silently
                            this.storeState.silentUpdate();

                        }

                        this.notificationState.showSuccessNotification(`Product created`);
                        this.productState.saveOriginalState('Original product');
                        await this.onView(createdProduct);

                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create product:', error);
                } finally {
                    if(parentProduct == null) {
                        this.productState.isUploading = false;
                        this.productState.isCreatingProduct = false;
                        this.changeHistoryState.actionButtons[1].loading = false;
                    }
                }

            },
            async updateProduct(parentProduct = null, variantIndex = null) {

                try {

                    if(parentProduct == null) {

                        if(this.productState.isUpdatingProduct || this.productState.isUploading) return;

                        this.formState.hideFormErrors();

                        if(this.isEmpty(this.productForm.name)) {
                            this.formState.setFormError('name', 'The name is required');
                        }

                        for (let index = 0; index < this.productForm.variants.length; index++) {
                            const variant = this.productForm.variants[index];

                            if(this.isEmpty(variant.name)) {
                                this.formState.setFormError(`variants.${index}.name`, 'The name is required');
                            }
                        }

                        if(this.formState.hasErrors) {
                            return;
                        }

                        this.productState.isUpdatingProduct = true;
                        this.changeHistoryState.actionButtons[1].loading = true;

                    }

                    let id;
                    let data;

                    if(parentProduct) {

                        id = this.productForm.variants[variantIndex].id;

                        data = {
                            store_id: this.store.id,
                            ...this.productForm.variants[variantIndex]
                        }

                    }else{

                        id = this.productForm.id;

                        data = {
                            ...this.productForm,
                            store_id: this.store.id
                        }

                    }

                    const response = await axios.put(`/api/products/${id}`, data);
                    const updatedProduct = response.data.product;

                    if(parentProduct) {

                        if(this.productForm.variants[variantIndex].photos.length) {
                            await this.uploadImages(this.productForm.variants[variantIndex].id, null, variantIndex);
                        }

                    }else{

                        if(this.productForm.photos.length) {
                            await this.uploadImages(this.productForm.id);
                        }

                        const originalState = this.changeHistoryState.getOriginalState();

                        if(this.productForm.variants.length) {

                            for (let index = 0; index < this.productForm.variants.length; index++) {

                                const variant = cloneDeep(this.productForm.variants[index]);
                                const originalVariant = cloneDeep(originalState.variants.find(originalVariant => originalVariant.temporary_id == variant.temporary_id));

                                if(originalVariant) {

                                    delete variant.show_more;
                                    delete originalVariant.show_more;

                                    if(!isEqual(variant, originalVariant)) {
                                        await this.updateProduct(updatedProduct, index);
                                    }
                                }else{
                                    await this.createProduct(updatedProduct, index);
                                }

                            }

                        }

                        if(!isEqual(this.productForm.tags, originalState.tags) || !isEqual(this.productForm.categories, originalState.categories)) {

                            //  Update store silently
                            this.storeState.silentUpdate();

                        }

                        this.notificationState.showSuccessNotification(`Product updated`);
                        this.productState.saveOriginalState('Original product');

                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update product:', error);
                } finally {
                    this.productState.isUploading = false;
                    this.productState.isUpdatingProduct = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async deleteProduct(hideModal) {

                try {

                    if(this.productState.isDeletingProduct) return;

                    this.productState.isDeletingProduct = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/products/${this.product.id}`, config);

                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500));    //  Wait for modal to close

                    this.notificationState.showSuccessNotification('Product deleted');

                    await this.navigateToProducts();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete product:', error);
                    hideModal();
                } finally {
                    this.productState.isDeletingProduct = false;
                }

            },
            async deleteVariant(hideModal) {

                const deletableVariant = this.deletableVariant;

                try {

                    if(this.isDeletingVariantIds.includes(deletableVariant.id)) return;

                    this.isDeletingVariantIds.push(deletableVariant.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/products/${deletableVariant.id}`, config);

                    this.notificationState.showSuccessNotification('Variant deleted');

                    const index = this.productForm.variants.findIndex(variant => variant.id == deletableVariant.id);
                    this.productState.removeVariant(index);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting variant';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete variant:', error);
                } finally {
                    hideModal();
                    this.isDeletingVariantIds = this.isDeletingVariantIds.filter(id => id != deletableVariant.id);
                }
            },
            async uploadImages(productId, photoIndex = null, variantIndex = null) {

                let photos = variantIndex !== null ? this.productForm.variants[variantIndex].photos : this.productForm.photos;

                let imageUploadPromises = [];

                for (let index = 0; index < photos.length; index++) {
                    let photo = photos[index];

                    if (photo.id == null && photo.uploading == false && (photo.uploaded === null || photo.uploaded === false)) {
                        if (photoIndex == null || photoIndex == index) {
                            imageUploadPromises.push(
                                this.uploadSingleImage(productId, photos[index], index)
                            );
                        }
                    }
                }

                if (imageUploadPromises.length === 0) {
                    this.productState.isUploading = false;
                    return;
                }

                this.productState.isUploading = true;

                return Promise.allSettled(imageUploadPromises).then((results) => {
                    let failedUploads = results.filter(result => result.status === 'rejected').length;

                    if (failedUploads > 0) {
                        this.notificationState.showWarningNotification(`⚠️ ${failedUploads} image(s) failed to upload. Try again or change the image.`);
                    }

                    this.productState.isUploading = false;
                });
            },
            async uploadSingleImage(productId, photo, index, retryCount = 0, error = null) {

                try{

                    if (retryCount > 2) {
                        console.log(`❌ Image ${index + 1} permanently failed after 3 attempts.`);
                        photo.uploaded = false;
                        photo.uploading = false;
                        photo.error_message = error?.response?.data?.message || error?.message || `Upload failed`;

                        return Promise.reject(`Failed after 3 attempts`);
                    }

                    let formData = new FormData();
                    formData.append('file', photo.file_ref);
                    formData.append('mediable_id', productId);
                    formData.append('store_id', this.store.id);
                    formData.append('mediable_type', 'product');
                    formData.append('upload_folder_name', 'product_photo');

                    photo.uploading = true;
                    photo.error_message = null;

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    const response = await axios.post('/api/media-files', formData, config);
                    const mediaFile = response.data.media_file;

                    photo.uploaded = true;
                    photo.uploading = false;
                    photo.id = mediaFile.id;
                    photo.path = mediaFile.path;

                    console.log(`✅ Image ${index + 1} uploaded successfully.`);

                    return response;

                } catch (error) {
                    console.error(`⚠️ Image ${index + 1} upload attempt ${retryCount + 1} failed.`, error);

                    // Don't retry on 504 or timeout error
                    if (error.code === 'ECONNABORTED' || error.response?.status === 504) return;

                    return this.uploadSingleImage(productId, photo, index, retryCount + 1, error);
                }
            },
            async showDeliveryMethods() {
                try {

                    this.isLoadingDeliveryMethods = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get('/api/delivery-methods', config);
                    this.deliveryMethods = response.data.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching delivery methods';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch delivery methods:', error);
                } finally {
                    this.isLoadingDeliveryMethods = false;
                    this.hasLoadedInitialDeliveryMethods = true;
                }
            },
            setActionButtons() {
                if(this.isCreating || this.isEditing) {
                    this.changeHistoryState.removeButtons();
                    this.changeHistoryState.addDiscardButton();
                    this.changeHistoryState.addActionButton(
                        this.isEditing ? 'Save Changes' : 'Create Product',
                        this.isEditing ? this.updateProduct : this.createProduct,
                        'primary',
                        null,
                    );
                }
            },
            setProductForm(productForm) {
                this.productState.productForm = productForm;
            }
        },
        beforeRouteLeave(to, from, next) {
            //  Triggered when navigating between routes not sharing same component e.g from "edit-product" to "show-store-home"
            if (this.changeHistoryState.hasChangeHistory) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        unmounted() {
            this.productState.reset();
        },
        created() {

            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setProductForm;
            }

        }
    };

</script>
