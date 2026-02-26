import { logger } from '@/utils/logger.js'
import { ref } from 'vue'

export function useDescriptionSearch(entityType) {
    const descriptions = ref([])
    let timeout = null

    function searchDescriptions(val) {
        if (!val || val.length < 2) {
            descriptions.value = []
            clearTimeout(timeout)
            return
        }

        clearTimeout(timeout)
        timeout = setTimeout(async () => {
            try {
                const response = await window.axios.get(`/${entityType}/search/${encodeURIComponent(val)}`)
                if (response.data && response.data.length > 0) {
                    descriptions.value = response.data
                }
            } catch (error) {
                logger.error('Description search error:', error)
            }
        }, 300)
    }

    function clearDescriptions() {
        descriptions.value = []
    }

    return { descriptions, searchDescriptions, clearDescriptions }
}
