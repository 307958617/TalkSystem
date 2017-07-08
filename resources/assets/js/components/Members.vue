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
                    <a class="users-list-name" href="#">{{ JSON.parse(user).name }}</a>
                    <span class="users-list-date">{{ JSON.parse(user).created_at }}</span>
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
    }
</script>
