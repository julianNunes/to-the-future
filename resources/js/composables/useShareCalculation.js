export function useShareCalculation() {
    function calculateShareValue(value, shareValue) {
        if (!value || !shareValue) return 0
        return ((parseFloat(value) * parseFloat(shareValue)) / 100).toFixed(2)
    }

    return { calculateShareValue }
}
