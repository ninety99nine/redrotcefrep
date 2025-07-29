<template>

    <div class="min-h-screen flex flex-col items-center pt-20 pb-40">

        <div class="w-full max-w-lg">

            <!-- Store Logo -->
            <StoreLogo :showButton="false" class="flex justify-center mb-4"></StoreLogo>

            <!-- Heading -->
            <h2 class="text-2xl font-semibold text-center mb-2">Share & Grow</h2>

            <!-- Instruction -->
            <p class="text-gray-500 text-center mb-6">Share your store link and start selling everywhere!</p>

        </div>

        <div class="w-full max-w-md">

            <div class="mb-4">

                <!-- Continue -->
                <Button
                    size="lg"
                    type="primary"
                    buttonClass="w-full mb-4"
                    :action="navigateToStoreHome">
                    <span>Go To Dashboard</span>
                </Button>

                <!-- Share -->
                <div class="space-y-3 mb-4">

                    <div class="bg-white space-y-4 py-8 px-4 shadow-sm rounded-xl flex flex-col items-center">

                        <img :src="store.qr_code_file_path" alt="QR Code" class="w-32 h-32">

                        <p class="text-center text-xs text-gray-600">
                            Customers can scan this QR code to visit your store instantly. Download it and add it to your marketing materials, such as flyers, business cards, t-shirts, and more, to make it easy for them to find your store.
                        </p>

                        <div class="flex space-x-2">

                            <Button
                                size="sm"
                                type="primary"
                                buttonClass="w-full"
                                :action="downloadQR"
                                :loading="downloadingQr"
                                :leftIcon="ArrowDownToLine">
                                <span>Download</span>
                            </Button>

                            <Button
                                size="sm"
                                type="success"
                                buttonClass="w-full"
                                :rightIcon="Forward"
                                :action="sendQrCodeToWhatsapp">
                                <span>Send To WhatsApp</span>
                            </Button>

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

                                <Copy
                                    :text="store.web_link">
                                </Copy>

                            </div>

                        </div>

                        <!-- Hint -->
                        <div class="flex items-center space-x-1 p-2 bg-blue-50 text-xs rounded-lg">
                            <Info size="14"></Info>
                            <span>You can change to your own domain later</span>
                        </div>

                    </div>

                    <h2 class="text-sm font-semibold text-center mb-2">Share On Social Media</h2>

                    <!-- Share On Social Platforms -->
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

            <!-- Continue -->
            <Button
                size="lg"
                type="primary"
                buttonClass="w-full"
                :action="navigateToStoreHome">
                <span>Go To Dashboard</span>
            </Button>

        </div>

    </div>

</template>

<script>

    import Copy from '@Partials/Copy.vue';
    import Button from '@Partials/Button.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
    import { Info, Link, Forward, ChevronUp, ChevronDown, ExternalLink, ArrowDownToLine } from 'lucide-vue-next';

    export default {
        inject: ['storeState'],
        components: {
            Copy, Button, StoreLogo,
            Info, Link, ExternalLink, ChevronUp, ChevronDown
        },
        data() {
            return {
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
