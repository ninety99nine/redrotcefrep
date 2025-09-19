<template>

    <template v-if="designCard.mode == '2'">

        <div
            :key="index"
            v-for="(accordion, index) in accordions"
            @click.stop="() => toggleAccordian(index)"
            :class="[
                { 'rounded-t-lg border-t' : index == 0 },
                { 'rounded-b-lg border-b' : index == accordions.length - 1 && !accordion.expanded },
                'border-x border-blue-300', { 'rounded-t-lg' : index == 0 }, { 'rounded-b-lg' : index == accordions.length - 1
                }]">

            <div :class="[
                { 'rounded-t-lg' : index == 0 },
                { 'shadow shadow-blue-100 border-b border-blue-300' : accordion.expanded },
                { 'rounded-b-lg' : index == accordions.length - 1 && !accordion.expanded },
                'flex items-center justify-between bg-blue-50 hover:bg-blue-100 p-2 cursor-pointer',
                ]">
                <p class="text-sm text-gray-700">{{ accordion.label }}</p>

                <ChevronUp v-if="accordion.expanded" size="16" class="text-blue-500"></ChevronUp>
                <ChevronDown v-else size="16" class="text-blue-500"></ChevronDown>
            </div>

            <vue-slide-up-down
                @click.stop
                :active="accordion.expanded">

                <template v-if="index == 0">

                    <div class="grid grid-cols-2 gap-x-20 gap-y-4 p-4">

                        <div
                            class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('icon_color')">
                            <span class="text-sm">Icon Color</span>
                            <Colorpicker v-model="designCard.metadata.design.icon_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div
                            class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('text_color')">
                            <span class="text-sm">Text Color</span>
                            <Colorpicker v-model="designCard.metadata.design.text_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('title_color')">
                            <span class="text-sm">Title Color</span>
                            <Colorpicker v-model="designCard.metadata.design.title_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('subtitle_color')">
                            <span class="text-sm">Subtitle Color</span>
                            <Colorpicker v-model="designCard.metadata.design.subtitle_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('optional_text_color')">
                            <span class="text-sm">Optional Text Color</span>
                            <Colorpicker v-model="designCard.metadata.design.optional_text_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('description_color')">
                            <span class="text-sm">Description Color</span>
                            <Colorpicker v-model="designCard.metadata.design.description_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('bg_color')">
                            <span class="text-sm">Background Color</span>
                            <Colorpicker v-model="designCard.metadata.design.bg_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('amount_color')">
                            <span class="text-sm">Amount Color</span>
                            <Colorpicker v-model="designCard.metadata.design.amount_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('pill_text_color')">
                            <span class="text-sm">Pill Text Color</span>
                            <Colorpicker v-model="designCard.metadata.design.pill_text_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('pill_bg_color')">
                            <span class="text-sm">Pill BG Color</span>
                            <Colorpicker v-model="designCard.metadata.design.pill_bg_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('media_text_color')">
                            <span class="text-sm">Media Text Color</span>
                            <Colorpicker v-model="designCard.metadata.design.media_text_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('media_bg_color')">
                            <span class="text-sm">Media BG Color</span>
                            <Colorpicker v-model="designCard.metadata.design.media_bg_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('checkbox_color')">
                            <span class="text-sm">Checkbox Color</span>
                            <Colorpicker v-model="designCard.metadata.design.checkbox_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('radio_color')">
                            <span class="text-sm">Radio Color</span>
                            <Colorpicker v-model="designCard.metadata.design.radio_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div
                            class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('divider_color')">
                            <span class="text-sm">Divider Color</span>
                            <Colorpicker v-model="designCard.metadata.design.divider_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div
                            class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('address_color')">
                            <span class="text-sm">Address Color</span>
                            <Colorpicker v-model="designCard.metadata.design.address_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div
                            class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('countdown_text_color')">
                            <span class="text-sm">Countdown Color</span>
                            <Colorpicker v-model="designCard.metadata.design.countdown_text_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('product_name_color')">
                            <span class="text-sm">Product Name Color</span>
                            <Colorpicker v-model="designCard.metadata.design.product_name_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('product_price_color')">
                            <span class="text-sm">Price Color</span>
                            <Colorpicker v-model="designCard.metadata.design.product_price_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                        <div class="flex space-x-2 items-center justify-between"
                            v-if="designCard.metadata.design.hasOwnProperty('product_cancelled_price_color')">
                            <span class="text-sm">Cancelled Price Color</span>
                            <Colorpicker v-model="designCard.metadata.design.product_cancelled_price_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                        </div>

                    </div>

                </template>

                <template v-else-if="index == 1">

                    <div class="flex space-x-2 items-center justify-between pt-4 px-4">
                        <span class="text-sm">Border Color</span>
                        <Colorpicker v-model="designCard.metadata.design.border_color" shape="square" :disableHistory="true" :disableAlpha="true"></Colorpicker>
                    </div>

                    <div class="flex space-x-2 justify-center items-center mt-4">
                        <Link2 size="20"></Link2>
                        <Input
                            type="checkbox"
                            v-model="canSyncBorder"
                            inputLabel="Same border">
                        </Input>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-4">
                        <Input
                            min="0"
                            label="Top"
                            type="number"
                            class="w-full"
                            v-model="designCard.metadata.design.t_border"
                            @input="designState.saveStateDebounced('Top border changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.t_border`)">
                        </Input>

                        <Input
                            min="0"
                            type="number"
                            class="w-full"
                            label="Bottom"
                            v-model="designCard.metadata.design.b_border"
                            @input="designState.saveStateDebounced('Bottom border changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.b_border`)">
                        </Input>

                        <Input
                            min="0"
                            label="Left"
                            type="number"
                            class="w-full"
                            v-model="designCard.metadata.design.l_border"
                            @input="designState.saveStateDebounced('Left border changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.l_border`)">
                        </Input>

                        <Input
                            min="0"
                            type="number"
                            label="Right"
                            class="w-full"
                            v-model="designCard.metadata.design.r_border"
                            @input="designState.saveStateDebounced('Right border changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.r_border`)">
                        </Input>

                    </div>

                </template>

                <template v-else-if="index == 2">

                    <div class="flex space-x-2 justify-center items-center mt-4">
                        <Link2 size="20"></Link2>
                        <Input
                            type="checkbox"
                            inputLabel="Same corners"
                            v-model="canSyncBorderRadius">
                        </Input>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-4">
                        <Input
                            min="0"
                            type="number"
                            class="w-full"
                            label="Top Left"
                            v-model="designCard.metadata.design.tl_border_radius"
                            @input="designState.saveStateDebounced('Border radius changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.tl_border_radius`)">
                        </Input>

                        <Input
                            min="0"
                            type="number"
                            class="w-full"
                            label="Top Right"
                            v-model="designCard.metadata.design.tr_border_radius"
                            @input="designState.saveStateDebounced('Border radius changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.tr_border_radius`)">
                        </Input>

                        <Input
                            min="0"
                            type="number"
                            class="w-full"
                            label="Bottom Left"
                            v-model="designCard.metadata.design.bl_border_radius"
                            @input="designState.saveStateDebounced('Border radius changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.bl_border_radius`)">
                        </Input>

                        <Input
                            min="0"
                            type="number"
                            class="w-full"
                            label="Bottom Right"
                            v-model="designCard.metadata.design.br_border_radius"
                            @input="designState.saveStateDebounced('Border radius changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.br_border_radius`)">
                        </Input>

                    </div>

                </template>

                <template v-if="index == 3">

                    <div class="flex space-x-2 justify-center items-center mt-4">
                        <Link2 size="20"></Link2>
                        <Input
                            type="checkbox"
                            v-model="canSyncPadding"
                            inputLabel="Same spacing">
                        </Input>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-4">
                        <Input
                            min="0"
                            label="Top"
                            type="number"
                            class="w-full"
                            v-model="designCard.metadata.design.t_padding"
                            @input="designState.saveStateDebounced('Top padding changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.t_padding`)">
                        </Input>

                        <Input
                            min="0"
                            type="number"
                            class="w-full"
                            label="Bottom"
                            v-model="designCard.metadata.design.b_padding"
                            @input="designState.saveStateDebounced('Bottom padding changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.b_padding`)">
                        </Input>

                        <Input
                            min="0"
                            label="Left"
                            type="number"
                            class="w-full"
                            v-model="designCard.metadata.design.l_padding"
                            @input="designState.saveStateDebounced('Left padding changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.l_padding`)">
                        </Input>

                        <Input
                            min="0"
                            type="number"
                            label="Right"
                            class="w-full"
                            v-model="designCard.metadata.design.r_padding"
                            @input="designState.saveStateDebounced('Right padding changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.r_padding`)">
                        </Input>

                    </div>

                </template>

                <template v-else-if="index == 4">

                    <div class="flex space-x-2 justify-center items-center mt-4">
                        <Link2 size="20"></Link2>
                        <Input
                            type="checkbox"
                            v-model="canSyncMargin"
                            inputLabel="Same spacing">
                        </Input>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-4">
                        <Input
                            min="0"
                            label="Top"
                            type="number"
                            class="w-full"
                            v-model="designCard.metadata.design.t_margin"
                            @input="designState.saveStateDebounced('Top margin changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.t_margin`)">
                        </Input>

                        <Input
                            min="0"
                            type="number"
                            class="w-full"
                            label="Bottom"
                            v-model="designCard.metadata.design.b_margin"
                            @input="designState.saveStateDebounced('Bottom margin changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.b_margin`)">
                        </Input>

                        <Input
                            min="0"
                            label="Left"
                            type="number"
                            class="w-full"
                            v-model="designCard.metadata.design.l_margin"
                            @input="designState.saveStateDebounced('Left margin changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.l_margin`)">
                        </Input>

                        <Input
                            min="0"
                            type="number"
                            label="Right"
                            class="w-full"
                            v-model="designCard.metadata.design.r_margin"
                            @input="designState.saveStateDebounced('Right margin changed')"
                            :errorText="formState.getFormError(`design_cards.${index}.metadata.design.r_margin`)">
                        </Input>

                    </div>

                </template>

            </vue-slide-up-down>

        </div>

    </template>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import VueSlideUpDown from 'vue-slide-up-down';
    import Colorpicker from '@Partials/Colorpicker.vue';
    import { Link2, ChevronUp, ChevronDown } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'designState'],
        components: { Link2, Input, VueSlideUpDown, Colorpicker, ChevronUp, ChevronDown },
        props: {
            designCard: {
                type: Object
            }
        },
        data() {
            return {
                canSyncBorder: true,
                canSyncMargin: true,
                canSyncPadding: true,
                canSyncBorderRadius: true,
                accordions: [
                    {
                        label: 'Color',
                        expanded: false
                    },
                    {
                        label: 'Border',
                        expanded: false
                    },
                    {
                        label: 'Corners',
                        expanded: false
                    },
                    {
                        label: 'Inner spacing',
                        expanded: false
                    },
                    {
                        label: 'Outer spacing',
                        expanded: false
                    },
                ]
            }
        },
        watch: {
            'designCard.metadata.design.tl_border_radius'(newValue) {
                this.syncBorderRadius(newValue)
            },
            'designCard.metadata.design.tr_border_radius'(newValue) {
                this.syncBorderRadius(newValue)
            },
            'designCard.metadata.design.bl_border_radius'(newValue) {
                this.syncBorderRadius(newValue)
            },
            'designCard.metadata.design.br_border_radius'(newValue) {
                this.syncBorderRadius(newValue)
            },

            'designCard.metadata.design.t_border'(newValue) {
                this.syncBorder(newValue)
            },
            'designCard.metadata.design.b_border'(newValue) {
                this.syncBorder(newValue)
            },
            'designCard.metadata.design.l_border'(newValue) {
                this.syncBorder(newValue)
            },
            'designCard.metadata.design.r_border'(newValue) {
                this.syncBorder(newValue)
            },

            'designCard.metadata.design.t_margin'(newValue) {
                this.syncMargin(newValue)
            },
            'designCard.metadata.design.b_margin'(newValue) {
                this.syncMargin(newValue)
            },
            'designCard.metadata.design.l_margin'(newValue) {
                this.syncMargin(newValue)
            },
            'designCard.metadata.design.r_margin'(newValue) {
                this.syncMargin(newValue)
            },

            'designCard.metadata.design.t_padding'(newValue) {
                this.syncPadding(newValue)
            },
            'designCard.metadata.design.b_padding'(newValue) {
                this.syncPadding(newValue)
            },
            'designCard.metadata.design.l_padding'(newValue) {
                this.syncPadding(newValue)
            },
            'designCard.metadata.design.r_padding'(newValue) {
                this.syncPadding(newValue)
            },
        },
        methods: {
            syncBorderRadius(newValue) {
                if(this.canSyncMargin) {
                    if(this.designCard.metadata.design.tl_border_radius != newValue) {
                        this.designCard.metadata.design.tl_border_radius = newValue;
                    }
                    if(this.designCard.metadata.design.tr_border_radius != newValue) {
                        this.designCard.metadata.design.tr_border_radius = newValue;
                    }
                    if(this.designCard.metadata.design.bl_border_radius != newValue) {
                        this.designCard.metadata.design.bl_border_radius = newValue;
                    }
                    if(this.designCard.metadata.design.br_border_radius != newValue) {
                        this.designCard.metadata.design.br_border_radius = newValue;
                    }
                }
            },
            syncBorder(newValue) {
                if(this.canSyncBorder) {
                    if(this.designCard.metadata.design.t_border != newValue) {
                        this.designCard.metadata.design.t_border = newValue;
                    }
                    if(this.designCard.metadata.design.b_border != newValue) {
                        this.designCard.metadata.design.b_border = newValue;
                    }
                    if(this.designCard.metadata.design.l_border != newValue) {
                        this.designCard.metadata.design.l_border = newValue;
                    }
                    if(this.designCard.metadata.design.r_border != newValue) {
                        this.designCard.metadata.design.r_border = newValue;
                    }
                }
            },
            syncMargin(newValue) {
                if(this.canSyncMargin) {
                    if(this.designCard.metadata.design.t_margin != newValue) {
                        this.designCard.metadata.design.t_margin = newValue;
                    }
                    if(this.designCard.metadata.design.b_margin != newValue) {
                        this.designCard.metadata.design.b_margin = newValue;
                    }
                    if(this.designCard.metadata.design.l_margin != newValue) {
                        this.designCard.metadata.design.l_margin = newValue;
                    }
                    if(this.designCard.metadata.design.r_margin != newValue) {
                        this.designCard.metadata.design.r_margin = newValue;
                    }
                }
            },
            syncPadding(newValue) {
                if(this.canSyncPadding) {
                    if(this.designCard.metadata.design.t_padding != newValue) {
                        this.designCard.metadata.design.t_padding = newValue;
                    }
                    if(this.designCard.metadata.design.b_padding != newValue) {
                        this.designCard.metadata.design.b_padding = newValue;
                    }
                    if(this.designCard.metadata.design.l_padding != newValue) {
                        this.designCard.metadata.design.l_padding = newValue;
                    }
                    if(this.designCard.metadata.design.r_padding != newValue) {
                        this.designCard.metadata.design.r_padding = newValue;
                    }
                }
            },
            toggleAccordian(index) {
                for (let i = 0; i < this.accordions.length; i++) {
                    if(i == index) {
                        this.accordions[i].expanded = !this.accordions[i].expanded;
                    }else{
                        this.accordions[i].expanded = false;
                    }
                }
            }
        }
    }

</script>
