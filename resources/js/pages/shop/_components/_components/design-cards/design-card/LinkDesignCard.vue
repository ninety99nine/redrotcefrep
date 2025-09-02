<template>

    <a :href="designCard.metadata.link" target="_blank" class="block text-blue-600 hover:text-blue-800">
        <div
            v-if="designCard.metadata.type == 'link' && designCard.metadata.title && designCard.metadata.link"
            class="bg-white rounded-2xl p-4 hover:bg-gray-50 hover:scale-95 transition-all duration-300 cursor-pointer">
            <div class="flex items-center justify-center space-x-4">
                <Link size="20"></Link>
                <span>{{ designCard.metadata.title }}</span>
            </div>
        </div>
    </a>

</template>

<script>

    import { Link } from 'lucide-vue-next';

    export default {
        components: { Link },
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
