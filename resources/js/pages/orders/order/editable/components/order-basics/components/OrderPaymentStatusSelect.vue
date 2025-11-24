<template>

    <Select
        :search="false"
        :options="paymentStatuses"
        :selectClasses="getSelectClasses"
        v-model="orderForm.payment_status"
        :label="showLabel ? 'Payment Status' : null"
        @change="(paymentStatus) => $emit('change', paymentStatus)">

        <template #selectedOption="props">

            <span :class=getSelectTextClasses>{{ props.selectedOption?.label }}</span>

        </template>

        <template #option="props">

            <!-- Default Option Layout -->
            <div class="w-full flex items-center space-x-2 pr-2">
                <div :class="getOptionDotClasses(props.option.value)"></div>
                <span class="truncate">{{ props.option.label }}</span>
            </div>

        </template>

    </Select>

</template>

<script>

    import Select from '@Partials/Select.vue';
    import { capitalize } from '@Utils/stringUtils.js';

    export default {
        inject: ['orderState'],
        components: { Select },
        props: {
            showLabel: {
                type: Boolean,
                default: true
            }
        },
        emits: ['change'],
        computed: {
            orderForm() {
                return this.orderState.orderForm;
            },
            paymentStatusName() {
                return this.orderForm.payment_status;
            },
            paymentStatuses() {
                const options = ['paid','unpaid','partially paid','waiting confirmation'];

                return options.map((option) => {
                    return {
                        'label': capitalize(option),
                        'value': option
                    }
                });
            },
            getSelectClasses() {
                let classes = ['w-full select-none rounded-md'];

                if(this.paymentStatusName === 'paid') {
                    classes.push('bg-green-100 border border-green-500');
                } else if(this.paymentStatusName === 'unpaid') {
                    classes.push('bg-gray-100 border border-gray-500');
                } else if(this.paymentStatusName === 'partially paid') {
                    classes.push('bg-blue-100 border border-blue-500');
                }else if(this.paymentStatusName === 'waiting confirmation') {
                    classes.push('bg-yellow-100 border border-yellow-500');
                }

                return classes;
            },
            getSelectTextClasses() {
                let classes = ['font-semibold text-sm truncate'];

                if(this.paymentStatusName === 'paid') {
                    classes.push('text-green-800');
                } else if(this.paymentStatusName === 'unpaid') {
                    classes.push('text-gray-800');
                } else if(this.paymentStatusName === 'partially paid') {
                    classes.push('text-blue-800');
                }else if(this.paymentStatusName === 'waiting confirmation') {
                    classes.push('text-yellow-800');
                }

                return classes;
            }
        },
        methods: {
            getOptionDotClasses(paymentStatusName) {
                let classes = ['w-2 h-2 rounded-full'];

                if(paymentStatusName === 'paid') {
                    classes.push('bg-green-500');
                } else if(paymentStatusName === 'unpaid') {
                    classes.push('bg-gray-300');
                } else if(paymentStatusName === 'partially paid') {
                    classes.push('bg-blue-500');
                } else if(paymentStatusName === 'waiting confirmation') {
                    classes.push('bg-yellow-500');
                }

                return classes;
            }
        }
    };

</script>
