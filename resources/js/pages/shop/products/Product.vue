<template>

    <div class="lg:max-w-6xl max-w-xl mx-auto grid grid-cols-1 lg:grid-cols-2 lg:divide-x lg:divide-gray-300">

        <div class="lg:p-8 px-4 pt-8">

            <Button
                size="xs"
                class="mb-4"
                type="light"
                :action="goBack"
                :leftIcon="MoveLeft">
                <span>Shop</span>
            </Button>

            <ImageCarousel></ImageCarousel>

        </div>

        <div class="lg:p-8 px-4 pb-8">

            <ProductDetails></ProductDetails>

        </div>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { MoveLeft } from 'lucide-vue-next';
    import ImageCarousel from '@Pages/shop/products/_components/ImageCarousel.vue';
    import ProductDetails from '@Pages/shop/products/_components/ProductDetails.vue';

    export default {
        inject: ['formState', 'productState', 'storeState', 'notificationState'],
        components: {
            Button, ImageCarousel, ProductDetails
        },
        data() {
            return {
                MoveLeft
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.showProduct();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            product() {
                return this.productState.product;
            },
            productId() {
                return this.$route.params.product_id;
            },
        },
        methods: {
            goBack() {
                this.$router.back();
            },
            async showProduct() {
                try {

                    this.productState.isLoadingProduct = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['photos', 'categories', 'variants.photos'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/products/${this.productId}`, config);

                    const product = response.data;
                    this.productState.setProduct(product);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching product';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch product:', error);

                    if(error.status == 404) {
                        await this.$router.replace({
                            name: 'show-storefront',
                            params: {
                                alias: this.store.alias
                            }
                        });
                    }

                } finally {
                    this.productState.isLoadingProduct = false;
                }
            },
        },
        unmounted() {
            this.productState.reset();
        },
        created() {
            if(this.store) this.showProduct();
        }
    }
</script>
