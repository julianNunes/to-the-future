<!-- <template>
    <VTextField
        ref="inputRef"
        v-money3="options"
        :value="modelValue"
        :model-modifiers="{ number: true }"
        type="text"
        @input="updateValue"
    />
</template>

<script>
import { Money3Directive } from 'v-money3'
export default {
    name: 'VCurrency',
    directives: { money3: Money3Directive },
    props: {
        modelValue: Number,
        options: Object,
    },
    emits: ['update:modelValue'],

    setup(props, context) {
        const updateValue = (event) => {
            context.emit('update:modelValue', event.target.value)
        }

        return { updateValue }
    },
    data() {
        return {
            value: this.modelValue,
        }
    },
}
</script> -->
