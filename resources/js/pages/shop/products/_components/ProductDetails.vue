<template>

    <div v-if="product">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-lg text-gray-700 font-semibold mb-2">{{ product.name }}</h1>

                <Pill v-if="product.is_free" type="success" size="xs">free</Pill>

                <div v-else class="space-x-2">

                    <span v-if="product.on_sale" class="text-right text-lg font-semibold text-gray-900">
                        {{ product.unit_sale_price.amount_with_currency }}
                    </span>

                    <span :class="['text-right', { 'line-through text-xs text-gray-400': product.on_sale, 'text-lg text-gray-900 font-semibold': !product.on_sale }]">
                        {{ product.unit_regular_price.amount_with_currency }}
                    </span>

                </div>

            </div>

            <div class="flex space-x-4">

                <Button
                    size="xs"
                    type="bare"
                    :leftIcon="Search"
                    :action="navigateToSearch">
                </Button>

                <Dropdown
                    position="left"
                    :options="options"
                    dropdownClasses="w-40">

                    <template #trigger="props">

                        <Button
                            size="xs"
                            type="bare"
                            leftIconSize="16"
                            :leftIcon="Share2"
                            :action="props.toggleDropdown">
                        </Button>

                    </template>

                    <template #content="props">

                        <ul class="max-h-60 overflow-auto">

                            <li
                                :key="index"
                                v-for="(option, index) in props.options"
                                @click="() => props.handleItemClick(option)"
                                :class="[
                                    'flex items-center space-x-2 px-4 py-1.5 text-sm cursor-pointer',
                                    option.label == 'Copy Link' ? 'hover:bg-blue-100 bg-blue-50' : 'hover:bg-gray-100 text-gray-700'
                                ]">
                                <div class="flex items-center space-x-2">
                                    <Link v-if="option.label == 'Copy Link'" size="14"></Link>
                                    <img v-else-if="option.icon" :src="`/images/social-media-icons/${option.icon}.png`" :alt="`${option.icon} Logo`" class="w-4 h-4" />
                                    <span class="truncate">{{ option.label }}</span>
                                </div>
                            </li>

                        </ul>

                    </template>

                </Dropdown>

            </div>

        </div>

        <p class="text-sm text-gray-500 text-justify mt-4"
            v-if="product.show_description && product.description">
            {{ product.description }}
        </p>

        <p class="text-sm text-gray-500 mt-2"
            v-if="product.sku">
            SKU: {{ product.sku }}
        </p>

        <div
            class="space-y-2 mt-4"
            v-if="product.variants.length">

            <div
                 :key="variant.id"
                 v-for="variant in product.variants"
                 @click.stop="productState.selectedVariantId = variant.id"
                 class="flex items-center space-x-4 p-2 hover:bg-gray-50 cursor-pointer rounded-lg">

                <div class="flex items-center justify-center w-5 h-5 bg-blue-100 rounded-full">
                    <div :class="['w-3 h-3 bg-blue-500 rounded-full transition-all', variant.id == selectedVariantId ? 'opacity-100' : 'opacity-0']"></div>
                </div>

                <div class="w-full flex items-center justify-between space-x-2">

                    <div
                        v-if="variant.photos.length"
                        class="max-w-12 max-h-12 aspect-square relative flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden">

                        <img
                            :alt="variant.name"
                            :src="variant.photos[0].path"
                            class="w-full h-full object-cover">

                    </div>

                    <div class="w-full flex items-center justify-between space-x-4">

                        <h4 class="text-sm text-gray-900 truncate">{{ product.name }}</h4>

                        <Pill v-if="variant.is_free" type="success" size="xs">free</Pill>

                        <div v-else>

                            <p v-if="variant.on_sale" class="text-right text-sm font-semibold text-gray-900">
                                {{ variant.unit_sale_price.amount_with_currency }}
                            </p>

                            <p :class="['text-right', { 'line-through text-xs text-gray-400': variant.on_sale, 'text-sm text-gray-900 font-semibold': !variant.on_sale }]">
                                {{ variant.unit_regular_price.amount_with_currency }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div
            v-if="product.stock_quantity_type != 'sold out'"
            class="flex items-center justify-between space-x-4 border-t border-black/20 pt-4 mt-4">

            <span class="text-sm font-bold">Quantity</span>
            <div class="w-fit text-sm flex items-center bg-white border border-black/20 rounded-full overflow-hidden">

                <button
                    @click.stop="decreaseQuantity"
                    class="px-3 py-1 bg-gray-200 text-gray-700 hover:bg-black/20 cursor-pointer">
                    -
                </button>

                <input
                    min="0"
                    value="2"
                    v-model="quantity"
                    @blur="onBlurQuntityInput"
                    class="w-16 text-center border-0 focus:outline-none">

                <button
                    @click.stop="increaseQuantity"
                    class="px-3 py-1 bg-gray-200 text-gray-700 hover:bg-black/20 cursor-pointer">
                    +
                </button>

            </div>

        </div>

        <div
            class="flex justify-center border-b border-black/20 pb-4 mt-4"
            v-if="orderProduct && (isInspectingShoppingCart || !shouldGoBack)">

            <span class="text-lg font-semibold text-gray-900">
                {{ estimatedPrice }}
            </span>

        </div>

        <div
            v-if="hasDataCollectionFields"
            class="border-t border-black/20 space-y-4 pt-4 mt-4">

            <div
                :key="index"
                v-for="(dataCollectionField, index) in dataCollectionFields">

                <template v-if="dataCollectionField.type == 'short answer'">

                    <Input
                        type="text"
                        v-model="responses[index]"
                        :label="dataCollectionField.name"
                        :showAsterisk="dataCollectionField.required"
                        :secondaryLabel="dataCollectionField.required ? null : '(optional)'"
                        :errorText="formState.getFormError(`data_collection_fields.${index}`)">
                    </Input>

                </template>

                <template v-if="dataCollectionField.type == 'long answer'">

                    <Input
                        type="textarea"
                        v-model="responses[index]"
                        :label="dataCollectionField.name"
                        :showAsterisk="dataCollectionField.required"
                        :secondaryLabel="dataCollectionField.required ? null : '(optional)'"
                        :errorText="formState.getFormError(`data_collection_fields.${index}`)">
                    </Input>

                </template>

                <template v-if="dataCollectionField.type == 'number'">

                    <Input
                        type="number"
                        v-model="responses[index]"
                        :label="dataCollectionField.name"
                        :showAsterisk="dataCollectionField.required"
                        :secondaryLabel="dataCollectionField.required ? null : '(optional)'"
                        :errorText="formState.getFormError(`data_collection_fields.${index}`)">
                    </Input>

                </template>

                <template v-if="dataCollectionField.type == 'date'">

                    <Datepicker
                        :enableTimePicker="true"
                        v-model="responses[index]"
                        format="dd MMM yyyy HH:mm"
                        placeholder="Select a date"
                        modelType="yyyy-MM-dd HH:mm"
                        :label="dataCollectionField.name"
                        :showAsterisk="dataCollectionField.required"
                        :secondaryLabel="dataCollectionField.required ? null : '(optional)'"
                        :errorText="formState.getFormError(`data_collection_fields.${index}`)">
                    </Datepicker>

                </template>

                <template v-if="dataCollectionField.type == 'checkbox'">

                    <div class="flex items-center text-sm leading-6 font-medium text-gray-900 space-x-1 mb-2">
                        <span>{{ dataCollectionField.name }}</span>
                        <span
                            class="text-red-500"
                            v-if="dataCollectionField.required">
                            *
                        </span>
                        <span v-else class="font-normal text-gray-400 ml-1">(optional)</span>
                    </div>

                    <div class="space-y-2">
                        <Input
                            type="checkbox"
                            :key="optionIndex"
                            :inputLabel="option.name"
                            :radioValue="option.name"
                            v-model="responses[index][optionIndex]"
                            v-for="(option, optionIndex) in dataCollectionField.options"
                            :errorText="formState.getFormError(`data_collection_fields.${index}`)">
                        </Input>
                    </div>
                </template>

                <template v-if="dataCollectionField.type == 'selection'">

                    <div class="flex items-center text-sm leading-6 font-medium text-gray-900 space-x-1 mb-2">
                        <span>{{ dataCollectionField.name }}</span>
                        <span
                            class="text-red-500"
                            v-if="dataCollectionField.required">
                            *
                        </span>
                        <span v-else class="font-normal text-gray-400 ml-1">(optional)</span>
                    </div>

                    <div class="space-y-2">
                        <Input
                            type="radio"
                            :key="optionIndex"
                            :inputLabel="option.name"
                            :radioValue="option.name"
                            v-model="responses[index]"
                            :name="`selection-${dataCollectionField.temporary_id}`"
                            v-for="(option, optionIndex) in dataCollectionField.options"
                            :errorText="formState.getFormError(`data_collection_fields.${index}`)">
                        </Input>
                    </div>

                    <Button
                        size="xs"
                        type="light"
                        class="mt-4"
                        v-if="!dataCollectionField.required"
                        :action="() => responses[index] = null">
                        <span>Clear selection</span>
                    </Button>

                </template>

                <template v-if="dataCollectionField.type == 'media'">
                    <Input
                        type="file"
                        :mimeTypes="['image/*']"
                        v-model="responses[index]"
                        :showAsterisk="dataCollectionField.required"
                        :maxFiles="parseInt(dataCollectionField.max)"
                        :errorText="formState.getFormError(`data_collection_fields.${index}`)">
                    </Input>
                </template>

            </div>

        </div>

        <div
            v-if="product.stock_quantity_type == 'sold out'"
            class="px-2.5 py-2 text-xl text-center font-bold text-red-600 mt-8">
            SOLD OUT
        </div>

        <div
            class="flex space-x-2"
            v-if="orderProduct && (isInspectingShoppingCart || !shouldGoBack)">

            <Button
                size="md"
                class="mt-8"
                type="light"
                :action="goBack"
                :leftIcon="MoveLeft"
                buttonClass="w-full"
                :disabled="isInspectingShoppingCart">
                <span>Shop</span>
            </Button>

            <Button
                size="md"
                class="mt-8"
                type="danger"
                buttonClass="w-full"
                :action="removeFromCart"
                :disabled="isInspectingShoppingCart">
                <span>Remove Item</span>
            </Button>

        </div>

        <Button
            size="lg"
            class="mt-8"
            type="primary"
            :action="addToCart"
            buttonClass="w-full"
            :disabled="isInspectingShoppingCart"
            v-else-if="product.stock_quantity_type != 'sold out' && !orderProduct">
            <span>Add</span>
            <span v-if="estimatedPrice">{{ estimatedPrice }}</span>
        </Button>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import { Link, Share2 } from 'lucide-vue-next';
    import Datepicker from '@Partials/Datepicker.vue';
    import { Search, MoveLeft } from 'lucide-vue-next';
    import { convertToMoneyWithSymbol } from '@Utils/numberUtils.js';

    export default {
        inject: ['formState', 'orderState', 'productState', 'storeState', 'notificationState'],
        components: { Link, Pill, Input, Button, Dropdown, Datepicker },
        data() {
            return {
                Search,
                Share2,
                MoveLeft,
                quantity: '1',
                responses: [],
                shouldGoBack: false,
                options: [
                    {
                        label: 'Whatsapp',
                        icon: 'whatsapp',
                        action: this.shareViaWhatsapp,
                    },
                    {
                        label: 'Facebook',
                        icon: 'facebook',
                        action: this.shareViaFacebook
                    },
                    {
                        label: 'LinkedIn',
                        icon: 'linkedin',
                        action: this.shareViaLinkedIn
                    },
                    {
                        label: 'X',
                        icon: 'x',
                        action: this.shareViaX
                    },
                    {
                        label: 'Copy Link',
                        action: this.copyLink
                    }
                ],
            }
        },
        watch: {
            product(newValue, oldValue) {
                if(!oldValue && newValue) {

                    this.setInitialQuantity();

                    if(this.product.variants.length) {
                        this.productState.selectedVariantId = this.product.variants[0].id;
                    }

                    this.responses = this.dataCollectionFields.map((dataCollectionField) => {
                        if(dataCollectionField.type == 'date') {
                            return null;
                        }else if(dataCollectionField.type == 'checkbox') {
                            return new Array(dataCollectionField.options.length).fill(false);
                        }else if(dataCollectionField.type == 'media') {
                            return [];
                        }else{
                            return '';
                        }
                    });
                }
            },
            quantity() {
                if(this.orderProduct) {
                    this.addToCart(false);
                }
            },
            isInspectingShoppingCart(newValue, oldValue) {
                if(oldValue && !newValue && this.shouldGoBack) {
                    this.goBack();
                }
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingProduct() {
                return this.productState.isLoadingProduct;
            },
            isInspectingShoppingCart() {
                return this.orderState.isInspectingShoppingCart;
            },
            product() {
                return this.productState.product;
            },
            orderProduct() {
                return this.shoppingCart && this.product ? this.shoppingCart.order_products.find(orderProduct => orderProduct.product_id == this.product.id) : null;
            },
            estimatedPrice() {
                if(this.isLoadingStore || this.isLoadingProduct) return null;
                return convertToMoneyWithSymbol(this.product.unit_price.amount * this.quantity, this.store.currency);
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            selectedVariantId() {
                return this.productState.selectedVariantId;
            },
            selectedVariant() {
                return this.product.variants.find(variant => variant.id == this.selectedVariantId);
            },
            dataCollectionFields() {
                return this.product.data_collection_fields;
            },
            hasDataCollectionFields() {
                return this.dataCollectionFields.length > 0;
            },
            productLink() {
                if(!this.store || !this.product) return null;
                const resolvedRoute = this.$router.resolve({
                    name: 'show-shop-product',
                    params: {
                        alias: this.store.alias,
                        product_id: this.product.id
                    },
                });

                const baseUrl = window.location.origin;
                return `${baseUrl}${resolvedRoute.href}`;
            },
        },
        methods: {
            goBack() {
                this.$router.back();
            },
            async navigateToSearch() {
                await this.$router.push({
                    name: 'show-search',
                    params: {
                        alias: this.store.alias
                    }
                });
            },
            setInitialQuantity() {
                if(this.orderProduct) {
                    this.quantity = this.orderProduct.quantity;
                }
            },
            shareViaWhatsapp() {
                if (!this.productLink) return;
                const text = encodeURIComponent(`Check out ${this.product.name} at ${this.store.name}:\n\n${this.productLink}`);
                const url = `https://wa.me/?text=${text}`;
                window.open(url, '_blank');
            },
            shareViaFacebook() {
                if (!this.productLink) return;
                const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(this.productLink)}`;
                window.open(url, '_blank');
            },
            shareViaLinkedIn() {
                if (!this.productLink) return;
                const url = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(this.productLink)}`;
                window.open(url, '_blank');
            },
            shareViaX() {
                if (!this.productLink) return;
                const text = encodeURIComponent(`Check out ${this.product.name} at ${this.store.name}: ${this.productLink}`);
                const url = `https://x.com/intent/post?text=${text}`;
                window.open(url, '_blank');
            },
            async copyLink() {
                try {

                    if(this.productLink) {
                        await navigator.clipboard.writeText(this.productLink);
                        this.notificationState.showSuccessNotification('Link copied');
                    }else{
                        this.notificationState.showWarningNotification('Link not ready');
                    }

                } catch (err) {
                    console.error('Failed to copy:', err);
                }
            },
            addToCart(goBack = true) {
                if(this.selectedVariant) {
                    this.orderState.addCartProductUsingProduct(this.selectedVariant, this.product, this.quantity, false);
                }else{
                    this.orderState.addCartProductUsingProduct(this.product, null, this.quantity, false);
                }
                this.shouldGoBack = goBack;
            },
            removeFromCart(goBack = true) {
                const index = this.shoppingCart.order_products.findIndex(orderProduct => orderProduct.product_id == this.product.id);
                this.orderState.removeCartProduct(index, false);
                this.shouldGoBack = goBack;
            },
            increaseQuantity() {
                const current = parseInt(this.quantity) || 0;
                const maxQuantity = parseInt(this.product.stock_quantity) || Infinity;
                if (current < maxQuantity) {
                    this.quantity = (current + 1).toString();
                }
            },
            decreaseQuantity() {
                const current = parseInt(this.quantity) || 1;
                if (current > 1) {
                    this.quantity = (current - 1).toString();
                }
            },
            onBlurQuantityInput() {
                const current = parseInt(this.quantity) || 1;
                const maxQuantity = parseInt(this.product.stock_quantity) || Infinity;
                if (current < 1) {
                    this.quantity = '1';
                } else if (current > maxQuantity) {
                    this.quantity = maxQuantity.toString();
                } else {
                    this.quantity = current.toString();
                }
            }
        }
    }
</script>
