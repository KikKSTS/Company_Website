// fade in on scroll box2
window.addEventListener('scroll', fadeUp);

    function fadeUp(){
      var fadeUps = document.querySelectorAll('.fadeUp');

      for(var i = 0; i < fadeUps.length; i++){

        var windowheight = window.innerHeight;
        var fadeUpbottom = fadeUps[i].getBoundingClientRect().bottom;
        var fadeUppoint = 5;

        if(fadeUpbottom < windowheight - fadeUppoint){
            fadeUps[i].classList.add('active');
        }
        else{
            fadeUps[i].classList.remove('active');
        }
      }
    }
