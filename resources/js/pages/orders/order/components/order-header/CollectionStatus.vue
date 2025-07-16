<template>

    <div class="flex items-center space-x-2">
        <Pill :type="_order.collection_verified ? 'success' : 'warning'" size="xs">
            <span class="whitespace-nowrap">{{ _order.collection_verified ? 'Verfiied' : 'Not Verified' }}</span>
        </Pill>
        <Popover
            placement="top"
            :wrapperClasses="popoverWrapperClasses"
            :content="_order.collection_verified ? 'The collection of this order has been verified' : 'The collection of this order has not been verified'">
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
            }
        }
    };

</script>
