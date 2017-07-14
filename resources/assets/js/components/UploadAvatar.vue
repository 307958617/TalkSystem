<template>
    <div class="upload-avatar">
        <div class="box-body" id="croppie"></div><!-- 定义一个容器，用来显示croppie，这是必须的第一步 -->
        <div class="upload-wapper text-center" style="padding-bottom: 10px">
            <div class="btn btn-primary btn-sm" @click="modelVisible = true">
                <i class="fa fa-camera"> Upload Avatar</i><!-- 添加一个按钮，点击它可以出现上传图片按钮和取消按钮 -->
            </div>

            <div class="Model" v-if="modelVisible">
                <div class="input-file">
                    <!-- 下面input的style是将文件选择框隐藏到h4标签的后面，达到美化界面的效果 -->
                    <input style="opacity: 0;position: absolute;margin-left: 20%" type="file" @change="setUpFileUploader">
                    <h4 id="fileName">点击这里选择上传文件</h4>
                </div>

                <div class="btn btn-success btn-sm" @click="uploadFile"><!-- 实现上传图片的功能 -->
                    <i class="fa fa-upload"> Upload</i>
                </div>

                <div class="btn btn-danger btn-sm" @click="modelVisible = false"><!-- 实现取消功能 -->
                    <i class="fa fa-times"> Cancel</i>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Croppie from 'croppie';//引入croppie，在此之前需要安装croppie，即执行：npm install croppie --save
    export default {
        props:['avatar'],//将当前登录用户的头像地址传递进来
        data(){
            return {
                image: null,//定义图片的src地址
                croppie: null,
                modelVisible: false,//定义model的显示状态，默认是不显示
            }
        },
        mounted() {
            this.$on('imageUploaded',function (imageData) {//监听上传图片事件，如果上传了新图片则执行下面代码
                this.image = imageData;//将当前的图片设置为新上传的图片
                this.croppie.destroy();//重新销毁croppie
                this.setupCroppie(imageData);//重新初始化croppie
            });

            this.image = this.avatar;//初始化时，显示的图片是用户的图片
            this.setupCroppie();//初始化croppie
        },
        methods:{
            setupCroppie() {//初始化croppie
                let el = document.getElementById('croppie');//指定容器
                this.croppie = new Croppie(el, {

                    viewport: { width: 200, height: 200, type: 'circle' },//生成的图片大小及形状设置
                    boundary: { width: 250, height: 250 },//背景大小设置
                });
                this.croppie.bind({
                    url: this.image //croppie里面显示图片地址，默认为传递进来的登录用户的头像地址avatar。
                });
            },
            setUpFileUploader(e) {//实现选择图片后生产图片功能
                let files = e.target.files || e.dataTransfer.files;
                if (!files) {
                    return
                }
                this.createImage(files[0]);

            },
            createImage(file) {//具体实现生成图片
                let image = new Image();
                let reader = new FileReader();
                let vm = this;

                reader.onload = (e) => {
                    vm.image = e.target.result;
                    vm.$emit('imageUploaded',e.target.result);//发布图片上传消息，将新的图片数据发送出去
                };

                reader.readAsDataURL(file)
            },
            uploadFile() {//实现点击图片上传功能
                this.croppie.result({//对图片进行如下处理并返回response
                    type: 'canvas',
                    size: 'viewport'
                }).then(response => {
                    this.image = response;//这里的response是经过croppie剪切处理后的图片
                    axios.post('/avatar-upload',{img: this.image}).then(response => {//将图片传递到larvel后台进行处理
                        this.modelVisible = false;//成功后隐藏上传按钮
                        console.log(response)
                    })
                })
            }
        }
    }
</script>
