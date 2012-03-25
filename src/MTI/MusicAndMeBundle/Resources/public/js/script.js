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
		showError(data['alert']['title'], data['alert']['message']);
		setTimeout(function() {
			$('td >	 div.btn-danger').remove();
			button.fadeIn();
		}, 2000);
	}

	function handleVoteSuccess(data, cell)
	{
		cell.find('.spinner').parent().remove();
		cell.append(validated);
		showSuccess(data['alert']['title'], data['alert']['message']);
	}

	// pre fetched UI elements
	var voteButtons = $('#stream-musics td div.btn:has(i.icon-arrow-up)');
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
		
		$.ajax({
			type: 'POST',
			url: '/stream/vote/',
			dataType: 'json',
			data: $.toJSON(voteContent),
			success: function(data) {
				if (data['alert'] && data['alert']['type'] == 'success')
					handleVoteSuccess(data, cell);
				else
					handleVoteError(data, cell, button);
			},
			error: function(data) {
				handleVoteError(data, cell, button);
			}
		});
	});
	
	playButton.click(function() {
		alert('Play !');
	});
	
});