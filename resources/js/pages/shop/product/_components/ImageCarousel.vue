<template>

    <div
        class="relative"
        v-if="images.length > 0">

        <!-- Main Image -->
        <div class="h-96 relative flex justify-center items-center bg-gray-100 rounded-lg overflow-hidden">

            <img :src="currentImage.path" alt="Selected product image" class="w-full h-full object-contain">

            <!-- Left Arrows (hidden if only one image) -->
            <button v-if="images.length > 1" @click="prev" class="absolute left-8 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 text-gray-800 border border-gray-200 flex items-center justify-center w-8 h-8 rounded-full shadow-md cursor-pointer hover:scale-105 active:scale-100">
                <ChevronLeft size="20"></ChevronLeft>
            </button>

            <!-- Right Arrows (hidden if only one image) -->
            <button v-if="images.length > 1" @click="next" class="absolute right-8 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 text-gray-800 border border-gray-200 flex items-center justify-center w-8 h-8 rounded-full shadow-md cursor-pointer hover:scale-105 active:scale-100">
                <ChevronRight size="20"></ChevronRight>
            </button>

        </div>

        <!-- Thumbnails -->
        <div v-if="images.length > 1" class="flex justify-center mt-4 space-x-2">
            <div v-for="(image, index) in images" :key="image.id" @click="selectImage(index)" class="cursor-pointer">
                <img :src="image.path" alt="Thumbnail" class="w-16 h-16 object-cover rounded-md border-2" :class="{ 'border-blue-500': index === currentIndex, 'border-transparent': index !== currentIndex }">
            </div>
        </div>

    </div>

    <div v-else class="h-96 relative flex justify-center items-center bg-gray-100 rounded-lg overflow-hidden">

        <Image size="40" class="text-gray-300"></Image>

    </div>

</template>

<script>
    import { Image, ChevronLeft, ChevronRight } from 'lucide-vue-next';

    export default {
        inject: ['productState'],
        components: { Image, ChevronLeft, ChevronRight },
        data() {
            return {
                currentIndex: 0
            };
        },
        watch: {
            selectedVariantId(newValue) {
                if (newValue) {
                    this.updateCurrentImageForVariant(newValue);
                }
            }
        },
        computed: {
            product() {
                return this.productState.product;
            },
            selectedVariantId() {
                return this.productState.selectedVariantId;
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
            currentImage() {
                return this.images[this.currentIndex] || {};
            }
        },
        methods: {
            next() {
                this.currentIndex = (this.currentIndex + 1) % this.images.length;
            },
            prev() {
                this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
            },
            selectImage(index) {
                this.currentIndex = index;
            },
            updateCurrentImageForVariant(variantId) {

                // Find the index of the first photo for the given variantId
                const variant = this.product.variants.find(variant =>
                    variant.id === variantId
                );

                if(variant.photos.length > 0) {

                    // Find the index of the first photo for the given variant
                    this.currentIndex = this.images.findIndex(image =>
                        image.path === variant.photos[0].path
                    );

                }else {

                    // Fallback to the first product photo
                    this.currentIndex = 0;

                }
            }
        }
    }
</script>
