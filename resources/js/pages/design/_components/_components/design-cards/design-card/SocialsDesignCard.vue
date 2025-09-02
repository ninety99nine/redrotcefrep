<template>

    <div>

        <Input
            type="text"
            class="w-full mb-4"
            placeholder="Title"
            v-model="designCard.metadata.title"
            @input="designState.saveStateDebounced('Title changed')"
            :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
        </Input>

        <!-- Draggable Fields -->
        <draggable
            class="space-y-2 mb-4"
            ghost-class="bg-yellow-50"
            handle=".draggable-handle-2"
            @change="designState.saveStateDebounced('Design card moved')"
            v-model="designState.designForm.design_cards[index].metadata.platforms">

            <template
                :key="index2"
                v-for="(platform, index2) in designCard.metadata.platforms">

                <div class="flex items-center space-x-2"
                    v-if="designCard.metadata.show_more || (!designCard.metadata.show_more && index2 <= 2)">

                    <img class="w-6"
                        :src="`/images/social-media-icons/${designCard.metadata.platforms[index2].name.toLowerCase()}.png`" />

                    <Input
                        type="text"
                        class="w-full"
                        placeholder="https://"
                        v-model="designCard.metadata.platforms[index2].link"
                        @input="designState.saveStateDebounced('Social link changed')"
                        :errorText="formState.getFormError(`design_cards.${index}.metadata.platforms.${index2}.link`)">
                    </Input>

                    <!-- Drag & Drop Handle -->
                    <Move @click.stop size="16" class="draggable-handle-2 cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                </div>

            </template>

        </draggable>

        <div class="flex justify-center">

            <!-- Show More Or Less Button -->
            <Button
                size="sm"
                type="bare"
                :rightIcon="designCard.metadata.show_more ? ChevronUp : ChevronDown"
                :action="() => designCard.metadata.show_more = !designCard.metadata.show_more">
                <span>{{ designCard.metadata.show_more ? 'show less' : 'show more' }}</span>
            </Button>

        </div>

    </div>


</template>

<script>

    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import { Move, ChevronUp, ChevronDown } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'designState'],
        components: { Move, Input, Button, draggable: VueDraggableNext },
        props: {
            index: {
                type: Number
            },
            designCard: {
                type: Object
            }
        },
        data() {
            return {
                ChevronUp,
                ChevronDown
            }
        }
    }
</script>
