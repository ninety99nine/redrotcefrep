<template>

    <div class="pt-24 px-4">

        <div class="grid grid-cols-12 gap-4 mb-4">

            <div class="col-span-8 z-10">

                <div class="select-none bg-white rounded-lg p-4 mb-4">

                    <div class="flex justify-between items-center">

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

                                    <Popover title="What Is This?" content="Products are physical or non physical items that customers can place orders and pay for using your preferred payment methods" placement="top"></Popover>

                                </div>

                            </template>

                        </div>

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <!-- Name Input -->
                        <Input
                            type=text
                            label="Name"
                            v-model="productForm.name"
                            placeholder="Standard Ticket"
                            :errorText="formState.getFormError('name')"
                            @input="productState.saveStateDebounced('Name changed')"
                            tooltipContent="The name of your product e.g Standard Ticket">
                        </Input>

                        <!-- Non Variation Settings -->
                        <template v-if="productForm.allow_variations == false">

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
                                type=text
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

                            <!-- Category Tags -->
                            <SelectTags
                                label="Categories"
                                :options="categories"
                                v-model="productForm.categories"
                                :errorText="formState.getFormError('categories')"
                                @change="productState.saveStateDebounced('Categories changed')" />

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
                                v-model="productForm.unit_weight"
                                :errorText="formState.getFormError('unit_weight')"
                                @input="productState.saveStateDebounced('Unit weight changed')"
                                tooltipContent="The weight of the product. Useful for delivery calculations">
                                <template #suffix>
                                    <span class="text-sm text-gray-400">{{ store?.weight_unit }}</span>
                                </template>
                            </Input>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

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

                        </template>

                    </div>

                </div>

                <div
                    class="relative mb-4"
                    v-if="productForm.allow_variations == false">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Pricing</p>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                            <!-- If Free Switch -->
                            <Switch
                                size="xs"
                                suffixText="Is Free"
                                v-model="productForm.is_free"
                                :errorText="formState.getFormError('is_free')"
                                @change="productState.saveStateDebounced('Free status changed')"
                                tooltipContent="Turn on if you want your product to be made Free for customers">
                            </Switch>

                            <!-- If Free Switch -->
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
                        <Alert v-if="productForm.is_free" type="success">
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
                                    v-model="productForm.unit_regular_price"
                                    :errorText="formState.getFormError('unit_regular_price')"
                                    @input="productState.saveStateDebounced('Regular price changed')"
                                    tooltipContent="Set the regular price for this product (How much the product is sold when its not on sale)">
                                </Input>

                                <!-- Unit Sale Price Input -->
                                <Input
                                    type="money"
                                    label="Sale Price"
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
                                type="number"
                                v-if="productForm.tax_overide"
                                v-model="productForm.tax_overide_amount"
                                :errorText="formState.getFormError('tax_overide_amount')"
                                @input="productState.saveStateDebounced('Tax overide changed')">
                            </Input>

                        </template>

                    </div>

                </div>

                <div class="relative">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <!-- Allow Variations Switch -->
                        <Switch
                            size="xs"
                            suffixText="Allow variations"
                            v-model="productForm.allow_variations"
                            :errorText="formState.getFormError('allow_variations')"
                            v-if="productForm.allow_variations || hasOriginalVariantAttributes"
                            @change="productState.saveStateDebounced('Allow variations status changed')"
                            tooltipContent="Turn on if you want your product to support variations (different versions of itself e.g different sizes, materials, colors, etc)">
                        </Switch>

                        <div v-if="!hasOriginalVariantAttributes">

                            <div class="flex justify-between p-20 border border-gray-300 rounded-lg bg-gray-50">

                                <div class="space-y-4">
                                    <h1 class="text-2xl font-bold">
                                        <template v-if="productForm.allow_variations && hasVariantAttributes">Create Variations</template>
                                        <template v-else-if="productForm.allow_variations">Add Options</template>
                                        <template v-else>Have Options?</template>
                                    </h1>
                                    <p v-if="hasVariantAttributes">Click the <Pill type="primary" size="xs">Create Variations</Pill> button to create different variations of your product e.g different sizes, materials, colors, etc</p>
                                    <p v-else-if="productForm.allow_variations">Click the <Pill type="primary" size="xs">+ Add Option</Pill> button to add different variations of your product e.g different sizes, materials, colors, etc</p>
                                    <p v-else>Turn on <Pill type="primary" size="xs">Allow variations</Pill> if you want your product to support variations (different versions of itself e.g different sizes, materials, colors, etc)</p>

                                    <!-- Allow Variations Switch -->
                                    <Switch
                                        size="md"
                                        suffixText="Allow variations"
                                        v-if="!productForm.allow_variations"
                                        v-model="productForm.allow_variations"
                                        :errorText="formState.getFormError('allow_variations')"
                                        @change="productState.saveStateDebounced('Allow variations status changed')"
                                        tooltipContent="Turn on if you want your product to support variations (different versions of itself e.g different sizes, materials, colors, etc)">
                                    </Switch>

                                </div>

                                <div>
                                    <span class="text-8xl">🛍️</span>
                                </div>

                            </div>

                        </div>

                        <!-- Variation Settings -->
                        <template v-if="productForm.allow_variations">

                            <div v-for="(variantAttribute, index) in productForm.variant_attributes" :key="index" class="relative bg-gray-50 p-4 border border-gray-300 rounded-lg">

                                <div class="absolute top-2 right-2 flex items-center space-x-2">

                                    <svg class="w-6 h-6 cursor-pointer hover:opacity-50" @click="productForm.variant_attributes[index].is_editable = !productForm.variant_attributes[index].is_editable" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path v-if="productForm.variant_attributes[index].is_editable" stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>

                                    <!-- Remove Variation Attribute Button -->
                                    <Button
                                        size="xs"
                                        type="danger"
                                        :leftIcon="Trash2"
                                        :action="() => onRemoveVariantAttribute(index)">
                                    </Button>

                                </div>

                                <div v-if="productForm.variant_attributes[index].is_editable" class="space-y-4">

                                    <!-- Variant Attribute Name Input -->
                                    <Input
                                        type="text"
                                        label="Name"
                                        placeholder="Size"
                                        v-model="productForm.variant_attributes[index].name"
                                        tooltipContent="The variation name e.g Size, Color, Material, etc"
                                        :errorText="formState.getFormError('variant_attributes'+index+'name')"
                                        @input="productState.saveStateDebounced('Variant attribute name changed')">
                                    </Input>

                                    <!-- Variant Attribute Instruction Textarea -->
                                    <Input
                                        type="text"
                                        label="Instruction"
                                        placeholder="Select your size"
                                        v-model="productForm.variant_attributes[index].instruction"
                                        tooltipContent="The variation instruction e.g Select your size"
                                        :errorText="formState.getFormError('variant_attributes'+index+'instruction')"
                                        @input="productState.saveStateDebounced('Variant attribute instruction changed')">
                                    </Input>

                                    <!-- Variant Attribute Value Tags -->
                                    <SelectTags
                                        label="Options"
                                        v-model="productForm.variant_attributes[index].values"
                                        tooltipContent="The variation options e.g Small, Medium, Large, etc"
                                        :errorText="formState.getFormError('variant_attributes'+index+'value')"
                                        @change="productState.saveStateDebounced('Variant attribute values changed')" />

                                </div>

                                <div
                                    v-else
                                    class="space-y-2 cursor-pointer"
                                    @click="productForm.variant_attributes[index].is_editable = true">

                                    <!-- Variant Attribute Name -->
                                    <p class="text-black">{{ productForm.variant_attributes[index].name }}</p>

                                    <!-- Error Message -->
                                    <span class="scroll-to-error font-medium text-red-500 text-xs mt-1 ml-1" v-if="formState.getFormError('variant_attributes'+index+'name')"></span>

                                    <!-- Variant Attribute Instruction -->
                                    <p class="text-xs text-black">{{ productForm.variant_attributes[index].instruction }}</p>

                                    <!-- Error Message -->
                                    <span class="scroll-to-error font-medium text-red-500 text-xs mt-1 ml-1" v-if="formState.getFormError('variant_attributes'+index+'name')"></span>

                                    <!-- Variant Attribute Value -->
                                    <div class="flex space-x-2">
                                        <span v-for="(value, index) in productForm.variant_attributes[index].values" :key="index" class="py-1 px-2 bg-black text-white text-xs rounded-lg">
                                            {{ value }}
                                        </span>
                                    </div>

                                    <!-- Error Message -->
                                    <span class="scroll-to-error font-medium text-red-500 text-xs mt-1 ml-1" v-if="formState.getFormError('variant_attributes'+index+'name')"></span>

                                </div>

                            </div>

                            <div class="flex justify-end space-x-2">

                                <!-- Undo Button -->
                                <Button
                                    size="xs"
                                    primary="light"
                                    :action="onResetVariantAttributes"
                                    v-if="variantAttributesHaveChanged && hasOriginalVariantAttributes">
                                    <span>Undo</span>
                                </Button>

                                <div class="flex justify-end">

                                    <!-- Add Option Button -->
                                    <div class="relative">
                                        <div v-if="!hasVariantAttributes" class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                                            <div class="animate-bounce text-4xl">👆</div>
                                        </div>
                                        <Button
                                            size="xs"
                                            type="primary"
                                            :leftIcon="Plus"
                                            :action="onAddVariantAttribute"
                                            :buttonClass="hasVariantAttributes ? 'w-48' : 'w-40'">
                                            <span>{{ hasVariantAttributes ? 'Add Another Option' : 'Add Option' }}</span>
                                        </Button>
                                    </div>

                                </div>

                                <!-- Create Variations Button -->
                                <div v-if="(variantAttributesHaveChanged && hasVariantAttributes) || (totalProductVariations == 0 && hasVariantAttributes)" class="relative">

                                    <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                                        <div v-if="!isCreatingVariations" class="animate-bounce text-4xl">👆</div>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="primary"
                                        buttonClass="w-40"
                                        :loading="isCreatingVariations"
                                        :action="() => product ? createProductVariations() : createProduct()">
                                        Create Variations
                                    </Button>

                                </div>

                            </div>

                        </template>

                    </div>

                </div>

            </div>

            <div class="col-span-4">

                <div
                    class="relative mb-4"
                    v-if="productForm.allow_variations == false">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Images</p>

                        <Input
                            type="file"
                            :maxFiles="5"
                            v-model="productForm.photos"
                            @retryUploads="(files) => uploadImages()"
                            @retryUpload="(file, fileIndex) => uploadImages(fileIndex)"
                            @change="productState.saveStateDebounced('Photos changed')">
                        </Input>

                    </div>

                </div>

                <div
                    class="relative mb-4"
                    v-if="productForm.allow_variations == false">

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
                            v-model="productForm.stock_quantity"
                            label="Stock Quantity" placeholder="100"
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

                <div
                    class="relative mb-4"
                    v-if="productForm.allow_variations == false">

                    <BackdropLoader v-if="isLoadingProduct || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Tags</p>

                        <!-- Tags -->
                        <SelectTags
                            :options="tags"
                            v-model="productForm.tags"
                            :errorText="formState.getFormError('tags')"
                            @change="productState.saveStateDebounced('Tags changed')" />

                    </div>

                </div>

            </div>

        </div>

        <template v-if="productForm.allow_variations && hasOriginalVariantAttributes">

            <!-- Variation List -->
            <div class="space-y-4 bg-white shadow-lg rounded-lg border border-gray-300 p-4 mb-4">

                <template v-if="variantAttributesHaveChanged">

                    <!-- Info Alert -->
                    <Alert type="light" class="flex items-start space-x-2">

                        <template #description>
                            <div class="flex space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                <div>
                                    The variation options have been changed. You can
                                    <template v-if="hasVariantAttributes">
                                        either
                                        <span @click="() => createProductVariations()" class="font-bold underline cursor-pointer">
                                        Create New Variations
                                        </span>
                                        or
                                    </template>
                                    <span @click="onResetVariantAttributes" class="font-bold underline cursor-pointer">
                                        Undo Changes
                                    </span>
                                    to revert back to the original variations you had before.
                                </div>
                            </div>
                        </template>

                    </Alert>

                </template>

                <ProductVariations
                    :product="product"
                    v-else-if="product"
                    :isLoadingProduct="isLoadingProduct"
                    :isCreatingVariations="isCreatingVariations"
                    @totalProductVariations="(total) => totalProductVariations = total">
                </ProductVariations>

            </div>

        </template>

        <div
            v-if="product"
            :class="['overflow-hidden rounded-lg space-y-4 p-4 border', isLoadingProduct ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

            <!-- Delete Product Info -->
            <p>Do you want to permanently delete <span class="font-bold text-black">{{ productForm.name }}</span>? Once this product is deleted you will not be able to recover it.</p>

            <div class="flex justify-end">

                <Modal
                    triggerType="danger"
                    approveType="danger"
                    triggerText="Delete Product"
                    approveText="Delete Product"
                    :isLoading="isDeletingProduct"
                    :approveAction="deleteProduct">

                    <template #content>
                        <p class="text-lg font-bold border-b border-dashed pb-4 mb-4">Confirm Delete</p>
                        <p class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ productForm.name }}</span>?</p>
                    </template>

                </Modal>

            </div>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import Switch from '@Partials/Switch.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import SelectTags from '@Partials/SelectTags.vue';
    import { Plus, Trash2, MoveLeft } from 'lucide-vue-next';
    import BackdropLoader from '@Partials/BackdropLoader.vue';
    import ProductVariations from '@Pages/Products/product/product-variations/ProductVariations.vue';

    export default {
        inject: ['formState', 'storeState', 'productState', 'changeHistoryState', 'notificationState'],
        components: {
            Pill, Alert, Input, Modal, Button, Loader, Switch, Select, Popover,
            Skeleton, SelectTags, BackdropLoader, ProductVariations
        },
        data() {
            return {
                Plus,
                Trash2,
                MoveLeft,
                tags: [],
                categories: [],
                isUploading: false,
                createdProduct: null,
                setupCompleted: false,
                totalProductVariations: null,
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
            store(newValue) {
                if(newValue) {
                    this.setup();
                }
            },
            '$route.params.product_id'(newValue) {
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
            isDeletingProduct() {
                return this.productState.isDeletingProduct;
            },
            isCreatingVariations() {
                return this.productState.isCreatingVariations;
            },
            hasVariantAttributes() {
                return this.productState.hasVariantAttributes;
            },
            hasOriginalVariantAttributes() {
                return this.productState.hasOriginalVariantAttributes;
            },
            variantAttributesHaveChanged() {
                return this.productState.variantAttributesHaveChanged;
            }
        },
        methods: {
            goBack() {
                this.navigateToProducts();
            },
            async setup() {
                if(this.productForm == null) this.productState.setProductForm(null, this.isCreating);
                if(this.isEditing && this.store) await this.showProduct();

                if(this.store) {

                    this.tags = this.store.tags.map((tag) => {
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
            onAddVariantAttribute() {
                this.productState.onAddVariantAttribute();
            },
            onRemoveVariantAttribute() {
                this.productState.onRemoveVariantAttribute();
            },
            onResetVariantAttributes() {
                this.productState.onResetVariantAttributes();
            },
            async showProduct() {
                try {

                    this.productState.isLoadingProduct = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['photos', 'tags', 'categories'].join(',')
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

                    if(error.status == 404) {
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
            productCreationCompleted() {
                this.orderComments.push({
                    user: this.authState.user,
                    ...this.createdOrderComment,
                    photos: this.form.photos.map(function(photo) { return { id: uuidv4(), temporary: true, path: photo.path } }),
                });
                this.notificationState.showSuccessNotification('Comment created!');
                this.isCreatingOrderComment = false;
                this.showOrderComments();
                this.reset();
            },
            async createProduct() {

                try {

                    if(this.isCreatingProduct || this.isUploading) return;

                    this.formState.hideFormErrors();

                    if(this.productForm.name == null || this.productForm.name.trim() === '') {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.productState.isCreatingProduct = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.productForm,
                        store_id: this.store.id
                    };

                    const response = await axios.post(`/api/products`, data);
                    this.createdProduct = response.data.product;

                    if(this.productForm.allow_variations) {
                        await this.createProductVariations();
                    }

                    if(this.productForm.photos.length) {
                        await this.uploadImages();
                    }

                    this.notificationState.showSuccessNotification(`Product created`);
                    this.productState.saveOriginalState('Original product');
                    //this.productState.reset();
                    //this.changeHistoryState.reset();
                    await this.onView(this.createdProduct);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create product:', error);
                } finally {
                    this.isUploading = false;
                    this.productState.isCreatingProduct = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async updateProduct() {

                try {

                    if(this.isUpdatingProduct || this.isUploading) return;

                    this.formState.hideFormErrors();

                    if(this.productForm.name == null || this.productForm.name.trim() === '') {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.productState.isUpdatingProduct = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.productForm,
                        store_id: this.store.id
                    };

                    await axios.put(`/api/products/${this.product.id}`, data);

                    if(this.productForm.allow_variations) {
                        await this.createProductVariations();
                    }

                    if(this.productForm.photos.length) {
                        await this.uploadImages();
                    }

                    this.notificationState.showSuccessNotification(`Product updated`);
                    this.productState.saveOriginalState('Original product');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update product:', error);
                } finally {
                    this.isUploading = false;
                    this.productState.isUpdatingProduct = true;
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
                    await new Promise(resolve => setTimeout(resolve, 1000));    //  Wait for modal to close

                    this.notificationState.showSuccessNotification('Product deleted');

                    await this.navigateToProducts();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete product:', error);
                } finally {
                    this.productState.isDeletingProduct = false;
                    hideModal();
                }

            },
            async createProductVariations() {

                try {

                    const condition1 = this.variantAttributesHaveChanged && this.hasVariantAttributes;
                    const condition2 = this.totalProductVariations == 0 && this.hasVariantAttributes;

                    if(!condition1 && !condition2) return;
                    if(this.productState.isCreatingVariations) return;

                    this.productState.isCreatingVariations = true;

                    if(this.product) {
                        this.changeHistoryState.actionButtons[1].loading = true;
                    }

                    const data = {
                        store_id: this.store.id,
                        variant_attributes: this.productForm.variant_attributes
                    };

                    await axios.post(`/api/products/${this.createdProduct ? this.createdProduct.id : this.product.id}/variations`, data);

                    this.productForm.variant_attributes = this.productForm.variant_attributes.map((variantAttribute) => {
                        variantAttribute.is_editable = false;
                        return variantAttribute;
                    });

                    if(this.product) {
                        this.notificationState.showSuccessNotification(`Product variations created`);
                        this.changeHistoryState.resetHistoryToCurrent();
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating product variations';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create product variations:', error);
                } finally {
                    this.productState.isCreatingVariations = false;
                    if(this.product) {
                        this.changeHistoryState.actionButtons[1].loading = false;
                    }
                }

            },
            async uploadImages(photoIndex = null) {

                let imageUploadPromises = [];

                for (let index = 0; index < this.productForm.photos.length; index++) {

                    let photo = this.productForm.photos[index];

                    if(photo.id == null && photo.uploading == false && (photo.uploaded === null || photo.uploaded === false)) {

                        if(photoIndex == null || photoIndex == index) {

                            imageUploadPromises.push(
                                this.uploadSingleImage(this.productForm.photos[index], index)
                            );
                        }
                    }
                }

                this.isUploading = true;

                return Promise.allSettled(imageUploadPromises).then((results) => {

                    let failedUploads = results.filter(result => result.status === 'rejected').length;

                    if (failedUploads > 0) {
                        this.notificationState.showWarningNotification(`⚠️ ${failedUploads} image(s) failed to upload. Try again or change the image.`);
                    }

                    this.isUploading = false; // ✅ All images uploaded (successful or failed)

                });
            },
            async uploadSingleImage(photo, index, retryCount = 0, error = null) {

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
                    formData.append('store_id', this.store.id);
                    formData.append('mediable_type', 'product');
                    formData.append('upload_folder_name', 'product_photo');
                    formData.append('mediable_id', this.createdProduct ? this.createdProduct.id : this.product.id);

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
                    return this.uploadSingleImage(photo, index, retryCount + 1, error);
                }
            },
            setActionButtons() {
                if(this.isCreating || this.isEditing) {
                    this.changeHistoryState.removeButtons();
                    this.changeHistoryState.addDiscardButton(this.onDiscard);
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
            this.changeHistoryState.reset();
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
