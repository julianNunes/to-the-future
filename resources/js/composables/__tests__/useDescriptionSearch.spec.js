import { useDescriptionSearch } from '@/composables/useDescriptionSearch.js'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

vi.mock('@/utils/logger.js', () => ({
    logger: {
        error: vi.fn(),
    },
}))

describe('useDescriptionSearch', () => {
    beforeEach(() => {
        vi.useFakeTimers()
        window.axios = {
            get: vi.fn().mockResolvedValue({
                data: [{ description: 'Travel' }],
            }),
        }
    })

    afterEach(() => {
        vi.useRealTimers()
        vi.restoreAllMocks()
    })

    it('encodes the query and updates loading state around the request', async () => {
        const { descriptions, isSearching, searchDescriptions } = useDescriptionSearch('budget-provision')

        searchDescriptions('trip card/2026')

        expect(isSearching.value).toBe(true)

        await vi.advanceTimersByTimeAsync(300)

        expect(window.axios.get).toHaveBeenCalledWith('/budget-provision/search/trip%20card%2F2026')
        expect(descriptions.value).toEqual([{ description: 'Travel' }])
        expect(isSearching.value).toBe(false)
    })

    it('clears results for short queries', async () => {
        const { descriptions, isSearching, searchDescriptions } = useDescriptionSearch('budget-provision')

        searchDescriptions('travel')
        await vi.advanceTimersByTimeAsync(300)

        expect(descriptions.value).toEqual([{ description: 'Travel' }])

        searchDescriptions('t')

        expect(descriptions.value).toEqual([])
        expect(isSearching.value).toBe(false)
    })
})
