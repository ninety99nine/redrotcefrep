<template>

    <div :style="{
            color: designCard.metadata.design.text_color,
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
        }">

        <div class="flex items-center justify-center space-x-4">
            <button
                @click="triggerPWAInstall"
                :style="{
                    backgroundColor: designCard.metadata.design.button_color,
                    color: designCard.metadata.design.button_text_color,
                    borderRadius: '8px',
                    padding: '8px 16px'
                }"
                :class="[
                    'font-semibold',
                    { 'opacity-25 cursor-not-allowed' : promptShown },
                    { 'cursor-pointer transition-all duration-300 hover:scale-105 active:scale-100' : !promptShown}
                ]"
                :title="promptShown ? 'Please refresh the page or interact with the page to try again' : 'Install the app'">
                <div class="flex items-center justify-center space-x-2">
                    <Download size="16" :style="{ color: designCard.metadata.design.button_text_color }"></Download>
                    <span v-if="designCard.metadata.button_text" class="text-sm">{{ designCard.metadata.button_text }}</span>
                </div>
            </button>
        </div>

    </div>

</template>

<script>
    import { Download } from 'lucide-vue-next';
    export default {
        inject: ['storeState', 'pwaStore'],
        components: { Download },
        props: {
            designCard: {
                type: Object
            }
        },
        data() {
            return {
                promptShown: false
            };
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            deferredPrompt() {
                return this.pwaStore.deferredPrompt;
            }
        },
        methods: {
            async triggerPWAInstall() {

                if (this.deferredPrompt) {

                    this.promptShown = true;
                    this.deferredPrompt.prompt();
                    const { outcome } = await this.deferredPrompt.userChoice;

                    if (outcome === 'accepted') {
                        console.log('User accepted the PWA install prompt');
                    } else {
                        console.log('User dismissed the PWA install prompt');
                    }

                } else {
                    console.warn('No deferredPrompt available for PWA installation');
                    alert('PWA install not available. Please refresh the page and try again.');
                }

            }
        }
    }
</script>
