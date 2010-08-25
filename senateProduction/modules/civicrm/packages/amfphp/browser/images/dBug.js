function dBug_toggleRow(source) {
	target=(document.all) ? source.parentElement.cells[1] : source.parentNode.lastChild
	dBug_toggleTarget(target,dBug_toggleSource(source));
}

function dBug_toggleSource(source) {
	if (source.style.fontWeight=='bold') {
		source.style.fontWeight='normal';
		source.title='click to collapse';
		return 'open';
	} else {
		source.style.fontWeight='bold';
		source.title='click to expand';
		return 'closed';
	}
}

function dBug_toggleTarget(target,switchToState) {
	if(target.style)
	{
		target.style.display=(switchToState=='open') ? '' : 'none';
	}
}

function dBug_toggleTable(source) {
	var switchToState=dBug_toggleSource(source);
	if(document.all) {
		var table=source.parentElement.parentElement;
		for(var i=1;i<table.rows.length;i++) {
			target=table.rows[i];
			dBug_toggleTarget(target,switchToState);
		}
	}
	else {
		var table=source.parentNode.parentNode;
		for (var i=1;i<table.childNodes.length;i++) {
			target=table.childNodes[i];
			if(target.style) {
				dBug_toggleTarget(target,switchToState);
			}
		}
	}
}