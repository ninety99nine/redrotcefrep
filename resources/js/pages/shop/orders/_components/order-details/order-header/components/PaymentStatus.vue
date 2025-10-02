<template>

    <div v-if="!isLoadingStore && !isLoadingOrder" class="flex items-center space-x-1">
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
        inject: ['orderState', 'storeState'],
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
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            paymentStatusName() {
                return this._order.payment_status.toLowerCase();
            },
            description() {
                if(this.paymentStatusName === 'paid') {
                    return 'This order has been fully paid';
                } else if(this.paymentStatusName === 'unpaid') {
                    return 'This order has not been paid';
                } else if(this.paymentStatusName === 'partially paid') {
                    return 'This order is partially paid';
                } else if(this.paymentStatusName === 'waiting confirmation') {
                    return 'This order payment is being confirmed';
                }
            },
            type() {
                if(this.paymentStatusName === 'paid') {
                    return 'success';
                }else if(this.paymentStatusName === 'unpaid') {
                    return 'light';
                } else if(this.paymentStatusName === 'partially paid') {
                    return 'primary';
                } else if(this.paymentStatusName === 'waiting confirmation') {
                    return 'success';
                }
            }
        }
    };

</script>
