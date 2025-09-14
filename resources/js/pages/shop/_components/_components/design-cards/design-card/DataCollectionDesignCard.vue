<template>

    <div
        class="bg-white rounded-2xl p-4"
        :key="`${designCard.metadata.name}-${designCard.metadata.required}-${designCard.metadata.options.length}`">

        <template v-if="designCard.metadata.type == 'short text'">

            <Input
                type="text"
                v-model="response"
                :label="designCard.metadata.name"
                :showAsterisk="designCard.metadata.required"
                :secondaryLabel="designCard.metadata.required ? null : '(optional)'"
                :errorText="formState.getFormError('design_cards'+index+'metadata.name')">
            </Input>

        </template>

        <template v-if="designCard.metadata.type == 'long text'">

            <Input
                rows="2"
                type="textarea"
                v-model="response"
                :label="designCard.metadata.name"
                :showAsterisk="designCard.metadata.required"
                :secondaryLabel="designCard.metadata.required ? null : '(optional)'"
                :errorText="formState.getFormError('design_cards'+index+'metadata.name')">
            </Input>

        </template>

        <template v-if="designCard.metadata.type == 'number'">

            <Input
                type="number"
                v-model="response"
                :label="designCard.metadata.name"
                :showAsterisk="designCard.metadata.required"
                :secondaryLabel="designCard.metadata.required ? null : '(optional)'"
                :errorText="formState.getFormError('design_cards'+index+'metadata.name')">
            </Input>

        </template>

        <template v-if="designCard.metadata.type == 'date'">

            <Datepicker
                v-model="response"
                :enableTimePicker="true"
                format="dd MMM yyyy HH:mm"
                placeholder="Select a date"
                modelType="yyyy-MM-dd HH:mm"
                :label="designCard.metadata.name"
                :showAsterisk="designCard.metadata.required"
                :secondaryLabel="designCard.metadata.required ? null : '(optional)'"
                :errorText="formState.getFormError('design_cards'+index+'metadata.name')">
            </Datepicker>

        </template>

        <template v-if="designCard.metadata.type == 'checkbox'">

            <div class="flex items-center text-sm leading-6 font-medium text-gray-900 space-x-1 mb-2">
                <span>{{ designCard.metadata.name }}</span>
                <span
                    class="text-red-500"
                    v-if="designCard.metadata.required">
                    *
                </span>
                <span v-else class="font-normal text-gray-400 ml-1">(optional)</span>
            </div>

            <div class="space-y-2">
                <Input
                    type="checkbox"
                    :key="optionIndex"
                    :inputLabel="option.name"
                    :radioValue="option.name"
                    v-model="response[optionIndex]"
                    v-for="(option, optionIndex) in designCard.metadata.options"
                    :errorText="formState.getFormError('design_cards'+index+'metadata.name')">
                </Input>
            </div>
        </template>

        <template v-if="designCard.metadata.type == 'selection'">

            <div class="flex items-center text-sm leading-6 font-medium text-gray-900 space-x-1 mb-2">
                <span>{{ designCard.metadata.name }}</span>
                <span
                    class="text-red-500"
                    v-if="designCard.metadata.required">
                    *
                </span>
                <span v-else class="font-normal text-gray-400 ml-1">(optional)</span>
            </div>

            <div class="space-y-2">
                <Input
                    type="radio"
                    v-model="response"
                    :key="optionIndex"
                    :inputLabel="option.name"
                    :radioValue="option.name"
                    :name="`selection-${designCard.metadata.temporary_id}`"
                    v-for="(option, optionIndex) in designCard.metadata.options"
                    :errorText="formState.getFormError('design_cards'+index+'metadata.name')">
                </Input>
            </div>

            <Button
                size="xs"
                type="light"
                class="mt-4"
                v-if="!designCard.metadata.required"
                :action="() => response = null">
                <span>Clear selection</span>
            </Button>

        </template>

        <template v-if="designCard.metadata.type == 'media'">
            <Input
                type="file"
                v-model="response"
                :mimeTypes="['image/*']"
                :imagePreviewGridCols="1"
                :label="designCard.metadata.name"
                :maxFiles="parseInt(designCard.metadata.max)"
                :showAsterisk="designCard.metadata.required"
                :secondaryLabel="designCard.metadata.required ? null : '(optional)'"
                :errorText="formState.getFormError('design_cards'+index+'metadata.name')">
            </Input>
        </template>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Datepicker from '@Partials/Datepicker.vue';

    export default {
        inject: ['formState'],
        components: { Input, Button, Datepicker },
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
                response: null
            }
        },
        watch: {
            'designCard.metadata.type'() {
                this.initializeResponse();
            }
        },
        methods: {
            initializeResponse() {
                if(this.designCard.metadata.type == 'checkbox') {
                    this.response = new Array(this.designCard.metadata.options.length).fill(false);
                }else if(this.designCard.metadata.type == 'media') {
                    this.response = [];
                }else{
                    this.response = '';
                }
            }
        },
        created() {
            this.initializeResponse();
        }
    }
</script>
