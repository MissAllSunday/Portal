
$(function() {
	// Retrieve the user's profile popup.
	$('.accbtn_holder').load($('#accbtn').data('url'), function() {});

	// Show each sub menu. By default its going to be hidden.
	$('.sub_menu_list').hide();

	// On click event to show the submenu.
	$(document).on('click', '.sub_menu_button', function() {
		$el = $('#' + $(this).data('for'));
		$el.toggle('slow');
	});
});
