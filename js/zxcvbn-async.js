(function(){
    var a = function(){
        var a,b;
        b = document.createElement("script");
        b.src = "./js/zxcvbn.js";
        b.type = "text/javascript";
        b.async =! 0;
        a = document.getElementsByTagName("script")[0];
        return a.parentNode.insertBefore(b,a)
    };

    if (window.attachEvent != null)
        window.attachEvent("onload",a);
    else
        window.addEventListener("load",a,!1);
}).call(this);


function checkPassword(pwd) {
    var output = document.getElementById('pmeter');

    if (pwd != '') {
        var ret = zxcvbn(pwd);
        switch(ret.score) {
            case 0:
                output.innerHTML = 'SUPER-FAIBLE';
                output.style.color = 'crimson';
                break;
            case 1:
                output.innerHTML = 'FAIBLE';
                output.style.color = 'red'
                    break;
            case 2:
                output.innerHTML = 'MEDIOCRE';
                output.style.color = 'darkorange'
                    break;
            case 3:
                output.innerHTML = 'MOYENNE';
                output.style.color = 'deeppink';
                break;
            case 4:
                output.innerHTML = 'CORRECTE';
                output.style.color = 'green';
                break;
        }
    } else {
        output.innerHTML  = 'Champ vide';
        output.style.color = 'black';
    }
}
