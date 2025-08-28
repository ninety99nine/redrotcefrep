<template>

    <div class="space-y-4 p-4">

        <template
            :key="index"
            v-for="(designCard, index) in designCards">

            <!-- Text Card -->
            <div class="bg-white rounded-2xl p-4"
                v-if="designCard.metadata.type == 'text' && designCard.metadata.body">
                <div class="markdown-content" v-html="renderMarkdown(designCard.metadata.body)"></div>
            </div>

            <!-- Link Card -->
            <div
                v-if="designCard.metadata.type == 'link' && designCard.metadata.title && designCard.metadata.link"
                class="bg-white rounded-2xl p-4 hover:bg-gray-50 hover:scale-95 transition-all duration-300 cursor-pointer">
                <div class="flex items-center justify-center space-x-4">
                    <Link size="20"></Link>
                    <a :href="designCard.metadata.link" target="_blank" class="text-blue-600 hover:text-blue-800">{{ designCard.metadata.title }}</a>
                </div>
            </div>

            <!-- Video Card -->
            <div class="bg-white rounded-2xl p-4"
                v-if="designCard.metadata.type == 'video' && (designCard.metadata.title || designCard.metadata.link)">
                <div class="space-y-2">
                    <div
                        v-if="designCard.metadata.title"
                        class="flex items-center space-x-4">
                        <Video size="20" class="text-gray-500"></Video>
                        <h3 class="text-lg font-semibold text-gray-900">{{ designCard.metadata.title }}</h3>
                    </div>
                    <div
                        v-if="designCard.metadata.link"
                        class="relative w-full" style="padding-bottom: 56.25%; /* 16:9 aspect ratio */">
                        <iframe
                            class="absolute top-0 left-0 w-full h-full rounded-md"
                            :src="getYouTubeEmbedUrl(designCard.metadata.link)"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
            </div>

            <!-- Socials Card -->
            <div
                class="bg-white rounded-2xl p-4"
                v-if="designCard.metadata.type == 'socials' && designCard.metadata.platforms?.some(platform => platform.link)">

                <div class="space-y-2">

                    <div
                        v-if="designCard.metadata.title"
                        class="flex items-center space-x-4">
                        <AtSign size="20" class="text-gray-500"></AtSign>
                        <h3 class="text-lg font-semibold text-gray-900">{{ designCard.metadata.title }}</h3>
                    </div>

                    <div class="flex flex-wrap justify-center gap-4">

                        <template
                            :key="index2"
                            v-for="(platform, index2) in designCard.metadata.platforms">

                            <a
                                target="_blank"
                                v-if="platform.link"
                                :href="platform.link"
                                :aria-label="`Visit ${platform.name}`"
                                class="w-12 h-12 flex items-center justify-center hover:scale-90 active:scale-80 transition-all duration-300 cursor-pointer">

                                <img
                                    :alt="platform.name"
                                    class="w-8 h-8 object-contain"
                                    :src="`/images/social-media-icons/${platform.name.toLowerCase()}.png`"/>

                            </a>

                        </template>

                    </div>

                </div>

            </div>

            <!-- Products Card -->
            <div
                class="bg-white rounded-2xl p-4"
                v-if="designCard.metadata.type == 'products' && designCard.metadata.category_id && categoryData[designCard.metadata.category_id]?.category">

                <div class="space-y-4">

                    <!-- Category Name and Description -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-0">{{ categoryData[designCard.metadata.category_id].category.name }}</h3>
                        <p v-if="categoryData[designCard.metadata.category_id].category.description" class="text-gray-600">{{ categoryData[designCard.metadata.category_id].category.description }}</p>
                    </div>

                    <!-- Loader -->
                    <div
                        class="flex justify-center items-center"
                        v-if="categoryData[designCard.metadata.category_id].loading">
                        <Loader></Loader>
                    </div>

                    <!-- Products -->
                    <div
                        v-else-if="categoryData[designCard.metadata.category_id].category.products?.length"
                        :class="[designCard.metadata.layout === 'list' ? 'flex flex-col space-y-4' : 'grid grid-cols-2 gap-4']">

                        <div
                            :key="product.id"
                            class="flex flex-col space-y-2 p-2 hover:bg-gray-50 rounded-lg transition-all duration-300"
                            v-for="product in categoryData[designCard.metadata.category_id].category.products.slice(0, parseInt(designCard.metadata.feature))">

                            <div class="w-full h-24 aspect-square relative flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden">

                                <img
                                    :alt="(product ?? product.variant).name"
                                    class="absolute w-full h-full object-cover"
                                    :src="(product ?? product.variant).photo.path"
                                    v-if="(product ?? product.variant).photo?.path">

                                <Image v-else size="24" class="text-gray-400"></Image>

                            </div>

                            <h4 class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</h4>

                            <div class="flex items-center space-x-2 flex-wrap gap-2">

                                <Pill v-if="(product.variant ?? product).is_free" type="success" size="xs">free</Pill>

                                <template v-else>

                                    <span v-if="(product.variant ?? product).on_sale" class="text-sm font-semibold text-gray-900">
                                        {{ (product.variant ?? product).unit_sale_price.amount_with_currency }}
                                    </span>

                                    <span :class="['text-sm', { 'line-through text-gray-400': (product.variant ?? product).on_sale, 'text-gray-900 font-semibold': !(product.variant ?? product).on_sale }]">
                                        {{ (product.variant ?? product).unit_regular_price.amount_with_currency }}
                                    </span>

                                </template>

                                <Pill
                                    :type="(product.variant ?? product).has_stock ? 'success' : 'warning'" size="xs"
                                    v-if="(product.variant ?? product).stock_quantity_type == 'limited' && (((product.variant ?? product).has_stock && (product.variant ?? product).stock_quantity <= 5) || !(product.variant ?? product).has_stock)">
                                    {{ (product.variant ?? product).has_stock ? `${(product.variant ?? product).stock_quantity} left` : 'no stock' }}
                                </Pill>

                            </div>

                        </div>

                    </div>

                    <p v-else class="text-gray-500 text-sm">No products available in this category.</p>

                </div>

            </div>

            <!-- Image Card -->
            <div
                class="bg-white rounded-2xl p-4"
                v-if="designCard.metadata.type === 'image' && (designCard.metadata.upper_text || designCard.metadata.lower_text || designCard.photo?.[0]?.path)">

                <div class="space-y-4">

                    <div
                        class="markdown-content"
                        v-if="designCard.metadata.upper_text"
                        v-html="renderMarkdown(designCard.metadata.upper_text)">
                    </div>

                    <div
                        v-if="designCard.photo?.[0]?.path"
                        class="w-full aspect-square relative flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden">

                        <img
                            alt="Countdown Image"
                            :src="designCard.photo[0].path"
                            class="w-full h-full object-cover" />

                    </div>

                    <div
                        class="markdown-content"
                        v-if="designCard.metadata.lower_text"
                        v-html="renderMarkdown(designCard.metadata.lower_text)">
                    </div>

                </div>

            </div>

            <!-- Contact Card -->
            <div
                @click="downloadVCard(designCard.metadata.title, designCard.metadata.mobile_number)"
                v-if="designCard.metadata.type === 'contact' && (designCard.metadata.title || designCard.metadata.mobile_number)"
                class="bg-white rounded-2xl p-4 hover:bg-gray-50 hover:scale-95 transition-all duration-300 cursor-pointer">

                <div class="flex items-center justify-center space-x-4">
                    <Phone size="20" class="text-gray-500"></Phone>
                    <h3 class="text-lg font-semibold text-gray-900">{{ designCard.metadata.title }}</h3>
                </div>

            </div>

            <!-- Countdown Card -->
            <div
                class="bg-white rounded-2xl p-4"
                v-if="designCard.metadata.type === 'countdown' && (designCard.metadata.body || designCard.metadata.date || designCard.photo?.[0]?.path)">

                <div class="space-y-4">

                    <div
                        class="markdown-content"
                        v-if="designCard.metadata.upper_text"
                        v-html="renderMarkdown(designCard.metadata.upper_text)">
                    </div>

                    <div
                        v-if="designCard.photo?.[0]?.path"
                        class="w-full aspect-square relative flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden">

                        <img
                            alt="Countdown Image"
                            :src="designCard.photo[0].path"
                            class="w-full h-full object-cover" />

                    </div>

                    <Countdown
                        type="design2"
                        class="w-full"
                        :time="designCard.metadata.date">
                        <template #prefix="{ hasExpired }">
                            <span v-if="hasExpired" class="text-lg font-semibold text-red-600">Expired</span>
                        </template>
                    </Countdown>

                    <div
                        class="markdown-content"
                        v-if="designCard.metadata.lower_text"
                        v-html="renderMarkdown(designCard.metadata.lower_text)">
                    </div>

                </div>

            </div>

            <div
                class="bg-white rounded-2xl p-4"
                v-if="designCard.metadata.type === 'map' && ((designCard.metadata.title) || (designCard.address && designCard.address?.latitude && designCard.address?.longitude))">

                <div class="space-y-4">

                    <div
                        class="markdown-content"
                        v-if="designCard.metadata.upper_text"
                        v-html="renderMarkdown(designCard.metadata.upper_text)">
                    </div>

                    <AddressInput
                        height="250px"
                        triggerClass=""
                        :editable="false"
                        :address="designCard.address"
                        v-if="designCard.address?.latitude && designCard.address?.longitude">
                    </AddressInput>

                    <p v-if="designCard.metadata.show_address && designCard.address?.complete_address" class="text-gray-600">
                        {{ designCard.address.complete_address }}
                    </p>

                    <div
                        class="markdown-content"
                        v-if="designCard.metadata.lower_text"
                        v-html="renderMarkdown(designCard.metadata.lower_text)">
                    </div>

                </div>

            </div>

        </template>

    </div>

