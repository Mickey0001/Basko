<footer class="w3-container w3-padding-64 w3-center w3-opacity w3-light-grey w3-xlarge">
<a class="w3-hover-opacity" href="https://www.facebook.com/BASHESKIA-EDWARD-EQ-83892532107/" target="_blank"><i class="fa fa-facebook-square fa-2x" aria-hidden="true"></i></a>
<a class="w3-hover-opacity" href="https://www.youtube.com/user/staripionir" target="_blank"><i class="fa fa-youtube-square fa-2x" aria-hidden="true"></i></a>
<a class="w3-hover-opacity" href="https://open.spotify.com/artist/75bkgEjebXg7u6CWDyNz77" target="_blank"><i class="fa fa-spotify fa-2x" aria-hidden="true"></i></a>
<a class="w3-hover-opacity" href="https://soundcloud.com/basheskia" target="_blank"><i class="fa fa-soundcloud fa-2x" aria-hidden="true"></i></a>
  <p class="w3-medium">&copy; 2018 Basheskia & Edward EQ</p>
</footer>

<script async>
// Automatic Slideshow - change image every 4 seconds
var myIndex = 0;
carousel();

function carousel() {
    var i;
    var x = document.getElementsByClassName("mySlides");
    for (i = 0; i < x.length; i++) {
       x[i].style.display = "none";
    }
    myIndex++;
    if (myIndex > x.length) {myIndex = 1}
    x[myIndex-1].style.display = "block";
    setTimeout(carousel, 4000);
}

// Used to toggle the menu on small screens when clicking on the menu button
function myFunction() {
    var x = document.getElementById("navDemo");
    if (x.className.indexOf("w3-show") == -1) {
        x.className += " w3-show";
    } else {
        x.className = x.className.replace(" w3-show", "");
    }
}

// When the user clicks anywhere outside of the modal, close it
var modal = document.getElementById('ticketModal');
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>


</body></html>
