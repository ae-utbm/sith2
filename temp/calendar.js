
function opencal(_ref)
{
	var ref = document.getElementById(_ref);
	var pos = findPos(ref);
		
	var elem = document.createElement('div');
	elem.id = 'calendar';
	elem.className = 'container';
	document.body.appendChild(elem);

	elem.style.display = 'block';
	elem.style.left = pos[0] + 20;
	elem.style.top = pos[1] - 10;
	openInContents('calendar', './little_calendar2.php', 'get_cal'); 
}

function closecal()
{
	var elem = document.getElementById('calendar');
	elem.style.display = 'none';

	return true;
}

function return_val(target_id, value)
{
	target = document.getElementById(target_id);
	target.value = value;
	closecal();
	return true;
}
