/**
 * Gestion des boutons de vote
 */
 document.querySelectorAll('.vote_btn').forEach(element => {
    element.addEventListener('click', event => {
        event.preventDefault();

        const clickedElement = event.currentTarget;
        const urlToCall = clickedElement.getAttribute('href');
        const playlistId = parseInt(clickedElement.dataset.playlistId);
        const vote = parseInt(clickedElement.dataset.vote);
        
        fetch(urlToCall)
            .then(res => res.json())
            .then(data => {
                const countElement = clickedElement.querySelector('.vote-count');
                const voteCount = parseInt(countElement.innerHTML);

                // Si l'utilisateur n'a jamais voté, on met à jour le compteur
                if (!data.hasVoted) {
                    countElement.innerHTML = voteCount + 1;
                // Sinon on ne met à jour le compteur de vote que si l'utilisateur a voté l'inverse
                } else if (data.voteHasChanged) {
                    countElement.innerHTML = voteCount + 1;

                    // Mise à jour du compteur opposé (si like : dislike et vice-versa)
                    const oppositeVoteElement = document.querySelector('[data-playlist-id="' + playlistId + '"][data-vote="' + (vote === 1 ? 0 : 1) + '"]');
                    const oppositeCountElement = oppositeVoteElement.querySelector('.vote-count');
                    const oppositeVoteCount = parseInt(oppositeCountElement.innerHTML);
                    oppositeCountElement.innerHTML = oppositeVoteCount - 1;
                }
            })
    }, false)
})