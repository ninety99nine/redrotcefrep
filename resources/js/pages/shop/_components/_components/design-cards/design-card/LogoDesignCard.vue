<template>

    <div
        v-if="store.logo"
        :class="['flex', alignment]"
        :style="{
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

        <StoreLogo size="w-20 h-20" :showButton="false"></StoreLogo>

    </div>

</template>

<script>

    import StoreLogo from '@Components/StoreLogo.vue';

    export default {
        inject: ['storeState'],
        components: { StoreLogo },
        props: {
            designCard: {
                type: Object
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            alignment() {
                if(this.designCard.metadata.alignment == 'left') {
                    return 'justify-start';
                }else if(this.designCard.metadata.alignment == 'right') {
                    return 'justify-end';
                }else{
                    return 'justify-center';
                }
            }
        }
    }
</script>
