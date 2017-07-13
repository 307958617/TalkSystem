<template>
    <img :src="imgSrc" :class="imgClass" :alt="alt">
</template>

<script>
    export default {
        props:['src', 'imgClass', 'alt'],
        created() {
            this.imgSrc = this.src;
        },
        data() {
            return {
                'imgSrc': '',
            }
        },
        methods:{
            userImageChanged(imgSrc) {
                this.imgSrc = imgSrc;
                console.log('changed to ',imgSrc)
            }
        },
        sockets:{
            user_image_upload(imgSrc) {
                console.log(imgSrc);
                this.userImageChanged(imgSrc);
            }
        }
    }
</script>
