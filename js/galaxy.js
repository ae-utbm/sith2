var galaxy=new Array();

var galaxy_see_top_x = 1000;
var galaxy_see_top_y = 1000;

var galaxy_x = 0;
var galaxy_y = 0;

function galaxy_get_contents(obj,x,y)
{
  //obj.innerHTML="<img src=\"galaxy.php?action=area_image&x="+(x*500)+"&y="+(y*500)+"\" width=\"500\" height=\"500\" alt=\"\" />";
  //;
  //area_html
  obj.innerHTML="";
  openInContents( obj.getAttribute("id"), "galaxy.php", "action=area_html&x="+(x*500)+"&y="+(y*500))
}

function galaxy_placeall()
{
  for(i=0;i<16;i++)
  {
    galaxy[i].style.left=((i%4)*500)-galaxy_see_top_x; 
    galaxy[i].style.top=(Math.floor(i/4)*500)-galaxy_see_top_y; 
  }
}

function galaxy_rotate ( dx, dy )
{
  galaxy_ox=galaxy_x;
  galaxy_oy=galaxy_y;
  
  galaxy_x+=dx;
  galaxy_y+=dy;
  galaxy_see_top_x-=(dx*500);
  galaxy_see_top_y-=(dy*500);
  
  var old_galaxy = galaxy;
  galaxy=new Array();
  i=0;
  for(y=0;y<4;y++)
  {
    for(x=0;x<4;x++)
    {
      ox = (x + dx) % 4;
      oy = (y + dy) % 4;
      if ( ox < 0 ) ox += 4;
      if ( oy < 0 ) oy += 4;
      galaxy[i]=old_galaxy[ox+(oy*4)]; 
      
      //galaxy[i].innerHTML="("+(x+galaxy_x)+","+(y+galaxy_y)+")";
      
      if ( galaxy_ox+ox != x+galaxy_x || galaxy_oy+oy != y+galaxy_y )
        galaxy_get_contents(galaxy[i],x+galaxy_x,y+galaxy_y);
      
      galaxy[i].style.top=(y*500)-galaxy_see_top_y; 
      galaxy[i].style.left=(x*500)-galaxy_see_top_x; 
      i++;
    }
  }
  galaxy_placeall();
}

var galaxy_drag=false;
var galaxy_sx=0,galaxy_sy=0;

var ie=document.all;var nn6=document.getElementById&&!document.all;

function galaxy_mousemove(e) {
  if ( galaxy_drag )
  {
    y = nn6 ? e.clientY : event.clientY;
    x = nn6 ? e.clientX : event.clientX;
    
    galaxy_see_top_x -= x-galaxy_sx;
    galaxy_see_top_y -= y-galaxy_sy;
    
    galaxy_sx=x;
    galaxy_sy=y;
    
    dx=0;
    dy=0;
    
    if ( galaxy_see_top_x > 1500 )
      dx = 1;
      
    if ( galaxy_see_top_y > 1500 )
      dy = 1;
      
    if ( galaxy_see_top_x < 500 )
      dx = -1;
      
    if ( galaxy_see_top_y < 500 )
      dy = -1;      
      
    if(dx!=0||dy!=0)
      galaxy_rotate(dx,dy);
    
    galaxy_placeall();
  }
  return false;}

function galaxy_mousedown(e) {
  galaxy_drag = true;
  
  galaxy_sy = nn6 ? e.clientY : event.clientY;
  galaxy_sx = nn6 ? e.clientX : event.clientX;
  
  return true;}

function galaxy_mouseup(e) {
  galaxy_drag = false;
    return true;}

function no_mousedown(e) {
  return false;}

function no_mouseup(e) {
  return false;}

function init_galaxy()
{

  for(i=0;i<16;i++)
  {
    galaxy[i]= document.getElementById("square"+i);
    galaxy_get_contents(galaxy[i],(i%4),Math.floor(i/4));
    galaxy[i].style.left=((i%4)*500)-galaxy_see_top_x; 
    galaxy[i].style.top=(Math.floor(i/4)*500)-galaxy_see_top_y; 
    galaxy[i].onmousedown = no_mousedown;
    galaxy[i].onmouseup = no_mouseup;
  }
  
  document.getElementById("viewer").onmouseup = galaxy_mouseup;
  document.getElementById("viewer").onmousedown = galaxy_mousedown;
  document.getElementById("viewer").onmousemove = galaxy_mousemove;
}
