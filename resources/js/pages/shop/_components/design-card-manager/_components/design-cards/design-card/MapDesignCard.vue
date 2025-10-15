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
        }"
        v-if="designCard.metadata.upper_text || designCard.metadata.lower_text || designCard.address">

        <div class="space-y-2">

            <Markdown
                :text="designCard.metadata.upper_text"
                v-if="isNotEmpty(designCard.metadata.upper_text)">
            </Markdown>

            <AddressInput
                height="250px"
                triggerClass=""
                :editable="false"
                :address="designCard.address"
                v-if="designCard.address?.latitude && designCard.address?.longitude">
            </AddressInput>

            <p
                :style="{ color: designCard.metadata.design.address_color }"
                v-if="designCard.metadata.show_address && designCard.address?.complete_address">
                {{ designCard.address.complete_address }}
            </p>

            <Markdown
                :text="designCard.metadata.lower_text"
                v-if="isNotEmpty(designCard.metadata.lower_text)">
            </Markdown>

        </div>

    </div>

</template>

<script>

    import Markdown from '@Partials/Markdown.vue';
    import { isNotEmpty } from '@Utils/stringUtils';
    import AddressInput from '@Partials/AddressInput.vue';

    export default {
        components: { Markdown, AddressInput },
        props: {
            designCard: {
                type: Object
            }
        },
        methods: {
            isNotEmpty,
        }
    }
</script>
