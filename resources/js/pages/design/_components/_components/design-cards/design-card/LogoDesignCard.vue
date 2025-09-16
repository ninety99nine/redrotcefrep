<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.metadata.mode == 'content' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'content'">Content</Pill>
            <Pill :type="designCard.metadata.mode == 'design' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'design'">Design</Pill>

        </div>

        <template v-if="designCard.metadata.mode == 'content'">

            <Tabs
                size="sm"
                class="w-full"
                :tabs="alignmentTabs"
                v-model="designCard.metadata.alignment"
                @change="designState.saveStateDebounced('Alignment changed')"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.alignment`)">
            </Tabs>

        </template>

        <Designer :designCard="designCard"></Designer>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Tabs from '@Partials/Tabs.vue';
    import { ArrowLeftToLine, ArrowRightToLine } from 'lucide-vue-next';
    import Designer from '@Pages/design/_components/_components/design-cards/design-card/_components/Designer.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Tabs, Designer },
        props: {
            index: {
                type: Number
            },
            designCard: {
                type: Object
            },
            editorOptions: {
                type: Object
            }
        },
        data() {
            return {
                alignmentTabs: [
                    { label: 'Left', value: 'left', leftIcon: ArrowLeftToLine },
                    { label: 'Center', value: 'center' },
                    { label: 'Right', value: 'right', rightIcon: ArrowRightToLine },
                ],
            }
        }
    }
</script>
