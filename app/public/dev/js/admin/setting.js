

function fetchSetting( url, module, tpl_id, parent_id ) {

    var params = {  module:module, format:'json' };
    $.ajax({
        type: "GET",
        dataType: "json",
        async: true,
        url: url,
        data: params ,
        success: function (res) {

            var source = $('#'+tpl_id).html();
            var template = Handlebars.compile(source);
            var result = template(res.data);

            $('#' + parent_id).html(result);
        },
        error: function (res) {
            notify_error("请求数据错误" + res);
        }
    });
}

function fetchProjectRoles( url,  tpl_id, parent_id ) {

    var params = {   format:'json' };
    $.ajax({
        type: "GET",
        dataType: "json",
        async: true,
        url: url,
        data: params ,
        success: function (res) {

            var source = $('#'+tpl_id).html();
            var template = Handlebars.compile(source);
            var result = template(res.data);

            $('#' + parent_id).html(result);
        },
        error: function (res) {
            notify_error("请求数据错误" + res);
        }
    });
}


function projectRolesAdd(  ) {

    var method = 'post';
    var url = '/admin/system/project_role_add';
    var params = $('#form_add').serialize();
    $.ajax({
        type: method,
        dataType: "json",
        async: true,
        url: url,
        data: params ,
        success: function (resp) {
            notify_success( resp.msg );
            if( resp.ret == 200 ){
                window.location.reload();
            }
        },
        error: function (res) {
            notify_error("请求数据错误" + res);
        }
    });
}

function projectRolesDelete( id ) {

    var method = 'GET';
    var url = '/admin/system/project_role_delete/'+id;
    $.ajax({
        type: method,
        dataType: "json",
        url: url,
        success: function (resp) {
            notify_success( resp.msg );
            if( resp.ret == 200 ){
                window.location.reload();
            }
        },
        error: function (res) {
            notify_error("请求数据错误" + res);
        }
    });
}

function fetchPermissionGlobal( url,  tpl_id, parent_id ) {

    var params = {   format:'json' };
    $.ajax({
        type: "GET",
        dataType: "json",
        async: true,
        url: url,
        data: params ,
        success: function (resp) {
            if(resp.data.groups.length){
                var source = $('#'+tpl_id).html();
                var template = Handlebars.compile(source);
                var result = template(resp.data);
                $('#' + parent_id).html(result);

                var select_perm_tpl = $('#select_perm_tpl').html();
                template = Handlebars.compile(select_perm_tpl);
                result = template(resp.data);
                $('#select_perm').html(result);

                var select_group_tpl = $('#select_group_tpl').html();
                template = Handlebars.compile(select_group_tpl);
                result = template(resp.data);
                $('#select_group').html(result);
            }else{
                var emptyHtml = defineStatusHtml({
                    message : '暂无数据',
                    type: 'error',
                    handleHtml: '',
                    wrap: '#render'
                })
            }
        },
        error: function (res) {
            notify_error("请求数据错误" + res);
        }
    });
}


function permissionGlobalAdd(  ) {

    var method = 'post';
    var url = '/admin/system/global_permission_group_add';
    var params = $('#form_add').serialize();
    $.ajax({
        type: method,
        dataType: "json",
        async: true,
        url: url,
        data: params ,
        success: function (resp) {
            notify_success( resp.msg );
            if( resp.ret == 200 ){
                window.location.reload();
            }
        },
        error: function (res) {
            notify_error("请求数据错误" + res);
        }
    });
}

function permissionGlobalDelete( id ) {

    if  (!window.confirm('Are you sure delete this item?')) {
        return false;
    }

    var method = 'GET';
    var url = '/admin/system/global_permission_group_delete/?id='+id;
    $.ajax({
        type: method,
        dataType: "json",
        url: url,
        success: function (resp) {
            notify_success( resp.msg );
            if( resp.ret == 200 ){
                window.location.reload();
            }
        },
        error: function (res) {
            notify_error("请求数据错误" + res);
        }
    });
}


$(function() {

    if("undefined" != typeof Handlebars.registerHelper){
        Handlebars.registerHelper('if_eq', function(v1, v2, opts) {
            if(v1 == v2)
                return opts.fn(this);
            else
                return opts.inverse(this);
        });
    }
    if("undefined" != typeof $('.colorpicker-component').colorpicker){
        $('.colorpicker-component').colorpicker({ /*options...*/ });
    }

    $(".btn-save").click(function(){

        var method = 'post';
        var url = '';

        method =  $(this).closest('form').attr('method') ;
        url =  $(this).closest('form').attr('action') ;
        var params = $(this).closest('form').serialize();
        $.ajax({
            type: method,
            dataType: "json",
            async: true,
            url: url,
            data: params ,
            success: function (res) {
                notify_success(res.msg );
            },
            error: function (res) {
                notify_error("请求数据错误" + res);
            }
        });

    });



    $(".btn-remove").click(function(){

        var method = 'post';
        var url = '';

        method =  $(this).closest('form').attr('method') ;
        url =  $(this).closest('form').attr('action') ;
        var params = $(this).closest('form').serialize();
        $.ajax({
            type: method,
            dataType: "json",
            async: true,
            url: url,
            data: params ,
            success: function (resp) {
                notify_success(resp.msg );
                if( resp.ret == 200 ){
                    window.location.reload();
                }
            },
            error: function (resp) {
                notify_error("请求数据错误" + resp);
            }
        });

    });

});

