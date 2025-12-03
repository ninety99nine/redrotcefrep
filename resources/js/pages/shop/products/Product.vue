<template>

    <div
        v-if="hasImages"
        class="lg:max-w-6xl max-w-xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-4 lg:divide-x lg:divide-black/20 pb-40">

        <div class="lg:p-8 px-4 pt-8">

            <Button
                size="xs"
                class="mb-4"
                type="light"
                :action="goBack"
                :leftIcon="MoveLeft">
                <span>Shop</span>
            </Button>

            <ImageCarousel :images="images"></ImageCarousel>

        </div>

        <div class="lg:p-8 px-4 pb-8">

            <ProductDetails></ProductDetails>

        </div>

    </div>

    <div v-else class="max-w-xl mx-auto sm:px-4 p-8">

        <div class="flex items-center justify-between mb-8">

            <Button
                size="xs"
                type="light"
                :action="goBack"
                :leftIcon="MoveLeft">
                <span>Shop</span>
            </Button>

            <div class="absolute left-1/2 transform -translate-x-1/2">
                <StoreLogo
                    size="w-16 h-16"
                    :editable="false"
                    :showButton="false"
                    class="cursor-pointer"
                    v-if="store && store.logo"
                    @click="navigateToStorefront">
                </StoreLogo>
            </div>

            <div class="w-20"></div>

        </div>

        <ProductDetails></ProductDetails>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { MoveLeft } from 'lucide-vue-next';
    import StoreLogo from '@Components/StoreLogo.vue';
    import ImageCarousel from '@Pages/shop/products/_components/ImageCarousel.vue';
    import ProductDetails from '@Pages/shop/products/_components/ProductDetails.vue';

    export default {
        inject: ['formState', 'productState', 'storeState', 'notificationState'],
        components: {
            Button, StoreLogo, ImageCarousel, ProductDetails
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
            images() {
                let allImages = [...(this.product?.photos || [])];

                // If variants exist, add their photos
                if (this.product?.variants?.length > 0) {

                    this.product.variants.forEach(variant => {

                        if (variant.photos?.length > 0) {
                            allImages = [...allImages, ...variant.photos];
                        }

                    });

                }

                return allImages;
            },
            hasImages() {
                return this.images.length > 0;
            }
        },
        methods: {
            goBack() {
                if (window.history.length > 1) {
                    this.$router.back()
                } else {
                    this.navigateToStorefront()
                }
            },
            async navigateToStorefront() {
                await this.$router.push({
                    name: 'show-storefront',
                    params: {
                        alias: this.store.alias
                    }
                });
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

                    if (error.response?.status === 404) {
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
