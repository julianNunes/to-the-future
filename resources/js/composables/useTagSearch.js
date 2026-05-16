import { logger } from '@/utils/logger.js'
import { ref } from 'vue'

export function useTagSearch() {
    const tags = ref([])
    const tagSearch = ref('')
    const isSearching = ref(false)
    let timeout = null

    function searchTags(val, existingTags = []) {
        const normalizedValue = String(val ?? '').trim()
        const normalizedUpperValue = normalizedValue.toUpperCase()

        if (!normalizedValue || normalizedValue.length <= 1) {
            clearTags()
            return
        }

        if (existingTags.length > 0 && existingTags.find((tag) => tag.name?.toUpperCase() === normalizedUpperValue)) {
            isSearching.value = false
            return
        }

        clearTimeout(timeout)
        isSearching.value = true

        timeout = setTimeout(async () => {
            let searchFieldsData = []

            try {
                const response = await window.axios.get('/tag/search/' + encodeURIComponent(normalizedValue))

                if (response.data && response.data.length > 0) {
                    searchFieldsData = response.data
                }

                if (
                    (searchFieldsData &&
                        searchFieldsData.length > 0 &&
                        !searchFieldsData.find((tag) => tag.name?.toUpperCase() === normalizedUpperValue)) ||
                    !searchFieldsData ||
                    searchFieldsData.length === 0
                ) {
                    searchFieldsData.unshift({ name: normalizedUpperValue })
                }
            } catch (error) {
                logger.error('Tag search error:', error)
            } finally {
                isSearching.value = false
            }

            tags.value = searchFieldsData
        }, 300)
    }

    function clearTags() {
        clearTimeout(timeout)
        tags.value = []
        tagSearch.value = ''
        isSearching.value = false
    }

    return { tags, tagSearch, isSearching, searchTags, clearTags }
}
