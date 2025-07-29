<template>

    <div class="flex items-center space-x-1">
        <Pill :type="type" size="xs">
            <span class="whitespace-nowrap">{{ _order.payment_status }}</span>
        </Pill>
        <Popover
            placement="top"
            :content="description"
            :wrapperClasses="popoverWrapperClasses">
        </Popover>
    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Popover from '@Partials/Popover.vue';

    export default {
        inject: ['orderState'],
        components: { Pill, Popover },
        props: {
            order: {
                type: Object,
                default: null
            },
            popoverWrapperClasses: {
                type: String
            }
        },
        computed: {
            _order() {
                return this.order ?? this.orderState.order;
            },
            description() {
                const paymentStatusName = this._order.payment_status.toLowerCase();

                if(paymentStatusName === 'paid') {
                    return 'This order has been fully paid';
                } else if(paymentStatusName === 'unpaid') {
                    return 'This order has not been paid';
                } else if(paymentStatusName === 'partially paid') {
                    return 'This order is partially paid';
                } else if(paymentStatusName === 'pending payment') {
                    return 'This order is pending payment';
                }
            },
            type() {
                const paymentpaymentStatusName = this._order.payment_status.toLowerCase();

                if(paymentpaymentStatusName === 'paid') {
                    return 'success';
                }else if(paymentpaymentStatusName === 'unpaid') {
                    return 'light';
                } else if(paymentpaymentStatusName === 'partially paid') {
                    return 'primary';
                } else if(paymentpaymentStatusName === 'pending payment') {
                    return 'warning';
                }
            }
        }
    };

</script>
