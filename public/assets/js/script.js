//Initialisation API + définition du player
// Avec musique

var tag = document.createElement('script');

tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

// Récupère l'id du lien youtube
// var. pas encore au point car on passe par l'id d'une div pour récupérer 
// l'id du lien YT. A voir quand il y aura connexion vers BDD
var keyMusicPlaylist = 0; 

var idHTML = '#videoid' + keyMusicPlaylist;

var lienVersYT = document.querySelector('#videoid0').dataset.id;

//Player avec les fonctionnalités

var player,
    time_update_interval = 0;

function onYouTubeIframeAPIReady() {
    player = new YT.Player('video-placeholder', {
        width: 600,
        height: 400,
        videoId: lienVersYT,
        playerVars: {
        },
        events: {
            onReady: initialize,
            onStateChange: onPlayerStateChange
        }
    });

    //--------------- DEBUT JS MEDIAPLAYER ------------------ //

    function initialize() {

        // Update the controls on load
        updateTimerDisplay();
        updateProgressBar();

        // Clear any old interval.
        clearInterval(time_update_interval);

        // Start interval to update elapsed time display and
        // the elapsed part of the progress bar every second.
        time_update_interval = setInterval(function () {
            updateTimerDisplay();
            updateProgressBar();
        }, 1000);


        $('#volume-input').val(Math.round(player.getVolume()));
    }


    // This function is called by initialize()
    function updateTimerDisplay() {
        // Update current time text display.
        $('#current-time').text(formatTime(player.getCurrentTime()));
        $('#duration').text(formatTime(player.getDuration()));
    }


    // This function is called by initialize()
    function updateProgressBar() {
        // Update the value of our progress bar accordingly.
        $('#progress-bar').val((player.getCurrentTime() / player.getDuration()) * 100);
    }

    // Progress bar

    $('#progress-bar').on('mouseup touchend', function (e) {

        // Calculate the new time for the video.
        // new time in seconds = total duration in seconds * ( value of range input / 100 )
        var newTime = player.getDuration() * (e.target.value / 100);

        // Skip video to new time.
        player.seekTo(newTime);

    });

    //Bouton play/pause

    //permet d'animer le bouton play et d'actionner en même temps le player

    const box = document.querySelector('.box');
    box.addEventListener('click', (e) => {
            e.target.classList.toggle('pause');
            const playButtonArr = box.classList;
    
            for (let classPlayButton of playButtonArr) {
                if (classPlayButton === 'pause') {
                    player.playVideo();
                }
                else {
                    player.pauseVideo();
                }
            }
    });

    //---------------------------------------------------------------------------------------------------------//

    // Fonctions utiles pour passer à travers les vidéos.

    function passToNextMusic()
    {
        var playlistMusiques = document.querySelectorAll(".hiddenVideoID");

        if(keyMusicPlaylist < playlistMusiques.length)
        {
            ++keyMusicPlaylist;
            idHTML = '#videoid' + keyMusicPlaylist;
            lienVersYT = document.querySelector(idHTML).dataset.id;
            player.loadVideoById(lienVersYT)
            player.playVideo();
        }
    }

    function backToPrevMusic()
    {
        if(keyMusicPlaylist >= 0)
        {   
            if(keyMusicPlaylist !== 0)
            {            
                --keyMusicPlaylist;
            }
            idHTML = '#videoid' + keyMusicPlaylist;
            lienVersYT = document.querySelector(idHTML).dataset.id;
            player.loadVideoById(lienVersYT)
            player.playVideo();
        }
    }

    //---------------------------------------------------------------------------------------------------------//


    // Sound volume


    $('#volume-input').on('change', function () {
        player.setVolume($(this).val());
    });



    function onPlayerStateChange(event) {
        if (event.data == YT.PlayerState.ENDED) {
            passToNextMusic();
        }
      }

    // Bouton next

    document.querySelector('#next').addEventListener('click', () => {
        passToNextMusic();
    });

    //Bouton prev

        document.querySelector('#prev').addEventListener('click', () => {
            backToPrevMusic();
    });


    // Helper Functions

    function formatTime(time) {
        time = Math.round(time);

        var minutes = Math.floor(time / 60),
            seconds = time - minutes * 60;

        seconds = seconds < 10 ? '0' + seconds : seconds;

        return minutes + ":" + seconds;
    }


    $('pre code').each(function (i, block) {
        hljs.highlightBlock(block);
    });

    
    //--------------- FIN JS MEDIAPLAYER ------------------ //

}

// ---------------------------- Début Background Video ---------------------------- //

(function () {

  var bv = new Bideo();
  bv.init({
    videoEl: document.querySelector('#background_video'),   
    container: document.querySelector('body'),
    resize: true,
    
    onLoad: function () {
      document.querySelector('#video_cover').style.display = 'none';
    }
  });
}());
// ---------------------------- Fin Background Video ---------------------------- //