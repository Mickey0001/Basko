<footer class="w3-container w3-padding-64 w3-center w3-opacity w3-light-grey w3-xlarge">
  <i class="w3-hover-opacity"><img src="images/fb.png" alt="FaceBook"></i>
  <i class="w3-hover-opacity"><img src="images/yt.png" alt="YouTube"></i>
  <i class="w3-hover-opacity"><img src="images/sc.png" alt="SoundCloud"></i>
  <i class="w3-hover-opacity"><img src="images/sp.png" alt="Spotify"></i>
  <i class="w3-hover-opacity"><img src="images/it.png" alt="iTunes"></i>
  <p class="w3-medium">Basheskia & Edward EQ</p>
</footer>

<script>
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
