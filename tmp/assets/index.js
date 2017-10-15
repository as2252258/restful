
$(document).ready(function () {

    var pid = 1;
    var price_list = '';
    var is_using_coupon = 0;
    var ed_num = 0;//额外域名
    var coupon_code = '';


    var original_pricing = '{"1":[],"2":{"1":439,"2":809,"3":1159},"3":{"1":1299,"2":2029,"3":2999,"m_1":250,"m_2":480,"m_3":550},"4":{"1":1999,"2":3998,"3":5997},"5":[]}';

    var pricing = jQuery.parseJSON(original_pricing);
// alert(myArray[1][1]);
    // $("#screen").click(function () {
    //   $(this).hide();
    // });

    // $(window).scroll(function () {
    //   var top=$(window).scrollTop();
    //   $("#screen").css("top",top);
    //   $("#ssl_confirm").css("top",top+40);
    // });


    $("a[data-btn='buy']").click(function () {


        pid = $(this).attr("data-pid");
        type = $(this).attr("data-type");


        if (type == "multi-domain") {
            $("p[data='multi-domain']").show();
            $("div[name='n_box']").show();
            var box_height = "360px";
            $("b[name='n']").html(3);
        } else {
            $("p[data='multi-domain']").hide();
            $("div[name='n_box']").hide();
            var box_height = "290px";
        }

        _czc.push(['_trackEvent', '用户', '首页', '购买', type, 'product_list']);

//formulation
        /*

        多年价格+多年额外域名价格*数量
        */

        if (pid == 1) {
            $("#price_").html(0);
        } else {
            $("#price_").html(pricing[pid][1]);
            $("#jd_card").show();
        }
        $("#product_name_").html($(this).attr("data-product-name"));
        if (pid == 1) {
            $("#year_").hide();
            $("#period").hide();
            $("#month_").html($(this).attr("data-month") + "月").css("margin-left", '50px');
            $("div[data='cp']").hide();
        } else {
            $("#period").show();
            $("#month_").html('&nbsp;').css("margin-left", '33px');
            $("#year_").show();
            $("div[data='cp']").show();
            // $("div[date='cp]").find("strong")
        }
        var ii = layer.load('请稍等...');
        $.post("/order/order_check", {pid: pid}, function (result) {
            var data = jQuery.parseJSON(result);
            layer.close(ii);
            if (data.success == '-1') {
                layer.alert("您已经有该类证书，请直接使用", 8, "提醒"); //风格一
                return false;
            }
            if (data.success == '-2') {

                layer.msg('请先登录后再尝试! 么么哒', 2, 7); //风格一
                $("#btn_login").click();
                return false;
            }
            if (data.success == '-5') {
                layer.msg(data.msg + generate_url(data.url), 5, 8); //风格一
                return false;
            }


            var iii = $.layer({
                type: 1,
                title: false,
                area: ['370px', box_height],
                shade: [0],
                // bgcolor: '#fff',
                // border: [5, 0.2, '#000'],
                shadeClose: true,
                shade: [0.5, '#000'],
                page: {
                    html: '<div class="buy_box">' + $(".buy_box").html() + '</div>'
                }, success: function () {
                    layer.shift('top'); //左边动画弹出
                }
            });
            $("a[id='cancel_coupon']").click(function () {
                /*取消优惠码*/
                is_using_coupon = 0;
                coupon_code = '';
                pricing = jQuery.parseJSON(original_pricing);
                ;
                $("select").change();
                layer.msg('优惠券取消成功', 1, 1);
                $("div[id='coupon_show']").hide();
                $("div[id='coupon_show']").prev().prev().show();
                // alert(1)
            });

            ui_process_used_coupon($(".buy_box").find("button"));

            $("select[name='multi-domain']").eq(1).change(function () {
                $("select[name='period']").eq(1).change();

            });
            $("select[name='period']").eq(1).change(function () {

                // var __price=$(this).val()-1;
                // $(this).parent().next().find("b").html( price_list[__price]); //old
// 计算价格并显示
                var temp = $(this).val();
                if (type == "multi-domain") {
                    var extra_domain = $(this).parent().prev().find('select').val();

                    $("b[name='n']").html(Math.floor(extra_domain) + 3);
                    echo_price = pricing[pid]["m_" + temp] * extra_domain + pricing[pid][temp];
                } else {
                    echo_price = pricing[pid][temp];
                }
                $(this).parent().next().find("b").html(echo_price);

            });
            $("button[id='use_coupon']").click(function () {
                // 获取优惠码
                var code = $(this).prev().val();
                var obj = $(this);
                coupon_code = code;
                $.post("/coupon/getPricing", {code: code}, function (result) {


                    var my_result = jQuery.parseJSON(result);
                    if (my_result.success == 1) {

                        layer.msg('优惠券使用成功', 1, 1);
                        pricing = my_result.pricing;
                        is_using_coupon = 1;
                        ui_process_used_coupon(obj);
                        $("select").change();


                    } else {

                        layer.msg(my_result.msg, 2, 7); //风格一

                        // alert(my_result.msg);
                    }

                });


            });


            function ui_process_used_coupon(obj) {
                if (is_using_coupon == 1) {
                    obj.parent().prev().hide();
                    obj.parent().hide();
                    obj.parent().next().show();
                    obj.parent().next().find("strong").html(coupon_code);
                    // $("select").change();
                }
            }

            $("a[id='buy_submit']").click(function () {

                period = $("select[name='period']").eq(1).val();
                ed_num = $("select[name='multi-domain']").eq(1).val();
                // period=$(this).prev().prev().prev().prev().find("select").val();

                // period=$("#period1").val();
                // alert($("#period1").find("option:selected").text());
                // return false;
                layer.close(iii);
                var ii = layer.load('订单提交中..');
                $.post("/order/new_order", {
                    pid: pid,
                    period: period,
                    extra_domain: ed_num,
                    coupon_code: coupon_code
                }, function (result) {
                    var data = jQuery.parseJSON(result);
                    oid = data.oid
                    if (data.is_paid == 0) {

                        layer.msg('订单提交成功,正在进入付款页面', 4, 1);
                        // return false;
                        setInterval(go_pay, 2000);

                        function go_pay() {
                            window.location.href = "/order/info?id=" + oid;
                        }

                        // return false;
                    } else {

                        layer.msg('订单提交成功,正在跳转到CSR提交页面', 4, 1);
                        setInterval(go_csr, 3000);

                        function go_csr() {
                            window.location.href = "/order/csr2?id=" + oid;
                        }
                    }
                });
            });
        });//post


    });//a


    $("a[func='more']").mouseover(function () {
        _t = $(this);
        $(this).attr("href", "#");
        $.get("/Support/baike", {kw: $(this).attr("kw")}, function (result) {
            layer.tips('<h5 style="font-size:13px;border-bottom:1px solid #fff;">小百科</h5>' + result, _t, {
                style: ['background-color:gray; color:#fff', 'gray'],
                guide: 2,
                maxWidth: 205,
                time: 3,
                closeBtn: [0, false]
            });
        });
        return false;


    });

    function go_index() {
        window.location.href = "/";
    }


    $("#logout").click(function () {
        $.post("/user/logout?", {}, function (result) {
            layer.msg(result.info, 2, 1);
            setInterval(go_index, 2000);
        });
        return false;
    });
    $("form[name='LoginForm']").submit(function (e) {
        /*
      登陆网站
        */
        var u = $("input[name='LoginForm[name]']").val();
        var p = $("input[name='LoginForm[password]']").val();
        var c = $("input[name='LoginForm[code]']").val();
        if (u.length < 3 || p.length < 6) {
            layer.msg("请输入用户名和密码", 1, 8); //fail
            return false;
        }
        $.post("/user/login_ajax?" + RndNum(6), {u: u, p: p, c: c}, function (result) {
            if (result.status == 0) {
                if (result.info.indexOf("验证码") != -1) {
                    $("#_code").show();
                }
                layer.msg(result.info, 2, 8); //fail
                $("#_code").show();
                $("#img_code").click();
                return false;
            }
            if (result.status == 1) {

                layer.msg(result.info, 2, 1); //suc
                setInterval(go_index, 2000);
                return false;
            }
        });
        return false;
    });


    $("#btn_service").click(function () {
        // alert(1);
        if ($('#service').is(":hidden")) {
            $('#service').show(200);
            $('#service').mouseleave(function () {
                $(this).hide(200);
            });
        } else {
            $('#service').hide(200);
        }
        return false;
    });


    $("#btn_login").click(function () {
        _czc.push(["_trackEvent", '用户', '登陆', '登陆', '0', 'btn_login']);
        // alert(1);
        if ($('.popup-layer').is(":hidden")) {
            $('.popup-layer').show(200);

        } else {
            $('.popup-layer').hide(200);
        }
        return false;
    });
    $("#user_btn").click(function () {
        // alert(1);
        if ($('#popup-usr-info').is(":hidden")) {
            $('#popup-usr-info').show(200);
        } else {
            $('#popup-usr-info').hide(200);
        }
        return false;
    });


});


