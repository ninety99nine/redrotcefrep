<template>

    <Select
        v-bind="$props"
        :options="languagesOptions"
        placeholder="Select a language"
        noResultsText="No languages found"
        :searchableFields="['label', 'value']">
    </Select>

</template>

<script>

    import Select from '@Partials/Select.vue';
    import languages from '@Json/languages.json';

    export default {
        components: { Select },
        props: {
            allowedLanguages: {
                type: [Array, null],
                default: null
            }
        },
        data() {
            return {
                languagesOptions: []
            }
        },
        created() {
            const allLanguages = Object.values(languages).map(languages => ({
                label: `${languages.name}`,
                value: languages.code
            }));

            if (this.allowedLanguages && Array.isArray(this.allowedLanguages) && this.allowedLanguages.length > 0) {
                this.languagesOptions = allLanguages.filter(option =>
                    this.allowedLanguages.includes(option.value)
                );
            } else {
                this.languagesOptions = allLanguages;
            }
        }
    }

</script>
