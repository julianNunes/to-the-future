import { logger } from '@/utils/logger.js'
import { ref } from 'vue'

export function useDescriptionSearch(entityType) {
    const descriptions = ref([])
    const isSearching = ref(false)
    let timeout = null

    function searchDescriptions(val) {
        const normalizedValue = String(val ?? '').trim()

        if (!normalizedValue || normalizedValue.length < 2) {
            clearDescriptions()
            return
        }

        clearTimeout(timeout)
        isSearching.value = true

        timeout = setTimeout(async () => {
            try {
                const response = await window.axios.get(`/${entityType}/search/${encodeURIComponent(normalizedValue)}`)
                descriptions.value = response.data && response.data.length > 0 ? response.data : []
            } catch (error) {
                logger.error('Description search error:', error)
            } finally {
                isSearching.value = false
            }
        }, 300)
    }

    function clearDescriptions() {
        clearTimeout(timeout)
        descriptions.value = []
        isSearching.value = false
    }

    return { descriptions, isSearching, searchDescriptions, clearDescriptions }
}
