
function updateStreamInfo()
{
  if ( document.getElementById("streaminfo") )
    openInContents( "streaminfo", "stream.php", "get=info" );
  setTimeout("updateStreamInfo()",10);
}

updateStreamInfo();
