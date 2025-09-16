<template>

    <div :style="{
            color: designCard.metadata.design.title_color,
            backgroundColor: designCard.metadata.design.bg_color,

            marginTop: `${designCard.metadata.design.t_margin ?? 0}px`,
            marginLeft: `${designCard.metadata.design.l_margin ?? 0}px`,
            marginRight: `${designCard.metadata.design.r_margin ?? 0}px`,
            marginBottom: `${designCard.metadata.design.b_margin ?? 0}px`,

            paddingTop: `${designCard.metadata.design.t_padding ?? 0}px`,
            paddingLeft: `${designCard.metadata.design.l_padding ?? 0}px`,
            paddingRight: `${designCard.metadata.design.r_padding ?? 0}px`,
            paddingBottom: `${designCard.metadata.design.b_padding ?? 0}px`,

            borderTopLeftRadius: `${designCard.metadata.design.tl_border_radius ?? 0}px`,
            borderTopRightRadius: `${designCard.metadata.design.tr_border_radius ?? 0}px`,
            borderBottomLeftRadius: `${designCard.metadata.design.bl_border_radius ?? 0}px`,
            borderBottomRightRadius: `${designCard.metadata.design.br_border_radius ?? 0}px`,

            borderTop: `${designCard.metadata.design.t_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderLeft: `${designCard.metadata.design.l_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderRight: `${designCard.metadata.design.r_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderBottom: `${designCard.metadata.design.b_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
        }"
        class="hover:scale-105 transition-all duration-300 cursor-pointer"
        v-if="designCard.metadata.title || designCard.metadata.mobile_number"
        @click="downloadVCard(designCard.metadata.title, designCard.metadata.mobile_number)">

        <div
            class="flex items-center justify-center space-x-4">
            <Phone size="20" :style="{ color: designCard.metadata.design.icon_color }"></Phone>
            <h3 class="text-lg font-semibold">{{ designCard.metadata.title }}</h3>
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
