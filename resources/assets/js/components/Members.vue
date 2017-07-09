<template>
    <div class="box box-danger">
        <div class="box-header with-border">
            <h3 class="box-title">Latest Members</h3>

            <div class="box-tools pull-right">
                <span class="label label-danger">8 New Members</span>
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                </button>
            </div>
        </div>
        <!-- /.box-header -->
        <div class="box-body no-padding">
            <ul class="users-list clearfix">
                <li v-for="user in users">
                    <img style="width: 50px;height: 50px" :src="JSON.parse(user).avatar" alt="User Image">
                    <a class="users-list-name" href="#">{{ tohanzi(JSON.parse(user).name) }}</a>
                    <span class="users-list-date">{{ goodTime(JSON.parse(user).loginTime) }}</span>
                </li>
            </ul>
            <!-- /.users-list -->
        </div>
        <!-- /.box-body -->
        <div class="box-footer text-center">
            <a href="javascript:void(0)" class="uppercase">View All Users</a>
        </div>
        <div>{{ users.length }}</div>
        <!-- /.box-footer -->
    </div>
</template>

<script>
    export default {
        props:['users'],
        methods:{
            tohanzi(data){//只对Unicode即含有'\u'的字符转码成汉字
                if(data.indexOf("\\u") == -1) return data;
                data = data.split("\\u");
                let str ='';
                for(let i=0;i<data.length;i++)
                {
                    str+=String.fromCharCode(parseInt(data[i],16).toString(10));
                }
                return str;
            },
            goodTime(str){//美化日期
                let now = new Date().getTime(),
                    oldTime = new Date(str).getTime(),
                    difference = now - oldTime,
                    result='',
                    minute = 1000 * 60,
                    hour = minute * 60,
                    day = hour * 24,
                    halfamonth = day * 15,
                    month = day * 30,
                    year = month * 12,

                    _year = difference/year,
                    _month =difference/month,
                    _week =difference/(7*day),
                    _day =difference/day,
                    _hour =difference/hour,
                    _min =difference/minute;
                if(_year>=1) {result= "" + ~~(_year) + " 年前登录"}
                else if(_month>=1) {result= "" + ~~(_month) + " 个月前登录"}
                else if(_week>=1) {result= "" + ~~(_week) + " 周前登录"}
                else if(_day>=1) {result= "" + ~~(_day) +" 天前登录"}
                else if(_hour>=1) {result= "" + ~~(_hour) +" 个小时前登录"}
                else if(_min>=1) {result= "" + ~~(_min) +" 分钟前登录"}
                else result="刚刚登录";
                return result;
            }
        }
    }
</script>
