<template>

    <div class="rounded-lg overflow-hidden border border-gray-300">

        <div class="space-y-2 p-2 px-4 bg-gray-50 hover:bg-gray-100">

            <div class="flex justify-between items-center space-x-8">

                <div class="flex items-center space-x-2">
                    <Banknote size="20" class="text-gray-400 flex-shrink-0"></Banknote>
                    <p class="font-semibold text-sm">{{ transaction.description }}</p>
                </div>

                <div class="flex items-center space-x-2 whitespace-nowrap">

                    <span class="text-gray-500 text-xs mr-4">{{ formattedDatetime(transaction.created_at) }}</span>
                    <Pill type="success" size="xs" :showDot="false">{{ transaction.payment_status }}</Pill>
                    <span class="text-sm">{{ transaction.amount.amount_with_currency }}</span>

                    <div :class="['flex items-center justify-center w-10 h-10 hover:bg-white rounded-lg cursor-pointer', { 'border border-dashed border-gray-200' : !hasPhoto }]">

                        <img v-if="hasPhoto" class="w-full max-h-10 object-contain rounded-lg flex-shrink-0" :src="photo.path">

                        <Image v-else size="16" class="text-gray-400 flex-shrink-0"></Image>

                    </div>

                    <!-- Delete Transaction -->
                    <Button
                        size="xs"
                        leftIconSize="14"
                        :leftIcon="Trash2"
                        type="bareDanger"
                        :action="() => onDeleteTransaction(transaction)">
                    </Button>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Button from '@Partials/Button.vue';
    import { formattedDatetime } from '@Utils/dateUtils.js';
    import { Image, Trash2, Banknote } from 'lucide-vue-next';

    export default {
        components: { Image, Pill, Button, Banknote },
        props: {
            index: {
                type: Number
            },
            transaction: {
                type: Object
            },
            onDeleteTransaction: {
                type: Function
            }
        },
        data() {
            return {
                Trash2,
                expanded: false
            }
        },
        computed: {
            hasPhoto() {
                return this.photo != null;
            },
            photo() {
                return this.transaction.photo;
            },
        },
        methods: {
            formattedDatetime: formattedDatetime
        }
    };

</script>
