<template>
<div>
<h1>Test Component</h1>
<v-btn @click="handleClick">Click Me</v-btn>
</div>
</template>

<script>
export default {
name: 'TestComponent',
methods: {
handleClick() {
console.log('Button clicked')
}
}
}
</script>

<style scoped>
.container {
padding: 20px;
margin: 10px;
}
</style>
