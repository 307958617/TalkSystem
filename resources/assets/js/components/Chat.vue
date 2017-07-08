<template>
    <div class="box box-warning direct-chat direct-chat-warning">
        <div class="box-header with-border">
            <h3 class="box-title">Direct Chat</h3>

            <div class="box-tools pull-right">
                <span data-toggle="tooltip" title="" class="badge bg-yellow" data-original-title="3 New Messages">3</span>
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-toggle="tooltip" title="" data-widget="chat-pane-toggle" data-original-title="Contacts">
                    <i class="fa fa-comments"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                </button>
            </div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <!-- Conversations are loaded here -->
            <div class="direct-chat-messages" id="chatContainer">
                <!-- Message. Default to the left -->
                <div class="direct-chat-msg" :class="[Luser.id == message.user_id ? 'right' :'']" v-for="message in messages">
                    <div class="direct-chat-info clearfix">
                        <span class="direct-chat-name pull-left">{{ message.user.name }}</span>
                        <span class="direct-chat-timestamp pull-right">{{ message.created_at }}</span>
                    </div>
                    <!-- /.direct-chat-info -->
                    <img class="direct-chat-img" :src="message.user.avatar" alt="message user image"><!-- /.direct-chat-img -->
                    <div class="direct-chat-text">
                        {{ message.message }}
                      </div>
                    <!-- /.direct-chat-text -->
                </div>
                <!-- /.direct-chat-msg -->
            </div>
            <!--/.direct-chat-messages-->
            <!-- Contacts are loaded here -->
            <div class="direct-chat-contacts">
                <ul class="contacts-list">
                    <li>
                        <a href="#">
                            <img class="contacts-list-img" src="dist/img/user1-128x128.jpg" alt="User Image">

                            <div class="contacts-list-info">
                                <span class="contacts-list-name">
                                  Count Dracula
                                  <small class="contacts-list-date pull-right">2/28/2015</small>
                                </span>
                                <span class="contacts-list-msg">How have you been? I was...</span>
                            </div>
                            <!-- /.contacts-list-info -->
                        </a>
                    </li>
                    <!-- End Contact Item -->
                    <li>
                        <a href="#">
                            <img class="contacts-list-img" src="dist/img/user7-128x128.jpg" alt="User Image">

                            <div class="contacts-list-info">
                                <span class="contacts-list-name">
                                  Sarah Doe
                                  <small class="contacts-list-date pull-right">2/23/2015</small>
                                </span>
                                <span class="contacts-list-msg">I will be waiting for...</span>
                            </div>
                            <!-- /.contacts-list-info -->
                        </a>
                    </li>
                    <!-- End Contact Item -->
                </ul>
                <!-- /.contatcts-list -->
            </div>
            <!-- /.direct-chat-pane -->
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
            <form @submit.prevent="sendMessage">
                <div class="input-group">
                    <input @input="typingWords" v-model="content" type="text" name="message" placeholder="Type Message ..." class="form-control">
                    <span class="input-group-btn">
                            <button type="submit" class="btn btn-warning btn-flat">Send</button>
                          </span>
                </div>
            </form>
            <div id="typingBox" v-show="isTyping">{{ typingUserName }} 正在输入...</div>
        </div>
        <!-- /.box-footer-->
    </div>
</template>

<script>
    export default {
        mounted() {
            $('.contacts-list').slimScroll();
            this.getMessages();
        },
        watch: {//实现vue滚动条总是在最底部
            messages() {
//                console.log("messages change");
                this.$nextTick(() => {
//                    var container = $("#chatContainer");
//                    console.log(container);
//                    $("#chatContainer").scrollTop = $("#chatContainer").scrollHeight;
                    $('#chatContainer').slimScroll({
                        start: 'bottom'
                    });
                });
            }
        },
        props:['user'],
        data() {
            return {
                messages:[],
                message:{},
                content:'',
                Luser:JSON.parse(this.user),
                isTyping:false,
                typingUserName:'',
                lastTypingTime:'',
                typingTimer:'',
                timeDiff:'',
                TYPING_TIMER_LENGTH:500
            }
        },
        sockets:{
            chatroom(data) {
                let msg = JSON.parse(data);
                console.log(msg.data);
                let message = msg.data.message;
                let user = msg.data.user;
                this.message = {created_at:message.created_at,message:message.message,user_id:message.user_id,user:user};
                this.messages.push(this.message);
            },
            sockTyping(data) {
                this.typingUserName = data;
                this.isTyping = true;
            },
            sockStopTyping() {
                this.isTyping = false;
            }
        },
        methods:{
            typingWords() {
                this.$socket.emit('typing',this.Luser.name);
                this.lastTypingTime = (new Date()).getTime();
                console.log(this.lastTypingTime);
                setTimeout(function () {
                    this.typingTimer = (new Date()).getTime();
                    console.log(this.typingTimer);
                    this.timeDiff = this.typingTimer - this.lastTypingTime;
                    console.log(this.timeDiff);
                    if (this.timeDiff >= this.TYPING_TIMER_LENGTH) {
                        this.$socket.emit('stopTyping');
                    }
                }.bind(this),this.TYPING_TIMER_LENGTH)

            },
            sendMessage() {
                axios.post('/messages',{message:this.content}).then((resp) => {
//                    console.log(resp.data);
//                    this.message = {created_at:resp.data.created_at,message:resp.data.message,user_id:resp.data.user_id,user:this.Luser};
//                    this.messages.push(this.message);
                    this.content='';
                });
            },
            getMessages() {
                axios.get('/messages').then((resp) => {
//                    console.log(resp.data);
                    this.messages = resp.data;
                })
            }
        }
    }
</script>