<template>

    <div class="bg-gray-100 p-2 rounded-lg leading-0">

        <p v-if="user" class="font-bold text-sm">{{ user.name }}</p>

        <p class="text-sm">{{ orderComment.comment }}</p>

        <div
            v-if="hasPhotos"
            class="flex flex-wrap space-x-2 space-y-2 my-2">

            <img
                :key="photo.id"
                :src="photo.path"
                v-for="photo in photos"
                @click="() => onViewPhoto(photo)"
                class="max-h-20 object-contain rounded-lg flex-shrink-0 hover:scale-105 transition-all cursor-pointer" />

        </div>

        <div class="flex justify-between">

            <p class="text-gray-400 text-xs">{{ formattedDatetime(orderComment.created_at) }}</p>

            <template v-if="user">

                <Loader v-if="isDeletingOrderComment" type="danger"></Loader>

                <Button
                    v-else
                    size="xs"
                    leftIconSize="14"
                    :leftIcon="Trash2"
                    type="bareDanger"
                    :action="() => deleteOrderComment(orderComment)">
                </Button>

            </template>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import { UserRound, Trash2 } from 'lucide-vue-next';
    import { formattedDatetime } from '@Utils/dateUtils.js';

    export default {
        components: { UserRound, Pill, Loader, Button },
        props: {
            index: {
                type: Number
            },
            orderComment: {
                type: Object
            },
            onViewPhoto: {
                type: Function
            },
            deleteOrderComment: {
                type: Function
            },
            isDeletingOrderCommentIds: {
                type: Array
            }
        },
        data() {
            return {
                Trash2,
                expanded: false
            }
        },
        computed: {
            user() {
                return this.orderComment.user;
            },
            hasPhotos() {
                return this.photos.length > 0;
            },
            photos() {
                return this.orderComment.photos;
            },
            isDeletingOrderComment() {
                return this.isDeletingOrderCommentIds.findIndex((id) => id == this.orderComment.id) != -1;
            }
        },
        methods: {
            formattedDatetime: formattedDatetime
        }
    };

</script>
