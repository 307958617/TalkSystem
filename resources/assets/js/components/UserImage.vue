<template>
    <!-- 需要注意的是，这里的src不能是props直接传递的src，因为这个值是需要根据socket监听图片上传事件而变化的 -->
    <img :src="imgSrc" :class="imgClass" :alt="alt">
</template>

<script>
    export default {
        props:['src', 'imgClass', 'alt'],
        created() {
            this.imgSrc = this.src;//初始化的地址为传递进来的地址
        },
        data() {
            return {
                'imgSrc': '',
            }
        },
        methods:{
            userImageChanged(imgSrc) {
                this.imgSrc = imgSrc;//替换当前的图片地址
                console.log('changed to ',imgSrc)
            }
        },
        sockets:{
            user_image_upload(imgSrc) {//监听server.js的user_image_upload频道
                console.log(imgSrc);
                this.userImageChanged(imgSrc);
            }
        }
    }
</script>
