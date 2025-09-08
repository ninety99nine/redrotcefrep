<template>

    <div
        class="bg-white rounded-2xl p-4 space-y-4"
        v-if="designCard.metadata.type == 'tips' && designCard.metadata.show_tips">

        <h1 v-if="designCard.metadata.title" class="text-base text-gray-700 font-semibold mb-4">{{ designCard.metadata.title }}</h1>

        <div v-if="hasTips" class="space-y-4">

            <div class="flex flex-wrap gap-2">

                <Pill
                    size="md"
                    :key="index"
                    :showDot="false"
                    :action="() => setTip(tip)"
                    v-for="(tip, index) in tips"
                    :type="isSelectedTip(tip) ? 'primary' : 'light'">
                    <template v-if="tip == 'none'">None</template>
                    <template v-else-if="tip == 'specify'">Specify</template>
                    <template v-else>{{ tip }}%</template>
                </Pill>

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

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState'],
        components: { Pill, Input },
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
