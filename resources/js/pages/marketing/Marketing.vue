<template>

    <div class="max-w-lg mx-auto pt-24 pb-40">

        <!-- Share -->
        <div class="space-y-4 mb-4">

            <div class="flex flex-col items-center bg-white py-8 px-4 shadow-sm rounded-xl">

                <div class="flex flex-col items-center space-y-8">

                    <div class="space-y-4">

                        <!-- Heading -->
                        <h2 class="text-2xl font-semibold text-center mb-2">Marketing</h2>

                        <!-- Instruction -->
                        <p class="text-gray-500 text-center mb-6">Get your store out there and start selling everywhere!</p>

                        <div class="animated-border-blue rounded-lg overflow-hidden">
                            <img :src="'/images/qr-code-example-use.jpg'" alt="QR Code" class="max-w-96 rounded-t-lg inset-shadow-sm inset-shadow-red-500">

                            <p class="max-w-96 text-center text-sm bg-gray-100 p-4 rounded-b-lg shadow">
                                Download this QR code and add it to your flyers, business cards, packaging, t-shirts and more
                            </p>
                        </div>
                    </div>

                   <div class="w-full space-y-2">

                        <div class="flex justify-between space-x-2">

                            <Button
                                size="sm"
                                type="primary"
                                class="w-full"
                                buttonClass="w-full"
                                :action="downloadQR"
                                :loading="downloadingQr"
                                :leftIcon="ArrowDownToLine">
                                <span>Download</span>
                            </Button>

                            <Button
                                size="sm"
                                class="w-full"
                                type="success"
                                buttonClass="w-full"
                                :rightIcon="Forward"
                                :action="sendQrCodeToWhatsapp">
                                <span>Send To WhatsApp</span>
                            </Button>

                        </div>

                        <Button
                            size="sm"
                            type="primary"
                            class="w-full"
                            buttonClass="w-full"
                            :action="visitCanva"
                            :skeleton="isLoadingStore" >
                            <span>Create design with</span>
                            <img class="h-4 ml-1.5" :src="'/images/canva-logo-white.png'">
                        </Button>

                   </div>

                </div>

            </div>

            <!-- Copy & Share Link -->
            <div class="bg-white space-y-4 py-3 px-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg relative">

                <div class="flex items-center gap-8">

                    <!-- Link Icon -->
                    <Link size="24"></Link>

                    <div class="w-full space-y-2">

                        <!-- Store Name -->
                        <p class="text-sm font-bold">{{ store.name }}</p>

                        <!-- Instruction -->
                        <p class="text-xs">Copy your store link and share it anywhere you want</p>

                        <!-- Copy -->
                        <Copy :text="store.web_link"></Copy>

                    </div>

                </div>

                <!-- Hint -->
                <div class="flex items-center justify-between space-x-1 py-2 px-4 bg-blue-50 text-xs rounded-lg">
                    <div class="flex items-center space-x-1">
                        <Info size="14"></Info>
                        <span>Want your own domain?</span>
                    </div>

                    <Button
                        size="xs"
                        type="success"
                        leftIconSize="16"
                        :leftIcon="Earth"
                        buttonClass="w-full"
                        :action="navigateToShowDomains">
                        <span>Connect Your Own Domain</span>
                    </Button>
                </div>

            </div>

            <h2 class="text-sm font-semibold text-center mb-4">Share On Social Media</h2>

            <!-- Share On Social Platforms -->
            <div class="space-y-2">

                <a
                    :key="index"
                    target="_blank"
                    :href="socialPlatform.link"
                    v-for="(socialPlatform, index) in socialPlatforms"
                    :class="['block', { 'hidden': !showMore && index >= 5 }]">

                    <div class="social-item flex justify-between items-center gap-8 bg-white py-3 px-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                        <div class="flex items-center space-x-4">

                            <!-- Logo -->
                            <img :src="`/images/social-media-icons/${socialPlatform.name.toLowerCase()}.png`" :alt="`${socialPlatform.name} Logo`" class="w-8 h-8" />

                            <!-- Name -->
                            <div class="space-y-1 text-sm">
                                <p class="font-bold">{{ socialPlatform.name }}</p>
                                <p class="text-xs">{{ socialPlatform.description }}</p>
                            </div>

                        </div>

                        <div class="rounded-md border p-1 border-transparent hover:border-gray-300 hover:bg-gray-50">

                            <ExternalLink size="20" class="text-gray-500"></ExternalLink>

                        </div>

                    </div>

                </a>

            </div>

        </div>

        <div
            @click="showMore = !showMore"
            class="cursor-pointer flex justify-center items-center gap-2 p-3 rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 hover:text-gray-700 transition-all duration-300 mb-8">

            <span
                class="text-xs font-semibold text-gray-700">
                {{ showMore ? 'Show less' : 'Show more' }}
            </span>

            <ChevronUp size="14" v-if="showMore"></ChevronUp>
            <ChevronDown size="14" v-else></ChevronDown>

        </div>

    </div>
