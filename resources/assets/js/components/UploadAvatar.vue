<template>
    <div class="upload-avatar">
        <div class="box-body" id="croppie"></div>
        <div class="upload-wapper text-center" style="padding-bottom: 10px">
            <div class="btn btn-primary btn-sm" @click="modelVisible = true">
                <i class="fa fa-camera"> Upload Avatar</i>
            </div>

            <div class="Model" v-if="modelVisible">
                <div class="input-file">
                    <input style="opacity: 0;position: absolute;margin-left: 20%" type="file" @change="setUpFileUploader">
                    <h4 id="fileName">点击这里选择上传文件</h4>
                </div>

                <div class="btn btn-success btn-sm" @click="uploadFile">
                    <i class="fa fa-upload"> Upload</i>
                </div>

                <div class="btn btn-danger btn-sm" @click="modelVisible = false">
                    <i class="fa fa-times"> Cancel</i>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Croppie from 'croppie';
    export default {
        props:['avatar'],
        data(){
            return {
                image: null,
                croppie: null,
                modelVisible: false,
            }
        },
        mounted() {
            this.$on('imageUploaded',function (imageData) {
                this.image = imageData;
                this.croppie.destroy();
                this.setupCroppie(imageData);
            });

            this.image = this.avatar;
            this.setupCroppie();//初始化croppie
        },
        methods:{
            setupCroppie() {
                let el = document.getElementById('croppie');
                this.croppie = new Croppie(el, {

                    viewport: { width: 200, height: 200, type: 'circle' },
                    boundary: { width: 250, height: 250 },
                });
                this.croppie.bind({
                    url: this.image
                });
            },
            setUpFileUploader(e) {
                let files = e.target.files || e.dataTransfer.files;
                if (!files) {
                    return
                }
                this.createImage(files[0]);

            },
            createImage(file) {
                let image = new Image();
                let reader = new FileReader();
                let vm = this;

                reader.onload = (e) => {
                    vm.image = e.target.result;
                    vm.$emit('imageUploaded',e.target.result);
                };

                reader.readAsDataURL(file)
            },
            uploadFile() {
                this.croppie.result({
                    type: 'canvas',
                    size: 'viewport'
                }).then(response => {
                    this.image = response;
                    axios.post('/avatar-upload',{img: this.image}).then(response => {
                        this.modelVisible = false
                    })
                })
            }
        }
    }
</script>
