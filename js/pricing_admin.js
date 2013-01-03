$jq =jQuery.noConflict();

$jq(document).ready(function(){

    $jq("#post-body-content").prepend("<div id='pricing_error' class='error' style='display:none' ></div>");

    $jq('#post').submit(function() {

        if($jq("#post_type").val() =='pricing_packages'){
            return wppt_validate_pricing_packages();
        }else if($jq("#post_type").val() =='pricing_tables'){
            return wppt_validate_pricing_tables();
        }


    });



    $jq("#add_features").click(function(){

        var feature = $jq("#package_feature").val();

        if(feature == ''){

        }else{
            $jq("#package_features_box").append("<li><input type='hidden' value='"+feature+"' name='package_features[]' />"
                +feature+"<a href='javascript:void(0);'>Delete</a></li>");
        }

    });


    $jq("#package_features_box a").click(function(){
        $jq(this).parent().remove();
    });


});

var wppt_validate_pricing_packages = function(){
    var err = 0;
    $jq("#pricing_error").html("");
    $jq("#pricing_error").hide();

    if($jq("#title").val() == ''){
        $jq("#pricing_error").append("<p>Please enter Package Name.</p>");
        err++;
    }
    if($jq("#package_price").val() == ''){
        $jq("#pricing_error").append("<p>Please enter Package Price.</p>");
        err++;
    }
    if($jq("#package_buy_link").val() == ''){
        $jq("#pricing_error").append("<p>Please enter Package Buy Link.</p>");
        err++;
    }

    if(err>0){
        $jq("#publish").removeClass("button-primary-disabled");
        $jq("#ajax-loading").hide();
        $jq("#pricing_error").show();
        return false;
    }else{
        return true;
    }
};

var wppt_validate_pricing_tables = function(){
    var err = 0;
    $jq("#pricing_error").html("");
    $jq("#pricing_error").hide();

    if($jq("#title").val() == ''){
        $jq("#pricing_error").append("<p>Please enter Pricing Table Name.</p>");
        err++;
    }
    if(err>0){
        $jq("#publish").removeClass("button-primary-disabled");
        $jq("#ajax-loading").hide();
        $jq("#pricing_error").show();
        return false;
    }else{
        return true;
    }
};
