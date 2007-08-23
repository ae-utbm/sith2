
function updateStreamInfo()
{
  if ( document.getElementById("streaminfo") )
    openInContents( "streaminfo", "stream.php", "get=info" );
  setTimeout("updateStreamInfo()",6);
}

updateStreamInfo();
