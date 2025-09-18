<template>

    <div
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
        }"
        v-if="designCard.metadata.title || designCard.metadata.show_first_name || designCard.metadata.show_last_name || designCard.metadata.show_email">

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

            <Input
                type="text"
                class="w-full"
                label="First name"
                v-model="orderForm.customer_first_name"
                v-if="designCard.metadata.show_first_name"
                :showAsterisk="designCard.metadata.first_name_required"
                :errorText="formState.getFormError('customer_first_name')"
                :labelStyle="{ color: designCard.metadata.design.title_color }"
                :secondaryLabelStyle="{ color: designCard.metadata.design.optional_text_color }"
                :secondaryLabel="designCard.metadata.first_name_required ? null : '(optional)'">
            </Input>

            <Input
                type="text"
                class="w-full"
                label="Last name"
                v-model="orderForm.customer_last_name"
                v-if="designCard.metadata.show_last_name"
                :showAsterisk="designCard.metadata.last_name_required"
                :errorText="formState.getFormError('customer_last_name')"
                :labelStyle="{ color: designCard.metadata.design.title_color }"
                :secondaryLabelStyle="{ color: designCard.metadata.design.optional_text_color }"
                :secondaryLabel="designCard.metadata.last_name_required ? null : '(optional)'">
            </Input>

            <Input
                type="text"
                class="w-full"
                label="Mobile"
                placeholder="+26772000001"
                v-model="orderForm.customer_mobile_number"
                v-if="designCard.metadata.show_mobile_number"
                :showAsterisk="designCard.metadata.mobile_number_required"
                :errorText="formState.getFormError('customer_mobile_number')"
                :labelStyle="{ color: designCard.metadata.design.title_color }"
                :secondaryLabelStyle="{ color: designCard.metadata.design.optional_text_color }"
                :secondaryLabel="designCard.metadata.mobile_number_required  ? null : '(optional)'">
            </Input>

            <Input
                type="email"
                label="Email"
                class="w-full"
                placeholder="johndoe@example.com"
                v-model="orderForm.customer_email"
                v-if="designCard.metadata.show_email"
                :showAsterisk="designCard.metadata.email_required"
                :errorText="formState.getFormError('customer_email')"
                :labelStyle="{ color: designCard.metadata.design.title_color }"
                :secondaryLabel="designCard.metadata.email_required ? null : '(optional)'"
                :secondaryLabelStyle="{ color: designCard.metadata.design.optional_text_color }">
            </Input>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';

    export default {
        inject: ['formState', 'orderState'],
        components: { Input },
        props: {
            designCard: {
                type: Object
            }
        },
        computed: {
            orderForm() {
                return this.orderState.orderForm;
            }
        }
    }
</script>
