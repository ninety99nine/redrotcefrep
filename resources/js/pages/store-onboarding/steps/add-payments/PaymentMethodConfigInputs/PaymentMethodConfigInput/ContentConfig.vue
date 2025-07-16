<template>

    <div class="space-x-1">

        <template
            :key="index"
            v-for="(pieceOfContent, index) in configSchemaEntity.content">

            <!-- Text & Link -->
            <a
                target="_blank"
                :href="pieceOfContent.href"
                v-if="pieceOfContent.text && pieceOfContent.href"
                class="text-xs text-blue-700 hover:underline hover:text-blue-90">
                {{ pieceOfContent.text }}
                <ExternalLink size="12" class="inline-block ml-0.5 -mt-0.5"></ExternalLink>
            </a>

            <!-- Text Only -->
            <span
                class="text-xs"
                v-else-if="pieceOfContent.text">
                {{ pieceOfContent.text }}
            </span>

        </template>

    </div>

</template>

<script>

    import { ExternalLink } from 'lucide-vue-next';

    export default {
        components: { ExternalLink },
        props: {
            configSchemaEntity: {
                type: Object
            },
        }
    };
  </script>
