<template>
    <div
        :key="inputKey"
        :style="{
            backgroundColor: designCard.metadata.design.bg_color,

            marginTop: `${designCard.metadata.design.t_margin ?? 0}px`,
            marginLeft: `${designCard.metadata.design.l_margin ?? 0}px`,
            marginRight: `${designCard.metadata.design.r_margin ?? 0}px`,
            marginBottom: `${designCard.metadata.design.b_margin ?? 0}px`,

            paddingTop: `${designCard.metadata.design.t_padding ?? 0}px`,
            paddingLeft: `${designCard.metadata.design.l_padding ?? 0}px`,
            paddingRight: `${designCard.metadata.design.r_padding ?? 0}px`,
            paddingBottom: `${designCard.metadata.design.b_padding ?? 0}px`,

            borderTopLeftRadius: `${designCard.metadata.design.tl_border_radius ?? 0}px`,
            borderTopRightRadius: `${designCard.metadata.design.tr_border_radius ?? 0}px`,
            borderBottomLeftRadius: `${designCard.metadata.design.bl_border_radius ?? 0}px`,
            borderBottomRightRadius: `${designCard.metadata.design.br_border_radius ?? 0}px`,

            borderTop: `${designCard.metadata.design.t_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderLeft: `${designCard.metadata.design.l_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderRight: `${designCard.metadata.design.r_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderBottom: `${designCard.metadata.design.b_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
        }">

        <template v-if="designCard.type == 'short answer'">

            <Input
                type="text"
                v-model="response"
                :label="designCard.metadata.title"
                :description="designCard.metadata.description"
                :labelStyle="{ color: designCard.metadata.design.title_color }"
                :errorText="formState.getFormError('design_cards'+index+'metadata.title')"
                :secondaryLabelStyle="{ color: designCard.metadata.design.optional_text_color }"
                :showAsterisk="!empty(designCard.metadata.title) && designCard.metadata.required"
                :secondaryLabel="!empty(designCard.metadata.title) && !designCard.metadata.required ? '(optional)' : null">
            </Input>

        </template>

        <template v-if="designCard.type == 'long answer'">

            <Input
                rows="2"
                type="textarea"
                v-model="response"
                :label="designCard.metadata.title"
                :description="designCard.metadata.description"
                :labelStyle="{ color: designCard.metadata.design.title_color }"
                :errorText="formState.getFormError('design_cards'+index+'metadata.title')"
                :secondaryLabelStyle="{ color: designCard.metadata.design.optional_text_color }"
                :showAsterisk="!empty(designCard.metadata.title) && designCard.metadata.required"
                :secondaryLabel="!empty(designCard.metadata.title) && !designCard.metadata.required ? '(optional)' : null">
            </Input>

        </template>

        <template v-if="designCard.type == 'number'">

            <Input
                type="number"
                v-model="response"
                :label="designCard.metadata.title"
                :description="designCard.metadata.description"
                :labelStyle="{ color: designCard.metadata.design.title_color }"
                :errorText="formState.getFormError('design_cards'+index+'metadata.title')"
                :secondaryLabelStyle="{ color: designCard.metadata.design.optional_text_color }"
                :showAsterisk="!empty(designCard.metadata.title) && designCard.metadata.required"
                :secondaryLabel="!empty(designCard.metadata.title) && !designCard.metadata.required ? '(optional)' : null">
            </Input>

        </template>

        <template v-if="designCard.type == 'date'">

            <Datepicker
                v-model="response"
                :enableTimePicker="true"
                format="dd MMM yyyy HH:mm"
                placeholder="Select a date"
                modelType="yyyy-MM-dd HH:mm"
                :label="designCard.metadata.title"
                :description="designCard.metadata.description"
                :labelStyle="{ color: designCard.metadata.design.title_color }"
                :errorText="formState.getFormError('design_cards'+index+'metadata.title')"
                :secondaryLabelStyle="{ color: designCard.metadata.design.optional_text_color }"
                :showAsterisk="!empty(designCard.metadata.title) && designCard.metadata.required"
                :secondaryLabel="!empty(designCard.metadata.title) && !designCard.metadata.required ? '(optional)' : null">
            </Datepicker>

        </template>

        <template v-if="designCard.type == 'time'">

            <Input
                type="time"
                v-model="response"
                :label="designCard.metadata.title"
                :description="designCard.metadata.description"
                :labelStyle="{ color: designCard.metadata.design.title_color }"
                :errorText="formState.getFormError('design_cards'+index+'metadata.title')"
                :secondaryLabelStyle="{ color: designCard.metadata.design.optional_text_color }"
                :showAsterisk="!empty(designCard.metadata.title) && designCard.metadata.required"
                :secondaryLabel="!empty(designCard.metadata.title) && !designCard.metadata.required ? '(optional)' : null">
            </Input>

        </template>

        <template v-if="['checkbox', 'selection', 'location'].includes(designCard.type)">

            <div class="mb-2"
                v-if="!empty(designCard.metadata.title) || !empty(designCard.metadata.description)">
                <div class="flex items-center text-sm leading-6 font-medium space-x-1">

                    <span
                        :style="{ color: designCard.metadata.design.title_color }">
                        {{ designCard.metadata.title }}
                    </span>

                    <template v-if="!empty(designCard.metadata.title)">
                        <span
                            class="text-red-500"
                            v-if="designCard.metadata.required">
                            *
                        </span>
                        <span v-else class="font-normal text-gray-400 ml-1"
                            :style="{ color: designCard.metadata.design.optional_text_color }">
                            (optional)
                        </span>
                    </template>

                </div>

                <span
                    v-if="!empty(designCard.metadata.description)"
                    :style="{ color: designCard.metadata.design.description_color }"
                    class="leading-4 text-xs text-gray-400">
                    {{ designCard.metadata.description }}
                </span>
            </div>

            <template v-if="designCard.type == 'checkbox'">

                <div class="space-y-2">

                    <div
                        :key="optionIndex"
                        class="flex items-center space-x-2"
                        v-for="(option, optionIndex) in designCard.metadata.options">

                        <input
                            type="checkbox"
                            v-model="response[optionIndex]"
                            :id="`${inputKey}-${optionIndex}`"
                            @change="(event) => response[optionIndex] = event.target.checked"
                            :style="`accent-color:${designCard.metadata.design.checkbox_color};`">

                        <label :for="`${inputKey}-${optionIndex}`">
                            <span :style="{ color: designCard.metadata.design.title_color }">{{ option.name }}</span>

                        </label>

                    </div>

                </div>

            </template>

            <template v-if="designCard.type == 'selection'">

                <div class="space-y-2">

                    <div
                        :key="optionIndex"
                        class="flex items-center space-x-2"
                        v-for="(option, optionIndex) in designCard.metadata.options">

                        <input
                            type="radio"
                            :name="inputKey"
                            v-model="response"
                            :value="option.name"
                            :id="`${inputKey}-${optionIndex}`"
                            @change="(event) => response = option.name"
                            :style="`accent-color:${designCard.metadata.design.radio_color};`">

                            <label :for="`${inputKey}-${optionIndex}`">
                            <span :style="{ color: designCard.metadata.design.title_color }">{{ option.name }}</span>

                        </label>

                    </div>

                </div>

                <Button
                    size="xs"
                    type="light"
                    class="mt-4"
                    :action="() => response = null"
                    v-if="!designCard.metadata.required && designCard.metadata.options >= 1">
                    <span>Clear selection</span>
                </Button>

            </template>

            <template v-if="designCard.type == 'location'">

                <AddressInput
                    height="250px"
                    :onlyValidate="true"
                    :pinLocationOnMap="true"
                    triggerClass="space-y-4"
                    @onDeleted="unsetAddress"
                    :address="designCard.address"
                    @onValidated="(address) => setAddress(address)"
                    :triggerText="designCard.metadata.trigger_text ?? 'Add Address'">
                </AddressInput>

            </template>

        </template>

        <template v-if="designCard.type == 'media'">

            <Input
                type="file"
                :maxFiles="3"
                v-model="response"
                :mimeTypes="['image/*']"
                :imagePreviewGridCols="3"
                :label="designCard.metadata.title"
                :description="designCard.metadata.description"
                :labelStyle="{ color: designCard.metadata.design.title_color }"
                :fileTextStyle="{ color: designCard.metadata.design.media_text_color }"
                :errorText="formState.getFormError('design_cards'+index+'metadata.photos')"
                :wrapperStyle="{ backgroundColor: designCard.metadata.design.media_bg_color }"
                :secondaryLabelStyle="{ color: designCard.metadata.design.optional_text_color }"
                :showAsterisk="!empty(designCard.metadata.title) && designCard.metadata.required"
                :secondaryLabel="!empty(designCard.metadata.title) && !designCard.metadata.required ? '(optional)' : null">
            </Input>

        </template>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Datepicker from '@Partials/Datepicker.vue';
    import AddressInput from '@Partials/AddressInput.vue';
    import { convertToMoneyWithSymbol } from '@Utils/numberUtils';

    export default {
        inject: ['formState', 'storeState'],
        components: { Pill, Input, Button, Datepicker, AddressInput },
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
            'designCard.type'() {
                this.initializeResponse();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            inputKey() {
                return `${this.designCard?.id ?? this.designCard.temporary_id}-${this.designCard.metadata.title.length}-${this.designCard.metadata.required}-${this.designCard.metadata?.options?.length ?? 0}}`;
            }
        },
        methods: {
            convertToMoneyWithSymbol: convertToMoneyWithSymbol,
            empty(value) {
                return value == null || value.trim() == '';
            },
            setAddress(address) {

            },
            unsetAddress() {

            },
            initializeResponse() {
                if(this.designCard.type == 'checkbox') {
                    this.response = new Array(this.designCard.metadata.options.length).fill(false);
                }else if(this.designCard.type == 'location') {
                    this.response = null;
                }else if(this.designCard.type == 'media') {
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
