(function() {
    var annonceContainer = document.querySelector('.annonce-defilante-container');
    var annoncesList = document.querySelector('.annonce-defilante-container ul');
  
    if (annonceContainer && annoncesList) {
      var containerWidth = annonceContainer.offsetWidth;
      var annoncesWidth = 0;
  
      var annonceTexts = annoncesList.children;
      for (var i = 0; i < annonceTexts.length; i++) {
        annoncesWidth += annonceTexts[i].offsetWidth;
      }
  
      if (annoncesWidth > containerWidth) {
        var distance = annoncesWidth + containerWidth;
        var duration = distance / 60; // 50 pixels per second
  
        var keyframes = '@keyframes defile { 0% { transform: translateX(' + containerWidth + 'px); } 100% { transform: translateX(-' + annoncesWidth + 'px); } }';
        var styleSheet = document.createElement('style');
        styleSheet.type = 'text/css';
        styleSheet.innerHTML = keyframes;
        document.head.appendChild(styleSheet);
  
        annoncesList.style.animation = 'defile ' + duration + 's linear infinite';
      }
    }
  })();
  