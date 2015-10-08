function getCityList(province_id, sel_city_id, callback) {
	var callback = callback || function() {};
	var data = {};
	data.province_id = province_id;
	data.sel_city_id = sel_city_id;
	$.ajax({
		url: ROOT + "/Area/getCityList",
		type: "post",
		data: data,
		dataType: 'html',
		success: function(html) {
			$("#city").html(html);
			callback();
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function getAreaList(pid, sel_id, callback) {
	var callback = callback || function() {};
	var data = {};
	data.pid = pid;
	data.sel_id = sel_id;
	$.ajax({
		url: ROOT + "/Issue/getAreaList",
		type: "post",
		data: data,
		dataType: 'html',
		success: function(html) {
			callback(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function getTargetList(pid, sel_id, callback) {
	var callback = callback || function() {};
	var data = {};
	data.pid = pid;
	data.sel_id = sel_id;
	$.ajax({
		url: ROOT + "/Issue/getTargetList",
		type: "post",
		data: data,
		dataType: 'html',
		success: function(html) {
			callback(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function getMaxSortNum(url, city_id, callback) {
	$.ajax({
		url: url,
		type: "post",
		data: {city_id: city_id},
		dataType: 'json',
		success: function(json) {
			callback(json['sort']);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function getNodeInfo(url, id, callback) {
	$.ajax({
		url: url,
		type: "post",
		data: {id: id},
		dataType: 'json',
		success: function(json) {
			if(!json['id']) {
				alert("参数错误，查询失败");
				return false;
			}
			callback(json);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function checkNodeDeletable(url, id, callback) {
	$.ajax({
		url: url,
		type: "post",
		data: {id: id},
		dataType: 'json',
		success: function(json) {
			callback(json.error);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function deleteNode(url, id, callback) {
	$.ajax({
		url: url,
		type: "post",
		data: {id: id},
		dataType: 'json',
		success: function(json) {
			callback(json.error, json);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function editNode(url, data, callback) {
	$.ajax({
		url: url,
		type: "post",
		data: data,
		dataType: 'json',
		success: function(json) {
			if (json["error"]) {
				alert(json["error"]); return false;
			}
			callback(json["data"]);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}