</template>

<script>
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import Pill from '@Partials/Pill.vue';
import Loader from '@Partials/Loader.vue';
import Countdown from '@Partials/Countdown.vue';
import AddressInput from '@Partials/AddressInput.vue';
import { Box, Map, Link, Clock, Image, Video, AtSign, Phone } from 'lucide-vue-next';

export default {
    inject: ['formState', 'storeState', 'notificationState'],
    components: {
        Box, Map, Link, Pill, Clock, Image, Video, AtSign, Phone, Loader, Countdown, AddressInput
    },
    props: {
        designCards: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            categoryData: {}
        }
    },
    watch: {
        designCards: {
            handler(newCards) {
                newCards.forEach((card, index) => {
                    if (card.metadata.type === 'products' && card.metadata.category_id) {
                        this.showCategory(index, card.metadata.category_id);
                    }
                });
            },
            immediate: true,
            deep: true
        }
    },
    computed: {
        store() {
            return this.storeState.store;
        }
    },
    methods: {
        renderMarkdown(text) {
            // Parse Markdown and sanitize the output
            const rawHtml = marked(text, {
                breaks: true, // Treat line breaks as <br>
                gfm: true     // Enable GitHub Flavored Markdown
            });
            return DOMPurify.sanitize(rawHtml, {
                ADD_TAGS: ['img'], // Allow images
                ADD_ATTR: ['src', 'alt'] // Allow image attributes
            });
        },
        getYouTubeEmbedUrl(url) {
            // Extract YouTube video ID from various URL formats
            const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
            const match = url.match(regex);
            const videoId = match ? match[1] : '';
            return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
        },
        downloadVCard(title, mobileNumber) {
            const vCard = [
                'BEGIN:VCARD',
                'VERSION:3.0',
                `FN;CHARSET=UTF-8:${this.store.name}`,
                `N;CHARSET=UTF-8:;${this.store.name};;;`,
                `TEL;TYPE=WORK,VOICE:${mobileNumber || ''}`,
                `ORG;CHARSET=UTF-8:${import.meta.env.VITE_APP_NAME}`,
                `REV:${new Date().toISOString()}`,
                'END:VCARD',
            ].join('\n');

            const blob = new Blob([vCard], { type: 'text/vcard' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${title || 'contact'}.vcf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        },
        async showCategory(index, categoryId) {

            // Initialize categoryData for this index if not already set
            if (!this.categoryData[categoryId]) {
                this.categoryData[categoryId] = { loading: false, category: null };
            }

            // Skip if already loaded or categoryId is invalid
            if (this.categoryData[categoryId].category || !categoryId) return;

            try {

                this.categoryData[categoryId].loading = true;

                let config = {
                    params: {
                        store_id: this.store.id,
                        _relationships: ['photo', 'products.photo'].join(',')
                    }
                };

                const response = await axios.get(`/api/categories/${categoryId}`, config);
                this.categoryData[categoryId] = { loading: false, category: response.data };

            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching category';
                this.notificationState.showWarningNotification(message);
                this.formState.setServerFormErrors(error);
                console.error(`Failed to fetch category for index ${index}:`, error);
                this.categoryData[categoryId] = { loading: false, category: null };
                this.$set(this.categoryData, index, { loading: false, category: null });
            }
        }
    },
    unmounted() {},
    created() {}
}
</script>