</template>

<script>
    import Copy from '@Partials/Copy.vue';
    import Button from '@Partials/Button.vue';
    import { Info, Link, Earth, Forward, ChevronUp, ChevronDown, ExternalLink, ArrowDownToLine } from 'lucide-vue-next';

    export default {
        inject: ['storeState'],
        components: {
            Copy, Button,
            Info, Link, ExternalLink, ChevronUp, ChevronDown
        },
        data() {
            return {
                Earth,
                Forward,
                ArrowDownToLine,
                showMore: false,
                downloadingQr: false
            };
        },
        watch: {
            showMore(newValue) {
                const socialItems = this.$el.querySelectorAll('.social-item');

                if (newValue) {

                    const startIndex = 5;
                    const lastItems = Array.from(socialItems).slice(startIndex);

                    lastItems.forEach(item => {
                        item.classList.remove('bg-white');
                        item.classList.add('bg-yellow-100');
                    });

                    // Remove the highlight after 2 seconds
                    setTimeout(() => {
                        lastItems.forEach(item => {
                            item.classList.add('bg-white');
                            item.classList.remove('bg-yellow-100');
                        });
                    }, 1000);

                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            socialPlatforms() {
                return [
                    {
                        name: 'Facebook',
                        description: 'Post your store link on Facebook and reach your customers',
                        link: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(this.store.web_link)}`
                    },
                    {
                        name: 'Whatsapp',
                        description: 'Share your store with friends, family, and customers via WhatsApp',
                        link: `https://wa.me/?text=${encodeURIComponent(
                            `${this.store.name}\n\nVisit our store: ${this.store.web_link}`
                        )}`
                    },
                    {
                        name: 'Instagram',
                        description: 'Add your store link to your Instagram bio and attract more visitors',
                        link: 'https://www.instagram.com/accounts/edit'
                    },
                    {
                        name: 'LinkedIn',
                        description: 'Share your store link on LinkedIn for professional connections',
                        link: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(this.store.web_link)}`
                    },
                    {
                        name: 'X',
                        description: 'Post your store link on X (formerly Twitter) and reach more customers',
                        link: `https://x.com/intent/post?text=${encodeURIComponent(
                            `Check out my store: ${this.store.web_link}`
                        )}`
                    },
                    {
                        name: 'TikTok',
                        description: 'Add your store link to your TikTok bio and drive traffic',
                        link: 'https://www.tiktok.com'
                    },
                    {
                        name: 'Snapchat',
                        description: 'Share your store link in Snapchat stories or messages',
                        link: `https://www.snapchat.com/scan?attachmentUrl=${encodeURIComponent(this.store.web_link)}`
                    },
                    {
                        name: 'Telegram',
                        description: 'Share your store link with your Telegram contacts and groups',
                        link: `https://t.me/share/url?url=${encodeURIComponent(this.store.web_link)}`
                    }
                ];
            }
        },
        methods: {
            async navigateToShowDomains() {
                await this.$router.push({
                    name: 'show-domains',
                    query: { store_id: this.store.id }
                });
            },
            async downloadQR() {
                try {
                    this.downloadingQr = true;

                    let config = {
                        responseType: 'blob'
                    };

                    const response = await axios.get(`/api/stores/${this.store.id}/qr-code`, config);
                    const blob = response.data;
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `${this.store.name.replace(/[\s_]+/g, '-')} QR Code.png`;
                    link.click();
                    URL.revokeObjectURL(url);
                } catch (error) {
                    console.error('Error downloading the store QR code image:', error);
                } finally {
                    this.downloadingQr = false;
                }
            },
            async sendQrCodeToWhatsapp() {
                const baseUrl = import.meta.env.VITE_APP_URL;
                const previewUrl = `${baseUrl}/api/stores/${this.store.id}/qr-code-preview`;
                const message = `Scan this QR code to access ${this.store.name}: ${encodeURIComponent(previewUrl)}`;
                const whatsappUrl = `https://wa.me/?text=${message}`;
                window.open(whatsappUrl, '_blank');
            },
            navigateToStoreHome() {
                this.$router.push({
                    name: 'show-store-home',
                    params: { store_id: this.store.id }
                });
            }
        }
    };
</script>
