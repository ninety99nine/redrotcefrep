<template>

    <div :style="{
        color: designCard.metadata.design.title_color,
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

        <!-- Case 1: Title is set, becomes outermost dropdown -->
        <div v-if="designCard.metadata.title">
            <div
                @click.stop="toggleExpanded('root')"
                class="cursor-pointer text-gray-900 group"
                :class="{ 'hover:bg-gray-100': !expanded['root'] }">
                <div class="flex items-center text-gray-700 hover:bg-gray-100 pr-4">
                    <span class="w-full text-lg font-semibold py-2 pl-4 ms-3 group-hover:text-gray-900">{{ designCard.metadata.title }}</span>
                    <div
                        class="p-2">
                        <ChevronUp size="20" v-if="expanded['root'] && categories?.length"></ChevronUp>
                        <ChevronDown size="20" v-else-if="categories?.length"></ChevronDown>
                    </div>
                </div>
            </div>

            <!-- Categories nested under title (if expanded) -->
            <div v-if="expanded['root'] && categories?.length">
                <!-- Level 1: Top-level categories (indented under title) -->
                <div
                    :key="category.id"
                    v-for="category in categories"
                    class="cursor-pointer text-gray-900 group"
                    @click.stop="() => navigateToShowShopCategory(category)">
                    <div class="flex items-center text-gray-700 hover:bg-gray-100 pr-4">
                        <span class="w-full text-lg font-semibold py-2 pl-8 ms-3 group-hover:text-gray-900">{{ category.name }}</span>
                        <div
                            class="p-2"
                            v-if="category.sub_categories?.length"
                            @click.stop="toggleExpanded(category.id)">
                            <ChevronUp size="20" v-if="expanded[category.id]"></ChevronUp>
                            <ChevronDown size="20" v-else></ChevronDown>
                        </div>
                    </div>

                    <!-- Level 2: Subcategories (if expanded) -->
                    <div v-if="expanded[category.id] && category.sub_categories?.length">
                        <div
                        :key="sub.id"
                        v-for="sub in category.sub_categories"
                        @click.stop="() => navigateToShowShopCategory(sub)"
                        class="cursor-pointer text-gray-900 group">
                        <div
                            class="flex items-center text-gray-700 hover:bg-gray-100 pr-4">
                            <span class="w-full text-lg font-semibold py-2 pl-12 ms-3 group-hover:text-gray-900">{{ sub.name }}</span>
                            <div
                                class="p-2"
                                v-if="sub.sub_categories?.length"
                                @click.stop="toggleExpanded(sub.id)">
                                <ChevronUp size="20" v-if="expanded[sub.id]"></ChevronUp>
                                <ChevronDown size="20" v-else></ChevronDown>
                            </div>
                        </div>

                            <!-- Level 3: Sub-subcategories (if expanded) -->
                            <div v-if="expanded[sub.id]">
                                <div
                                    :key="subSub.id"
                                    v-for="subSub in sub.sub_categories"
                                    @click.stop="toggleExpanded(subSub.id)"
                                    class="cursor-pointer text-gray-900 group hover:bg-gray-100">
                                    <div class="flex items-center text-gray-700">
                                        <span class="w-full text-lg font-semibold py-2 pl-16 ms-3 group-hover:text-gray-900">{{ subSub.name }}</span>
                                        <!-- No further nesting beyond level 3; no chevron if no deeper subs -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Case 2: No title, parent categories are outermost dropdown -->
        <div v-else>
            <!-- Level 1: Top-level categories -->
            <div
                :key="category.id"
                v-for="category in categories"
                class="cursor-pointer text-gray-900 group"
                @click.stop="() => navigateToShowShopCategory(category)">
                <div class="flex items-center text-gray-700 hover:bg-gray-100 pr-4">
                    <span class="w-full text-lg font-semibold py-2 pl-4 ms-3 group-hover:text-gray-900">{{ category.name }}</span>
                    <div
                        class="p-2"
                        v-if="category.sub_categories?.length"
                        @click.stop="toggleExpanded(category.id)">
                        <ChevronUp size="20" v-if="expanded[category.id]"></ChevronUp>
                        <ChevronDown size="20" v-else></ChevronDown>
                    </div>
                </div>

                <!-- Level 2: Subcategories (if expanded) -->
                <div v-if="expanded[category.id] && category.sub_categories?.length">
                    <div
                        :key="sub.id"
                        v-for="sub in category.sub_categories"
                        @click.stop="() => navigateToShowShopCategory(sub)"
                        class="cursor-pointer text-gray-900 group">
                        <div class="flex items-center text-gray-700 hover:bg-gray-100 pr-4">
                            <span class="w-full text-lg font-semibold py-2 pl-8 ms-3 group-hover:text-gray-900">{{ sub.name }}</span>
                            <div
                                class="p-2"
                                v-if="sub.sub_categories?.length"
                                @click.stop="toggleExpanded(sub.id)">
                                <ChevronUp size="20" v-if="expanded[sub.id]"></ChevronUp>
                                <ChevronDown size="20" v-else></ChevronDown>
                            </div>
                        </div>

                        <!-- Level 3: Sub-subcategories (if expanded) -->
                        <div v-if="expanded[sub.id]">
                            <div
                                :key="subSub.id"
                                v-for="subSub in sub.sub_categories"
                                @click.stop="() => navigateToShowShopCategory(subSub)"
                                class="cursor-pointer text-gray-900 group hover:bg-gray-100">
                                <div class="flex items-center text-gray-700">
                                    <span class="w-full text-lg font-semibold py-2 pl-16 ms-3 group-hover:text-gray-900">{{ subSub.name }}</span>
                                    <!-- No further nesting beyond level 3; no chevron if no deeper subs -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<script>
    import { ChevronUp, ChevronDown } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: { ChevronUp, ChevronDown },
        props: {
            designCard: {
                type: Object
            }
        },
        data() {
            return {
                expanded: {},
                categories: [],
                isLoadingCategories: false
            }
        },
        watch: {
            store() {
                this.setup();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment', 'edit-menu'].includes(this.$route.name);
            }
        },
        methods: {
            async setup() {
                if (this.store) {
                    this.showCategories();
                }
            },
            toggleExpanded(id) {
                this.expanded = { ...this.expanded, [id]: !this.expanded[id] };
            },
            async navigateToShowShopCategory(category) {
                if(this.isDesigning) {
                    this.notificationState.showSuccessNotification(`Only opens on the actual store`);
                    return;
                }
                await this.$router.push({
                    name: 'show-shop-category',
                    params: {
                        alias: this.store.alias,
                        category_id: category.id
                    }
                });
            },
            async showCategories() {
                try {
                    this.isLoadingCategories = true;

                    let config = {
                        params: {
                            per_page: 100,
                            type: 'parent',
                            store_id: this.store.id,
                            _relationships: ['subCategories.subCategories.subCategories'].join(',')
                        }
                    };

                    const response = await axios.get('/api/categories', config);

                    const pagination = response.data;
                    this.categories = pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching categories';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch categories:', error);
                } finally {
                    this.isLoadingCategories = false;
                }
            }
        },
        created() {
            this.setup();
        }
    }
</script>
