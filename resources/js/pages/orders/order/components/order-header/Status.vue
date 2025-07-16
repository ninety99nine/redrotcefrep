<template>

    <div class="flex items-center space-x-2">
        <Pill :type="type" size="xs">
            <span class="whitespace-nowrap">{{ _order.status }}</span>
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
                const statusName = this._order.status.toLowerCase();

                if(statusName === 'completed') {
                    return 'Order has been completed';
                } else if(statusName === 'waiting') {
                    return 'Waiting a response from the team';
                } else if(statusName === 'on its way') {
                    return 'Waiting for confirmation of delivery';
                } else if(statusName === 'ready for pickup') {
                    return 'Waiting for customer to pickup their order';
                } else if(statusName === 'cancelled') {
                    return this._order.cancellation_reason ?? 'Order has been cancelled';
                }
            },
            type() {
                const statusName = this._order.status.toLowerCase();

                if(statusName === 'completed') {
                    return 'success';
                } else if(statusName === 'waiting') {
                    return 'light';
                } else if(statusName === 'on its way' || statusName === 'ready for pickup') {
                    return 'primary';
                } else if(statusName === 'cancelled') {
                    return 'warning';
                }
            }
        }
    };

</script>
