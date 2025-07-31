<template>

    <Select
        :search="false"
        :options="statuses"
        v-model="orderForm.status"
        :selectClasses="getSelectClasses"
        :label="showLabel ? 'Status' : null"
        @change="(status) => $emit('change', status)">

        <template #selectedOption="props">

            <span :class=getSelectTextClasses>{{ props.selectedOption?.label }}</span>

        </template>

        <template #option="props">

            <!-- Default Option Layout -->
            <div class="w-full flex items-center space-x-2">
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
            statuses() {
                const options = ['waiting','cancelled','completed','on its way','ready for pickup'];

                return options.map((option) => {
                    return {
                        'label': capitalize(option),
                        'value': option
                    }
                });
            },
            getSelectClasses() {
                let classes = ['w-full select-none rounded-md'];

                const statusName = this.orderForm.status;

                if(statusName === 'completed') {
                    classes.push('bg-green-100 border border-green-500');
                } else if(statusName === 'waiting') {
                    classes.push('bg-gray-100 border border-gray-500');
                } else if(statusName === 'on its way' || statusName === 'ready for pickup') {
                    classes.push('bg-blue-100 border border-blue-500');
                } else if(statusName === 'cancelled') {
                    classes.push('bg-yellow-100 border border-yellow-500');
                }

                return classes;
            },
            getSelectTextClasses() {
                let classes = ['font-semibold text-sm truncate'];

                const statusName = this.orderForm.status;

                if(statusName === 'completed') {
                    classes.push('text-green-800');
                } else if(statusName === 'waiting') {
                    classes.push('text-gray-800');
                } else if(statusName === 'on its way' || statusName === 'ready for pickup') {
                    classes.push('text-blue-800');
                } else if(statusName === 'cancelled') {
                    classes.push('text-yellow-800');
                }

                return classes;
            }
        },
        methods: {
            getOptionDotClasses(statusName) {
                let classes = ['w-2 h-2 rounded-full'];

                if(statusName === 'completed') {
                    classes.push('bg-green-500');
                } else if(statusName === 'waiting') {
                    classes.push('bg-gray-300');
                } else if(statusName === 'on its way' || statusName === 'ready for pickup') {
                    classes.push('bg-blue-500');
                } else if(statusName === 'cancelled') {
                    classes.push('bg-yellow-500');
                }

                return classes;
            },
        }
    };

</script>
