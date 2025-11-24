<template>

    <div class="pt-24 px-8 relative select-none">

        <!-- No Promotions -->
        <div
            v-if="hasInitialResults == false"
            class="flex flex-col items-center justify-center bg-linear-to-b from-white p-8 rounded-2xl">

            <div class="bg-blue-200 text-blue-900 rounded-full p-10 mt-8 mb-16">
                <TicketPercent size="40"></TicketPercent>
            </div>

            <div class="text-center max-w-md">

                <h1 class="text-3xl font-extrabold mb-3">
                    Bulk Edit Products
                </h1>

                <p class="text-base leading-relaxed">
                    Your promotions will appear here for fast bulk editing. Get started by creating your first promotion.
                </p>

            </div>

            <div class="mt-10">

                <Button
                    size="lg"
                    type="primary"
                    :leftIcon="Plus"
                    leftIconSize="20"
                    :skeleton="!store"
                    :action="onAddPromotion">
                    <span class="ml-1">Create Promotion</span>
                </Button>

            </div>

        </div>

        <div
            v-else
            class="relative bg-white/80 p-4 rounded-md mb-60">

            <h1 class="text-lg font-semibold mb-4">Bulk Edit Promotions</h1>

            <!-- Promotions Table -->
            <Table
                @search="search"
                :columns="columns"
                :perPage="perPage"
                resource="promotions"
                @paginate="paginate"
                @refresh="showPromotions"
                :searchTerm="searchTerm"
                :pagination="pagination"
                :isLoading="isLoadingPromotions"
                @updatedColumns="updatedColumns"
                @updatedFilters="updatedFilters"
                @updatedSorting="updatedSorting"
                @updatedPerPage="updatedPerPage"
                :filterExpressions="filterExpressions"
                :sortingExpressions="sortingExpressions"
                searchPlaceholder="Promotion name, code, or description">

                <template #afterRefreshButton>
                    <div class="flex items-center space-x-2">
                        <!-- Add Promotion Button -->
                        <Button
                            size="xs"
                            type="primary"
                            :leftIcon="Plus"
                            :action="onAddPromotion">
                            <span>Add Promotion</span>
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
                        :key="promotionForm.id"
                        v-for="(promotionForm, promotionIndex) in promotionForms"
                        :class="[checkedRows[promotionForm.id] ? 'bg-blue-50' : 'bg-white hover:bg-gray-50', 'group cursor-pointer border-b border-blue-100']">
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
                                    v-model="checkedRows[promotionForm.id]">
                                </Input>
                            </td>
                            <template v-if="column.active">
                                <!-- Name -->
                                <td v-if="column.name == 'Name'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <Input
                                        type="text"
                                        class="min-w-40"
                                        v-model="promotionForms[promotionIndex].name"
                                        @input="captureChange(promotionIndex, 'Name changed')"
                                        :errorText="formState.getFormError(`promotion.${promotionIndex}.name`)">
                                    </Input>
                                </td>
                                <!-- Description -->
                                <td v-if="column.name == 'Description'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <Input
                                        rows="2"
                                        type="textarea"
                                        class="min-w-60"
                                        v-model="promotionForms[promotionIndex].description"
                                        @input="captureChange(promotionIndex, 'Description changed')"
                                        :errorText="formState.getFormError(`promotion.${promotionIndex}.description`)">
                                    </Input>
                                </td>
                                <!-- Active -->
                                <td v-if="column.name == 'Active'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <Select
                                        class="min-w-40"
                                        :search="false"
                                        :options="activeTypes"
                                        v-model="promotionForms[promotionIndex].active"
                                        @change="captureChange(promotionIndex, 'Active status changed')"
                                        :errorText="formState.getFormError(`promotion.${promotionIndex}.active`)">
                                    </Select>
                                </td>
                                <!-- Code -->
                                <td v-if="column.name == 'Code'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_code"
                                            @change="captureChange(promotionIndex, 'Activate using code changed')">
                                        </Input>
                                        <Input
                                            type="text"
                                            class="min-w-40"
                                            v-model="promotionForms[promotionIndex].code"
                                            @input="captureChange(promotionIndex, 'Code changed')"
                                            :disabled="!promotionForms[promotionIndex].activate_using_code"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.code`)">
                                        </Input>
                                    </div>
                                </td>
                                <!-- Discount -->
                                <td v-if="column.name == 'Discount'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].offer_discount"
                                            @change="captureChange(promotionIndex, 'Offer discount changed')">
                                        </Input>
                                        <Select
                                            class="min-w-40"
                                            :search="false"
                                            :options="discountRateTypes"
                                            :disabled="!promotionForms[promotionIndex].offer_discount"
                                            v-model="promotionForms[promotionIndex].discount_rate_type"
                                            @change="captureChange(promotionIndex, 'Discount rate type changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.discount_rate_type`)">
                                        </Select>
                                        <Input
                                            type="percentage"
                                            class="min-w-40"
                                            v-if="promotionForms[promotionIndex].offer_discount && promotionForms[promotionIndex].discount_rate_type == 'percentage'"
                                            v-model="promotionForms[promotionIndex].discount_percentage_rate"
                                            @input="captureChange(promotionIndex, 'Discount percentage rate changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.discount_percentage_rate`)">
                                        </Input>
                                        <Input
                                            type="money"
                                            class="min-w-40"
                                            v-if="promotionForms[promotionIndex].offer_discount && promotionForms[promotionIndex].discount_rate_type == 'flat'"
                                            v-model="promotionForms[promotionIndex].discount_flat_rate"
                                            @input="captureChange(promotionIndex, 'Discount flat rate changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.discount_flat_rate`)">
                                        </Input>
                                    </div>
                                </td>
                                <!-- Free Delivery -->
                                <td v-if="column.name == 'Free Delivery'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center justify-center">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].offer_free_delivery"
                                            @change="captureChange(promotionIndex, 'Offer free delivery changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.offer_free_delivery`)">
                                        </Input>
                                    </div>
                                </td>
                                <!-- Minimum Grand Total -->
                                <td v-if="column.name == 'Minimum Grand Total'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_minimum_grand_total"
                                            @change="captureChange(promotionIndex, 'Activate using minimum grand total changed')">
                                        </Input>
                                        <Input
                                            type="money"
                                            class="min-w-40"
                                            :disabled="!promotionForms[promotionIndex].activate_using_minimum_grand_total"
                                            v-model="promotionForms[promotionIndex].minimum_grand_total"
                                            @input="captureChange(promotionIndex, 'Minimum grand total changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.minimum_grand_total`)">
                                        </Input>
                                    </div>
                                </td>
                                <!-- Minimum Total Products -->
                                <td v-if="column.name == 'Minimum Total Products'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_minimum_total_products"
                                            @change="captureChange(promotionIndex, 'Activate using minimum total products changed')">
                                        </Input>
                                        <Input
                                            type="number"
                                            class="min-w-40"
                                            :disabled="!promotionForms[promotionIndex].activate_using_minimum_total_products"
                                            v-model="promotionForms[promotionIndex].minimum_total_products"
                                            @input="captureChange(promotionIndex, 'Minimum total products changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.minimum_total_products`)">
                                        </Input>
                                    </div>
                                </td>
                                <!-- Minimum Total Product Quantities -->
                                <td v-if="column.name == 'Minimum Total Product Quantities'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_minimum_total_product_quantities"
                                            @change="captureChange(promotionIndex, 'Activate using minimum total product quantities changed')">
                                        </Input>
                                        <Input
                                            type="number"
                                            class="min-w-40"
                                            :disabled="!promotionForms[promotionIndex].activate_using_minimum_total_product_quantities"
                                            v-model="promotionForms[promotionIndex].minimum_total_product_quantities"
                                            @input="captureChange(promotionIndex, 'Minimum total product quantities changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.minimum_total_product_quantities`)">
                                        </Input>
                                    </div>
                                </td>
                                <!-- Start Datetime -->
                                <td v-if="column.name == 'Start Datetime'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_start_datetime"
                                            @change="captureChange(promotionIndex, 'Activate using start datetime changed')">
                                        </Input>
                                        <Datepicker
                                            class="w-60"
                                            :enableTimePicker="true"
                                            format="dd MMM yyyy HH:mm"
                                            modelType="yyyy-MM-dd HH:mm"
                                            :disabled="!promotionForms[promotionIndex].activate_using_start_datetime"
                                            v-model="promotionForms[promotionIndex].start_datetime"
                                            @change="captureChange(promotionIndex, 'Start datetime changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.start_datetime`)">
                                        </Datepicker>
                                    </div>
                                </td>
                                <!-- End Datetime -->
                                <td v-if="column.name == 'End Datetime'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_end_datetime"
                                            @change="captureChange(promotionIndex, 'Activate using end datetime changed')">
                                        </Input>
                                        <Datepicker
                                            class="w-60"
                                            :enableTimePicker="true"
                                            format="dd MMM yyyy HH:mm"
                                            modelType="yyyy-MM-dd HH:mm"
                                            :disabled="!promotionForms[promotionIndex].activate_using_end_datetime"
                                            v-model="promotionForms[promotionIndex].end_datetime"
                                            @change="captureChange(promotionIndex, 'End datetime changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.end_datetime`)">
                                        </Datepicker>
                                    </div>
                                </td>
                                <!-- Hours of Day -->
                                <td v-if="column.name == 'Hours of Day'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_hours_of_day"
                                            @change="captureChange(promotionIndex, 'Activate using hours of day changed')">
                                        </Input>
                                        <SelectTags
                                            class="min-w-60"
                                            :options="hourOptions"
                                            :disabled="!promotionForms[promotionIndex].activate_using_hours_of_day"
                                            v-model="promotionForms[promotionIndex].hours_of_day"
                                            @change="captureChange(promotionIndex, 'Hours of day changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.hours_of_day`)">
                                        </SelectTags>
                                    </div>
                                </td>
                                <!-- Days of the Week -->
                                <td v-if="column.name == 'Days of the Week'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_days_of_the_week"
                                            @change="captureChange(promotionIndex, 'Activate using days of the week changed')">
                                        </Input>
                                        <SelectTags
                                            class="min-w-60"
                                            :options="dayOptions"
                                            :disabled="!promotionForms[promotionIndex].activate_using_days_of_the_week"
                                            v-model="promotionForms[promotionIndex].days_of_the_week"
                                            @change="captureChange(promotionIndex, 'Days of the week changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.days_of_the_week`)">
                                        </SelectTags>
                                    </div>
                                </td>
                                <!-- Days of the Month -->
                                <td v-if="column.name == 'Days of the Month'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_days_of_the_month"
                                            @change="captureChange(promotionIndex, 'Activate using days of the month changed')">
                                        </Input>
                                        <SelectTags
                                            class="min-w-60"
                                            :options="dayOfMonthOptions"
                                            :disabled="!promotionForms[promotionIndex].activate_using_days_of_the_month"
                                            v-model="promotionForms[promotionIndex].days_of_the_month"
                                            @change="captureChange(promotionIndex, 'Days of the month changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.days_of_the_month`)">
                                        </SelectTags>
                                    </div>
                                </td>
                                <!-- Months of the Year -->
                                <td v-if="column.name == 'Months of the Year'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_months_of_the_year"
                                            @change="captureChange(promotionIndex, 'Activate using months of the year changed')">
                                        </Input>
                                        <SelectTags
                                            class="min-w-60"
                                            :options="monthOptions"
                                            :disabled="!promotionForms[promotionIndex].activate_using_months_of_the_year"
                                            v-model="promotionForms[promotionIndex].months_of_the_year"
                                            @change="captureChange(promotionIndex, 'Months of the year changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.months_of_the_year`)">
                                        </SelectTags>
                                    </div>
                                </td>
                                <!-- Usage Limit -->
                                <td v-if="column.name == 'Usage Limit'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_using_usage_limit"
                                            @change="captureChange(promotionIndex, 'Activate using usage limit changed')">
                                        </Input>
                                        <Input
                                            type="number"
                                            class="min-w-40"
                                            :disabled="!promotionForms[promotionIndex].activate_using_usage_limit"
                                            v-model="promotionForms[promotionIndex].remaining_quantity"
                                            @input="captureChange(promotionIndex, 'Remaining quantity changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.remaining_quantity`)">
                                        </Input>
                                    </div>
                                </td>
                                <!-- New Customer -->
                                <td v-if="column.name == 'New Customer'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center justify-center">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_for_new_customer"
                                            @change="captureChange(promotionIndex, 'Activate for new customer changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.activate_for_new_customer`)">
                                        </Input>
                                    </div>
                                </td>
                                <!-- Existing Customer -->
                                <td v-if="column.name == 'Existing Customer'" class="whitespace-nowrap align-center pr-4 py-4">
                                    <div class="flex items-center justify-center">
                                        <Input
                                            type="checkbox"
                                            v-model="promotionForms[promotionIndex].activate_for_existing_customer"
                                            @change="captureChange(promotionIndex, 'Activate for existing customer changed')"
                                            :errorText="formState.getFormError(`promotion.${promotionIndex}.activate_for_existing_customer`)">
                                        </Input>
                                    </div>
                                </td>
                                <!-- Actions -->
                                <td v-if="columnIndex == (columns.length - 1)" class="align-center pr-4 py-4">
                                    <div class="flex items-center space-x-4">
                                        <!-- View Button -->
                                        <span v-if="!isDeletingPromotion(promotionForms[promotionIndex])" @click.stop.prevent="onView(promotionForms[promotionIndex])" class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">View</span>
                                        <!-- Deleting Loader -->
                                        <Loader v-if="isDeletingPromotion(promotionForms[promotionIndex])" type="danger">
                                            <span class="text-xs ml-2">Deleting...</span>
                                        </Loader>
                                        <!-- Delete Button -->
                                        <span v-else @click.stop.prevent="showDeleteConfirmationModal(promotionForms[promotionIndex])" class="text-sm font-medium text-red-600 dark:text-red-500 hover:underline">Delete</span>
                                    </div>
                                </td>
                            </template>
                        </template>
                    </tr>
                </template>

            </Table>

        </div>

        <!-- Update Promotions -->
        <Modal
            size="md"
            approveType="primary"
            :scrollOnContent="false"
            ref="updatePromotionsModal"
            :leftApproveIcon="RefreshCcw"
            approveText="Update Promotions"
            :approveLoading="isUpdatingPromotions"
            :approveAction="() => updatePromotions('bulk')">

            <template #content>

                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Update Promotions</p>

                <div class="space-y-4 mb-8">

                    <!-- Update Scope -->
                    <Select
                        class="w-full"
                        :search="false"
                        label="Update Scope"
                        v-model="updateScope"
                        :options="updateScopeOptions"
                        placeholder="Select update scope">
                    </Select>

                    <!-- Active Status -->
                    <Select
                        class="w-full"
                        :search="false"
                        label="Status"
                        v-model="active"
                        :options="activeTypes"
                        placeholder="Select status">
                    </Select>

                    <div class="p-4 bg-blue-50 rounded-lg space-y-4">

                        <p class="text-lg text-gray-700 font-semibold">Offers</p>

                        <!-- Offer Discount -->
                        <Input
                            type="checkbox"
                            v-model="offer_discount"
                            inputLabel="Offer Discount">
                        </Input>

                        <template v-if="offer_discount">

                            <!-- Discount Rate Type -->
                            <Select
                                class="w-full"
                                :search="false"
                                label="Discount Rate Type"
                                :options="discountRateTypes"
                                v-model="discount_rate_type"
                                placeholder="Select discount rate type">
                            </Select>

                            <!-- Discount Percentage Rate -->
                            <Input
                                class="w-full"
                                type="percentage"
                                label="Discount Percentage Rate"
                                v-model="discount_percentage_rate"
                                placeholder="Enter discount percentage"
                                v-if="discount_rate_type == 'percentage'">
                            </Input>

                            <!-- Discount Flat Rate -->
                            <Input
                                type="money"
                                class="w-full"
                                label="Discount Flat Rate"
                                v-model="discount_flat_rate"
                                v-if="discount_rate_type == 'flat'"
                                placeholder="Enter flat discount amount">
                            </Input>

                        </template>

                        <!-- Offer Free Delivery -->
                        <Input
                            type="checkbox"
                            v-model="offer_free_delivery"
                            inputLabel="Offer Free Delivery">
                        </Input>

                    </div>

                    <div class="p-4 bg-indigo-50 rounded-lg space-y-4">

                        <p class="text-lg text-gray-700 font-semibold">Conditions</p>

                        <!-- Activate Using Minimum Grand Total -->
                        <Input
                            type="checkbox"
                            v-model="activate_using_minimum_grand_total"
                            inputLabel="Activate Using Minimum Grand Total">
                        </Input>

                        <!-- Minimum Grand Total -->
                        <Input
                            type="money"
                            class="w-full"
                            v-model="minimum_grand_total"
                            placeholder="Enter minimum grand total"
                            v-if="activate_using_minimum_grand_total">
                        </Input>

                        <!-- Activate Using Minimum Total Products -->
                        <Input
                            type="checkbox"
                            v-model="activate_using_minimum_total_products"
                            inputLabel="Activate Using Minimum Total Products">
                        </Input>

                        <!-- Minimum Total Products -->
                        <Input
                            type="number"
                            class="w-full"
                            v-model="minimum_total_products"
                            placeholder="Enter minimum total products"
                            v-if="activate_using_minimum_total_products">
                        </Input>

                        <!-- Activate Using Minimum Total Product Quantities -->
                        <Input
                            type="checkbox"
                            v-model="activate_using_minimum_total_product_quantities"
                            inputLabel="Activate Using Minimum Total Product Quantities">
                        </Input>

                        <!-- Minimum Total Product Quantities -->
                        <Input
                            type="number"
                            class="w-full"
                            v-model="minimum_total_product_quantities"
                            placeholder="Enter minimum total product quantities"
                            v-if="activate_using_minimum_total_product_quantities">
                        </Input>

                        <!-- Activate Using Start Datetime -->
                        <Input
                            type="checkbox"
                            v-model="activate_using_start_datetime"
                            inputLabel="Activate Using Start Datetime">
                        </Input>

                        <!-- Start Datetime -->
                        <Datepicker
                            :enableTimePicker="true"
                            v-model="start_datetime"
                            format="dd MMM yyyy HH:mm"
                            modelType="yyyy-MM-dd HH:mm"
                            v-if="activate_using_start_datetime"
                            placeholder="Select start datetime">
                        </Datepicker>

                        <!-- Activate Using End Datetime -->
                        <Input
                            type="checkbox"
                            v-model="activate_using_end_datetime"
                            inputLabel="Activate Using End Datetime">
                        </Input>

                        <!-- End Datetime -->
                        <Datepicker
                            v-model="end_datetime"
                            :enableTimePicker="true"
                            format="dd MMM yyyy HH:mm"
                            modelType="yyyy-MM-dd HH:mm"
                            placeholder="Select end datetime"
                            v-if="activate_using_end_datetime">
                        </Datepicker>

                        <!-- Activate Using Hours of Day -->
                        <Input
                            type="checkbox"
                            v-model="activate_using_hours_of_day"
                            inputLabel="Activate Using Hours of Day">
                        </Input>

                        <!-- Hours of Day -->
                        <SelectTags
                            class="w-full"
                            :options="hourOptions"
                            v-model="hours_of_day"
                            placeholder="Select hours"
                            v-if="activate_using_hours_of_day">
                        </SelectTags>

                        <!-- Activate Using Days of the Week -->
                        <Input
                            type="checkbox"
                            v-model="activate_using_days_of_the_week"
                            inputLabel="Activate Using Days of the Week">
                        </Input>

                        <!-- Days of the Week -->
                        <SelectTags
                            class="w-full"
                            :options="dayOptions"
                            placeholder="Select days"
                            v-model="days_of_the_week"
                            v-if="activate_using_days_of_the_week">
                        </SelectTags>

                        <!-- Activate Using Days of the Month -->
                        <Input
                            type="checkbox"
                            v-model="activate_using_days_of_the_month"
                            inputLabel="Activate Using Days of the Month">
                        </Input>

                        <!-- Days of the Month -->
                        <SelectTags
                            class="w-full"
                            placeholder="Select days"
                            v-model="days_of_the_month"
                            :options="dayOfMonthOptions"
                            v-if="activate_using_days_of_the_month">
                        </SelectTags>

                        <!-- Activate Using Months of the Year -->
                        <Input
                            type="checkbox"
                            v-model="activate_using_months_of_the_year"
                            inputLabel="Activate Using Months of the Year">
                        </Input>

                        <!-- Months of the Year -->
                        <SelectTags
                            class="w-full"
                            :options="monthOptions"
                            placeholder="Select months"
                            v-model="months_of_the_year"
                            v-if="activate_using_months_of_the_year">
                        </SelectTags>

                        <!-- Activate Using Usage Limit -->
                        <Input
                            type="checkbox"
                            v-model="activate_using_usage_limit"
                            inputLabel="Activate Using Usage Limit">
                        </Input>

                        <!-- Remaining Quantity -->
                        <Input
                            type="number"
                            class="w-full"
                            v-model="remaining_quantity"
                            v-if="activate_using_usage_limit"
                            placeholder="Enter remaining quantity">
                        </Input>

                        <!-- Activate for New Customer -->
                        <Input
                            type="checkbox"
                            v-model="activate_for_new_customer"
                            inputLabel="Activate for New Customer">
                        </Input>

                        <!-- Activate for Existing Customer -->
                        <Input
                            type="checkbox"
                            v-model="activate_for_existing_customer"
                            inputLabel="Activate for Existing Customer">
                        </Input>

                    </div>
                </div>
            </template>
        </Modal>

        <!-- Delete Promotions -->
        <Modal
            approveType="danger"
            ref="deletePromotionsModal"
            :leftApproveIcon="Trash2"
            :approveAction="deletePromotions"
            :approveLoading="isDeletingPromotions"
            :approveText="totalCheckedRows == 1 ? 'Delete Promotion' : 'Delete Promotions'">
            <template #content>
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Delete Promotions</p>
                <div class="flex space-x-2 items-center p-4 text-xs bg-red-50 rounded-lg mb-8">
                    <svg class="w-6 h-6 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span class="text-red-500">Are you sure you want to delete {{ totalCheckedRows > 1 ? `these ${totalCheckedRows} promotions` : 'this promotion' }}?</span>
                </div>
            </template>
        </Modal>

        <!-- Confirm Delete Promotion -->
        <Modal
            approveType="danger"
            ref="deletePromotionModal"
            :leftApproveIcon="Trash2"
            approveText="Delete Promotion"
            :approveAction="deletePromotion"
            :approveLoading="isDeletingPromotion(deletablePromotion)">
            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Delete</p>
                <p v-if="deletablePromotion" class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ deletablePromotion.name }}</span>?</p>
            </template>
        </Modal>
    </div>
