<template>

    <div>

        <Dropdown
            ref="dropdown"
            dropdownClasses="w-4/5">

            <template #trigger="props">

                <Button
                    size="md"
                    type="light"
                    :leftIcon="Plus"
                    buttonClass="w-full"
                    class="w-4/5 mx-auto"
                    :action="props.toggleDropdown">
                    <span>Add Card</span>
                </Button>

            </template>

            <template #content="props">

                <div class="z-20 bg-white rounded-lg shadow-md">
                    <div class="grid grid-cols-3 divide-x divide-y divide-gray-200">
                        <div
                            :key="index"
                            v-for="(cardOption, index) in cardOptions"
                            @click="(e) => addCard(cardOption.value, props.toggleDropdown, e)"
                            class="py-4 hover:bg-gray-100 cursor-pointer flex flex-col space-y-4 justify-between items-center">
                            <Map v-if="cardOption.value == 'map'" size="20"></Map>
                            <Link v-if="cardOption.value == 'link'" size="20"></Link>
                            <Type v-if="cardOption.value == 'text'" size="20"></Type>
                            <Image v-if="cardOption.value == 'image'" size="20"></Image>
                            <Video v-if="cardOption.value == 'video'" size="20"></Video>
                            <Clock v-if="cardOption.value == 'countdown'" size="20"></Clock>
                            <Contact v-if="cardOption.value == 'contact'" size="20"></Contact>
                            <Grid2x2 v-if="cardOption.value == 'product category'" size="20"></Grid2x2>
                            <span class="text-xs whitespace-nowrap">{{ cardOption.label }}</span>
                        </div>
                    </div>
                </div>

            </template>

        </Dropdown>

        <!-- Draggable Fields -->
        <draggable
            v-model="cards"
            class="mt-4 space-y-2"
            handle=".draggable-handle"
            ghost-class="bg-yellow-50">

            <div
                :key="index"
                v-for="(card, index) in cards"
                class="w-4/5 mx-auto bg-gray-50 p-4 border border-gray-300 rounded-lg">

                <div class="flex items-center justify-between mb-2">

                    <div class="flex items-center space-x-2 text-gray-400">
                        <Map v-if="card.type == 'map'" size="16"></Map>
                        <Link v-if="card.type == 'link'" size="16"></Link>
                        <Type v-if="card.type == 'text'" size="16"></Type>
                        <Image v-if="card.type == 'image'" size="16"></Image>
                        <Video v-if="card.type == 'video'" size="16"></Video>
                        <Clock v-if="card.type == 'countdown'" size="16"></Clock>
                        <Contact v-if="card.type == 'contact'" size="16"></Contact>
                        <Grid2x2 v-if="card.type == 'product category'" size="16"></Grid2x2>
                        <span class="text-xs whitespace-nowrap">{{ getCardLabel(card) }}</span>
                    </div>

                    <!-- Drag & Drop Handle -->
                    <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                </div>

                <template v-if="card.type == 'product category'">

                    <!-- Category -->
                    <Select
                        width="w-full"
                        :search="false"
                        label="Category"
                        :options="categories"
                        v-model="card.category_id">
                    </Select>

                </template>

                <div class="flex justify-end space-x-4 mt-4">

                    <!-- Visible Switch -->
                    <div  class="cursor-pointer hover:text-blue-500 transition-all duration-500">
                        <EyeOff v-if="card.active" size="20" @click.stop="() => toggleActive(index)"></EyeOff>
                        <Eye v-else size="20" @click.stop="() => toggleActive(index)"></Eye>
                    </div>

                    <Trash size="20" class="cursor-pointer"></Trash>

                </div>

            </div>

        </draggable>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import Switch from '@Partials/Switch.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import { Eye, Map, Move, Plus, Link, Type, Clock, Trash, Image, Video, EyeOff, Grid2x2, Contact } from 'lucide-vue-next';

    export default {
        inject: ['storeState'],
        components: { Eye, Map, Move, Link, Type, Clock, Trash, Image, Video, EyeOff, Grid2x2, Contact, Button, Select, Switch, Dropdown, draggable: VueDraggableNext },
        data() {
            return {
                Plus,
                cards: [],
                categories: [],
                cardOptions: [
                    { label: 'Product Category', value: 'product category'},
                    { label: 'Link', value: 'link'},
                    { label: 'Text', value: 'text'},
                    { label: 'Image', value: 'image'},
                    { label: 'Video', value: 'video'},
                    { label: 'Contact', value: 'contact'},
                    { label: 'Countdown', value: 'countdown'},
                    { label: 'Map', value: 'map'},
                ],
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
        },
        methods: {
            async setup() {
                if(this.store) {
                    this.categories = this.store.categories.map((category) => {
                        return {
                            label: category.name,
                            value: category.id
                        }
                    });
                }
            },
            getCardLabel(card) {
                return this.cardOptions.find(cardOption => cardOption.value == card.type).label;
            },
            toggleActive(index) {
                this.cards[index].active = !this.cards[index].active;
            },
            addCard(type, toggleDropdown, e) {

                let card;

                if(type == 'product category') {
                    card = {
                        category_id: this.categories.length ? this.categories[0].value : null,
                        layout: 'grid',
                        limit: true,
                    };
                }

                card = {
                    ...card,
                    type: type,
                    visible: true
                };

                this.cards.push(card);
                toggleDropdown(e);
            }
        },
        created() {
            this.setup();
        }
    }
</script>
