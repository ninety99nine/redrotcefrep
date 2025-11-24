<template>

    <div class="pt-24 px-4 pb-80">

        <div class="grid grid-cols-12 gap-4 mb-4">

            <div class="col-span-8 col-start-3">

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

                        <div v-if="isLoadingStore || isLoadingPromotion || (isEditing && !hasPromotion)" class="flex items-center space-x-2">
                            <Skeleton width="w-40"></Skeleton>
                            <Skeleton width="w-4"></Skeleton>
                        </div>

                        <template v-else>

                            <!-- Heading -->
                            <div class="flex items-center space-x-1">
                                <h1 class="text-lg text-gray-700 font-semibold">
                                    {{ isCreating ? 'Add Promotion' : promotionForm.name || '...' }}
                                </h1>
                                <Popover content="Promotions are special offers that provide discounts or free delivery to customers based on specific conditions." placement="top"></Popover>
                            </div>

                        </template>

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingPromotion || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class = "bg-white rounded-lg space-y-4 p-4">

                        <div class="grid grid-cols-2 gap-4">

                            <!-- Name Input -->
                            <Input
                                type="text"
                                label="Name"
                                placeholder="10% Off"
                                v-model="promotionForm.name"
                                :errorText="formState.getFormError('name')"
                                tooltipContent="The name of the promotion, e.g., 10% Off"
                                @input="promotionState.saveStateDebounced('Name changed')">
                            </Input>

                            <!-- Active Select -->
                            <Select
                                class="w-full"
                                label="Status"
                                :search="false"
                                :options="activeTypes"
                                v-model="promotionForm.active"
                                :errorText="formState.getFormError('active')"
                                @change="promotionState.saveStateDebounced('Active status changed')"
                                tooltipContent="Turn on if you want your promotion to be available on checkout">
                            </Select>

                        </div>

                        <!-- Description Textarea -->
                        <Input
                            rows="2"
                            type="textarea"
                            label="Description"
                            v-model="promotionForm.description"
                            placeholder="Get 10% off your order"
                            :errorText="formState.getFormError('description')"
                            tooltipContent="A short description of the promotion"
                            @input="promotionState.saveStateDebounced('Description changed')">
                        </Input>

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingPromotion || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Offers</p>

                        <!-- Offers SelectTags -->
                        <SelectTags
                            placeholder="Add offer"
                            :options="offerOptions"
                            v-model="selectedOffers"
                            @change="handleOffersChange"
                            tooltipContent="Select the offers to include in the promotion">
                        </SelectTags>

                        <template
                            :key="index"
                            v-for="(selectedOffer, index) in selectedOffers">

                            <div v-if="selectedOffer == 'offer_free_delivery' && promotionForm.offer_free_delivery" class="border border-green-200 rounded-lg mb-4">

                                <div class="bg-green-50 flex items-center justify-between rounded-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Truck size="20" class="text-green-600"></Truck>
                                        <span class="text-green-600 text-sm font-semibold">Free delivery</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeOffer('offer_free_delivery')">
                                    </Button>

                                </div>

                            </div>

                            <div v-if="selectedOffer == 'offer_discount' && promotionForm.offer_discount" class="border border-green-200 rounded-lg mb-4">

                                <div class="bg-green-50 border-b border-green-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <CirclePercent size="20" class="text-green-600"></CirclePercent>
                                        <span class="text-green-600 text-sm font-semibold">Discount</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeOffer('offer_discount')">
                                    </Button>

                                </div>

                                <div class="w-full grid grid-cols-2 gap-4 p-4">

                                    <Select
                                        class="w-full"
                                        :search="false"
                                        label="Discount Type"
                                        :options="discountRateTypes"
                                        v-model="promotionForm.discount_rate_type"
                                        :errorText="formState.getFormError('discount_rate_type')"
                                        @change="promotionState.saveStateDebounced('Discount rate type changed')"
                                        tooltipContent="Choose whether the discount is a flat amount or percentage of the checkout total">
                                    </Select>

                                    <Input
                                        type="money"
                                        class="w-full"
                                        label="Flat Rate"
                                        v-model="promotionForm.discount_flat_rate"
                                        tooltipContent="Set the flat discount amount"
                                        v-if="promotionForm.discount_rate_type === 'flat'"
                                        :errorText="formState.getFormError('discount_flat_rate')"
                                        @input="promotionState.saveStateDebounced('Discount flat rate changed')">
                                    </Input>

                                    <Input
                                        class="w-full"
                                        type="percentage"
                                        label="Percentage Rate"
                                        v-model="promotionForm.discount_percentage_rate"
                                        v-if="promotionForm.discount_rate_type === 'percentage'"
                                        :errorText="formState.getFormError('discount_percentage_rate')"
                                        tooltipContent="Set the percentage discount (e.g., 10 for 10%)"
                                        @input="promotionState.saveStateDebounced('Discount percentage rate changed')">
                                    </Input>

                                </div>

                            </div>

                        </template>

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingPromotion || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <p class="text-lg text-gray-700 font-semibold">Conditions</p>

                        <!-- Conditions SelectTags -->
                        <SelectTags
                            placeholder="Add condition"
                            :options="conditionOptions"
                            v-model="selectedConditions"
                            @change="handleConditionsChange"
                            tooltipContent="Select the conditions to include in the promotion">
                        </SelectTags>

                        <template
                            :key="index"
                            v-for="(selectedCondition, index) in selectedConditions">

                            <div v-if="selectedCondition == 'offer_free_delivery' && promotionForm.offer_free_delivery" class="border border-green-200 rounded-lg mb-4">

                                <div class="bg-green-50 flex items-center justify-between rounded-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Truck size="20" class="text-green-600"></Truck>
                                        <span class="text-green-600 text-sm font-semibold">Free delivery</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeOffer('offer_free_delivery')">
                                    </Button>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_code' && promotionForm.activate_using_code" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Asterisk size="20" class="text-indigo-600"></Asterisk>
                                        <span class="text-indigo-600 text-sm font-semibold">Promo code</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_code')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <Input
                                        type="text"
                                        placeholder="PROMO123"
                                        v-model="promotionForm.code"
                                        :errorText="formState.getFormError('code')"
                                        @input="promotionState.saveStateDebounced('Code changed')"
                                        tooltipContent="The code customers must enter to claim the promotion">
                                    </Input>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_for_new_customer' && promotionForm.activate_for_new_customer" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 flex items-center justify-between rounded-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <UserRoundPlus size="20" class="text-indigo-600"></UserRoundPlus>
                                        <span class="text-indigo-600 text-sm font-semibold">New customers</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_for_new_customer')">
                                    </Button>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_for_existing_customer' && promotionForm.activate_for_existing_customer" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 flex items-center justify-between rounded-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <UserRoundSearch size="20" class="text-indigo-600"></UserRoundSearch>
                                        <span class="text-indigo-600 text-sm font-semibold">Existing customers</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_for_existing_customer')">
                                    </Button>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_usage_limit' && promotionForm.activate_using_usage_limit" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Users size="20" class="text-indigo-600"></Users>
                                        <span class="text-indigo-600 text-sm font-semibold">Usage limit</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_usage_limit')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <Input
                                        min="0"
                                        type="number"
                                        placeholder="100"
                                        v-model="promotionForm.remaining_quantity"
                                        :errorText="formState.getFormError('remaining_quantity')"
                                        @input="promotionState.saveStateDebounced('Remaining quantity changed')"
                                        tooltipContent="Set the number of times the promotion can still be used">
                                    </Input>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_minimum_grand_total' && promotionForm.activate_using_minimum_grand_total" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Receipt size="20" class="text-indigo-600"></Receipt>
                                        <span class="text-indigo-600 text-sm font-semibold">Minimum Grand Total</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_minimum_grand_total')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <Input
                                        type="money"
                                        :currency="store.currency"
                                        v-model="promotionForm.minimum_grand_total"
                                        :errorText="formState.getFormError('minimum_grand_total')"
                                        @input="promotionState.saveStateDebounced('Minimum grand total changed')"
                                        tooltipContent="Set the minimum order total required to claim the promotion">
                                    </Input>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_minimum_total_products' && promotionForm.activate_using_minimum_total_products" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Box size="20" class="text-indigo-600"></Box>
                                        <span class="text-indigo-600 text-sm font-semibold">Minimum Total Products</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_minimum_total_products')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <Input
                                        min="1"
                                        type="number"
                                        placeholder="3"
                                        v-model="promotionForm.minimum_total_products"
                                        :errorText="formState.getFormError('minimum_total_products')"
                                        tooltipContent="Set the minimum number of different products required"
                                        @input="promotionState.saveStateDebounced('Minimum total products changed')">
                                    </Input>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_minimum_total_product_quantities' && promotionForm.activate_using_minimum_total_product_quantities" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Boxes size="20" class="text-indigo-600"></Boxes>
                                        <span class="text-indigo-600 text-sm font-semibold">Minimum Total Product Quantities</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_minimum_total_product_quantities')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <Input
                                        min="1"
                                        type="number"
                                        placeholder="10"
                                        v-model="promotionForm.minimum_total_product_quantities"
                                        tooltipContent="Set the minimum total quantity of products required"
                                        :errorText="formState.getFormError('minimum_total_product_quantities')"
                                        @input="promotionState.saveStateDebounced('Minimum total product quantities changed')">
                                    </Input>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_start_datetime' && promotionForm.activate_using_start_datetime" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Clock size="20" class="text-indigo-600"></Clock>
                                        <span class="text-indigo-600 text-sm font-semibold">Start Datetime</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_start_datetime')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <Datepicker
                                        :enableTimePicker="true"
                                        placeholder="Start date"
                                        format="dd MMM yyyy HH:mm"
                                        modelType="yyyy-MM-dd HH:mm"
                                        v-model="promotionForm.start_datetime"
                                        :errorText="formState.getFormError('start_datetime')"
                                        @change="promotionState.saveStateDebounced('Start datetime changed')">
                                    </Datepicker>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_end_datetime' && promotionForm.activate_using_end_datetime" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Clock9 size="20" class="text-indigo-600"></Clock9>
                                        <span class="text-indigo-600 text-sm font-semibold">End Datetime</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_end_datetime')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <Datepicker
                                        placeholder="End date"
                                        :enableTimePicker="true"
                                        format="dd MMM yyyy HH:mm"
                                        modelType="yyyy-MM-dd HH:mm"
                                        v-model="promotionForm.end_datetime"
                                        :errorText="formState.getFormError('end_datetime')"
                                        @change="promotionState.saveStateDebounced('End datetime changed')">
                                    </Datepicker>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_hours_of_day' && promotionForm.activate_using_hours_of_day" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Hourglass size="20" class="text-indigo-600"></Hourglass>
                                        <span class="text-indigo-600 text-sm font-semibold">Hours of day</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_hours_of_day')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <SelectTags
                                        :options="hourOptions"
                                        placeholder="Add hour"
                                        v-model="promotionForm.hours_of_day"
                                        :errorText="formState.getFormError('hours_of_day')"
                                        @change="promotionState.saveStateDebounced('Hours of day changed')"
                                        tooltipContent="Select the hours of the day when the promotion is active">
                                    </SelectTags>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_days_of_the_week' && promotionForm.activate_using_days_of_the_week" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Calendar size="20" class="text-indigo-600"></Calendar>
                                        <span class="text-indigo-600 text-sm font-semibold">Days of the week</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_days_of_the_week')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <SelectTags
                                        :options="dayOptions"
                                        placeholder="Add day"
                                        v-model="promotionForm.days_of_the_week"
                                        :errorText="formState.getFormError('days_of_the_week')"
                                        @change="promotionState.saveStateDebounced('Days of the week changed')"
                                        tooltipContent="Select the days of the week when the promotion is active">
                                    </SelectTags>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_days_of_the_month' && promotionForm.activate_using_days_of_the_month" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Calendar size="20" class="text-indigo-600"></Calendar>
                                        <span class="text-indigo-600 text-sm font-semibold">Days of the month</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_days_of_the_month')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <SelectTags
                                        placeholder="Add day"
                                        :options="dayOfMonthOptions"
                                        v-model="promotionForm.days_of_the_month"
                                        :errorText="formState.getFormError('days_of_the_month')"
                                        @change="promotionState.saveStateDebounced('Days of the month changed')"
                                        tooltipContent="Select the days of the month when the promotion is active">
                                    </SelectTags>

                                </div>

                            </div>

                            <div v-if="selectedCondition == 'activate_using_months_of_the_year' && promotionForm.activate_using_months_of_the_year" class="border border-indigo-200 rounded-lg mb-4">

                                <div class="bg-indigo-50 border-b border-indigo-200 flex items-center justify-between rounded-t-lg space-x-4 py-2 px-4">

                                    <div class="flex items-center space-x-2">
                                        <Calendar size="20" class="text-indigo-600"></Calendar>
                                        <span class="text-indigo-600 text-sm font-semibold">Months of the year</span>
                                    </div>

                                    <Button
                                        size="xs"
                                        type="bareDanger"
                                        :leftIcon="Trash2"
                                        :action="() => removeCondition('activate_using_months_of_the_year')">
                                    </Button>

                                </div>

                                <div class="p-4">

                                    <SelectTags
                                        :options="monthOptions"
                                        placeholder="Add month"
                                        v-model="promotionForm.months_of_the_year"
                                        :errorText="formState.getFormError('months_of_the_year')"
                                        @change="promotionState.saveStateDebounced('Months of the year changed')"
                                        tooltipContent="Select the months of the year when the promotion is active">
                                    </SelectTags>

                                </div>

                            </div>

                        </template>

                        <div
                            v-if="!hasSelectedConditions"
                            class="bg-indigo-50 border border-indigo-200 text-indigo-600 p-4 rounded-lg text-sm">
                            <span class="font-bold">No conditions</span>
                            <span> 󠁯•󠁏󠁏 Just place an order to enjoy this offer!</span>
                        </div>

                    </div>

                </div>

                <div
                    v-if="promotion"
                    :class="['overflow-hidden rounded-lg space-y-4 p-4 border mb-20', isLoadingPromotion ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">
                    <!-- Delete Promotion Info -->
                    <p>Do you want to permanently delete <span class="font-bold text-black">{{ promotionForm.name }}</span>? Once this promotion is deleted you will not be able to recover it.</p>
                    <div class="flex justify-end">
                        <Modal
                            triggerType="danger"
                            approveType="danger"
                            :approveLeftIcon="Trash2"
                            triggerText="Delete Promotion"
                            approveText="Delete Promotion"
                            :approveAction="deletePromotion"
                            :triggerLoading="isDeletingPromotion"
                            :approveLoading="isDeletingPromotion">
                            <template #content>
                                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                                <p class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ promotionForm.name }}</span>?</p>
                            </template>
                        </Modal>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import Switch from '@Partials/Switch.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import { isEmpty } from '@Utils/stringUtils';
    import Skeleton from '@Partials/Skeleton.vue';
    import Datepicker from '@Partials/Datepicker.vue';
    import SelectTags from '@Partials/SelectTags.vue';
    import BackdropLoader from '@Partials/BackdropLoader.vue';
    import { Plus, Truck, Trash2, MoveLeft, Box, Boxes, Users, Clock, Hourglass, Clock9, Receipt, Calendar, Asterisk, UserRoundSearch, UserRoundPlus, CirclePercent } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'promotionState', 'changeHistoryState', 'notificationState'],
        components: {
            Input, Modal, Button, Loader, Switch, Select, Popover, Skeleton, Datepicker, SelectTags, BackdropLoader, Plus,
            Truck, Trash2, Box, Boxes, Users, Clock, Hourglass, Clock9, Receipt, Calendar, Asterisk, UserRoundSearch, UserRoundPlus, MoveLeft, CirclePercent
        },
        data() {
            return {
                Plus,
                Trash2,
                MoveLeft,
                selectedOffers: [],
                offerOptions: [
                    { label: 'Discount', value: 'offer_discount' },
                    { label: 'Free Delivery', value: 'offer_free_delivery' }
                ],
                selectedConditions: [],
                conditionOptions: [
                    { label: 'Promo Code', value: 'activate_using_code' },
                    { label: 'New Customers', value: 'activate_for_new_customer' },
                    { label: 'Existing Customers', value: 'activate_for_existing_customer' },
                    { label: 'Usage Limit', value: 'activate_using_usage_limit' },
                    { label: 'Minimum Grand Total', value: 'activate_using_minimum_grand_total' },
                    { label: 'Minimum Total Products', value: 'activate_using_minimum_total_products' },
                    { label: 'Minimum Total Product Quantities', value: 'activate_using_minimum_total_product_quantities' },
                    { label: 'Start Datetime', value: 'activate_using_start_datetime' },
                    { label: 'End Datetime', value: 'activate_using_end_datetime' },
                    { label: 'Hours of Day', value: 'activate_using_hours_of_day' },
                    { label: 'Days of the Week', value: 'activate_using_days_of_the_week' },
                    { label: 'Days of the Month', value: 'activate_using_days_of_the_month' },
                    { label: 'Months of the Year', value: 'activate_using_months_of_the_year' },
                ],
                activeTypes: [
                    { label: 'Active', value: true},
                    { label: 'Inactive', value: false},
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
            };
        },
        watch: {
            store(newValue, oldValue) {
                if (!oldValue && newValue) {
                    this.setup();
                }
            },
            promotionId(newValue) {
                if (newValue) {
                    this.setup();
                    this.setActionButtons();
                }
            },
            promotionForm: {
                handler() {
                    this.setSelectedOffers();
                    this.setSelectedConditions();
                },
                deep: true
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            promotion() {
                return this.promotionState.promotion;
            },
            hasPromotion() {
                return this.promotionState.hasPromotion;
            },
            promotionId() {
                return this.$route.params.promotion_id;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingPromotion() {
                return this.promotionState.isLoadingPromotion;
            },
            isEditing() {
                return this.$route.name === 'edit-promotion';
            },
            isCreating() {
                return this.$route.name === 'create-promotion';
            },
            promotionForm() {
                return this.promotionState.promotionForm;
            },
            isSubmitting() {
                if (this.changeHistoryState.actionButtons.length == 0) return false;
                return this.changeHistoryState.actionButtons[1].loading;
            },
            isDeletingPromotion() {
                return this.promotionState.isDeletingPromotion;
            },
            hasSelectedConditions() {
                return this.selectedConditions.length > 0;
            }
        },
        methods: {
            isEmpty,
            goBack() {
                if (window.history.length > 1) {
                    this.$router.back()
                } else {
                    this.navigateToPromotions();
                }
            },
            async setup() {
                if (this.promotionForm == null) this.promotionState.setPromotionForm(null, this.isCreating);
                if (this.isEditing && this.store) await this.showPromotion();
            },
            async navigateToPromotions() {
                await this.$router.replace({
                    name: 'show-promotions',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async navigateToEditPromotion(promotion) {
                await this.$router.push({
                    name: 'edit-promotion',
                    params: {
                        promotion_id: promotion.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            setSelectedItems(keys, targetProperty) {
                this[targetProperty] = keys.filter(key => this.promotionForm[key]);
            },
            setSelectedOffers() {
                const offerKeys = ['offer_discount', 'offer_free_delivery'];
                this.setSelectedItems(offerKeys, 'selectedOffers');
            },
            setSelectedConditions() {
                const conditionKeys = [
                    'activate_using_code',
                    'activate_for_new_customer',
                    'activate_for_existing_customer',
                    'activate_using_usage_limit',
                    'activate_using_minimum_grand_total',
                    'activate_using_minimum_total_products',
                    'activate_using_minimum_total_product_quantities',
                    'activate_using_start_datetime',
                    'activate_using_end_datetime',
                    'activate_using_hours_of_day',
                    'activate_using_days_of_the_week',
                    'activate_using_days_of_the_month',
                    'activate_using_months_of_the_year',
                ];
                this.setSelectedItems(conditionKeys, 'selectedConditions');
            },
            addOffer(offer) {
                const messages = {
                    offer_discount: 'Discount added',
                    offer_free_delivery: 'Free delivery added'
                };

                this.promotionForm[offer] = true;
                this.promotionState.saveStateDebounced(messages[offer]);
            },
            removeOffer(offer) {
                const messages = {
                    offer_discount: 'Discount removed',
                    offer_free_delivery: 'Free delivery removed'
                };

                this.promotionForm[offer] = false;
                this.promotionState.saveStateDebounced(messages[offer]);
            },
            handleOffersChange(selectedOffers) {
                const offers = ['offer_discount', 'offer_free_delivery'];

                offers.forEach(offer => {
                    if (selectedOffers.includes(offer) && !this.promotionForm[offer]) {
                        this.addOffer(offer);
                    } else if (!selectedOffers.includes(offer) && this.promotionForm[offer]) {
                        this.removeOffer(offer);
                    }
                });
            },
            addCondition(condition) {
                const messages = {
                    activate_using_code: 'Promo code added',
                    activate_for_new_customer: 'New customers added',
                    activate_for_existing_customer: 'Existing customers added',
                    activate_using_usage_limit: 'Usage limit added',
                    activate_using_minimum_grand_total: 'Minimum grand total added',
                    activate_using_minimum_total_products: 'Minimum total products added',
                    activate_using_minimum_total_product_quantities: 'Minimum total product quantities added',
                    activate_using_start_datetime: 'Start datetime added',
                    activate_using_end_datetime: 'End datetime added',
                    activate_using_hours_of_day: 'Hours of day added',
                    activate_using_days_of_the_week: 'Days of the week added',
                    activate_using_days_of_the_month: 'Days of the month added',
                    activate_using_months_of_the_year: 'Months of the year added',
                };

                this.promotionForm[condition] = true;
                this.promotionState.saveStateDebounced(messages[condition]);
            },
            removeCondition(condition) {
                const messages = {
                    activate_using_code: 'Promo code removed',
                    activate_for_new_customer: 'New customers removed',
                    activate_for_existing_customer: 'Existing customers removed',
                    activate_using_usage_limit: 'Usage limit removed',
                    activate_using_minimum_grand_total: 'Minimum grand total removed',
                    activate_using_minimum_total_products: 'Minimum total products removed',
                    activate_using_minimum_total_product_quantities: 'Minimum total product quantities removed',
                    activate_using_start_datetime: 'Start datetime removed',
                    activate_using_end_datetime: 'End datetime removed',
                    activate_using_hours_of_day: 'Hours of day removed',
                    activate_using_days_of_the_week: 'Days of the week removed',
                    activate_using_days_of_the_month: 'Days of the month removed',
                    activate_using_months_of_the_year: 'Months of the year removed',
                };

                this.promotionForm[condition] = false;
                this.promotionState.saveStateDebounced(messages[condition]);
            },
            handleConditionsChange(selectedConditions) {
                const conditions = [
                    'activate_using_code', 'activate_for_new_customer', 'activate_for_existing_customer', 'activate_using_usage_limit',
                    'activate_using_minimum_grand_total', 'activate_using_minimum_total_products', 'activate_using_minimum_total_product_quantities',
                    'activate_using_start_datetime', 'activate_using_end_datetime', 'activate_using_hours_of_day', 'activate_using_days_of_the_week',
                    'activate_using_days_of_the_month', 'activate_using_months_of_the_year'
                ];

                conditions.forEach(condition => {
                    if (selectedConditions.includes(condition) && !this.promotionForm[condition]) {
                        this.addCondition(condition);
                    } else if (!selectedConditions.includes(condition) && this.promotionForm[condition]) {
                        this.removeCondition(condition);
                    }
                });
            },
            async showPromotion() {
                try {
                    this.promotionState.isLoadingPromotion = true;
                    let config = {
                        params: {
                            store_id: this.store.id,
                        }
                    };
                    const response = await axios.get(`/api/promotions/${this.promotionId}`, config);
                    const promotion = response.data;
                    this.promotionState.setPromotion(promotion);
                    this.promotionState.setPromotionForm(promotion);
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching promotion';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch promotion:', error);
                    if (error.response?.status === 404) {
                        await this.$router.replace({
                            name: 'show-promotions',
                            query: {
                                store_id: this.store.id,
                                searchTerm: this.$route.query.searchTerm,
                                filterExpressions: this.$route.query.filterExpressions,
                                sortingExpressions: this.$route.query.sortingExpressions
                            }
                        });
                    }
                } finally {
                    this.promotionState.isLoadingPromotion = false;
                }
            },
            async createPromotion() {
                try {
                    if (this.promotionState.isCreatingPromotion) return;
                    this.formState.hideFormErrors();
                    if (this.isEmpty(this.promotionForm.name)) {
                        this.formState.setFormError('name', 'The name is required');
                    }
                    if (this.formState.hasErrors) {
                        return;
                    }
                    this.promotionState.isCreatingPromotion = true;
                    this.changeHistoryState.actionButtons[1].loading = true;
                    const data = {
                        ...this.promotionForm,
                        store_id: this.store.id
                    };
                    const response = await axios.post(`/api/promotions`, data);
                    const createdPromotion = response.data.promotion;

                    this.notificationState.showSuccessNotification(`Promotion created`);
                    this.promotionState.saveOriginalState('Original promotion');
                    await this.navigateToEditPromotion(createdPromotion);
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating promotion';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create promotion:', error);
                } finally {
                    this.promotionState.isCreatingPromotion = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },
            async updatePromotion() {
                try {
                    if (this.promotionState.isUpdatingPromotion) return;
                    this.formState.hideFormErrors();
                    if (this.isEmpty(this.promotionForm.name)) {
                        this.formState.setFormError('name', 'The name is required');
                    }
                    if (this.formState.hasErrors) {
                        return;
                    }
                    this.promotionState.isUpdatingPromotion = true;
                    this.changeHistoryState.actionButtons[1].loading = true;
                    const data = {
                        ...this.promotionForm,
                        store_id: this.store.id
                    };
                    await axios.put(`/api/promotions/${this.promotionForm.id}`, data);
                    this.notificationState.showSuccessNotification(`Promotion updated`);
                    this.promotionState.saveOriginalState('Original promotion');
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating promotion';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update promotion:', error);
                } finally {
                    this.promotionState.isUpdatingPromotion = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },
            async deletePromotion(hideModal) {
                try {
                    if (this.promotionState.isDeletingPromotion) return;
                    this.promotionState.isDeletingPromotion = true;
                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    };
                    await axios.delete(`/api/promotions/${this.promotion.id}`, config);
                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500)); // Wait for modal to close
                    this.notificationState.showSuccessNotification('Promotion deleted');
                    await this.navigateToPromotions();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting promotion';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete promotion:', error);
                    hideModal();
                } finally {
                    this.promotionState.isDeletingPromotion = false;
                }
            },
            setActionButtons() {
                if (this.isCreating || this.isEditing) {
                    this.changeHistoryState.removeButtons();
                    this.changeHistoryState.addDiscardButton();
                    this.changeHistoryState.addActionButton(
                        this.isEditing ? 'Save Changes' : 'Create Promotion',
                        this.isEditing ? this.updatePromotion : this.createPromotion,
                        'primary',
                        null,
                    );
                }
            },
            setPromotionForm(promotionForm) {
                this.promotionState.promotionForm = promotionForm;
            }
        },
        beforeRouteLeave(to, from, next) {
            if (this.changeHistoryState.hasChangeHistory) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        unmounted() {
            this.promotionState.reset();
        },
        created() {
            this.setup();
            this.setActionButtons();
            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];
            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setPromotionForm;
            }
        },
    };
</script>
