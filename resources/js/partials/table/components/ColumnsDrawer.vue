<template>

    <div>

        <!-- Columns Button -->
        <Button
            size="xs"
            type="bare"
            :leftIcon="Logs"
            :action="openColumnsDrawer">
        </Button>

        <Drawer
            position="right"
            ref="columnsDrawer"
            :showFooter="false"
            :scrollOnContent="false">

            <template #content>

                <!-- Header -->
                <div class="flex items-center space-x-2 bg-gray-100 border-b border-gray-300 shadow-sm p-4">

                    <!-- Filter Icon -->
                    <Logs size="20"></Logs>

                    <!-- Heading -->
                    <h2>Columns</h2>

                </div>

                <p class="p-4 text-sm bg-indigo-100">
                    Show, hide and move your data they way you want to see it
                </p>

                <!-- Show Everything Toggle Switch -->
                <div class="p-4 border-b shadow-sm">

                    <Switch
                        size="sm"
                        v-model="showEverything"
                        suffixText="Show Everything"/>

                </div>

                <div class="divide-y mb-4">

                    <draggable
                        class="divide-y mb-4"
                        v-model="localColumns"
                        handle=".draggable-handle"
                        ghost-class="bg-yellow-50">

                        <template
                            :key="index"
                            v-for="(column, index) in localColumns">

                            <div class="flex items-center justify-between p-4">

                                <Input
                                    @click.stop
                                    type="checkbox"
                                    v-model="column.active"
                                    :inputLabel="column.name">
                                </Input>

                                <!-- Drag & Drop Handle -->
                                <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                            </div>

                        </template>

                    </draggable>

                </div>

                <!-- Reset Columns Button -->
                <div class="px-4 mb-60">

                    <Button
                        size="sm"
                        type="light"
                        buttonClass="w-full"
                        :action="resetColumns">
                        <span>Reset Columns</span>
                    </Button>

                </div>

            </template>

        </Drawer>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import cloneDeep from 'lodash.cloneDeep';
    import Button from '@Partials/Button.vue';
    import Drawer from '@Partials/Drawer.vue';
    import Switch from '@Partials/Switch.vue';
    import { Move, Logs } from 'lucide-vue-next';
    import { VueDraggableNext } from 'vue-draggable-next';

    export default {
        inject: ['formState'],
        components: { Move, Logs, draggable: VueDraggableNext, Input, Pill, Button, Drawer, Switch },
        props: {
            columns: {
                type: Array,
                default: () => []
            },
        },
        emits: ['updatedColumns'],
        data() {
            return {
                Logs,
                showMore: false,
                columnsDrawer: null,
                showEverything: false,
                localColumns: cloneDeep(this.columns),
                originalColumns: cloneDeep(this.columns)
            }
        },
        watch: {
            localColumns: {
                handler(newVal) {
                    if(this.showEverything != this.isShowingEverything) {
                        this.showEverything = this.isShowingEverything;
                    }
                    this.$emit('updatedColumns', newVal);
                },
                deep: true
            },
            showEverything(newVal) {
                if(newVal) {
                    this.localColumns.map((localColumn) => localColumn.active = true);
                }else if(this.isShowingEverything) {
                    this.localColumns.map((localColumn) => localColumn.active = localColumn.priority);
                }
            },
        },
        computed: {
            isShowingEverything() {
                return this.localColumns.filter((localColumn) => localColumn.active == true).length == this.localColumns.length;
            }
        },
        methods: {
            openColumnsDrawer() {
                this.columnsDrawer.showDrawer();
            },
            closeColumnsDrawer() {
                this.columnsDrawer.hideDrawer();
            },
            resetColumns() {
                this.localColumns = cloneDeep(this.originalColumns);
            }
        },
        mounted() {
            this.columnsDrawer = this.$refs.columnsDrawer;
        },
    };
</script>
