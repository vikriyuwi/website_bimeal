<script>
import axios from "axios"
import {ref,onMounted} from 'vue'

const products = ref([])
const getProducts = () => {
    axios.get('/api/product')
    .then(res = products.value = res.data)
    .catch(error => console.log(error))
}

onMounted(() => getProducts())
</script>

<template>
    <div>
        <h1>All product</h1>

    </div>
</template>