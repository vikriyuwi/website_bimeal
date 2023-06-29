import './bootstrap';

import { createApp } from "vue";
import HomeIndex from "./components/home/homeIndex.vue"

const app = createApp;
app.component("home-index",HomeIndex);

app.mount("#app");
