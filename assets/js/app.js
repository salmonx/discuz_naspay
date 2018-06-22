var jq = $.noConflict();
jq(document).ready(function(){
	jq("#number").keyup(function(){
		var tpnumber = jq(this).val();
		tpnumber = parseInt(tpnumber);
		if (isNaN(tpnumber)) {
			tpnumber = jq("#min").val();
		}
		jq(this).val(tpnumber);
		changermb();
	});
	jq(".tpay_dialog_alert .tpay_mask").click(function(){
		jq(this).parent().hide();
		jq(".tpay_dialog_alert").find(".tpay_dialog_bd").html('');
	});
	jq("#tp_queding").click(function(){
		jq(".tpay_dialog_alert").hide();
		jq(".tpay_dialog_alert").find(".tpay_dialog_bd").html('');
	});
});

jq(function(){ jq('input, textarea').placeholder(); });

function changermb(){
	var tpnumber = jq("#number").val();
	var ratio = parseInt(jq("#ratio").val());
	var tprmb = tpnumber / ratio; 
	tprmb = tprmb.toFixed(3)
	jq("#tprmb").text(lang06+tprmb);
}

function checkepay(){
	var min = parseInt(jq("#min").val());
	var max = parseInt(jq("#max").val());
	var tpinput = parseInt(jq("#number").val());

	if (isNaN(tpinput)) {
		tpshow(lang32+min);
		return false;
	}
	
	if(tpinput < min){
		tpshow(lang32+min);
		return false;
	}
	
	if(tpinput > max){
		tpshow(lang33+max);
		return false;
	}
	
	tpshow("点击[立即充值]后，将转向支付页面，支付成功后请点击[我已支付]完成充值！");
	jq("#thinfell_pay_form").submit();
}

function tpshow(txt){
	jq(".tpay_dialog_alert").find(".tpay_dialog_bd").html(txt);
	jq(".tpay_dialog_alert").show();
}
function credit_submit(obj){
	ajaxpost(obj.id, 'return_thinfell_pay_form');
	return false;
}