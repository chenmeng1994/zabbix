<script type="text/x-jquery-tmpl" id="user_group_row_tpl">
<?= (new CRow([
	new CCol([
		(new CTextBox('userGroups[#{usrgrpid}][usrgrpid]', '#{usrgrpid}'))->setAttribute('type', 'hidden'),
		(new CSpan('#{name}')),
	]),
	new CCol(
		(new CTag('ul', false, [
			new CTag('li', false, [
				(new CInput('radio', 'userGroups[#{usrgrpid}][permission]', PERM_READ))
				->setId('user_group_#{usrgrpid}_permission_'.PERM_READ),
				(new CTag('label', false, _('Read-only')))
				->setAttribute('for', 'user_group_#{usrgrpid}_permission_'.PERM_READ)
			]),
			new CTag('li', false, [
				(new CInput('radio', 'userGroups[#{usrgrpid}][permission]', PERM_READ_WRITE))
				->setId('user_group_#{usrgrpid}_permission_'.PERM_READ_WRITE),
				(new CTag('label', false, _('Read-write')))
				->setAttribute('for', 'user_group_#{usrgrpid}_permission_'.PERM_READ_WRITE)
			])
		]))->addClass(ZBX_STYLE_RADIO_SEGMENTED)
	),
	(new CCol(
		(new CButton('remove', _('Remove')))
		->addClass(ZBX_STYLE_BTN_LINK)
		->onClick('removeUserGroupShares("#{usrgrpid}");')
	))->addClass(ZBX_STYLE_NOWRAP)
]))
->setId('user_group_shares_#{usrgrpid}')
->toString()
	?>
</script>

<script type="text/x-jquery-tmpl" id="user_row_tpl">
<?= (new CRow([
	new CCol([
		(new CTextBox('users[#{id}][userid]', '#{id}'))->setAttribute('type', 'hidden'),
		(new CSpan('#{name}')),
	]),
	new CCol(
		(new CTag('ul', false, [
			new CTag('li', false, [
				(new CInput('radio', 'users[#{id}][permission]', PERM_READ))
				->setId('user_#{id}_permission_'.PERM_READ),
				(new CTag('label', false, _('Read-only')))
				->setAttribute('for', 'user_#{id}_permission_'.PERM_READ)
			]),
			new CTag('li', false, [
				(new CInput('radio', 'users[#{id}][permission]', PERM_READ_WRITE))
				->setId('user_#{id}_permission_'.PERM_READ_WRITE),
				(new CTag('label', false, _('Read-write')))
				->setAttribute('for', 'user_#{id}_permission_'.PERM_READ_WRITE)
			])
		]))->addClass(ZBX_STYLE_RADIO_SEGMENTED)
	),
	(new CCol(
		(new CButton('remove', _('Remove')))
		->addClass(ZBX_STYLE_BTN_LINK)
		->onClick('removeUserShares("#{id}");')
	))->addClass(ZBX_STYLE_NOWRAP)
]))
->setId('user_shares_#{id}')
->toString()
	?>
</script>

<script type="text/javascript">

	jQuery(document).ready(function() {
		var form = jQuery('form[name="dashboard_sharing_form"]');

		// overwrite submit action to AJAX call
		form.submit(function(event) {
			var me = this;
			jQuery.ajax({
				data: jQuery(me).serialize(), // get the form data
				type: jQuery(me).attr('method'),
				url: jQuery(me).attr('action')
			});
			event.preventDefault(); // cancel original event to prevent form submitting
		});
	});

// fill the form with actual data
jQuery.fn.fillForm = function(data) {
	addPopupValues({'object': 'private', 'values': [data.private] });

	removeUserShares();
	addPopupValues({'object': 'userid', 'values': data.users });

	removeUserGroupShares();
	addPopupValues({'object': 'usrgrpid', 'values': data.user_groups });
};

/**
 * @see init.js add.popup event
 */
function addPopupValues(list) {
	var i,
		value,
		tpl,
		container;

	for (i = 0; i < list.values.length; i++) {
		if (empty(list.values[i])) {
			continue;
		}

		value = list.values[i];
		if (typeof value.permission === 'undefined') {
			if (jQuery('input[name=private]:checked').val() == <?= PRIVATE_SHARING ?>) {
				value.permission = <?= PERM_READ ?>;
			}
		else {
				value.permission = <?= PERM_READ_WRITE ?>;
			}
		}

		switch (list.object) {
			case 'private':
				jQuery('input[name=private][value=' + value + ']').prop('checked', 'checked');
				break;
			case 'usrgrpid':
				if (jQuery('#user_group_shares_' + value.usrgrpid).length) {
					continue;
				}

				tpl = new Template(jQuery('#user_group_row_tpl').html());

				container = jQuery('#user_group_list_footer');
				container.before(tpl.evaluate(value));

				jQuery('#user_group_' + value.usrgrpid + '_permission_' + value.permission + '')
					.prop('checked', true);
				break;

			case 'userid':
				if (jQuery('#user_shares_' + value.id).length) {
					continue;
				}

				tpl = new Template(jQuery('#user_row_tpl').html());

				container = jQuery('#user_list_footer');
				container.before(tpl.evaluate(value));

				jQuery('#user_' + value.id + '_permission_' + value.permission + '')
					.prop('checked', true);
				break;
		}
	}
}

function removeUserGroupShares(usrgrpid) {
	if (typeof usrgrpid === 'undefined') {
		// clear all data
		jQuery("[id^='user_group_shares']").remove();
	} else {
		jQuery('#user_group_shares_' + usrgrpid).remove();
	}
}

function removeUserShares(userid) {
	if (typeof userid === 'undefined') {
		jQuery("[id^='user_shares']").remove();
	} else {
		jQuery('#user_shares_' + userid).remove();
	}
}
</script>
