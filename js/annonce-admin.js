// Suppression des annonces dans Admin
(function() {
    var deleteLinks = document.getElementsByClassName('delete-annonce');

    for (var i = 0; i < deleteLinks.length; i++) {
        deleteLinks[i].addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')) {
                window.location.href = this.getAttribute('href');
            }
        });
    }
})();
