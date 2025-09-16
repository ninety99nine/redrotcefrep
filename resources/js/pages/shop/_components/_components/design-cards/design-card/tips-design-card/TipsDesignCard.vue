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
        v-if="designCard.metadata.show_tips">

        <h1
            v-if="designCard.metadata.title"
            class="text-base text-gray-700 font-semibold">
            {{ designCard.metadata.title }}
        </h1>

        <p
            class="text-sm text-gray-700 mt-2"
            v-if="designCard.metadata.description">
            {{ designCard.metadata.description }}
        </p>

        <div v-if="hasTips" class="space-y-4 mt-4">

            <div class="flex flex-wrap gap-2">

                <div
                    :key="index"
                    v-for="(tip, index) in tips"
                    @click.stop="() => setTip(tip)"
                    :style="[
                        isSelectedTip(tip) ? {
                            border: `1px solid transparent`,
                            color: designCard.metadata.design.pill_text_color,
                            backgroundColor: designCard.metadata.design.pill_bg_color,
                        } : {
                            color: '#111827',
                            backgroundColor: '#ffffff',
                            border: `1px solid #d2d2d2`,
                        }
                    ]"
                    class="select-none whitespace-nowrap inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold transition-all cursor-pointer hover:scale-95 hover:opacity-80 active:scale-90">
                    <template v-if="tip == 'none'">None</template>
                    <template v-else-if="tip == 'specify'">Specify</template>
                    <template v-else>{{ tip }}%</template>
                </div>

            </div>

            <div v-if="isSelectedTip('specify')" class="mt-4">

                <Input
                    type="money"
                    :currency="store.currency"
                    v-model="orderForm.tip_flat_rate"
                    :errorText="formState.getFormError('discount_flat_rate')">
                </Input>

            </div>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState'],
        components: { Input },
        props: {
            designCard: {
                type: Object
            }
        },
        watch: {
            tips(newValue) {
                if(!newValue.includes('specify') && this.orderForm.tip_flat_rate != null) {
                    this.orderForm.tip_flat_rate = null;
                }
            }
        },
        computed: {
            tips() {
                let tips = ['none', ...this.designCard.metadata.tips];

                if(this.designCard.metadata.show_specify_tip) {
                    tips.push('specify');
                }

                return tips;
            },
            store() {
                return this.storeState.store;
            },
            hasTips() {
                return this.tips.length > 0;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
        },
        methods: {
            setTip(tip) {
                if(tip == 'none') {

                    this.orderForm.tip_flat_rate = null;
                    this.orderForm.tip_percentage_rate = null;

                }else if(tip == 'specify') {

                    this.orderForm.tip_percentage_rate = null;

                    if(this.designCard.metadata.tips.length) {
                        this.orderForm.tip_flat_rate = this.designCard.metadata.tips[0];
                    }else{
                        this.orderForm.tip_flat_rate = '0.00';
                    }

                }else if(this.designCard.metadata.tips.includes(tip)) {

                    this.orderForm.tip_percentage_rate = tip;

                }
            },
            isSelectedTip(tip) {
                return (tip == 'specify' && this.orderForm.tip_percentage_rate == null && this.orderForm.tip_flat_rate != null) ||
                       (tip == 'none' && this.orderForm.tip_percentage_rate == null && this.orderForm.tip_flat_rate == null) ||
                       (this.orderForm.tip_percentage_rate == tip);
            }
        }
    }
</script>
