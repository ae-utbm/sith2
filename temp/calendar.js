
function findPos(obj)
{
	var curleft = curtop = 0;
	if (obj.offsetParent)
	{
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		while (obj = obj.offsetParent)
		{
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return [curleft,curtop];
}

function opencal(_ref)
{
	var ref = document.getElementById(_ref);
	var elem = document.getElementById('calendar');
	var pos = findPos(ref);
	
	elem.style.display = "block";
	elem.style.left = pos[0] + 20;
	elem.style.top = pos[1];
	
}

function closecal()
{
	var elem = document.getElementById('calendar');
	elem.style.display = 'none';

	return true;
}
