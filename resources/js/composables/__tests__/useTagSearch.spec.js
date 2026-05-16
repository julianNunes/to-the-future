import { useTagSearch } from '@/composables/useTagSearch.js'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

vi.mock('@/utils/logger.js', () => ({
    logger: {
        error: vi.fn(),
    },
}))

describe('useTagSearch', () => {
    beforeEach(() => {
        vi.useFakeTimers()
        window.axios = {
            get: vi.fn().mockResolvedValue({
                data: [{ name: 'TRAVEL' }],
            }),
        }
    })

    afterEach(() => {
        vi.useRealTimers()
        vi.restoreAllMocks()
    })

    it('encodes the query and prepends the normalized tag when missing', async () => {
        const { tags, isSearching, searchTags } = useTagSearch()

        searchTags('trip card/2026')

        expect(isSearching.value).toBe(true)

        await vi.advanceTimersByTimeAsync(300)

        expect(window.axios.get).toHaveBeenCalledWith('/tag/search/trip%20card%2F2026')
        expect(tags.value).toEqual([{ name: 'TRIP CARD/2026' }, { name: 'TRAVEL' }])
        expect(isSearching.value).toBe(false)
    })

    it('skips requests when the tag is already selected regardless of case', async () => {
        const { tags, isSearching, searchTags } = useTagSearch()

        searchTags('travel', [{ name: 'TRAVEL' }])

        await vi.advanceTimersByTimeAsync(300)

        expect(window.axios.get).not.toHaveBeenCalled()
        expect(tags.value).toEqual([])
        expect(isSearching.value).toBe(false)
    })
})
