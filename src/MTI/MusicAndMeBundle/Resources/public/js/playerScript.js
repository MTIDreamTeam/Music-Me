function my_play(url)
{
    var httpRequest = false;

    if (window.XMLHttpRequest) { // Mozilla, Safari,...
        httpRequest = new XMLHttpRequest();
        if (httpRequest.overrideMimeType) {
            httpRequest.overrideMimeType('text/xml');
        }
    }
    else if (window.ActiveXObject) { // IE
        try {
            httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e) {
            try {
                httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch (e) {}
        }
    }

    if (!httpRequest) {
        window.alert('Abandon :( Impossible de créer une instance XMLHTTP');
        return false;
    }
    httpRequest.onreadystatechange = function() { alertContents(httpRequest); };
    httpRequest.open('GET', url, true);
    httpRequest.send(null);
}

function alertContents(httpRequest)
{
    if (httpRequest.readyState == 4)
    {
        if (httpRequest.status == 200)
        {
            $("#jquery_jplayer_1").jPlayer("clearMedia");
            $("#jquery_jplayer_1").jPlayer("setMedia", {
                    mp3:httpRequest.responseText
            });
            $("#jquery_jplayer_1").jPlayer("play");

        } else {
            window.alert('Un problème est survenu avec la requête.');
        }
    }

}