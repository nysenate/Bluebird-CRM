/*
 * jQuery plugin: fieldSelection - v0.1.0 - last change: 2006-12-16
 * (c) 2006 Alex Brem <alex@0xab.cd> - http://blog.0xab.cd
 */

(function() {

	var fieldSelection = {

		getSelection: function() {

			var e = this.jquery ? this[0] : this;

			return (

				/* mozilla / dom 3.0 */
				('selectionStart' in e && function() {
					var l = e.selectionEnd - e.selectionStart;
					return { start: e.selectionStart, end: e.selectionEnd, length: l, text: e.value.substr(e.selectionStart, l) };
				}) ||

				/* exploder */
				(document.selection && function() {

					e.focus();

					var r = document.selection.createRange();
					if (r == null) {
						return { start: 0, end: e.value.length, length: 0 }
					}

					var re = e.createTextRange();
					var rc = re.duplicate();
					re.moveToBookmark(r.getBookmark());
					rc.setEndPoint('EndToStart', re);

					return { start: rc.text.length, end: rc.text.length + r.text.length, length: r.text.length, text: r.text };
				}) ||

				/* browser not supported */
				function() {
					return { start: 0, end: e.value.length, length: 0 };
				}

			)();

		},

		replaceSelection: function() {
			var e = this.jquery ? this[0] : this;
			var text = arguments[0] || '';

			return (

				/* mozilla / dom 3.0 */
				('selectionStart' in e && function() {
                    var cursorlength = e.selectionStart + text.length;
                    e.value = e.value.substr(0, e.selectionStart) + text + e.value.substr(e.selectionEnd, e.value.length);
                    
					e.selectionStart = e.selectionEnd = cursorlength;
                    return this;
				}) ||

				/* exploder */
				(document.selection && function() {
				
			
				
				
                    //NYSS 3524 & NYSS 4073
		    // get the current cursor position
                    // really, really, really inefficient way to move variables around, but because of my unfamiliarity
                    // on how to store variables in CCRM, this is the work to be done.
                    // it takes data from insert token
                    var gSPi = cj('#gSP').text();
                    var gEPi = cj('#gEP').text();
                    
                    // set the value
                    e.value = e.value.substr(0, gSPi) + text + e.value.substr( gEPi, e.value.length);
                    //move the focus to correct position, end of inserted token
		    //NYSS 3524
                    var range = e.createTextRange(); 
                    range.move( "character", gEPi ); 
                    range.select();

					//return this;
				}) ||

				/* browser not supported */
				function() {
					e.value += text;
					return this;
				}

			)();

		}

	};

	jQuery.each(fieldSelection, function(i) { jQuery.fn[i] = this; });

})();
