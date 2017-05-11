$(document).ready(function() {
	$(".my-info").mouseover(function() {
		$(".info-f").show();
	});
	$(".my-info").mouseout(function() {
		$(".info-f").hide();
	});

	//关闭图层
	$(".close").click(function() {
		$(".tc").hide();
		$(".b-tc").hide();
		$(".business-claims").hide();
		$(".info").hide();
		$(".modify-pwd").hide();
	});

	$("#personal").click(function() {
		$(".tc").show();
		$(".modify-pwd").hide();
		$(".info").show();
	});

	$("#mdf-pwd").click(function() {
		$(".tc").show();
		$(".info").hide();
		$(".modify-pwd").show();
	});

	getHeight();

	/**获取页面高度**/
	function getHeight() {
		var zHeight = document.documentElement.scrollHeight;	
		console.log(zHeight);	
		$(".tc").css("height", zHeight);
		$(".b-tc").css("height", zHeight);
	}
});
