import { useShareCalculation } from '@/composables/useShareCalculation.js'

describe('useShareCalculation', () => {
    it('calculates a percentage-based share value', () => {
        const { calculateShareValue } = useShareCalculation()

        expect(calculateShareValue('100', '35')).toBe('35.00')
        expect(calculateShareValue('250', '10')).toBe('25.00')
    })

    it('returns zero when input is incomplete', () => {
        const { calculateShareValue } = useShareCalculation()

        expect(calculateShareValue('', '35')).toBe(0)
        expect(calculateShareValue('100', '')).toBe(0)
    })
})