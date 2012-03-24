
jQuery(document).ready(function($) {
	
	// HTML Objects dynamically inserted
	var upArrow = '<div class="btn tip" data-placement="bottom" rel="popover" data-original-title="Voter !"><i class="icon-arrow-up icon-black"></i></div>';
	var spinner = '<div class="btn disabled"><div class="spinner"><div class="bar1"></div><div class="bar2"></div><div class="bar3"></div><div class="bar4"></div><div class="bar5"></div><div class="bar6"></div><div class="bar7"></div><div class="bar8"></div><div class="bar9"></div><div class="bar10"></div><div class="bar11"></div><div class="bar12"></div></div></div>';
	var validated = '<div class="btn btn-success disabled"><i class="icon-ok icon-white"></i></div>';
	var error = '<div class="btn btn-danger disabled"><i class="icon-remove icon-white"></i></div>';
	
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
				cell.find('.spinner').parent().remove();
				cell.append(validated);
			},
			error: function(data) {
				cell.find('.spinner').parent().remove();
				cell.append(error);
				setTimeout(function() {
					$('td >	 div.btn-danger').remove();
					button.fadeIn();
				}, 2000);
			}
		});
	});
	
	playButton.click(function() {
		alert('Play !');
	});
	
});