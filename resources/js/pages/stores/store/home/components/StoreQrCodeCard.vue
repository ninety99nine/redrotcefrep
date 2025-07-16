<template>

    <div class="animated-border-cyan space-y-4 py-4 px-4 shadow-sm rounded-xl flex flex-col items-center">

        <h1 class="space-x-2 text-sm text-gray-700 font-bold">
            Store QR Code
        </h1>

        <img :src="'/images/qr-code-example-use.jpg'" alt="QR Code" class="w-full rounded-lg inset-shadow-sm inset-shadow-red-500">

        <p class="text-center text-xs text-gray-600">
            Download this QR code and add it to your flyers, business cards, packaging, t-shirts and more
        </p>

        <div class="w-full flex justify-between space-x-2">

            <Button
                size="xs"
                type="primary"
                buttonClass="w-full"
                :action="downloadQR"
                :loading="downloadingQr"
                :leftIcon="ArrowDownToLine">
                <span>Download</span>
            </Button>

            <Button
                size="xs"
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
            buttonClass="w-full"
            :action="visitCanva"
            :skeleton="isLoadingStore" >
            <span>Create design with</span>
            <img class="h-4 ml-1.5" :src="'/images/canva-logo-white.png'">
        </Button>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { Forward, ArrowDownToLine } from 'lucide-vue-next';

    export default {
        inject: ['storeState'],
        components: {
            Button
        },
        data() {
            return {
                Forward,
                ArrowDownToLine,
                downloadingQr: false
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            }
        },
        methods: {
            visitCanva() {
                window.open('https://www.canva.com/templates/?query=qr-code', '_blank');
            },
            async downloadQR() {
                try {
                    this.downloadingQr = true;
                    const response = await axios.get(`/api/stores/${this.store.id}/qr-code`, {
                        responseType: 'blob',
                    });
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
        }
    };

</script>
