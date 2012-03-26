// HTML Objects dynamically inserted
var upArrow = '<div class="btn tip" data-placement="bottom" rel="popover" data-original-title="Voter !"><i class="icon-arrow-up icon-black"></i></div>';
var spinner = '<div class="btn disabled"><div class="spinner"><div class="bar1"></div><div class="bar2"></div><div class="bar3"></div><div class="bar4"></div><div class="bar5"></div><div class="bar6"></div><div class="bar7"></div><div class="bar8"></div><div class="bar9"></div><div class="bar10"></div><div class="bar11"></div><div class="bar12"></div></div></div>';
var validated = '<div class="btn btn-success disabled"><i class="icon-ok icon-white"></i></div>';
var error = '<div class="btn btn-danger disabled"><i class="icon-remove icon-white"></i></div>';

jQuery(document).ready(function($) {
	
	var errorAlert = $('#error-alert');
	var infoAlert = $('#info-alert');
	var warningAlert = $('#warning-alert');
	var successAlert = $('#success-alert');
	
	var closeAlertButton = $('div.alert > a.close').click(function() {
		$(this).parent().fadeOut();
	});

	function showAlert(title, message, id, alert)
	{
		console.log('plop');
		alert.fadeOut();
		alert.find('h4').text(title);
		alert.find('span').text(message);
		alert.fadeIn();
		setTimeout(function() {
			alert.fadeOut();
		}, 3000);
	}

	function showError(title, message)
	{
		showAlert(title, message, 'error', errorAlert);
	}

	function showWarning(title, message)
	{
		showAlert(title, message, 'warning', warningAlert);
	}

	function showInfo(title, message)
	{
		showAlert(title, message, 'info', infoAlert);
	}

	function showSuccess(title, message)
	{
		showAlert(title, message, 'success', successAlert);
	}

	function handleVoteError(data, cell, button)
	{
		cell.find('.spinner').parent().remove();
		cell.append(error);
		if (data && data['alert'] && data['alert']['title'] && data['alert']['message'])
		{
			showError(data['alert']['title'], data['alert']['message']);
			setTimeout(function() {
				$('td >	 div.btn-danger').remove();
				button.fadeIn();
			}, 2000);
		}
	}

	function handleVoteSuccess(data, cell)
	{
		cell.find('.spinner').parent().remove();
		cell.append(validated);
		showSuccess(data['alert']['title'], data['alert']['message']);
	}

	// pre fetched UI elements
	var voteButtons = $('#stream-musics td div.btn:has(i.icon-arrow-up), #search-zik td div.btn:has(i.icon-plus)');
	var playButton = $('#stream-musics td div.btn:has(i.icon-play)');
	
	
	$('.tip').mouseover(function() {
		$(this).tooltip('show');
	});
	
	voteButtons.click(function() {
		
		var button = $(this);
		var cell = button.parent();
		button.tooltip('hide');
		button.hide();
		cell.append(spinner);
		
		var voteContent = {};
		voteContent['stream'] = parseInt($('#stream-id').html());
		voteContent['music'] = parseInt(cell.siblings('.music-id').html());
		console.log(voteContent['music']);
		
		$.ajax({
			type: 'POST',
			url: '/stream/vote/',
			dataType: 'json',
			data: $.toJSON(voteContent),
			success: function(data) {
				if (button.closest('#search-zik').length)
				{
					console.log('boom');
					window.location.replace( '/stream/'+voteContent['stream'] );
					return;
				}
				else
				console.log('fuck');
				if (data['alert'] && data['alert']['type'] == 'success')
					handleVoteSuccess(data, cell);
				else if (data['alert'] && data['alert']['type'] == 'error')
					handleVoteError(data, cell, button);
			},
			error: function(data) {
				console.log('error');
				handleVoteError(data, cell, button);
			}
		});
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

$("#search-zik-form").submit(function(){
	var DATA = '{"searchZik":"' + $("#searchZikId").val() + '"}';
	$("#searchZikId").val("");
	return callAjax(DATA, "/searchzik/");
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
