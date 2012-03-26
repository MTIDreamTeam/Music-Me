jQuery(document).ready(function($) {
	
	// HTML Objects dynamically inserted
	var upArrow = '<div class="btn tip" data-placement="bottom" rel="popover" data-original-title="Voter !"><i class="icon-arrow-up icon-black"></i></div>';
	var spinner = '<div class="btn disabled"><div class="spinner"><div class="bar1"></div><div class="bar2"></div><div class="bar3"></div><div class="bar4"></div><div class="bar5"></div><div class="bar6"></div><div class="bar7"></div><div class="bar8"></div><div class="bar9"></div><div class="bar10"></div><div class="bar11"></div><div class="bar12"></div></div></div>';
	
	// pre fetched UI elements
	var voteButtons = $('#stream-musics td div.btn:has(i.icon-arrow-up)');
	var playButton = $('#stream-musics td div.btn:has(i.icon-play)');
	
	
	$('.tip').mouseover(function() {
		$(this).tooltip('show');
	});
	
	voteButtons.click(function() {
		var parent = $(this).parent();
		$(this).tooltip('hide');
		$(this).hide();
		parent.html(spinner);
	});
	
	playButton.click(function() {
		alert('Play !');
	});
	
});

function callAjax(data, url)
{
	$.ajax({
		type: "POST",
		url: url,
		data: data,
		datatype: "json",
		contentType: "application/json",
		success: function(data){
			if(data.substr(0, 5) == "redir")
				callAjax("", data.substr(6, data.length));
			else
			{
				$('#block-content').html(data);
				if ($('#reload-header').attr('data-reload-value') == "true")
				reloadHeader();
			}
		}
	});
	return false;
}

$("#search-flux-form").submit(function(){
	var DATA = '{"searchFlux":"' + $("#searchFluxIn").val() + '"}';
	$("#searchFluxIn").val("");
	return callAjax(DATA, "/search/");
});

function reloadHeader()
{
	$.ajax({
		type: "POST",
		url: "/home/header/",
		data: "",
		contentType: "application/json",
		success: function(data){
			$('#block-header').html(data);
		}
	});
	return false;
}