tinymce.PluginManager.add('mailto', function (editor, url) {

	var openDialog = function () {
		return editor.windowManager.open({
			title: 'Mailto',
			body: [
				{
					type: 'textbox',
					name: 'email',
					label: 'Email'
				},
				{
					type: 'textbox',
					name: 'subject',
					label: 'Subject'
				},
				{
					type: 'textbox',
					name: 'cc',
					label: 'Cc'
				},
				{
					type: 'textbox',
					multiline: true,
					name: 'body',
					label: 'Body',
					minWidth: 350,
					minHeight: 150,
				},
			],
			onsubmit: function (e) {
				// Retrieve submitted values
				var email = (e.data.email) ? e.data.email : '';
				var cc = (e.data.cc) ? e.data.cc : '';
				var body = (e.data.body) ? e.data.body : '';
				var subject = (e.data.subject) ? e.data.subject : '';
				var validate = true;

				// Validate email
				if (email) {
					email = email.replace(/\s+/g, '');
					var email_validation = email.split(',');
					email_validation.forEach(element => {
						if (!/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(element)) {
							editor.notificationManager.open({
								text: 'Please enter a valid email address',
								type: 'error',
								timeout: 3000
							});
							validate = false;
						}
					});
				}

				// Validate cc
				if (cc) {
					cc = cc.replace(/\s+/g, '');
					var cc_validation = cc.split(',');
					cc_validation.forEach(element => {
						if (!/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(element)) {
							editor.notificationManager.open({
								text: 'Please enter a valid email address cc',
								type: 'error',
								timeout: 3000
							});
							validate = false;
						}
					});
				}

				if (validate == true) {
					// Create mailto
					var content = "<a href=\"mailto:" + email + "?subject=" + subject + "&amp;cc=" + cc + "&amp;body=" + escape(body) + "\">" + email + "</a>";
					editor.insertContent(content);
				}
			}
		});
	};

	// Add a button that opens a window
	editor.addButton('mailto', {
		title: 'Mail to',
		text: "M",
		onclick: function () {
			openDialog();
		}
	});

	return {
		getMetadata: function () {
			return {
				name: 'Mailto Plugin',
				url: 'https://ixiam.com',
			};
		}
	};

});
