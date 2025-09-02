<template>

    <div>

        <div class="grid grid-cols-2 gap-4">

            <Select
                class="mb-2"
                width="w-full"
                :search="true"
                label="Category"
                :options="categories"
                v-model="designCard.metadata.category_id"
                @change="designState.saveStateDebounced('Category changed')"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.category_id`)">
            </Select>

            <Select
                :search="false"
                label="Feature"
                class="w-full mb-4"
                :options="featureOptions"
                v-model="designCard.metadata.feature"
                @change="designState.saveStateDebounced('Feature number changed')"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.feature`)">
            </Select>

        </div>

        <Tabs
            size="sm"
            class="w-full"
            :tabs="layoutTabs"
            v-model="designCard.metadata.layout"
            @change="designState.saveStateDebounced('Layout changed')"
            :errorText="formState.getFormError(`design_cards.${index}.metadata.layout`)">
        </Tabs>

    </div>

</template>

<script>

    import Tabs from '@Partials/Tabs.vue';
    import Select from '@Partials/Select.vue';
    import { List, LayoutGrid } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'designState'],
        components: { Tabs, Select, List, LayoutGrid },
        props: {
            index: {
                type: Number
            },
            designCard: {
                type: Object
            }
        },
        computed: {
            categories() {
                return this.designState.categories;
            },
            designForm() {
                return this.designState.designForm;
            },
        },
        data() {
            return {
                featureOptions: [
                    { label: '4 products', value: '4'},
                    { label: '6 products', value: '6'},
                    { label: '8 products', value: '8'},
                ],
                layoutTabs: [
                    { label: 'List', value: 'list', leftIcon: List },
                    { label: 'Grid', value: 'grid', leftIcon: LayoutGrid },
                ],
            }
        }
    }
</script>
