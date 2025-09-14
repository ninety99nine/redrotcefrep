<template>

    <div
        @click="downloadVCard(designCard.metadata.title, designCard.metadata.mobile_number)"
        v-if="designCard.type === 'contact' && (designCard.metadata.title || designCard.metadata.mobile_number)"
            class="bg-white border border-gray-200 rounded-2xl p-4 hover:bg-gray-50 hover:scale-95 transition-all duration-300 cursor-pointer">

        <div class="flex items-center justify-center space-x-4">
            <Phone size="20" class="text-gray-500"></Phone>
            <h3 class="text-lg font-semibold text-gray-900">{{ designCard.metadata.title }}</h3>
        </div>

    </div>

</template>

<script>

    import { Phone } from 'lucide-vue-next';

    export default {
        inject: ['storeState'],
        components: { Phone },
        props: {
            designCard: {
                type: Object
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            }
        },
        methods: {
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
            }
        }
    }
</script>
