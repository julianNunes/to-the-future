<template>
    <VTextField ref="inputRef" type="text" />
</template>

<script>
import { watch } from 'vue'
import { useCurrencyInput } from 'vue-currency-input'

export default {
    name: 'CurrencyInput',
    props: {
        modelValue: Number,
        options: Object,
    },
    setup(props) {
        const { inputRef, setOptions, setValue } = useCurrencyInput(props.options)

        watch(
            () => props.modelValue,
            (value) => {
                console.log('setValue', value)
                setValue(value)
            }
        )

        watch(
            () => props.options,
            (options) => {
                setOptions(options)
            }
        )

        return { inputRef }
    },
}
</script>
