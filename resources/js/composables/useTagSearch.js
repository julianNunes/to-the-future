import { logger } from '@/utils/logger.js'
import { ref } from 'vue'

export function useTagSearch() {
    const tags = ref([])
    const tagSearch = ref('')
    let timeout = null

    function searchTags(val, existingTags = []) {
        if (!val || val.length <= 1) {
            tags.value = []
            clearTimeout(timeout)
            return
        }

        if (existingTags.length > 0 && existingTags.find((x) => x.name === val)) {
            return
        }

        clearTimeout(timeout)
        timeout = setTimeout(async () => {
            let searchFieldsData = []
            try {
                const response = await window.axios.get('/tag/search/' + val)
                if (response.data && response.data.length > 0) {
                    searchFieldsData = response.data
                }

                if (
                    (searchFieldsData &&
                        searchFieldsData.length > 0 &&
                        !searchFieldsData.find((x) => x.name === val.toUpperCase())) ||
                    !searchFieldsData ||
                    searchFieldsData.length === 0
                ) {
                    searchFieldsData.unshift({ name: val.toUpperCase() })
                }
            } catch (error) {
                logger.error('Tag search error:', error)
            }

            tags.value = searchFieldsData
        }, 300)
    }

    function clearTags() {
        tags.value = []
        tagSearch.value = ''
    }

    return { tags, tagSearch, searchTags, clearTags }
}