function showCodeArea(a) {

    a.next().show();
}

function generate_url(url) {
    if (url !== '') {
        return '<a href="' + url + '">点击进入»</a>';
    } else {
        return '';
    }
}

function RndNum(n) {
    var rnd = "";
    for (var i = 0; i < n; i++)
        rnd += Math.floor(Math.random() * 10);
    return rnd;
}

function showDetail() {
    $("#sslDetail").css("height", "780px");//slideDown();
    $("html,body").animate({scrollTop: $("#sslDetail").offset().top - 90}, 1000);
    $("#A_show_detail").fadeOut();
}

function hideBtn() {
    $("#hideBtn").hide();
    $("#sslDetail").hide();
    $("html,body").animate({scrollTop: 0}, 1000);

}


var refer = document.referrer;
var sosuo = refer.split(".")[1];
var grep = null;
var str = null;
var keyword = null;
KW_str = "verisign,VeriSign,Symantec";
var KW_array = new Array();
KW_array = KW_str.split(",");


switch (sosuo) {
    case "baidu":
        grep = /wd\=.*\&/i;
        str = refer.match(grep)
        keyword = str.toString().split("=")[1].split("&")[0];
        keyword = decodeURIComponent(keyword);

        for (i = 0; i < KW_array.length; i++) {
            if (keyword.indexOf(KW_array[i]) >= 0) {
                window.location.href = "http://www.shuziqianming.com/?pyssl";
            }
        }


        break;

    case "google":
        grep = /&q\=.*\&/i;
        str = refer.match(grep)
        keyword = str.toString().split("&")[1].split("=")[1];
        // alert(keyword)
        // alert(decodeURIComponent(keyword))
        break;
}
