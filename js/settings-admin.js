$(document).ready(function() {
	$( '#ownerfixer .actions' ).on('click', '.save',
		function() {
			var permission = $( '#of-permission' ).val();
			var quota_uri = $( '#of-quota_uri' ).val();
			var protocol = $( '#of-quota_protocol' ).val();
			OC.msg.startSaving('#of-msg');
			$.post(
				OC.generateUrl('apps/owner_fixer/settings/savepreferences'),
				{ quotaServiceUri: quota_uri, permissionUmask: permission }
			).done(function(result) {
				OC.msg.finishedSuccess('#of-msg', result.message);
			}).fail(function(result) {
				OC.msg.finishedError('#of-msg', result.message);
			});
		}
	);
});
