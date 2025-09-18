<template>

    <div
        v-if="designCard.metadata.show_items"
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

        <h1
            class="text-base font-semibold"
            v-if="designCard.metadata.title"
            :style="{ color: designCard.metadata.design.title_color }">
            {{ designCard.metadata.title }}
        </h1>

        <p
            class="text-sm text-gray-700 mt-2"
            v-if="designCard.metadata.description"
            :style="{ color: designCard.metadata.design.description_color }">
            {{ designCard.metadata.description }}
        </p>

        <div class="space-y-2 mt-4">

            <CartProducts></CartProducts>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import CartProducts from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/items-design-card/cart-products/CartProducts.vue';

    export default {
        components: { Input, CartProducts },
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