</template>

<script>
    import axios from 'axios';
    import isEqual from 'lodash.isequal';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import Table from '@Partials/table/Table.vue';
    import Datepicker from '@Partials/Datepicker.vue';
    import SelectTags from '@Partials/SelectTags.vue';
    import { isNotEmpty } from '@Utils/stringUtils';
    import { Plus, Trash2, RefreshCcw, TicketPercent } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'promotionState', 'storeState', 'changeHistoryState', 'notificationState'],
        components: {
            TicketPercent, Input, Modal, Loader, Button, Select, Dropdown, Table, Datepicker, SelectTags, Plus, Trash2, RefreshCcw
        },
        data() {
            return {
                Plus,
                Trash2,
                RefreshCcw,
                perPage: '15',
                checkedRows: [],
                pagination: null,
                searchTerm: null,
                selectAll: false,
                latestRequestId: 0,
                filterExpressions: [],
                sortingExpressions: [],
                hasInitialResults: null,
                isDeletingPromotionIds: [],
                deletablePromotion: null,
                cancelTokenSource: null,
                isLoadingPromotions: false,
                isUpdatingPromotions: false,
                columns: this.prepareColumns(),
                includePromotionFieldNames: true,
                activeTypes: [
                    { label: 'Active', value: true },
                    { label: 'Inactive', value: false },
                ],
                discountRateTypes: [
                    { label: 'Flat', value: 'flat' },
                    { label: 'Percentage', value: 'percentage' },
                ],
                hourOptions: Array.from({ length: 24 }, (_, i) => ({
                    label: i.toString().padStart(2, '0') + ':00',
                    value: i.toString().padStart(2, '0') + ':00'
                })),
                dayOptions: [
                    { label: 'Monday', value: 'Monday' },
                    { label: 'Tuesday', value: 'Tuesday' },
                    { label: 'Wednesday', value: 'Wednesday' },
                    { label: 'Thursday', value: 'Thursday' },
                    { label: 'Friday', value: 'Friday' },
                    { label: 'Saturday', value: 'Saturday' },
                    { label: 'Sunday', value: 'Sunday' },
                ],
                dayOfMonthOptions: Array.from({ length: 31 }, (_, i) => ({
                    label: `${i + 1}`,
                    value: (i + 1).toString().padStart(2, '0')
                })),
                monthOptions: [
                    { label: 'January', value: 'January' },
                    { label: 'February', value: 'February' },
                    { label: 'March', value: 'March' },
                    { label: 'April', value: 'April' },
                    { label: 'May', value: 'May' },
                    { label: 'June', value: 'June' },
                    { label: 'July', value: 'July' },
                    { label: 'August', value: 'August' },
                    { label: 'September', value: 'September' },
                    { label: 'October', value: 'October' },
                    { label: 'November', value: 'November' },
                    { label: 'December', value: 'December' },
                ],
                bulkSelectionOptions: [
                    {
                        label: 'Update Promotions',
                        action: this.showUpdatePromotionsModal
                    },
                    {
                        label: 'Delete',
                        action: this.showDeletePromotionsModal
                    }
                ],
                updateScope: 'checked',
                updateScopeOptions: [
                    { label: 'Update Only Checked Fields', value: 'checked' },
                    { label: 'Update Checked & Unchecked Fields', value: 'all' },
                ],
                active: true,
                code: null,
                offer_discount: false,
                discount_rate_type: 'flat',
                discount_percentage_rate: '0',
                discount_flat_rate: '0.00',
                offer_free_delivery: false,
                activate_using_code: false,
                activate_using_minimum_grand_total: false,
                minimum_grand_total: '0.00',
                activate_using_minimum_total_products: false,
                minimum_total_products: '1',
                activate_using_minimum_total_product_quantities: false,
                minimum_total_product_quantities: '1',
                activate_using_start_datetime: false,
                start_datetime: null,
                activate_using_end_datetime: false,
                end_datetime: null,
                activate_using_hours_of_day: false,
                hours_of_day: [],
                activate_using_days_of_the_week: false,
                days_of_the_week: [],
                activate_using_days_of_the_month: false,
                days_of_the_month: [],
                activate_using_months_of_the_year: false,
                months_of_the_year: [],
                activate_using_usage_limit: false,
                remaining_quantity: '0',
                activate_for_new_customer: false,
                activate_for_existing_customer: false
            };
        },
        watch: {
            store(newValue, oldValue) {
                if (!oldValue && newValue) {
                    this.showPromotions();
                }
            },
            selectAll(newValue) {
                this.checkedRows = this.promotionForms.reduce((acc, promotionForm) => {
                    acc[promotionForm.id] = newValue;
                    return acc;
                }, {});
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasSearchTerm() {
                return this.isNotEmpty(this.searchTerm);
            },
            isDeletingPromotions() {
                return this.isDeletingPromotionIds.length > 0;
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
            promotionForms() {
                return this.promotionState.promotionForms;
            },
        },
        methods: {
            isNotEmpty,
            prepareColumns() {
                const columnNames = [
                    'Name', 'Description', 'Active', 'Discount', 'Free Delivery', 'Code', 'Minimum Grand Total',
                    'Minimum Total Products', 'Minimum Total Product Quantities', 'Start Datetime', 'End Datetime',
                    'Hours of Day', 'Days of the Week', 'Days of the Month', 'Months of the Year', 'Usage Limit',
                    'New Customer', 'Existing Customer'
                ];
                const defaultColumnNames = [
                    'Name', 'Description', 'Active', 'Discount', 'Free Delivery', 'Code', 'Minimum Grand Total',
                    'Minimum Total Products', 'Minimum Total Product Quantities', 'Start Datetime', 'End Datetime',
                    'Hours of Day', 'Days of the Week', 'Days of the Month', 'Months of the Year', 'Usage Limit',
                    'New Customer', 'Existing Customer'
                ];
                return columnNames.map(name => ({
                    name,
                    active: defaultColumnNames.includes(name),
                    priority: defaultColumnNames.includes(name)
                }));
            },
            showDeletePromotionsModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.deletePromotionsModal.showModal();
            },
            showUpdatePromotionsModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.updatePromotionsModal.showModal();
            },
            showDeleteConfirmationModal(promotion) {
                this.deletablePromotion = promotion;
                this.$refs.deletePromotionModal.showModal();
            },
            isDeletingPromotion(promotion) {
                if (promotion == null) return false;
                return this.isDeletingPromotionIds.findIndex((id) => id == promotion.id) != -1;
            },
            onView(promotion) {
                this.$router.push({
                    name: 'edit-promotion',
                    params: {
                        promotion_id: promotion.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            onAddPromotion() {
                this.$router.push({
                    name: 'create-promotion',
                    query: { store_id: this.store.id }
                });
            },
            captureChange(promotionIndex, actionName) {
                this.promotionForms[promotionIndex].modified = true;
                this.promotionState.saveStateDebounced(actionName);
            },
            paginate(page) {
                this.showPromotions(page);
            },
            search(searchTerm) {
                this.searchTerm = searchTerm;
                this.showPromotions();
            },
            updatedColumns(columns) {
                this.columns = columns;
            },
            updatedFilters(filters) {
                const newFilterExpressions = filters.map((filter) => filter.expression);
                if (!isEqual(this.filterExpressions, newFilterExpressions)) {
                    this.filterExpressions = newFilterExpressions;
                    this.showPromotions();
                }
            },
            updatedSorting(sorting) {
                const newSortingExpressions = sorting.map((sort) => sort.expression);
                if (!isEqual(this.sortingExpressions, newSortingExpressions)) {
                    this.sortingExpressions = newSortingExpressions;
                    this.showPromotions();
                }
            },
            updatedPerPage(perPage) {
                this.perPage = perPage;
                this.showPromotions();
            },
            async showPromotions(page = 1) {

                const currentRequestId = ++this.latestRequestId;

                try {

                    this.isLoadingPromotions = true;

                    if (this.cancelTokenSource) {
                        this.cancelTokenSource.cancel('Request superseded by a newer one');
                    }

                    this.cancelTokenSource = axios.CancelToken.source();

                    let config = {
                        params: {
                            page: page,
                            per_page: this.perPage,
                            store_id: this.store.id
                        },
                        cancelToken: this.cancelTokenSource.token
                    };

                    if (this.hasSearchTerm) config.params['search'] = this.searchTerm;

                    if (this.hasFilterExpressions) {
                        config.params['_filters'] = this.filterExpressions.join('|');
                    }

                    if (this.hasSortingExpressions) {
                        config.params['_sort'] = this.sortingExpressions.join('|');
                    }

                    const response = await axios.get(`/api/promotions`, config);

                    if (currentRequestId !== this.latestRequestId) return;

                    if(this.pagination == null) {
                        this.hasInitialResults = response.data.meta.total > 0;
                    }

                    this.pagination = response.data;
                    const promotions = this.pagination.data;
                    this.promotionState.setPromotionForms(promotions);

                    this.checkedRows = this.promotionForms.reduce((acc, promotionForm) => {
                        acc[promotionForm.id] = false;
                        return acc;
                    }, {});

                } catch (error) {
                    if (axios.isCancel(error)) return;
                    if (currentRequestId !== this.latestRequestId) return;
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching promotions';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch promotions:', error);
                } finally {
                    if (currentRequestId !== this.latestRequestId) return;
                    this.isLoadingPromotions = false;
                    this.cancelTokenSource = null;
                }
            },
            async updatePromotions(type = null) {
                try {
                    if (this.isUpdatingPromotions) return;

                    const data = {
                        store_id: this.store.id
                    };

                    if (type === null) {
                        data['promotions'] = this.promotionForms.filter(promotionForm => promotionForm.modified);
                    } else {

                        let filteredPromotions = this.promotionForms.filter(promotionForm => this.checkedRows[promotionForm.id]);

                        data['promotions'] = filteredPromotions.map(promotionForm => {

                            const updatedPromotion = { id: promotionForm.id };

                            // Active status
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.active !== null)) {
                                updatedPromotion.active = this.active;
                            }

                            // Offer Discount and related fields
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.offer_discount === true)) {
                                if (this.offer_discount !== null) updatedPromotion.offer_discount = this.offer_discount;
                                if (this.offer_discount && this.discount_rate_type !== null) updatedPromotion.discount_rate_type = this.discount_rate_type;
                                if (this.offer_discount && this.discount_rate_type === 'percentage' && this.discount_percentage_rate !== null) {
                                    updatedPromotion.discount_percentage_rate = this.discount_percentage_rate;
                                }
                                if (this.offer_discount && this.discount_rate_type === 'flat' && this.discount_flat_rate !== null) {
                                    updatedPromotion.discount_flat_rate = this.discount_flat_rate;
                                }
                            }

                            // Offer Free Delivery
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.offer_free_delivery === true)) {
                                if (this.offer_free_delivery !== null) updatedPromotion.offer_free_delivery = this.offer_free_delivery;
                            }

                            // Activate Using Minimum Grand Total and related field
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_using_minimum_grand_total === true)) {
                                if (this.activate_using_minimum_grand_total !== null) updatedPromotion.activate_using_minimum_grand_total = this.activate_using_minimum_grand_total;
                                if (this.activate_using_minimum_grand_total && this.minimum_grand_total !== null) updatedPromotion.minimum_grand_total = this.minimum_grand_total;
                            }

                            // Activate Using Minimum Total Products and related field
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_using_minimum_total_products === true)) {
                                if (this.activate_using_minimum_total_products !== null) updatedPromotion.activate_using_minimum_total_products = this.activate_using_minimum_total_products;
                                if (this.activate_using_minimum_total_products && this.minimum_total_products !== null) updatedPromotion.minimum_total_products = this.minimum_total_products;
                            }

                            // Activate Using Minimum Total Product Quantities and related field
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_using_minimum_total_product_quantities === true)) {
                                if (this.activate_using_minimum_total_product_quantities !== null) updatedPromotion.activate_using_minimum_total_product_quantities = this.activate_using_minimum_total_product_quantities;
                                if (this.activate_using_minimum_total_product_quantities && this.minimum_total_product_quantities !== null) updatedPromotion.minimum_total_product_quantities = this.minimum_total_product_quantities;
                            }

                            // Activate Using Start Datetime and related field
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_using_start_datetime === true)) {
                                if (this.activate_using_start_datetime !== null) updatedPromotion.activate_using_start_datetime = this.activate_using_start_datetime;
                                if (this.activate_using_start_datetime && this.start_datetime !== null) updatedPromotion.start_datetime = this.start_datetime;
                            }

                            // Activate Using End Datetime and related field
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_using_end_datetime === true)) {
                                if (this.activate_using_end_datetime !== null) updatedPromotion.activate_using_end_datetime = this.activate_using_end_datetime;
                                if (this.activate_using_end_datetime && this.end_datetime !== null) updatedPromotion.end_datetime = this.end_datetime;
                            }

                            // Activate Using Hours of Day and related field
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_using_hours_of_day === true)) {
                                if (this.activate_using_hours_of_day !== null) updatedPromotion.activate_using_hours_of_day = this.activate_using_hours_of_day;
                                if (this.activate_using_hours_of_day && this.hours_of_day !== null) updatedPromotion.hours_of_day = this.hours_of_day;
                            }

                            // Activate Using Days of the Week and related field
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_using_days_of_the_week === true)) {
                                if (this.activate_using_days_of_the_week !== null) updatedPromotion.activate_using_days_of_the_week = this.activate_using_days_of_the_week;
                                if (this.activate_using_days_of_the_week && this.days_of_the_week !== null) updatedPromotion.days_of_the_week = this.days_of_the_week;
                            }

                            // Activate Using Days of the Month and related field
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_using_days_of_the_month === true)) {
                                if (this.activate_using_days_of_the_month !== null) updatedPromotion.activate_using_days_of_the_month = this.activate_using_days_of_the_month;
                                if (this.activate_using_days_of_the_month && this.days_of_the_month !== null) updatedPromotion.days_of_the_month = this.days_of_the_month;
                            }

                            // Activate Using Months of the Year and related field
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_using_months_of_the_year === true)) {
                                if (this.activate_using_months_of_the_year !== null) updatedPromotion.activate_using_months_of_the_year = this.activate_using_months_of_the_year;
                                if (this.activate_using_months_of_the_year && this.months_of_the_year !== null) updatedPromotion.months_of_the_year = this.months_of_the_year;
                            }

                            // Activate Using Usage Limit and related field
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_using_usage_limit === true)) {
                                if (this.activate_using_usage_limit !== null) updatedPromotion.activate_using_usage_limit = this.activate_using_usage_limit;
                                if (this.activate_using_usage_limit && this.remaining_quantity !== null) updatedPromotion.remaining_quantity = this.remaining_quantity;
                            }

                            // Activate for New Customer
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_for_new_customer === true)) {
                                if (this.activate_for_new_customer !== null) updatedPromotion.activate_for_new_customer = this.activate_for_new_customer;
                            }

                            // Activate for Existing Customer
                            if (this.updateScope === 'all' || (this.updateScope === 'checked' && this.activate_for_existing_customer === true)) {
                                if (this.activate_for_existing_customer !== null) updatedPromotion.activate_for_existing_customer = this.activate_for_existing_customer;
                            }

                            return updatedPromotion;
                        });
                    }

                    if (data['promotions'].length > 0) {
                        this.isUpdatingPromotions = true;
                        await axios.put(`/api/promotions`, data);

                        this.showPromotions();
                        this.notificationState.showSuccessNotification('Promotions updated');
                    }

                    data['promotions'].forEach(promotionForm => {
                        if (this.checkedRows[promotionForm.id] !== undefined) {
                            this.checkedRows[promotionForm.id] = false;
                        }
                    });

                    this.selectAll = false;
                    this.resetBulkUpdateFields();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating promotions';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update promotions:', error);
                } finally {
                    this.isUpdatingPromotions = false;
                    this.$refs.updatePromotionsModal.hideModal();
                }
            },
            async deletePromotions() {
                try {

                    const promotionFormIds = this.promotionForms.filter(promotionForm => this.checkedRows[promotionForm.id]).map(promotionForm => promotionForm.id)
                        .filter(promotionFormId => !this.isDeletingPromotionIds.includes(promotionFormId));

                    if (promotionFormIds.length === 0) return;

                    this.isDeletingPromotionIds.push(...promotionFormIds);

                    const config = {
                        data: {
                            store_id: this.store.id,
                            promotion_ids: promotionFormIds
                        }
                    };

                    await axios.delete(`/api/promotions`, config);

                    this.notificationState.showSuccessNotification(promotionFormIds.length == 1 ? 'Promotion deleted' : 'Promotions deleted');
                    this.promotionState.promotionForms = this.promotionForms.filter(promotionForm => !promotionFormIds.includes(promotionForm.id));

                    if (this.promotionForms.length == 0) this.showPromotions();

                    this.isDeletingPromotionIds = this.isDeletingPromotionIds.filter(id => !promotionFormIds.includes(id));
                    promotionFormIds.forEach(promotionFormId => {
                        if (this.checkedRows[promotionFormId] !== undefined) {
                            this.checkedRows[promotionFormId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting promotions';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete promotions:', error);
                } finally {
                    this.$refs.deletePromotionsModal.hideModal();
                }
            },
            async deletePromotion() {

                try {

                    if (this.isDeletingPromotionIds.includes(this.deletablePromotion.id)) return;
                    this.isDeletingPromotionIds.push(this.deletablePromotion.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    };

                    await axios.delete(`/api/promotions/${this.deletablePromotion.id}`, config);

                    this.notificationState.showSuccessNotification('Promotion deleted');

                    this.promotionState.promotionForms = this.promotionForms.filter(promotionForm => promotionForm.id != this.deletablePromotion.id);
                    if (this.promotionForms.length == 0) this.showPromotions();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting promotion';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete promotion:', error);
                } finally {
                    this.isDeletingPromotionIds.splice(this.isDeletingPromotionIds.findIndex((id) => id == this.deletablePromotion.id), 1);
                    this.$refs.deletePromotionModal.hideModal();
                }
            },
            resetBulkUpdateFields() {
                this.active = true;
                this.offer_discount = false;
                this.discount_rate_type = 'flat';
                this.discount_percentage_rate = '0';
                this.discount_flat_rate = '0.00';
                this.offer_free_delivery = false;
                this.activate_using_minimum_grand_total = false;
                this.minimum_grand_total = '0.00';
                this.activate_using_minimum_total_products = false;
                this.minimum_total_products = '1';
                this.activate_using_minimum_total_product_quantities = false;
                this.minimum_total_product_quantities = '1';
                this.activate_using_start_datetime = false;
                this.start_datetime = null;
                this.activate_using_end_datetime = false;
                this.end_datetime = null;
                this.activate_using_hours_of_day = false;
                this.hours_of_day = [];
                this.activate_using_days_of_the_week = false;
                this.days_of_the_week = [];
                this.activate_using_days_of_the_month = false;
                this.days_of_the_month = [];
                this.activate_using_months_of_the_year = false;
                this.months_of_the_year = [];
                this.activate_using_usage_limit = false;
                this.remaining_quantity = '0';
                this.activate_for_new_customer = false;
                this.activate_for_existing_customer = false;
            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    'Save Changes',
                    this.updatePromotions,
                    'primary',
                    null
                );
            },
            setPromotionForms(promotionForms) {
                this.promotionState.promotionForms = promotionForms;
            }
        },
        unmounted() {
            this.promotionState.reset();
        },
        created() {
            this.setActionButtons();
            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];
            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setPromotionForms;
            }
            this.isLoadingPromotions = true;
            this.searchTerm = this.$route.query.searchTerm;
            if (this.$route.query.filterExpressions) this.filterExpressions = this.$route.query.filterExpressions.split('|');
            if (this.$route.query.sortingExpressions) this.sortingExpressions = this.$route.query.sortingExpressions.split('|');
            if (this.store) this.showPromotions();
        }
    };
</script>
